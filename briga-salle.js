/* BRIGA Plan de Salle v3.1 — salleLoad, salleInitEvents, salleRender */
;(function() {
  'use strict';
  // ══════════════════════════════════════════════════════════════════════════
  // MODULE PLAN DE SALLE v3.0
  // 3 zones + vue globale — BRIGA PRO design — Constantin BARNA
  // ══════════════════════════════════════════════════════════════════════════

  // ── État ──────────────────────────────────────────────────────────────────
  const salle = {
    tables:    [],      // données depuis API
    groupsCap: {},
    zone:      'gilbert',
    mode:      'normal',   // normal | move | merge
    moveTarget: null,
    mergeList:  [],
  };

  // ── Config statuts ────────────────────────────────────────────────────────
  const ST = {
    libre:     { label:'Libre',      color:'#22c55e', dim:'rgba(34,197,94,0.18)',   ring:'rgba(34,197,94,0.55)',   icon:'○' },
    reservee:  { label:'Réservée',   color:'#f59e0b', dim:'rgba(245,158,11,0.18)',  ring:'rgba(245,158,11,0.55)',  icon:'◐' },
    occupee:   { label:'Occupée',    color:'#ef4444', dim:'rgba(239,68,68,0.18)',   ring:'rgba(239,68,68,0.55)',   icon:'●' },
    nettoyage: { label:'À nettoyer', color:'#3b82f6', dim:'rgba(59,130,246,0.18)',  ring:'rgba(59,130,246,0.55)',  icon:'◌' },
  };

  // ── Zone labels ───────────────────────────────────────────────────────────
  const ZONE_NAME = { gilbert:'Salle Gilbert', terrasse:'Terrasse', cuisine:'Côté cuisine' };

  // ── Chargement ────────────────────────────────────────────────────────────
  async function salleLoad() {
    const plan = document.getElementById('sallePlan');
    if (plan) plan.innerHTML = '<div class="bs-loading"><div class="bs-spinner"></div><span>Chargement du plan…</span></div>';
    try {
      const data = await brigaApiGet('salle/map');
      if (!data) {
        const msg = 'API non accessible — vérifiez /wp-json/briga/v1/salle/map';
        console.error('[BRIGA Salle]', msg);
        if (plan) plan.innerHTML = `<div class="bs-error"><span>⚠️</span><p>${msg}</p><small>Ouvrir F12 → Réseau pour voir la réponse HTTP</small></div>`;
        return;
      }
      salle.tables    = data.tables    || [];
      salle.groupsCap = data.groups_cap || {};
      console.log('[BRIGA Salle] tables chargées:', salle.tables.length);
      salleRender();
      salleUpdateKpis();
      salleUpdateZoneCounts();
    } catch(e) {
      console.error('[BRIGA Salle] erreur:', e);
      if (plan) plan.innerHTML = `<div class="bs-error"><span>⚠️</span><p>${e.message||'Erreur API'}</p><small>F12 → Console pour le détail</small></div>`;
    }
  }

  // ── Rendu principal ───────────────────────────────────────────────────────
  function salleRender() {
    const plan = document.getElementById('sallePlan');
    if (!plan) return;
    plan.innerHTML = '';
    plan.className = 'bs-plan';

    if (salle.zone === 'all') {
      salleRenderGlobal(plan);
    } else {
      salleRenderZone(plan, salle.zone);
    }
  }

  // ── Vue zone unique ───────────────────────────────────────────────────────
  function salleRenderZone(container, zone) {
    const tables = salle.tables.filter(t => t.zone === zone);
    if (!tables.length) { container.innerHTML = '<div class="bs-empty">Aucune table dans cette zone.</div>'; return; }

    // Wrapper relatif : image pleine taille + hotspots par-dessus
    const wrap = document.createElement('div');
    wrap.className = 'bs-zone-wrap';
    wrap.dataset.zone = zone;

    // Image de fond à 100% (pas d'opacité réduite)
    const img = document.createElement('div');
    img.className = 'bs-zone-img';
    // L'image est chargée via CSS data-zone
    wrap.appendChild(img);

    // Grille hotspots superposée
    const overlay = document.createElement('div');
    overlay.className = 'bs-hotspot-layer';
    const grid = salleCreateGrid(tables, zone);
    overlay.appendChild(grid);
    wrap.appendChild(overlay);

    container.appendChild(wrap);
    salleSyncModeBar();
  }

  // ── Vue globale ───────────────────────────────────────────────────────────
  function salleRenderGlobal(container) {
    container.classList.add('bs-plan--global');
    ['gilbert','terrasse','cuisine'].forEach(zone => {
      const tables = salle.tables.filter(t => t.zone === zone);
      if (!tables.length) return;

      // Section avec image de fond + hotspots compacts
      const section = document.createElement('div');
      section.className = 'bs-global-section';
      section.dataset.zone = zone;

      // Label zone
      const lbl = document.createElement('div');
      lbl.className = 'bs-global-label';
      lbl.textContent = ZONE_NAME[zone];
      section.appendChild(lbl);

      // Wrapper image + hotspots
      const wrap = document.createElement('div');
      wrap.className = 'bs-zone-wrap bs-zone-wrap--compact';
      wrap.dataset.zone = zone;

      const img = document.createElement('div');
      img.className = 'bs-zone-img';
      wrap.appendChild(img);

      const overlay = document.createElement('div');
      overlay.className = 'bs-hotspot-layer';
      const grid = salleCreateGrid(tables, zone, true);
      overlay.appendChild(grid);
      wrap.appendChild(overlay);

      section.appendChild(wrap);
      container.appendChild(section);
    });
  }

  // ── Créer grille CSS ──────────────────────────────────────────────────────
  function salleCreateGrid(tables, zone, compact=false) {
    const maxCol = Math.max(...tables.map(t => t.col));
    const maxRow = Math.max(...tables.map(t => t.row));
    const size   = compact ? 58 : 84;
    const gap    = compact ? 7  : 11;

    const grid = document.createElement('div');
    grid.className = 'bs-grid' + (compact ? ' bs-grid--compact' : '');
    grid.style.cssText = `
      display:grid;
      grid-template-columns:repeat(${maxCol}, ${size}px);
      grid-template-rows:repeat(${maxRow}, ${size}px);
      gap:${gap}px;
      width:fit-content;
    `;

    // Cellules vides en mode déplacement
    if (salle.mode === 'move' && salle.moveTarget !== null) {
      for (let r=1; r<=maxRow; r++) {
        for (let c=1; c<=maxCol; c++) {
          if (!tables.find(t => t.col===c && t.row===r)) {
            const cell = document.createElement('div');
            cell.className = 'bs-cell-drop';
            cell.style.cssText = `grid-column:${c};grid-row:${r};width:${size}px;height:${size}px`;
            cell.addEventListener('click', () => salleMoveConfirm(c, r));
            grid.appendChild(cell);
          }
        }
      }
    }

    tables.forEach(t => {
      const el = salleCreateTable(t, size, compact);
      el.style.gridColumn = t.col;
      el.style.gridRow    = t.row;
      grid.appendChild(el);
    });

    return grid;
  }

  // ── Créer élément table ───────────────────────────────────────────────────
  function salleCreateTable(t, size=84, compact=false) {
    // MODE HOTSPOT : zone cliquable transparente par-dessus l'image de fond
    // L'image montre déjà les tables physiques — on superpose juste la couleur de statut
    const st  = ST[t.status] || ST.libre;
    const isRound    = t.shape === 'round';
    const isSelMerge = salle.mergeList.includes(t.number);
    const isMoving   = salle.mode==='move' && salle.moveTarget===t.number;

    const el = document.createElement('div');
    el.className = [
      'bs-hotspot',
      `bs-hotspot--${t.status}`,
      isRound    ? 'bs-hotspot--round'  : '',
      isSelMerge ? 'bs-hotspot--sel'    : '',
      isMoving   ? 'bs-hotspot--moving' : '',
      compact    ? 'bs-hotspot--sm'     : '',
    ].filter(Boolean).join(' ');

    el.style.cssText = `width:${size}px;height:${size}px;--hs-color:${st.color};--hs-dim:${st.dim};--hs-ring:${st.ring};`;
    el.dataset.n = t.number;

    // Tooltip minimaliste : numéro + statut si pas libre
    const showLabel = t.status !== 'libre';
    const label = showLabel
      ? `<div class="hs-label">${t.number}${t.persons ? ` · ${t.persons}p` : ''}</div>`
      : `<div class="hs-num">${t.number}</div>`;

    // Indicateur statut (petit point coloré en bas)
    const dot = t.status !== 'libre'
      ? `<div class="hs-dot" style="background:${st.color}"></div>` : '';

    // Badge fusion
    const merge = t.merged_with ? `<div class="hs-badge">⇌</div>` : '';

    el.innerHTML = label + dot + merge;

    el.addEventListener('click', () => {
      if (salle.mode === 'merge') { salleMergeToggle(t.number); return; }
      if (salle.mode === 'move'  && salle.moveTarget !== t.number) return;
      salleOpenMenu(t.number);
    });

    return el;
  }

  // ── Menu d'actions ────────────────────────────────────────────────────────
  function salleOpenMenu(num) {
    const t = salle.tables.find(x => x.number===num);
    if (!t) return;
    salle.selected = num;
    salleCloseMenu();

    const st   = ST[t.status] || ST.libre;
    const zone = ZONE_NAME[t.zone] || t.zone;
    const canMerge = t.groups && t.groups.length > 0;
    const isMerged = !!(t.merged_with);

    const overlay = document.createElement('div');
    overlay.id = 'bsMenu';
    overlay.className = 'bsm-overlay';
    overlay.innerHTML = `
      <div class="bsm-box">
        <div class="bsm-head">
          <div class="bsm-head__info">
            <span class="bsm-num">Table ${t.number}</span>
            <span class="bsm-badge bsm-badge--${t.status}">${st.label}</span>
            <span class="bsm-zone">${zone} · ${t.capacity} pers.</span>
          </div>
          <button class="bsm-close" id="bsmClose">✕</button>
        </div>

        ${t.client_name||t.persons||t.note ? `
        <div class="bsm-info">
          ${t.client_name ? `<div class="bsm-row"><span>Client</span><strong>${t.client_name}</strong></div>` : ''}
          ${t.persons     ? `<div class="bsm-row"><span>Couverts</span><strong>${t.persons} personnes</strong></div>` : ''}
          ${t.note        ? `<div class="bsm-row"><span>Note</span><em>${t.note}</em></div>` : ''}
          ${isMerged      ? `<div class="bsm-row"><span>Fusionnée avec</span><strong>${t.merged_with}</strong></div>` : ''}
        </div>` : ''}

        <div class="bsm-form briga__hidden" id="bsmForm">
          <div class="bsm-field">
            <label>Nom du client</label>
            <input type="text" id="bsmName" class="bsm-input" placeholder="Nom" value="${t.client_name||''}">
          </div>
          <div class="bsm-field">
            <label>Couverts</label>
            <input type="number" id="bsmPersons" class="bsm-input" min="1" max="20" placeholder="2" value="${t.persons||''}">
          </div>
          <div class="bsm-field">
            <label>Note (allergie, occasion…)</label>
            <input type="text" id="bsmNote" class="bsm-input" placeholder="Optionnel" value="${t.note||''}">
          </div>
          <div class="bsm-form-btns">
            <button class="bs-btn bs-btn--green" id="bsmFormOk">✅ Confirmer</button>
            <button class="bs-btn bs-btn--ghost" id="bsmFormCancel">Annuler</button>
          </div>
        </div>

        <div class="bsm-actions" id="bsmActions"></div>
      </div>
    `;

    document.body.appendChild(overlay);

    document.getElementById('bsmClose').addEventListener('click', salleCloseMenu);
    overlay.addEventListener('click', e => { if (e.target===overlay) salleCloseMenu(); });

    // Construire les boutons selon statut
    const acts = document.getElementById('bsmActions');
    const btn = (ico, lbl, cls, fn) => {
      const b = document.createElement('button');
      b.className = `bsm-btn bsm-btn--${cls}`;
      b.innerHTML = `<span class="bsm-btn__ico">${ico}</span><span>${lbl}</span>`;
      b.addEventListener('click', fn);
      acts.appendChild(b);
    };

    if (t.status === 'libre') {
      btn('👤','Ajouter un client',      'green',  () => salleShowForm('occupee'));
      btn('🗓','Placer une réservation', 'orange', () => salleShowForm('reservee'));
      if (canMerge && !isMerged) btn('⇌','Fusionner les tables', 'blue', () => { salleCloseMenu(); salleStartMerge(t); });
      if (isMerged)              btn('✂️','Séparer',              'ghost',() => salleSplitTable(num));
      btn('✦','Déplacer la table',      'ghost',  () => { salleCloseMenu(); salleStartMove(num); });
      btn('🍽','Ouvrir la commande',    'ghost',  () => { salleCloseMenu(); brigaToast('Commandes — module à connecter','info'); });
    } else if (t.status === 'reservee') {
      btn('✅','Client arrivé — Placer', 'green',  () => salleShowForm('occupee'));
      btn('🚫','No-show — Libérer',      'red',    () => salleSetStatus(num,'libre',{},'No-show — table libérée'));
      btn('✏️','Modifier réservation',  'ghost',  () => salleShowForm('reservee'));
      btn('🍽','Ouvrir la commande',    'ghost',  () => { salleCloseMenu(); brigaToast('Commandes — module à connecter','info'); });
    } else if (t.status === 'occupee') {
      btn('🧹','Client parti — Nettoyer','blue',  () => salleSetStatus(num,'nettoyage',{},'Table en cours de nettoyage'));
      btn('↩','Libérer directement',    'ghost',  () => salleSetStatus(num,'libre',{},'Table libérée'));
      if (isMerged) btn('✂️','Séparer les tables','ghost',() => salleSplitTable(num));
      btn('✦','Déplacer',               'ghost',  () => { salleCloseMenu(); salleStartMove(num); });
      btn('🍽','Ouvrir la commande',    'ghost',  () => { salleCloseMenu(); brigaToast('Commandes — module à connecter','info'); });
    } else if (t.status === 'nettoyage') {
      btn('✅','Nettoyage terminé — Libre','green',() => salleSetStatus(num,'libre',{},'✅ Table prête'));
      btn('👤','Placer directement un client','orange',() => salleShowForm('occupee'));
    }
  }

  function salleCloseMenu() {
    const m = document.getElementById('bsMenu');
    if (m) m.remove();
  }

  // ── Formulaire client ─────────────────────────────────────────────────────
  function salleShowForm(targetStatus) {
    const form = document.getElementById('bsmForm');
    const acts = document.getElementById('bsmActions');
    if (!form||!acts) return;
    form.classList.remove('briga__hidden');
    acts.classList.add('briga__hidden');

    document.getElementById('bsmFormOk').onclick = () => {
      const name    = (document.getElementById('bsmName').value||'').trim();
      const persons = parseInt(document.getElementById('bsmPersons').value)||1;
      const note    = (document.getElementById('bsmNote').value||'').trim();
      salleSetStatus(salle.selected, targetStatus,
        {client_name:name,persons,note},
        `${name||'Client'} · ${persons}p · table ${salle.selected}`);
    };
    document.getElementById('bsmFormCancel').onclick = () => {
      form.classList.add('briga__hidden');
      acts.classList.remove('briga__hidden');
    };
    document.getElementById('bsmName').focus();
  }

  // ── Set statut ────────────────────────────────────────────────────────────
  async function salleSetStatus(num, status, extra={}, toastMsg='') {
    try {
      await brigaApiPost(`salle/status/${num}`, {status,...extra});
      const t = salle.tables.find(x=>x.number===num);
      if (t) {
        t.status = status;
        Object.assign(t, extra);
        if (status==='libre') Object.assign(t,{client_name:'',persons:0,note:'',reservation_id:0,merged_with:'',group_code:''});
      }
      salleCloseMenu();
      salleRender();
      salleUpdateKpis();
      salleUpdateZoneCounts();
      if (toastMsg) brigaToast(toastMsg,'success');
    } catch(e) { brigaToast('Erreur : '+(e.message||'API'),'error'); }
  }

  // ── Fusion ────────────────────────────────────────────────────────────────
  function salleStartMerge(t) {
    salle.mode      = 'merge';
    salle.mergeList = [t.number];
    salleRender();
    salleSyncModeBar();
    brigaToast(`Sélectionnez les tables à fusionner (${t.groups[0]||'groupe'})`, 'info');
  }

  function salleMergeToggle(num) {
    const idx = salle.mergeList.indexOf(num);
    idx >= 0 ? salle.mergeList.splice(idx,1) : salle.mergeList.push(num);
    salleRender();
    salleSyncModeBar();
  }

  async function salleConfirmMerge() {
    if (salle.mergeList.length < 2) { brigaToast('Sélectionnez au moins 2 tables','error'); return; }
    try {
      await brigaApiPost('salle/merge',{tables:salle.mergeList});
      brigaToast(`Tables ${salle.mergeList.join(' + ')} fusionnées`,'success');
      salleCancelMode(); salleLoad();
    } catch(e) { brigaToast('Fusion impossible : '+(e.message||'groupe invalide'),'error'); salleCancelMode(); }
  }

  async function salleSplitTable(num) {
    try {
      await brigaApiPost(`salle/split/${num}`,{});
      const t = salle.tables.find(x=>x.number===num);
      if (t) { t.merged_with=''; t.group_code=''; }
      salleCloseMenu(); salleRender();
      brigaToast('Tables séparées','success');
    } catch(e) { brigaToast('Erreur séparation','error'); }
  }

  // ── Déplacement ───────────────────────────────────────────────────────────
  function salleStartMove(num) {
    salle.mode       = 'move';
    salle.moveTarget = num;
    salleRender();
    salleSyncModeBar();
    brigaToast(`Table ${num} — cliquez sur une case vide pour déplacer`,'info');
  }

  async function salleMoveConfirm(col, row) {
    const num = salle.moveTarget;
    try {
      await brigaApiPost(`salle/move/${num}`,{col,row});
      const t = salle.tables.find(x=>x.number===num);
      if (t) { t.col=col; t.row=row; }
      salleCancelMode(); salleRender();
      brigaToast(`Table ${num} déplacée`,'success');
    } catch(e) { brigaToast('Erreur déplacement','error'); salleCancelMode(); }
  }

  // ── Cancel mode ───────────────────────────────────────────────────────────
  function salleCancelMode() {
    salle.mode       = 'normal';
    salle.mergeList  = [];
    salle.moveTarget = null;
    salleSyncModeBar();
    salleRender();
  }

  // ── Barre de mode (fusion / déplacement) ─────────────────────────────────
  function salleSyncModeBar() {
    const bar = document.getElementById('salleModeBar');
    if (!bar) return;

    if (salle.mode === 'normal') {
      bar.classList.add('briga__hidden');
      bar.innerHTML = '';
      return;
    }

    bar.classList.remove('briga__hidden');

    if (salle.mode === 'merge') {
      bar.className = 'bs-mode-bar bs-mode-bar--merge';
      bar.innerHTML = `
        <span class="bs-mode-bar__ico">⇌</span>
        <span>Fusion — sélectionnées : <strong>${salle.mergeList.join(', ')||'aucune'}</strong></span>
        <button class="bs-btn bs-btn--green bs-btn--sm" id="bsMergeOk">Fusionner</button>
        <button class="bs-btn bs-btn--ghost bs-btn--sm" id="bsMergeCancel">Annuler</button>
      `;
      document.getElementById('bsMergeOk').addEventListener('click', salleConfirmMerge);
      document.getElementById('bsMergeCancel').addEventListener('click', salleCancelMode);
    } else if (salle.mode === 'move') {
      bar.className = 'bs-mode-bar bs-mode-bar--move';
      bar.innerHTML = `
        <span class="bs-mode-bar__ico">✦</span>
        <span>Déplacement — Table <strong>${salle.moveTarget}</strong> — cliquez sur une case vide</span>
        <button class="bs-btn bs-btn--ghost bs-btn--sm" id="bsMoveCancel">Annuler</button>
      `;
      document.getElementById('bsMoveCancel').addEventListener('click', salleCancelMode);
    }
  }

  // ── KPIs ──────────────────────────────────────────────────────────────────
  function salleUpdateKpis() {
    const cnt = {libre:0,reservee:0,occupee:0,nettoyage:0};
    let couverts = 0;
    salle.tables.forEach(t => {
      cnt[t.status] = (cnt[t.status]||0) + 1;
      if (t.status==='occupee') couverts += (t.persons||0);
    });
    const set = (id,v) => { const el=document.getElementById(id); if(el)el.textContent=v; };
    set('salleKpiOccupee',   `${cnt.occupee} occupée${cnt.occupee>1?'s':''}`);
    set('salleKpiReservees', `${cnt.reservee} réservée${cnt.reservee>1?'s':''}`);
    set('salleKpiLibre',     `${cnt.libre} libre${cnt.libre>1?'s':''}`);
    set('salleKpiCouverts',  `${couverts} couvert${couverts>1?'s':''}`);
  }

  function salleUpdateZoneCounts() {
    ['gilbert','terrasse','cuisine'].forEach(zone => {
      const el = document.getElementById(`zoneCount${zone.charAt(0).toUpperCase()+zone.slice(1)}`);
      if (!el) return;
      const tables = salle.tables.filter(t=>t.zone===zone);
      const occ    = tables.filter(t=>t.status==='occupee').length;
      el.textContent = `${occ}/${tables.length}`;
      el.className   = `bs-zone-count ${occ>0?'bs-zone-count--active':''}`;
    });
  }

  // ── Init événements ───────────────────────────────────────────────────────
  function salleInitEvents() {
    document.querySelectorAll('.bs-zone-tab').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.bs-zone-tab').forEach(b=>b.classList.remove('is-active'));
        btn.classList.add('is-active');
        salle.zone = btn.dataset.zone;
        salleCancelMode();
      });
    });
    const ref = document.getElementById('salleRefreshBtn');
    if (ref) ref.addEventListener('click', salleLoad);
    const rst = document.getElementById('salleResetBtn');
    if (rst) rst.addEventListener('click', async () => {
      if (!confirm('Remettre toutes les tables en Libre ?')) return;
      await brigaApiPost('salle/reset',{});
      brigaToast('Nouveau service — toutes les tables sont libres','success');
      salleLoad();
    });
  }

  // salleLoad, salleInitEvents, salleRender → window global

  // ── Exposition globale ─────────────────────────────
  window.salleLoad = salleLoad;
  window.salleRender = salleRender;
  window.salleRenderZone = salleRenderZone;
  window.salleRenderGlobal = salleRenderGlobal;
  window.salleCreateGrid = salleCreateGrid;
  window.salleCreateTable = salleCreateTable;
  window.salleOpenMenu = salleOpenMenu;
  window.salleCloseMenu = salleCloseMenu;
  window.salleShowForm = salleShowForm;
  window.salleSetStatus = salleSetStatus;
  window.salleStartMerge = salleStartMerge;
  window.salleMergeToggle = salleMergeToggle;
  window.salleConfirmMerge = salleConfirmMerge;
  window.salleSplitTable = salleSplitTable;
  window.salleStartMove = salleStartMove;
  window.salleMoveConfirm = salleMoveConfirm;
  window.salleCancelMode = salleCancelMode;
  window.salleSyncModeBar = salleSyncModeBar;
  window.salleUpdateKpis = salleUpdateKpis;
  window.salleUpdateZoneCounts = salleUpdateZoneCounts;
  window.salleInitEvents = salleInitEvents;

})();
