/* BRIGA Dashboard — loadDashboard */
;(function() {
  'use strict';
  const $ = id => document.getElementById(id);
  const today = () => new Date().toISOString().split('T')[0];

async function loadDashboard() {
  try {
    console.log('[BRIGA DEBUG] API URL:', BRIGA.rest_url + 'dashboard');
    const data = await loadBrigaDashboard();
    console.log('[BRIGA DEBUG] Dashboard data:', data);
    if (!data) {
      console.warn('[BRIGA DEBUG] Dashboard: data est null → vérifier ' + BRIGA.rest_url + 'dashboard');
      const alertsEl = $('briga-dash-alerts');
      if (alertsEl) alertsEl.innerHTML = '<li class="ok">⚠️ API non accessible — vérifier la console</li>';
      return;
    }
    const alertsEl = $('briga-dash-alerts');
    if (alertsEl) alertsEl.innerHTML = !data.alerts?.length
      ? '<li class="ok">✅ Aucune alerte</li>'
      : data.alerts.map(a => `<li>${a.level==='danger'?'🚨':'⚠️'} ${a.message}</li>`).join('');
    const teamEl = $('briga-dash-team');
    if (teamEl && data.team) teamEl.innerHTML =
      `<li>Bar : ${data.team.bar}</li><li>Salle : ${data.team.salle}</li><li>Cuisine : ${data.team.cuisine}</li>`;
  } catch(e) {
    console.warn('[BRIGA] loadDashboard:', e.message);
  }
}

// ═══════════════════════════════════════════════════════
// ── MODULE CAISSE ────────────────────────────────────────
// ═══════════════════════════════════════════════════════
let caisseDate = today();
let caisseRecord = null;

  window.loadDashboard = loadDashboard;
})();
