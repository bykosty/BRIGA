/* ══════════════════════════════════════════════════════════════════════════
   BRIGA API — helpers REST v3.2
   Toutes les fonctions retournent null en cas d'erreur (jamais throw)
   ce qui évite qu'une API cassée bloque le rendu d'un module
   ══════════════════════════════════════════════════════════════════════════ */

// ── Primitives fetch ───────────────────────────────────────────────────────

async function brigaApiGet(path) {
  try {
    const url = `${BRIGA.rest_url}${path}`;
    const res  = await fetch(url, {
      method:  'GET',
      headers: { 'X-WP-Nonce': BRIGA.rest_nonce, 'Content-Type': 'application/json' }
    });
    const text = await res.text();
    if (!res.ok) {
      console.error(`[BRIGA GET ❌] ${url} → HTTP ${res.status} ${res.statusText}`);
      console.error('[BRIGA GET ❌] Réponse:', text.slice(0, 300));
      return null;
    }
    // Extraire le JSON — cherche le dernier bloc JSON valide
    // (ignore les warnings/notices WordPress injectés avant)
    let parsed = null;
    // Essai 1 : parse direct (réponse propre)
    try { parsed = JSON.parse(text); } catch(_) {}
    // Essai 2 : trouver le dernier '{' qui ouvre un JSON valide
    if (!parsed) {
      let pos = text.lastIndexOf('{');
      while (pos >= 0 && !parsed) {
        try { parsed = JSON.parse(text.slice(pos)); } catch(_) {}
        if (!parsed) pos = text.lastIndexOf('{', pos - 1);
      }
    }
    // Essai 3 : premier '[' (réponse tableau)
    if (!parsed) {
      const arrPos = text.indexOf('[');
      if (arrPos >= 0) {
        try { parsed = JSON.parse(text.slice(arrPos)); } catch(_) {}
      }
    }
    if (!parsed) {
      console.error(`[BRIGA GET ❌] ${url} → JSON invalide`, text.slice(0, 300));
      return null;
    }
    return parsed;
  } catch(e) {
    console.warn(`[BRIGA GET] ${path} → erreur réseau:`, e.message);
    return null;
  }
}

async function brigaApiPost(path, data = {}) {
  try {
    const res  = await fetch(`${BRIGA.rest_url}${path}`, {
      method:  'POST',
      headers: { 'X-WP-Nonce': BRIGA.rest_nonce, 'Content-Type': 'application/json' },
      body:    JSON.stringify(data)
    });
    const text = await res.text();
    if (!res.ok) {
      console.warn(`[BRIGA POST] ${path} → HTTP ${res.status}`);
      throw new Error(text.substring(0, 200));   // POST garde throw pour feedback UI
    }
    const jsonStart = text.indexOf('{');
    if (jsonStart >= 0) return JSON.parse(text.slice(jsonStart));
    return JSON.parse(text);
  } catch(e) {
    throw e; // POST re-throw pour que les handlers UI puissent afficher l'erreur
  }
}

async function brigaApiDelete(path) {
  try {
    const res = await fetch(`${BRIGA.rest_url}${path}`, {
      method:  'DELETE',
      headers: { 'X-WP-Nonce': BRIGA.rest_nonce, 'Content-Type': 'application/json' }
    });
    if (!res.ok) throw new Error(`DELETE ${path} → HTTP ${res.status}`);
    return await res.json();
  } catch(e) {
    console.warn(`[BRIGA DELETE] ${path}:`, e.message);
    return null;
  }
}

// ── DASHBOARD ──────────────────────────────────────────────────────────────
async function loadBrigaDashboard() {
  return brigaApiGet('dashboard');
}

// ── STOCK ──────────────────────────────────────────────────────────────────
async function loadBrigaStock()                         { return brigaApiGet('stock'); }
async function addStock(itemId, quantity, note = '')    { return brigaApiPost('stock/move', { item_id:itemId, type:'in',  quantity, note }); }
async function removeStock(itemId, quantity, note = '') { return brigaApiPost('stock/move', { item_id:itemId, type:'out', quantity, note }); }
async function addStockItem(data)                       { return brigaApiPost('stock', data); }
async function postStockMove(itemId, type, qty, note='') { return brigaApiPost('stock/move', { item_id:itemId, type, quantity:qty, note }); }
async function loadStockMoves(itemId=null, limit=50) {
  const q = itemId ? `item_id=${itemId}&limit=${limit}` : `limit=${limit}`;
  return brigaApiGet(`stock/moves?${q}`);
}

// ── DLC ────────────────────────────────────────────────────────────────────
async function loadBrigaDlc() { return brigaApiGet('dlc'); }
async function addDlc(productName, quantity, expiryDate, zone='cuisine') {
  return brigaApiPost('dlc', { product_name:productName, quantity, expiry_date:expiryDate, zone });
}

// ── TÂCHES ─────────────────────────────────────────────────────────────────
async function loadBrigaTasks()               { return brigaApiGet('tasks'); }
async function addBrigaTask(title, priority='med') { return brigaApiPost('tasks/create', { title, priority }); }
async function toggleBrigaTask(id, status)    { return brigaApiPost(`tasks/${id}/${status==='done'?'done':'reset'}`, {}); }
async function deleteBrigaTask(id)            { return brigaApiDelete(`tasks/${id}`); }

// ── CAISSE ─────────────────────────────────────────────────────────────────
async function loadBrigaCaisse(limit=30)      { return brigaApiGet(`caisse?limit=${limit}`); }
async function loadBrigaCaisseByDate(date)    { return brigaApiGet(`caisse/${date}`); }
async function saveBrigaCaisse(payload)       { return brigaApiPost('caisse', payload); }

// ── COMMANDES ──────────────────────────────────────────────────────────────
async function loadBrigaCatalogue(type)       { return brigaApiGet(`commandes/catalogue?type=${type}`); }
async function loadBrigaCommandes()           { return brigaApiGet('commandes'); }
async function saveBrigaCommande(supplier, lines, note) { return brigaApiPost('commandes', { supplier, lines, note }); }

// ── ALERTS ─────────────────────────────────────────────────────────────────
async function loadBrigaAlerts()              { return brigaApiGet('alerts'); }

// ── CASSE & PERTES ─────────────────────────────────────────────────────────
async function loadCasseLibrary()             { return brigaApiGet('casse/library'); }
async function loadCasseHistorique(zone='')   { return brigaApiGet('casse?' + (zone ? `zone=${zone}&limit=100` : 'limit=100')); }
async function saveCasse(payload)             { return brigaApiPost('casse', payload); }
async function deleteCasse(id)                { return brigaApiDelete(`casse/${id}`); }

// ── STOCK BAR QUOTIDIEN ────────────────────────────────────────────────────
async function loadStockBarDaily(date)        { return brigaApiGet(`stockbar/daily?date=${date}`); }
async function saveStockBarOuverture(date, lines) { return brigaApiPost('stockbar/ouverture', { date, lines }); }
async function saveStockBarVentes(date, lines)    { return brigaApiPost('stockbar/ventes', { date, lines }); }
async function loadStockBarSemaine(date)          { return brigaApiGet(`stockbar/semaine?date=${date}`); }

// ── OFFERTS ────────────────────────────────────────────────────────────────
async function loadOfferts(date)              { return brigaApiGet(`offerts?date=${date}`); }
async function saveOffert(data)               { return brigaApiPost('offerts', data); }
async function deleteOffert(id)               { return brigaApiDelete(`offerts/${id}`); }
async function loadOffertsSummary(f, t)       { return brigaApiGet(`offerts/summary?from=${f}&to=${t}`); }

// ── DÉMO ───────────────────────────────────────────────────────────────────
async function loadDemoStatus()               { return brigaApiGet('demo/status'); }
async function runDemoSeed()                  { return brigaApiPost('demo/seed', {}); }
async function runDemoReset()                 { return brigaApiPost('demo/reset', {}); }

// ── TOAST notification globale ─────────────────────────────────────────────
function brigaToast(message, type = 'success') {
  const existing = document.getElementById('briga-toast');
  if (existing) existing.remove();

  const colors = {
    success: { bg:'rgba(22,163,74,.95)',   border:'#16a34a' },
    error:   { bg:'rgba(220,38,38,.95)',   border:'#dc2626' },
    info:    { bg:'rgba(37,99,235,.95)',   border:'#2563eb' },
    warning: { bg:'rgba(217,119,6,.95)',   border:'#d97706' },
  };
  const c = colors[type] || colors.info;

  const toast = document.createElement('div');
  toast.id = 'briga-toast';
  toast.textContent = message;
  toast.style.cssText = `
    position:fixed; bottom:24px; left:50%;
    transform:translateX(-50%) translateY(20px);
    background:${c.bg}; border:1px solid ${c.border};
    color:#fff; font-family:inherit; font-size:.875rem; font-weight:600;
    padding:10px 20px; border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.4);
    z-index:9999; opacity:0; pointer-events:none;
    transition:opacity .2s,transform .2s;
    max-width:90vw; text-align:center;
  `;
  document.body.appendChild(toast);
  requestAnimationFrame(() => {
    toast.style.opacity = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';
  });
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(-50%) translateY(10px)';
    setTimeout(() => toast.remove(), 250);
  }, type === 'error' ? 4000 : 2500);
}

// ── Exposition globale (garantie navigateur) ────────────────────────
window.brigaApiGet = brigaApiGet;
window.brigaApiPost = brigaApiPost;
window.brigaApiDelete = brigaApiDelete;
window.loadBrigaDashboard = loadBrigaDashboard;
window.loadBrigaStock = loadBrigaStock;
window.addStock = addStock;
window.removeStock = removeStock;
window.addStockItem = addStockItem;
window.postStockMove = postStockMove;
window.loadStockMoves = loadStockMoves;
window.loadBrigaDlc = loadBrigaDlc;
window.addDlc = addDlc;
window.loadBrigaTasks = loadBrigaTasks;
window.addBrigaTask = addBrigaTask;
window.toggleBrigaTask = toggleBrigaTask;
window.deleteBrigaTask = deleteBrigaTask;
window.loadBrigaCaisse = loadBrigaCaisse;
window.loadBrigaCaisseByDate = loadBrigaCaisseByDate;
window.saveBrigaCaisse = saveBrigaCaisse;
window.loadBrigaCatalogue = loadBrigaCatalogue;
window.loadBrigaCommandes = loadBrigaCommandes;
window.saveBrigaCommande = saveBrigaCommande;
window.loadBrigaAlerts = loadBrigaAlerts;
window.loadCasseLibrary = loadCasseLibrary;
window.loadCasseHistorique = loadCasseHistorique;
window.saveCasse = saveCasse;
window.deleteCasse = deleteCasse;
window.loadStockBarDaily = loadStockBarDaily;
window.saveStockBarOuverture = saveStockBarOuverture;
window.saveStockBarVentes = saveStockBarVentes;
window.loadStockBarSemaine = loadStockBarSemaine;
window.loadOfferts = loadOfferts;
window.saveOffert = saveOffert;
window.deleteOffert = deleteOffert;
window.loadOffertsSummary = loadOffertsSummary;
window.loadDemoStatus = loadDemoStatus;
window.runDemoSeed = runDemoSeed;
window.runDemoReset = runDemoReset;
