/* BRIGA Caisse — initCaisse, loadCaisseForDate */
;(function() {
  'use strict';
  const $ = id => document.getElementById(id);
  const today = () => new Date().toISOString().split('T')[0];
  const fmt  = v => (v != null) ? parseFloat(v).toFixed(2) + ' €' : '—';
  const fmtN = v => (v != null) ? parseFloat(v).toLocaleString('fr-FR',{minimumFractionDigits:2})+' €' : '—';

function initCaisse() {
  const dateInput = $('brigaCaisseDate');
  if (dateInput) { dateInput.value = caisseDate; }
  loadCaisseForDate(caisseDate);
}

// Navigation dates
$('brigaCaisseDate')?.addEventListener('change', e => { caisseDate = e.target.value; loadCaisseForDate(caisseDate); });
$('brigaCaissePrevDay')?.addEventListener('click', () => {
  const d = new Date(caisseDate); d.setDate(d.getDate()-1);
  caisseDate = d.toISOString().split('T')[0];
  $('brigaCaisseDate').value = caisseDate;
  loadCaisseForDate(caisseDate);
});
$('brigaCaisseNextDay')?.addEventListener('click', () => {
  const d = new Date(caisseDate); d.setDate(d.getDate()+1);
  caisseDate = d.toISOString().split('T')[0];
  $('brigaCaisseDate').value = caisseDate;
  loadCaisseForDate(caisseDate);
});
$('brigaCaisseToday')?.addEventListener('click', () => {
  caisseDate = today();
  $('brigaCaisseDate').value = caisseDate;
  loadCaisseForDate(caisseDate);
});

// Tabs caisse
document.querySelectorAll('.briga-caisse__tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.briga-caisse__tab').forEach(t => t.classList.remove('is-active'));
    tab.classList.add('is-active');
    document.querySelectorAll('.briga-caisse__tabcontent').forEach(c => c.classList.add('briga__hidden'));
    const target = $('briga-tab-' + tab.dataset.tab);
    if (target) target.classList.remove('briga__hidden');
    if (tab.dataset.tab === 'recap')      renderRecap();
    if (tab.dataset.tab === 'historique') loadCaisseHistorique();
  });
});


async function loadCaisseForDate(date) {
  const res = await loadBrigaCaisseByDate(date);
  caisseRecord = res?.record || null;
  if (caisseRecord) {
    fillCaisseForm('midi',    caisseRecord);
    fillCaisseForm('journee', caisseRecord);
    updatePreviewMidi();
    updatePreviewDay();
  } else {
    clearCaisseForm();
  }
}

// Champs midi → colonnes SQL
const FIELDS_MIDI = {
  brigaCouvertsMidi:     'covers_midi',
  brigaTTC20Midi:        'ttc20_midi',
  brigaTTC10Midi:        'ttc10_midi',
  brigaTTC55Midi:        'ttc55_midi',
  brigaUberMidi:         'uber_midi',
  brigaDeliverooMidi:    'deliveroo_midi',
  brigaRemiseMidi:       'remise_midi',
  brigaAnnulationsMidi:  'annulations_midi',
  brigaOffertsMidi:      'offerts_midi',
  brigaFondsCaisseMidi:  'fonds_caisse_midi',
};
const FIELDS_DAY = {
  brigaCouvertsDay:      'covers_day',
  brigaTTC20Day:         'ttc20_day',
  brigaTTC10Day:         'ttc10_day',
  brigaTTC55Day:         'ttc55_day',
  brigaUberDay:          'uber_day',
  brigaDeliverooDay:     'deliveroo_day',
  brigaRemiseDay:        'remise_day',
  brigaAnnulationsDay:   'annulations_day',
  brigaOffertsDay:       'offerts_day',
  brigaFondsCaisseDay:   'fonds_caisse_day',
};

// Mapping param API → id HTML
const API_TO_HTML_MIDI = {
  covers:'brigaCouvertsMidi', ttc20:'brigaTTC20Midi', ttc10:'brigaTTC10Midi',
  ttc55:'brigaTTC55Midi', uber:'brigaUberMidi', deliveroo:'brigaDeliverooMidi',
  remise:'brigaRemiseMidi', annulations:'brigaAnnulationsMidi',
  offerts:'brigaOffertsMidi', fonds_caisse:'brigaFondsCaisseMidi'
};
const API_TO_HTML_DAY = {
  covers:'brigaCouvertsDay', ttc20:'brigaTTC20Day', ttc10:'brigaTTC10Day',
  ttc55:'brigaTTC55Day', uber:'brigaUberDay', deliveroo:'brigaDeliverooDay',
  remise:'brigaRemiseDay', annulations:'brigaAnnulationsDay',
  offerts:'brigaOffertsDay', fonds_caisse:'brigaFondsCaisseDay'
};

function fillCaisseForm(service, rec) {
  const map = service === 'midi' ? API_TO_HTML_MIDI : API_TO_HTML_DAY;
  const suffix = service === 'midi' ? '_midi' : '_day';
  Object.entries(map).forEach(([param, htmlId]) => {
    const el = $(htmlId);
    const col = param + suffix;
    if (el && rec[col] !== undefined) el.value = parseFloat(rec[col]) || '';
  });
}

function clearCaisseForm() {
  [...Object.keys(API_TO_HTML_MIDI), ...Object.keys(API_TO_HTML_DAY)].forEach(param => {
    const idMidi = API_TO_HTML_MIDI[param];
    const idDay  = API_TO_HTML_DAY[param];
    if (idMidi && $(idMidi)) $(idMidi).value = '';
    if (idDay  && $(idDay))  $(idDay).value  = '';
  });
}

// Preview temps réel MIDI
function updatePreviewMidi() {
  const ttc20 = parseFloat($('brigaTTC20Midi')?.value || 0);
  const ttc10 = parseFloat($('brigaTTC10Midi')?.value || 0);
  const couverts = parseInt($('brigaCouvertsMidi')?.value || 0);
  const ca = ttc20 + ttc10;
  const ticket = couverts > 0 ? (ca / couverts).toFixed(2) : '—';
  const prevMidi = $('prev-midi');
  if (prevMidi) prevMidi.style.display = (ca > 0 || couverts > 0) ? 'flex' : 'none';
  const caEl = $('prev-midi-ca'); if (caEl) caEl.textContent = ca > 0 ? ca.toFixed(2)+' €' : '—';
  const tkEl = $('prev-midi-ticket'); if (tkEl) tkEl.textContent = ticket !== '—' ? ticket+' €' : '—';
}

// Preview temps réel JOURNÉE (calcule aussi le soir)
function updatePreviewDay() {
  const ttc20d = parseFloat($('brigaTTC20Day')?.value || 0);
  const ttc10d = parseFloat($('brigaTTC10Day')?.value || 0);
  const ttc20m = parseFloat($('brigaTTC20Midi')?.value || 0);
  const ttc10m = parseFloat($('brigaTTC10Midi')?.value || 0);
  const cDay = parseInt($('brigaCouvertsDay')?.value || 0);
  const cMidi = parseInt($('brigaCouvertsMidi')?.value || 0);

  const caDay  = ttc20d + ttc10d;
  const caMidi = ttc20m + ttc10m;
  const caSoir = caDay - caMidi;
  const cSoir  = Math.max(0, cDay - cMidi);
  const ticketDay  = cDay  > 0 ? (caDay  / cDay).toFixed(2)  : '—';
  const ticketSoir = cSoir > 0 ? (caSoir / cSoir).toFixed(2) : '—';

  const prevDay = $('prev-day');
  if (prevDay) prevDay.style.display = (caDay > 0 || cDay > 0) ? 'flex' : 'none';
  const caEl = $('prev-day-ca');       if (caEl) caEl.textContent = caDay > 0 ? caDay.toFixed(2)+' €' : '—';
  const tkEl = $('prev-day-ticket');   if (tkEl) tkEl.textContent = ticketDay !== '—' ? ticketDay+' €' : '—';
  const csEl = $('prev-day-casoir');   if (csEl) csEl.textContent = caSoir > 0 ? caSoir.toFixed(2)+' €' : '—';
  const tsEl = $('prev-day-ticketsoir'); if (tsEl) tsEl.textContent = ticketSoir !== '—' ? ticketSoir+' €' : '—';
}

// Écouter les inputs pour mise à jour preview temps réel
Object.keys(API_TO_HTML_MIDI).forEach(p => {
  $(API_TO_HTML_MIDI[p])?.addEventListener('input', updatePreviewMidi);
});
Object.keys(API_TO_HTML_DAY).forEach(p => {
  $(API_TO_HTML_DAY[p])?.addEventListener('input', updatePreviewDay);
});

// Boutons enregistrer
document.querySelectorAll('.briga-caisse__savebtn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const service = btn.dataset.save; // midi | journee
    const map = service === 'midi' ? API_TO_HTML_MIDI : API_TO_HTML_DAY;
    const msgEl = $('msg-' + service);
    if (msgEl) msgEl.textContent = '';

    const payload = { date: caisseDate, service };
    Object.entries(map).forEach(([param, htmlId]) => {
      const val = $(htmlId)?.value;
      if (val !== '' && val !== undefined) payload[param] = val;
    });

    const res = await saveBrigaCaisse(payload);
    if (res?.success) {
      caisseRecord = res.record;
      if (msgEl) { msgEl.style.color='#059669'; msgEl.textContent='✅ Enregistré'; setTimeout(()=>msgEl.textContent='',3000); }
    } else {
      if (msgEl) { msgEl.style.color='#dc2626'; msgEl.textContent='❌ Erreur'; }
    }
  });
});

// Récap du jour
function renderRecap() {
  const el = $('briga-caisse-recap-content');
  if (!el) return;
  if (!caisseRecord) {
    el.innerHTML = '<div class="briga__card"><div class="briga__muted">Aucune donnée pour ce jour.</div></div>';
    return;
  }
  const r = caisseRecord;
  const c = r.calculs || {};
  el.innerHTML = `
    <div class="briga__card">
      <h3 class="briga-caisse__subtitle">📊 Récap du ${r.report_date}</h3>
      <div class="briga-recap__sections">
        <div class="briga-recap__col">
          <div class="briga-recap__label">🌞 MIDI</div>
          <div class="briga-recap__row"><span>Couverts</span><strong>${r.covers_midi||0}</strong></div>
          <div class="briga-recap__row"><span>CA salle</span><strong>${fmtN(c.ca_salle_midi)}</strong></div>
          <div class="briga-recap__row"><span>Ticket moyen</span><strong>${fmtN(c.ticket_midi)}</strong></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>TTC 20%</span><span>${fmtN(r.ttc20_midi)}</span></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>TTC 10%</span><span>${fmtN(r.ttc10_midi)}</span></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>Uber</span><span>${fmtN(r.uber_midi)}</span></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>Deliveroo</span><span>${fmtN(r.deliveroo_midi)}</span></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>Offerts</span><span>${fmtN(r.offerts_midi)}</span></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>Remises</span><span>${fmtN(r.remise_midi)}</span></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>Annulations</span><span>${fmtN(r.annulations_midi)}</span></div>
        </div>
        <div class="briga-recap__col briga-recap__col--soir">
          <div class="briga-recap__label">🌙 SOIR (calculé)</div>
          <div class="briga-recap__row"><span>Couverts</span><strong>${c.soir?.covers||0}</strong></div>
          <div class="briga-recap__row"><span>CA salle</span><strong>${fmtN(c.ca_salle_soir)}</strong></div>
          <div class="briga-recap__row"><span>Ticket moyen</span><strong>${fmtN(c.ticket_soir)}</strong></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>TTC 20%</span><span>${fmtN(c.soir?.ttc20)}</span></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>TTC 10%</span><span>${fmtN(c.soir?.ttc10)}</span></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>Uber</span><span>${fmtN(c.soir?.uber)}</span></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>Deliveroo</span><span>${fmtN(c.soir?.deliveroo)}</span></div>
        </div>
        <div class="briga-recap__col briga-recap__col--total">
          <div class="briga-recap__label">📋 JOURNÉE</div>
          <div class="briga-recap__row"><span>Couverts total</span><strong>${r.covers_day||0}</strong></div>
          <div class="briga-recap__row briga-recap__row--big"><span>CA salle total</span><strong>${fmtN(c.ca_salle_day)}</strong></div>
          <div class="briga-recap__row briga-recap__row--big"><span>Ticket moyen</span><strong>${fmtN(c.ticket_day)}</strong></div>
          <div class="briga-recap__row briga-recap__row--sub"><span>CA total (TTC)</span><span>${fmtN(c.ca_total_day)}</span></div>
        </div>
      </div>
    </div>`;
}

// Historique
async function loadCaisseHistorique() {
  const el = $('briga-caisse-histo-list');
  if (!el) return;
  el.innerHTML = '<div class="briga__muted">Chargement…</div>';
  const data = await loadBrigaCaisse();
  if (!data?.items?.length) { el.innerHTML = '<div class="briga__muted">Aucun enregistrement.</div>'; return; }
  el.innerHTML = data.items.map(rec => {
    const c = rec.calculs || {};
    const hasData = rec.covers_midi > 0 || rec.covers_day > 0;
    return `
      <div class="briga-histo__row" data-date="${rec.report_date}">
        <div class="briga-histo__date">${rec.report_date}</div>
        ${hasData ? `
          <div class="briga-histo__stat"><span>Couverts</span><strong>${rec.covers_day||rec.covers_midi||0}</strong></div>
          <div class="briga-histo__stat"><span>CA salle</span><strong>${fmtN(c.ca_salle_day||c.ca_salle_midi)}</strong></div>
          <div class="briga-histo__stat"><span>Ticket</span><strong>${fmtN(c.ticket_day||c.ticket_midi)}</strong></div>
        ` : '<div class="briga__muted">Données partielles</div>'}
      </div>`;
  }).join('');

  // Clic sur une ligne → charger cette date
  el.querySelectorAll('.briga-histo__row').forEach(row => {
    row.addEventListener('click', () => {
      caisseDate = row.dataset.date;
      $('brigaCaisseDate').value = caisseDate;
      loadCaisseForDate(caisseDate);
      // Revenir sur l'onglet midi
      document.querySelectorAll('.briga-caisse__tab').forEach(t => {
        t.classList.toggle('is-active', t.dataset.tab === 'midi');
      });
      document.querySelectorAll('.briga-caisse__tabcontent').forEach(c => c.classList.add('briga__hidden'));
      $('briga-tab-midi')?.classList.remove('briga__hidden');
    });
  });
}

// ═══════════════════════════════════════════════════════════════════════
// MODULE TÂCHES v3.0
// Workflow: libre → prise → a_valider → validee / en_retard
// ═══════════════════════════════════════════════════════════════════════

const TASK_STATE = {
  libre:     { label:'Libre',      color:'var(--txt3)',   bg:'rgba(255,255,255,.05)', icon:'○', btn_label:'Prendre',    btn_cls:'b-btn--navy' },
  prise:     { label:'Prise',      color:'var(--blue)',   bg:'var(--blue-dim)',       icon:'◐', btn_label:'Terminée',   btn_cls:'b-btn--green' },
  a_valider: { label:'À valider',  color:'var(--orange)', bg:'var(--orange-dim)',     icon:'◑', btn_label:'Valider ✓',  btn_cls:'b-btn--primary' },
  validee:   { label:'Validée',    color:'var(--green)',  bg:'var(--green-dim)',      icon:'●', btn_label:'—',          btn_cls:'' },
  en_retard: { label:'En retard',  color:'var(--red)',    bg:'var(--red-dim)',        icon:'!', btn_label:'Prendre',    btn_cls:'b-btn--red' },
};

const DAY_COLOR = {
  blue:   { label:'Lun–Mer', color:'var(--blue)',   badge:'b-badge--blue' },
  yellow: { label:'Jeu–Ven', color:'var(--orange)', badge:'b-badge--orange' },
  red:    { label:'Sam–Dim', color:'var(--red)',     badge:'b-badge--red' },
};

const ZONE_ICONS = {
  bar:'🍷', salle:'🍽', vestiaires:'👔', cuisine:'🍳', general:'⭐',
};

const tasks = {
  data:        [],
  counts:      {},
  dayColor:    'blue',
  currentZone: 'all',
  currentRole: 'all',
  employeeName:'',
  isManager:   false,
};

// ── Charger les tâches ────────────────────────────────────────────────

  window.initCaisse = initCaisse;
  window.loadCaisseForDate = loadCaisseForDate;
})();
