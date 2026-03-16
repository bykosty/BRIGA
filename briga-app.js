/* BRIGA App V5 — bootstrap SPA */
;(function() {
  'use strict';

  const screenTitles = { 'home':'Tableau de bord','taches':'Tâches','caisse':'Caisse','stock':'Stock cuisine','dlc':'Dates limite','commandes':'Commandes','salle':'Plan de salle' };
  const state = { authed: localStorage.getItem('briga_authed') === '1' };

  // ── Navigation ──────────────────────────────────────────────────
  function showScreen(name) {
    document.querySelectorAll('.briga-screen').forEach(s => s.classList.add('briga__hidden'));
    const el = document.getElementById('briga-screen-' + name);
    if (el) el.classList.remove('briga__hidden');

    document.querySelectorAll('.briga-nav__btn').forEach(b => {
      b.classList.toggle('is-active', b.dataset.screen === name);
    });

    const titleEl = document.getElementById('briga-screen-title');
    if (titleEl) titleEl.textContent = screenTitles[name] || name;

    if (name === 'home') { loadDashboard(); }
    if (name === 'taches') { tasksInitEvents(); tasksLoad(); }
    if (name === 'caisse') { initCaisse(); }
    if (name === 'stock') { loadStock('cuisine'); }
    if (name === 'dlc') { loadDlc(); }
    if (name === 'commandes') { loadCmdForm('brake'); }
    if (name === 'salle') { salleInitEvents(); salleLoad(); }
  }

  function showApp() {
    const app   = document.getElementById('briga-app');
    const login = document.getElementById('briga-login');
    if (app)   app.classList.remove('briga__hidden');
    if (login) login.classList.add('briga__hidden');

    // Injecter la navigation
    const navEl = document.getElementById('briga-nav-items');
    if (navEl && !navEl.dataset.built) {
      navEl.innerHTML = `      <button class="briga-nav__btn" data-screen="home" aria-label="Tableau de bord"><span class="briga-nav__ico">🏠</span><span class="briga-nav__lbl">Tableau de bord</span></button>
      <button class="briga-nav__btn" data-screen="taches" aria-label="Tâches"><span class="briga-nav__ico">✅</span><span class="briga-nav__lbl">Tâches</span></button>
      <button class="briga-nav__btn" data-screen="caisse" aria-label="Caisse"><span class="briga-nav__ico">💰</span><span class="briga-nav__lbl">Caisse</span></button>
      <button class="briga-nav__btn" data-screen="stock" aria-label="Stock cuisine"><span class="briga-nav__ico">📦</span><span class="briga-nav__lbl">Stock cuisine</span></button>
      <button class="briga-nav__btn" data-screen="dlc" aria-label="Dates limite"><span class="briga-nav__ico">📅</span><span class="briga-nav__lbl">Dates limite</span></button>
      <button class="briga-nav__btn" data-screen="commandes" aria-label="Commandes"><span class="briga-nav__ico">🛒</span><span class="briga-nav__lbl">Commandes</span></button>
      <button class="briga-nav__btn" data-screen="salle" aria-label="Plan de salle"><span class="briga-nav__ico">🗺️</span><span class="briga-nav__lbl">Plan de salle</span></button>`;
      navEl.dataset.built = '1';
      navEl.querySelectorAll('.briga-nav__btn').forEach(btn => {
        btn.addEventListener('click', () => showScreen(btn.dataset.screen));
      });
    }
    showScreen('home');
  }

  function showLogin() {
    const app   = document.getElementById('briga-app');
    const login = document.getElementById('briga-login');
    if (app)   app.classList.add('briga__hidden');
    if (login) login.classList.remove('briga__hidden');
  }

  // ── Auth ─────────────────────────────────────────────────────────
  async function doLogin() {
    const pinEl = document.getElementById('brigaPinInput');
    const pin   = pinEl ? pinEl.value.trim() : '';
    if (!pin) return;
    try {
      const data = await window.brigaAjaxPost({ action:'briga_pin_login', nonce:BRIGA.nonce, pin });
      if (data.success) {
        localStorage.setItem('briga_authed','1');
        state.authed = true;
        showApp();
      } else {
        const m = document.getElementById('brigaLoginMsg');
        if (m) m.textContent = data.data?.message || 'PIN incorrect';
      }
    } catch(e) {
      console.warn('[BRIGA] login:', e.message);
    }
  }

  // ── Init ─────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof BRIGA === 'undefined') return;

    const btnLogin  = document.getElementById('brigaLoginBtn');
    const btnLogout = document.getElementById('brigaLogoutBtn');
    if (btnLogin)  btnLogin.addEventListener('click', doLogin);
    if (btnLogout) btnLogout.addEventListener('click', async function() {
      await window.brigaAjaxPost({ action:'briga_pin_logout', nonce:BRIGA.nonce });
      localStorage.removeItem('briga_authed');
      state.authed = false;
      showLogin();
    });

    // Connexion PIN sur Enter
    const pinEl = document.getElementById('brigaPinInput');
    if (pinEl) pinEl.addEventListener('keydown', e => { if (e.key==='Enter') doLogin(); });

    if (state.authed) showApp();
    else showLogin();

    // Debug console
    setTimeout(function() {
      const fns = ['loadDashboard', 'tasksLoad', 'tasksInitEvents', 'initCaisse', 'loadStock', 'loadDlc', 'loadCmdForm', 'salleLoad'];
      const miss = fns.filter(f => typeof window[f] !== 'function');
      if (!miss.length) console.log('%c[BRIGA V5] ✅ Tous les modules OK', 'color:#22c55e;font-weight:bold');
      else              console.error('[BRIGA V5] ❌ Manquants:', miss.join(', '));
    }, 800);
  });

  window.brigaShowScreen = showScreen;
})();
