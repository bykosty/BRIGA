/* BRIGA Tâches v3 — tasksLoad, tasksInitEvents */
;(function() {
  'use strict';
  const $ = id => document.getElementById(id);
  const today = () => new Date().toISOString().split('T')[0];

async function tasksLoad() {
  const el = $('brigaTasksList');
  if (el) el.innerHTML = '<div class="b-loading"><div class="b-spinner"></div><span>Chargement…</span></div>';

  const params = new URLSearchParams();
  if (tasks.currentRole !== 'all') params.set('role', tasks.currentRole);
  if (tasks.currentZone !== 'all') params.set('zone', tasks.currentZone);

  try {
    const data = await brigaApiGet(`tasks?${params}`);
    tasks.data     = data.tasks     || [];
    tasks.counts   = data.counts    || {};
    tasks.dayColor = data.day_color || 'blue';
    tasksRender();
    tasksUpdateHeader();
  } catch(e) {
    if (el) el.innerHTML = `<div class="b-empty"><div class="b-empty__ico">⚠️</div><div class="b-empty__title">Erreur : ${e.message}</div></div>`;
  }
}

// ── Rendu principal ───────────────────────────────────────────────────
function tasksRender() {
  const el = $('brigaTasksList');
  if (!el) return;

  if (!tasks.data.length) {
    el.innerHTML = '<div class="b-empty"><div class="b-empty__ico">✅</div><div class="b-empty__title">Aucune tâche</div><div>Cliquez "Initialiser" pour charger les tâches du jour.</div></div>';
    return;
  }

  // Grouper par zone
  const grouped = {};
  tasks.data.forEach(t => {
    const z = t.zone || 'general';
    if (!grouped[z]) grouped[z] = [];
    grouped[z].push(t);
  });

  el.innerHTML = '';

  Object.entries(grouped).forEach(([zone, zoneTasks]) => {
    const section = document.createElement('div');
    section.className = 'tasks-zone';

    // Header zone
    const zh = document.createElement('div');
    zh.className = 'tasks-zone__head';
    const done  = zoneTasks.filter(t=>t.status==='validee').length;
    const total = zoneTasks.length;
    const pct   = Math.round(done/total*100);
    zh.innerHTML = `
      <span class="tasks-zone__icon">${ZONE_ICONS[zone]||'📋'}</span>
      <span class="tasks-zone__name">${zone.charAt(0).toUpperCase()+zone.slice(1)}</span>
      <span class="tasks-zone__prog">${done}/${total}</span>
      <div class="tasks-zone__bar"><div class="tasks-zone__bar-fill" style="width:${pct}%"></div></div>
    `;
    section.appendChild(zh);

    // Liste des tâches
    const list = document.createElement('div');
    list.className = 'tasks-list';
    zoneTasks.forEach(t => list.appendChild(tasksCreateItem(t)));
    section.appendChild(list);
    el.appendChild(section);
  });
}

// ── Créer un item tâche ───────────────────────────────────────────────
function tasksCreateItem(t) {
  const st = TASK_STATE[t.status] || TASK_STATE.libre;
  const dc = DAY_COLOR[tasks.dayColor] || DAY_COLOR.blue;
  const isManager = tasks.isManager;
  const canTake   = t.status === 'libre' || t.status === 'en_retard';
  const canDone   = t.status === 'prise';
  const canValidate = isManager && t.status === 'a_valider';
  const canReject   = isManager && t.status === 'a_valider';

  const el = document.createElement('div');
  el.className = `task-item task-item--${t.status}`;
  el.dataset.id = t.id;

  const freqBadge = t.frequency === 'weekly'
    ? `<span class="b-badge b-badge--gold" style="font-size:9px">Hebdo</span>` : '';
  const retardBadge = t.status === 'en_retard'
    ? `<span class="b-badge b-badge--red" style="font-size:9px">En retard</span>` : '';

  el.innerHTML = `
    <div class="task-item__status" style="--st-color:${st.color}">
      <span class="task-item__status-icon">${st.icon}</span>
    </div>
    <div class="task-item__body">
      <div class="task-item__title">${t.title}</div>
      <div class="task-item__meta">
        ${freqBadge}${retardBadge}
        ${t.taken_by  ? `<span class="ti-meta">Pris par <strong>${t.taken_by}</strong></span>` : ''}
        ${t.done_by   ? `<span class="ti-meta">Fait par <strong>${t.done_by}</strong></span>` : ''}
        ${t.validated_by ? `<span class="ti-meta">Validé par <strong>${t.validated_by}</strong></span>` : ''}
      </div>
    </div>
    <div class="task-item__actions">
      ${canTake     ? `<button class="b-btn b-btn--navy b-btn--sm ti-take" data-id="${t.id}">Prendre</button>` : ''}
      ${canDone     ? `<button class="b-btn b-btn--green b-btn--sm ti-done" data-id="${t.id}">Terminée ✓</button>` : ''}
      ${canValidate ? `<button class="b-btn b-btn--primary b-btn--sm ti-validate" data-id="${t.id}" style="color:#0B0F22">Valider ✓</button>` : ''}
      ${canReject   ? `<button class="b-btn b-btn--ghost b-btn--sm ti-reject" data-id="${t.id}">Rejeter</button>` : ''}
      ${t.status==='validee' ? `<span style="color:var(--green);font-size:13px;font-weight:700">✅</span>` : ''}
    </div>
  `;

  // Events
  el.querySelector('.ti-take')?.addEventListener('click',     () => tasksAction(t.id,'take'));
  el.querySelector('.ti-done')?.addEventListener('click',     () => tasksAction(t.id,'done'));
  el.querySelector('.ti-validate')?.addEventListener('click', () => tasksAction(t.id,'validate'));
  el.querySelector('.ti-reject')?.addEventListener('click',   () => tasksReject(t.id));

  return el;
}

// ── Actions ───────────────────────────────────────────────────────────
async function tasksAction(id, action) {
  const name = tasks.employeeName || await tasksAskName();
  if (!name) return;

  const body = action === 'validate'
    ? { manager_name: name }
    : { employee_name: name, employee_role: tasks.currentRole };

  try {
    await brigaApiPost(`tasks/${id}/${action}`, body);
    brigaToast(
      action==='take'     ? `Tâche prise par ${name}` :
      action==='done'     ? `${name} a terminé la tâche` :
      `✅ Tâche validée par ${name}`, 'success'
    );
    tasksLoad();
  } catch(e) {
    brigaToast(e.message||'Erreur','error');
  }
}

async function tasksReject(id) {
  const name = tasks.employeeName || await tasksAskName();
  if (!name) return;
  const note = prompt('Raison du rejet (optionnel) :') || '';
  try {
    await brigaApiPost(`tasks/${id}/reject`, {manager_name:name, note});
    brigaToast('Tâche rejetée — remise en Libre','info');
    tasksLoad();
  } catch(e) {
    brigaToast(e.message||'Erreur','error');
  }
}

function tasksAskName() {
  return new Promise(resolve => {
    const name = prompt('Votre prénom :');
    if (name) {
      tasks.employeeName = name.trim();
      // Sauvegarder pour la session
      try { sessionStorage.setItem('briga_emp', name.trim()); } catch(e){}
    }
    resolve(name?.trim() || '');
  });
}

// ── Header KPIs ───────────────────────────────────────────────────────
function tasksUpdateHeader() {
  const dc = DAY_COLOR[tasks.dayColor] || DAY_COLOR.blue;
  const set = (id,v) => { const e=$(`tasks${id}`); if(e) e.textContent=v; };
  set('KpiLibre',     tasks.counts.libre     || 0);
  set('KpiPrises',    tasks.counts.prise      || 0);
  set('KpiValider',   tasks.counts.a_valider  || 0);
  set('KpiValidees',  tasks.counts.validee    || 0);
  set('KpiRetard',    tasks.counts.en_retard  || 0);
  set('DayLabel',     dc.label);
  const badge = $('tasksDayBadge');
  if (badge) { badge.className=`b-badge ${dc.badge}`; badge.textContent=dc.label; }
}

// ── Seed (initialiser les tâches du jour) ─────────────────────────────
async function tasksSeed() {
  try {
    const data = await brigaApiPost('tasks/seed', {});
    brigaToast(`${data.inserted} tâche(s) initialisée(s) pour aujourd'hui`,'success');
    tasksLoad();
  } catch(e) { brigaToast('Erreur initialisation','error'); }
}

// ── Filtres zone ──────────────────────────────────────────────────────
function tasksInitEvents() {
  // Seed btn
  $('tasksSeedBtn')?.addEventListener('click', tasksSeed);

  // Filtre zone
  document.querySelectorAll('.tasks-zone-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tasks-zone-btn').forEach(b=>b.classList.remove('is-active'));
      btn.classList.add('is-active');
      tasks.currentZone = btn.dataset.zone;
      tasksLoad();
    });
  });

  // Filtre rôle
  document.querySelectorAll('.tasks-role-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tasks-role-btn').forEach(b=>b.classList.remove('is-active'));
      btn.classList.add('is-active');
      tasks.currentRole = btn.dataset.role;
      tasks.isManager   = (btn.dataset.role === 'manager');
      tasksLoad();
    });
  });

  // Ajouter tâche manuelle
  $('tasksAddBtn')?.addEventListener('click', tasksShowAddForm);

  // Refresh
  $('tasksRefreshBtn')?.addEventListener('click', tasksLoad);

  // Restaurer nom employé de la session
  try {
    const saved = sessionStorage.getItem('briga_emp');
    if (saved) tasks.employeeName = saved;
  } catch(e) {}
}

// ── Formulaire ajout manuel ───────────────────────────────────────────
function tasksShowAddForm() {
  const existing = document.getElementById('tasksAddModal');
  if (existing) { existing.remove(); return; }

  const overlay = document.createElement('div');
  overlay.id = 'tasksAddModal';
  overlay.className = 'b-overlay';
  overlay.innerHTML = `
    <div class="b-modal">
      <div class="b-modal__head">
        <span class="b-modal__title">Nouvelle tâche</span>
        <button class="b-modal__close" id="tasksModalClose">✕</button>
      </div>
      <div class="b-modal__body">
        <div class="b-field">
          <label class="b-label">Titre</label>
          <input type="text" id="tasksNewTitle" class="b-input" placeholder="Ex: Nettoyer le frigo">
        </div>
        <div class="b-form-grid">
          <div class="b-field">
            <label class="b-label">Zone</label>
            <select id="tasksNewZone" class="b-select">
              <option value="bar">Bar</option>
              <option value="salle">Salle</option>
              <option value="vestiaires">Vestiaires</option>
              <option value="cuisine">Cuisine</option>
              <option value="general">Général</option>
            </select>
          </div>
          <div class="b-field">
            <label class="b-label">Fréquence</label>
            <select id="tasksNewFreq" class="b-select">
              <option value="daily">Quotidien</option>
              <option value="weekly">Hebdomadaire</option>
              <option value="once">Unique</option>
            </select>
          </div>
        </div>
        <div class="b-field">
          <label class="b-label">Rôle concerné</label>
          <select id="tasksNewRole" class="b-select">
            <option value="all">Tous</option>
            <option value="salle">Salle / Bar</option>
            <option value="cuisine">Cuisine</option>
            <option value="manager">Manager</option>
          </select>
        </div>
        <div class="b-field">
          <label class="b-label">Note</label>
          <input type="text" id="tasksNewNote" class="b-input" placeholder="Optionnel">
        </div>
      </div>
      <div class="b-modal__footer">
        <button class="b-btn b-btn--ghost" id="tasksModalCancel">Annuler</button>
        <button class="b-btn b-btn--primary" id="tasksModalSave" style="color:#0B0F22">Créer ✓</button>
      </div>
    </div>`;

  document.body.appendChild(overlay);
  document.getElementById('tasksModalClose').addEventListener('click', () => overlay.remove());
  document.getElementById('tasksModalCancel').addEventListener('click', () => overlay.remove());
  overlay.addEventListener('click', e => { if(e.target===overlay) overlay.remove(); });
  document.getElementById('tasksNewTitle').focus();

  document.getElementById('tasksModalSave').addEventListener('click', async () => {
    const title = document.getElementById('tasksNewTitle').value.trim();
    if (!title) { brigaToast('Titre requis','error'); return; }
    await brigaApiPost('tasks/create', {
      title,
      zone:          document.getElementById('tasksNewZone').value,
      frequency:     document.getElementById('tasksNewFreq').value,
      employee_role: document.getElementById('tasksNewRole').value,
      note:          document.getElementById('tasksNewNote').value,
    });
    brigaToast('Tâche créée','success');
    overlay.remove();
    tasksLoad();
  });
}


// ═══════════════════════════════════════════════════════
// ── MODULE STOCK ─────────────────────────────────────────
// ═══════════════════════════════════════════════════════

const CAT_LABELS = {
  vin_rouge:'🍷 Vin Rouge', vin_rose:'🌸 Vin Rosé', vin_blanc:'🥂 Vin Blanc',
  champagne:'🍾 Champagne', alcool:'🥃 Alcools', vin_mois:'📅 Vin du Mois',
  soft:'🥤 Softs & Apéritifs', cuisine:'🍳 Cuisine',
};
const MOVE_LABELS = { in:'Entrée', out:'Sortie', offered:'Offert', broken:'Casse', adjustment:'Inventaire' };
const MOVE_COLORS = { in:'#059669', out:'#dc2626', offered:'#7c3aed', broken:'#f97316', adjustment:'#2563eb' };
const ALERT_ICONS = { ok:'', warning:'⚠️', danger:'🔴', critical:'🚨' };

// Tabs stock section Bar / Cuisine / Ajouter / Historique
document.querySelectorAll('.briga-stock__tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.briga-stock__tab').forEach(t => t.classList.remove('is-active'));
    tab.classList.add('is-active');
    document.querySelectorAll('.briga-stock__tabcontent').forEach(ct => ct.classList.add('briga__hidden'));
    const target = $('briga-stab-' + tab.dataset.stab);
    if (target) target.classList.remove('briga__hidden');
    if (tab.dataset.stab === 'bar')        loadStock('bar');
    if (tab.dataset.stab === 'cuisine')    loadStock('cuisine');
    if (tab.dataset.stab === 'historique') loadStockMovesUI();
  });
});


  window.tasksLoad = tasksLoad;
  window.tasksInitEvents = tasksInitEvents;
  window.tasksRender = tasksRender;
  window.tasksSeed = tasksSeed;
  window.tasksShowAddForm = tasksShowAddForm;
})();
