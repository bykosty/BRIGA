/* BRIGA Commandes & DLC — loadDlc, loadCmdForm */
;(function() {
  'use strict';
  const $ = id => document.getElementById(id);
  const today = () => new Date().toISOString().split('T')[0];

async function loadDlc() {
  const el = $('briga-dlc-list');
  if (!el) return;
  el.innerHTML = '<div class="briga__muted">Chargement…</div>';
  const data = await loadBrigaDlc();
  if (!data?.items) { el.innerHTML = '<div class="briga__muted">Erreur.</div>'; return; }
  const todayD = new Date(); todayD.setHours(0,0,0,0);
  el.innerHTML = !data.items.length ? '<div class="briga__muted">Aucune DLC.</div>' :
    data.items.map(item => {
      const exp = new Date(item.expiry_date); exp.setHours(0,0,0,0);
      const days = Math.round((exp - todayD) / 86400000);
      const cls = days < 0 ? 'danger' : days <= 1 ? 'danger' : days <= 2 ? 'warning' : 'ok';
      const lbl = days < 0 ? `Expiré ${Math.abs(days)}j` : days === 0 ? 'Auj.' : `J-${days}`;
      return `<div class="briga-dlc__row briga-dlc__row--${cls}">
        <div class="briga-dlc__name">${item.product_name}</div>
        <div class="briga-dlc__date">${item.expiry_date}</div>
        <div class="briga-dlc__badge briga-dlc__badge--${cls}">${lbl}</div>
        <div class="briga__muted" style="font-size:12px">${item.zone}</div>
      </div>`;
    }).join('');
}

$('briga-dlc-form')?.addEventListener('submit', async e => {
  e.preventDefault();
  const name = $('brigaDlcName')?.value.trim();
  const qty  = $('brigaDlcQty')?.value.trim();
  const date = $('brigaDlcDate')?.value;
  const zone = $('brigaDlcZone')?.value;
  if (!name || !date) return;
  await addDlc(name, qty, date, zone);
  e.target.reset();
  await loadDlc();
});

// ── COMMANDES ───────────────────────────────────────────
let currentSupplier = 'brake';
let currentCatalogue = [];

document.querySelectorAll('.briga-cmd__tab').forEach(tab => {
  tab.addEventListener('click', async () => {
    document.querySelectorAll('.briga-cmd__tab').forEach(t => t.classList.remove('is-active'));
    tab.classList.add('is-active');
    const f = tab.dataset.fournisseur;
    if (f === 'historique') {
      $('briga-cmd-form-wrap').style.display = 'none';
      $('briga-cmd-historique').style.display = 'block';
      loadCmdHistorique();
    } else {
      $('briga-cmd-form-wrap').style.display = 'block';
      $('briga-cmd-historique').style.display = 'none';
      currentSupplier = f;
      $('briga-cmd-title').textContent = `Commande ${f === 'brake' ? 'BRAKE / Sysco' : 'Brasseur'}`;
      $('briga-cmd-deadline-brake').style.display    = f === 'brake'    ? 'block' : 'none';
      $('briga-cmd-deadline-brasseur').style.display = f === 'brasseur' ? 'block' : 'none';
      await loadCmdForm(f);
    }
  });
});

async function loadCmdForm(supplier) {
  const el = $('briga-cmd-list');
  if (!el) return;
  el.innerHTML = '<div class="briga__muted">Chargement…</div>';
  const data = await loadBrigaCatalogue(supplier);
  currentCatalogue = data?.items || [];
  if (!currentCatalogue.length) { el.innerHTML = '<div class="briga__muted">Catalogue vide.</div>'; return; }
  el.innerHTML = `
    <div class="briga-cmd__header-row"><span>Produit</span><span>Unité</span><span>Min</span><span>Réf.</span><span>Qté</span></div>
    ${currentCatalogue.map(item => `
      <div class="briga-cmd__row" data-id="${item.id}">
        <div class="briga-cmd__name">${item.name}</div>
        <div class="briga-cmd__unit briga__muted">${item.unit}</div>
        <div class="briga-cmd__min briga__muted">${item.stock_min||'-'}</div>
        <div class="briga-cmd__ref briga__muted" style="font-size:11px">${item.ref||''}</div>
        <div class="briga-cmd__qty-wrap">
          <button class="briga-cmd__minus">−</button>
          <input type="number" class="briga-cmd__qty briga__input" value="0" min="0" step="1" />
          <button class="briga-cmd__plus">+</button>
        </div>
      </div>`).join('')}`;
  el.querySelectorAll('.briga-cmd__row').forEach(row => {
    const input = row.querySelector('.briga-cmd__qty');
    row.querySelector('.briga-cmd__plus')?.addEventListener('click',  () => input.value = parseFloat(input.value||0)+1);
    row.querySelector('.briga-cmd__minus')?.addEventListener('click', () => input.value = Math.max(0, parseFloat(input.value||0)-1));
  });
}

$('briga-cmd-select-all')?.addEventListener('click', () => {
  document.querySelectorAll('.briga-cmd__row').forEach(row => {
    const id = row.dataset.id;
    const item = currentCatalogue.find(i => i.id === id);
    const input = row.querySelector('.briga-cmd__qty');
    if (item && input && parseFloat(input.value||0) === 0) input.value = item.debit || item.stock_min || 1;
  });
});

$('briga-cmd-save')?.addEventListener('click', async () => {
  const msg = $('briga-cmd-msg');
  if (msg) msg.textContent = '';
  const lines = [];
  document.querySelectorAll('.briga-cmd__row').forEach(row => {
    const qty = parseFloat(row.querySelector('.briga-cmd__qty')?.value || 0);
    if (qty <= 0) return;
    const item = currentCatalogue.find(i => i.id === row.dataset.id);
    if (item) lines.push({ id:item.id, name:item.name, unit:item.unit, ref:item.ref||'', qty });
  });
  if (!lines.length) { if(msg){msg.style.color='#dc2626';msg.textContent='Aucun produit sélectionné.';} return; }
  const note = $('briga-cmd-note')?.value || '';
  const res = await saveBrigaCommande(currentSupplier, lines, note);
  if (res?.success) {
    if(msg){msg.style.color='#059669';msg.textContent=`✅ Commande enregistrée (${lines.length} produits)`;}
    document.querySelectorAll('.briga-cmd__qty').forEach(i => i.value='0');
  } else { if(msg){msg.style.color='#dc2626';msg.textContent='❌ Erreur';} }
});

async function loadCmdHistorique() {
  const el = $('briga-cmd-histo-list');
  if (!el) return;
  el.innerHTML = '<div class="briga__muted">Chargement…</div>';
  const data = await loadBrigaCommandes();
  if (!data?.items?.length) { el.innerHTML = '<div class="briga__muted">Aucune commande.</div>'; return; }
  el.innerHTML = data.items.map(o => `
    <div class="briga-cmd__histo-row">
      <div><strong>${o.supplier==='brake'?'🚛 BRAKE':'🍺 Brasseur'}</strong>
      <span class="briga__muted" style="margin-left:8px">${(o.created_at||'').split(' ')[0]}</span></div>
      <div class="briga-cmd__histo-lines">${(o.lines||[]).map(l=>`${l.product_name}×${l.quantity}`).join(' · ')}</div>
    </div>`).join('');
}

// ═══════════════════════════════════════════════════════
// ── MODULE CASSE & PERTE ──────────────────────────────
// ═══════════════════════════════════════════════════════
let casseSelected = null; // item sélectionné dans la bibliothèque
let casseFilter   = 'all';

const TYPE_LABELS = {
  casse_materiel: '🔴 Casse matériel',
  casse_produit:  '🟠 Casse produit',
  perte_produit:  '🟡 Perte produit',
  incident:       '🔵 Incident',
};
const ZONE_LABELS = { bar:'Bar', cuisine:'Cuisine', salle:'Salle' };

// Tabs Casse
document.querySelectorAll('.briga-casse__tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.briga-casse__tab').forEach(t => t.classList.remove('is-active'));
    tab.classList.add('is-active');
    document.querySelectorAll('.briga-casse__tabcontent').forEach(c => c.classList.add('briga__hidden'));
    const target = $('briga-ctab-' + tab.dataset.ctab);
    if (target) target.classList.remove('briga__hidden');
    if (tab.dataset.ctab === 'historique') loadCasseHisto();
    if (tab.dataset.ctab === 'saisie') renderCasseLibrary();
  });
});

// Filtres zone
document.querySelectorAll('.briga-casse__zfilter').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.briga-casse__zfilter').forEach(b => b.classList.remove('is-active'));
    btn.classList.add('is-active');
    casseFilter = btn.dataset.filter;
    renderCasseLibrary();
  });
});

let casseLibCache = null;


  window.loadDlc = loadDlc;
  window.loadCmdForm = loadCmdForm;
  window.loadCmdHistorique = loadCmdHistorique;
})();
