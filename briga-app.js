document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('#briga-root');
  if (!root || typeof BRIGA === 'undefined') return;

  const loginScreen = document.querySelector('#briga-screen-login');
  const appLayout   = document.querySelector('#briga-layout, #briga-app-layout');

  const screens = {
    home:         document.querySelector('#briga-screen-home'),
    taches:       document.querySelector('#briga-screen-taches'),
    stock:        document.querySelector('#briga-screen-stock'),
    hygiene:      document.querySelector('#briga-screen-hygiene'),
    dlc:          document.querySelector('#briga-screen-dlc'),
    commandes:    document.querySelector('#briga-screen-commandes'),
    reservations: document.querySelector('#briga-screen-reservations'),
    journal:      document.querySelector('#briga-screen-journal'),
  };

  const logoutBtn  = document.querySelector('#brigaLogoutBtn');
  const headerTitle = document.querySelector('#briga-header-title');

  const screenLabels = {
    home: 'Dashboard', taches: 'Tâches', stock: 'Stock',
    hygiene: 'Hygiène', dlc: 'DLC', commandes: 'Commandes',
    reservations: 'Réservations', journal: 'Journal',
  };

  function showScreen(name) {
    // cacher tous les écrans
    Object.values(screens).forEach(s => { if (s) s.classList.add('briga__hidden'); });
    if (screens[name]) screens[name].classList.remove('briga__hidden');

    // mettre à jour le titre header
    if (headerTitle) headerTitle.textContent = screenLabels[name] || '';

    // mettre à jour la sidebar
    document.querySelectorAll('.briga-sidebar__item').forEach(btn => {
      btn.classList.toggle('is-active', btn.dataset.go === name);
    });
  }

  function isAuthed() { return localStorage.getItem('briga_authed') === '1'; }

  function setAuthed(v) {
    localStorage.setItem('briga_authed', v ? '1' : '0');
  }

  function showApp() {
    if (loginScreen) loginScreen.classList.add('briga__hidden');
    if (appLayout)   appLayout.classList.remove('briga__hidden');
    showScreen('home');
  }

  function showLogin() {
    if (appLayout)   appLayout.classList.add('briga__hidden');
    if (loginScreen) loginScreen.classList.remove('briga__hidden');
    const pinInput = document.querySelector('#brigaPinInput');
    if (pinInput) pinInput.value = '';
  }

  async function ajaxPost(params) {
    const res = await fetch(BRIGA.ajaxurl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: new URLSearchParams(params)
    });
    return res.json();
  }

  // --- LOGIN ---
  const pinInput = document.querySelector('#brigaPinInput');
  const pinBtn   = document.querySelector('#brigaPinBtn');
  const loginMsg = document.querySelector('#brigaLoginMsg');

  async function doLogin() {
    if (loginMsg) loginMsg.textContent = '';
    const pin = (pinInput?.value || '').trim();
    const data = await ajaxPost({ action: 'briga_pin_login', nonce: BRIGA.nonce, pin });
    if (data.success) {
      setAuthed(true);
      showApp();
      await loadTasks();
    } else {
      setAuthed(false);
      if (loginMsg) loginMsg.textContent = data.data?.message || 'PIN incorrect';
    }
  }

  pinBtn?.addEventListener('click', doLogin);
  pinInput?.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });

  // --- LOGOUT ---
  logoutBtn?.addEventListener('click', async () => {
    await ajaxPost({ action: 'briga_pin_logout', nonce: BRIGA.nonce });
    setAuthed(false);
    showLogin();
  });

  // --- NAVIGATION SIDEBAR ---
  document.querySelectorAll('[data-go]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const screen = btn.dataset.go;
      showScreen(screen);
      if (screen === 'taches') await loadTasks();
    });
  });

  // --- TACHES ---
  const tasksListEl    = document.querySelector('#brigaTasksList');
  const taskTitleEl    = document.querySelector('#brigaTaskTitle');
  const taskPriorityEl = document.querySelector('#brigaTaskPriority');
  const taskAddBtn     = document.querySelector('#brigaTaskAddBtn');

  function priorityLabel(p) {
    return p === 'low' ? 'LOW' : p === 'high' ? 'HIGH' : 'MED';
  }

  function renderTasks(tasks) {
    if (!tasksListEl) return;
    tasksListEl.innerHTML = '';
    if (!tasks.length) {
      tasksListEl.innerHTML = '<div class="briga__muted">Aucune tâche.</div>';
      return;
    }
    tasks.forEach(t => {
      const row = document.createElement('div');
      row.className = 'briga__task';
      row.innerHTML = `
        <label class="briga__taskLeft">
          <input type="checkbox" ${t.status === 'done' ? 'checked' : ''} />
          <span class="${t.status === 'done' ? 'briga__taskDone' : ''}">${t.title}</span>
          <span class="briga__pill briga__pill--${t.priority}">${priorityLabel(t.priority)}</span>
        </label>
        <button class="briga__xbtn">X</button>
      `;
      row.querySelector('input')?.addEventListener('change', async e => {
        await ajaxPost({ action: 'briga_tasks_toggle', nonce: BRIGA.nonce, id: t.id, status: e.target.checked ? 'done' : 'todo' });
        await loadTasks();
      });
      row.querySelector('.briga__xbtn')?.addEventListener('click', async () => {
        await ajaxPost({ action: 'briga_tasks_delete', nonce: BRIGA.nonce, id: t.id });
        await loadTasks();
      });
      tasksListEl.appendChild(row);
    });
  }

  async function loadTasks() {
    const data = await ajaxPost({ action: 'briga_tasks_list', nonce: BRIGA.nonce });
    renderTasks(data.success && Array.isArray(data.data) ? data.data : []);
  }

  taskAddBtn?.addEventListener('click', async () => {
    const title    = (taskTitleEl?.value || '').trim();
    const priority = taskPriorityEl?.value || 'med';
    if (!title) return;
    const data = await ajaxPost({ action: 'briga_tasks_add', nonce: BRIGA.nonce, title, priority });
    if (data.success) {
      if (taskTitleEl) taskTitleEl.value = '';
      await loadTasks();
    }
  });

  // --- BOOT ---
  if (isAuthed()) {
    showApp();
    loadTasks();
  } else {
    showLogin();
  }
});
