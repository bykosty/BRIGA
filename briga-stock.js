/* BRIGA Stock cuisine & bar — loadStock */
;(function() {
  'use strict';
  const $ = id => document.getElementById(id);
  const today = () => new Date().toISOString().split('T')[0];

async function loadStock(zone) {
  const elId = zone === 'bar' ? 'briga-stock-bar' : 'briga-stock-cuisine';
  const el = $(elId);
  if (!el) return;
  el.innerHTML = '<div class="b-loading"><div class="b-spinner"></div><span>Chargement…</span></div>';
  try {
  const data = await loadBrigaStock();
  if (!data?.items?.length) {
    el.innerHTML = '<div class="b-empty"><div class="b-empty__ico">📦</div><div class="b-empty__title">Aucun produit</div></div>'; return;
  }

  // Filtrer par zone
  const items = data.items.filter(i => i.zone === zone);

  // Grouper par catégorie dans l'ordre de la feuille
  const groups = {};
  items.forEach(item => {
    const cat = item.category || 'autre';
    if (!groups[cat]) groups[cat] = [];
    groups[cat].push(item);
  });

  const isBar = zone === 'bar';

  el.innerHTML = Object.entries(groups).map(([cat, catItems]) => `
    <div class="briga-stock__group">
      <div class="briga-stock__group-title">${CAT_LABELS[cat] || cat}</div>
      <div class="briga-stock__group-header">
        <span>Produit</span><span>Unité</span><span>Réel</span><span>Théo</span><span>Min</span><span>Écart</span><span>Actions</span>
      </div>
      ${catItems.map(item => {
        const alert = item.alert || 'ok';
        const theo  = item.stock_theoretical ?? '—';
        const var_  = item.variance;
        const varCls = var_ < 0 ? 'neg' : var_ > 0 ? 'pos' : '';
        const varTxt = var_ !== null && var_ !== undefined ? (var_ > 0 ? '+' : '') + var_ : '—';
        return `
        <div class="briga-stock__row briga-stock__row--${alert}" data-id="${item.id}" data-zone="${zone}">
          <div class="briga-stock__name">${ALERT_ICONS[alert]} ${item.name}</div>
          <div class="briga-stock__unit briga__muted">${item.unit}</div>
          <div class="briga-stock__real"><strong>${item.stock_current}</strong></div>
          <div class="briga-stock__theo" style="color:#7c3aed">${theo}</div>
          <div class="briga-stock__min briga__muted">${item.stock_min}</div>
          <div class="briga-stock__var ${varCls}">${varTxt}</div>
          <div class="briga-stock__actions">
            <button class="briga-stock__btn briga-stock__btn--in"  data-id="${item.id}" title="Entrée">📥</button>
            ${isBar ? `
            <button class="briga-stock__btn briga-stock__btn--sold"    data-id="${item.id}" title="Vendu">💰</button>
            <button class="briga-stock__btn briga-stock__btn--offered" data-id="${item.id}" title="Offert">🎁</button>
            <button class="briga-stock__btn briga-stock__btn--broken"  data-id="${item.id}" title="Casse">💔</button>
            ` : `
            <button class="briga-stock__btn briga-stock__btn--out" data-id="${item.id}" title="Sortie">📤</button>
            `}
            <button class="briga-stock__btn briga-stock__btn--adj" data-id="${item.id}" title="Inventaire réel">📋</button>
          </div>
        </div>`;
      }).join('')}
    </div>`).join('');

  // Attacher les événements boutons
  const btnConfig = isBar ? {
    'briga-stock__btn--in':      { type:'in',         label:'Quantité reçue (livraison)' },
    'briga-stock__btn--sold':    { type:'out',        label:'Quantité vendue' },
    'briga-stock__btn--offered': { type:'offered',    label:'Quantité offerte' },
    'briga-stock__btn--broken':  { type:'broken',     label:'Quantité cassée / perdue' },
    'briga-stock__btn--adj':     { type:'adjustment', label:'Stock réel (inventaire)' },
  } : {
    'briga-stock__btn--in':  { type:'in',         label:'Quantité entrante' },
    'briga-stock__btn--out': { type:'out',        label:'Quantité sortie / consommée' },
    'briga-stock__btn--adj': { type:'adjustment', label:'Stock réel (inventaire)' },
  };

  Object.entries(btnConfig).forEach(([cls, cfg]) => {
    el.querySelectorAll('.' + cls).forEach(btn => {
      btn.addEventListener('click', async () => {
        const raw = prompt(cfg.label + ' ?');
        if (raw === null) return;
        const qty = parseFloat(raw) || 0;
        if (qty <= 0) return;
        await postStockMove(parseInt(btn.dataset.id), cfg.type, qty);
        await loadStock(zone);
      });
    });
  });
  } catch(e) {
    console.warn('[BRIGA] loadStock:', e.message);
    el.innerHTML = '<div class="b-empty"><div class="b-empty__ico">⚠️</div><div class="b-empty__title">Erreur chargement stock</div></div>';
  }
}

// Ajouter un produit
$('brigaStockAddBtn')?.addEventListener('click', async () => {
  const name = $('brigaStockNewName')?.value.trim();
  const msg  = $('brigaStockAddMsg');
  if (!name) { if(msg){msg.style.color='#dc2626';msg.textContent='Nom obligatoire';} return; }
  const res = await addStockItem({
    name,
    category:      $('brigaStockNewCat')?.value,
    unit:          $('brigaStockNewUnit')?.value,
    zone:          $('brigaStockNewZone')?.value,
    stock_min:     $('brigaStockNewMin')?.value || 0,
    stock_current: $('brigaStockNewCurrent')?.value || 0,
  });
  if (res?.success) {
    if(msg){msg.style.color='#059669';msg.textContent='✅ Produit ajouté';}
    $('brigaStockNewName').value = $('brigaStockNewMin').value = $('brigaStockNewCurrent').value = '';
    setTimeout(() => {
      const zone = $('brigaStockNewZone')?.value || 'bar';
      document.querySelector('.briga-stock__tab[data-stab="' + zone + '"]')?.click();
    }, 800);
  } else {
    if(msg){msg.style.color='#dc2626';msg.textContent='❌ Erreur';}
  }
});

// Historique mouvements
async function loadStockMovesUI() {
  const el = $('briga-stock-moves-list');
  if (!el) return;
  el.innerHTML = '<div class="briga__muted">Chargement…</div>';
  const data = await loadStockMoves(null, 50);
  if (!data?.items?.length) { el.innerHTML = '<div class="briga__muted">Aucun mouvement.</div>'; return; }
  el.innerHTML = data.items.map(m => `
    <div class="briga-move__row">
      <div class="briga-move__type" style="color:${MOVE_COLORS[m.move_type]||'#64748b'}">${MOVE_LABELS[m.move_type]||m.move_type}</div>
      <div class="briga-move__name">${m.item_name}</div>
      <div class="briga-move__qty">${m.quantity} ${m.unit}</div>
      <div class="briga-move__date briga__muted">${(m.created_at||'').slice(0,16)}</div>
    </div>`).join('');
}


// ── DLC ─────────────────────────────────────────────────

  window.loadStock = loadStock;
  window.loadStockMovesUI = loadStockMovesUI;
})();
