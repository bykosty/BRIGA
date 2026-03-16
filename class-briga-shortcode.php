<?php
if (!defined('ABSPATH')) exit;

class BRIGA_Shortcode {

    public static function init() {
        add_shortcode('briga_app', [__CLASS__, 'render']);
    }

    public static function render() {
        ob_start();
        // ── Triple filet de sécurité : créer les tables même si plugins_loaded a planté ──
        if (class_exists('BRIGA_DB')) {
            ob_start();
            BRIGA_DB::install();               // crée toutes les tables si absentes (idempotent)
            BRIGA_DB::run_column_migrations(); // ajoute colonnes v3 manquantes
            BRIGA_DB::create_task_logs_table();
            ob_end_clean();
        } ?>
        <div id="briga-root" class="briga briga-root">

            <!-- LOGIN -->
            <section id="briga-screen-login" class="briga__screen briga__screen--login">
                <div class="briga-login__box">
                    <div class="briga-login__logo">BRIGA PRO</div>
                    <p class="briga-login__muted">Entre ton PIN pour accéder.</p>
                    <input id="brigaPinInput" class="b-input" type="password" inputmode="numeric" placeholder="PIN" />
                    <button id="brigaPinBtn" class="b-btn b-btn--navy">Connexion</button>
                    <div id="brigaLoginMsg" class="briga__msg"></div>
                </div>
            </section>

            <!-- APP LAYOUT -->
            <div id="briga-app-layout" class="briga-layout briga__hidden">

                <!-- SIDEBAR -->
                <aside class="briga-sidebar">
                    <div class="briga-sidebar__logo">
                        <div class="briga-sidebar__logo-icon">B</div>
                        <div>
                          <div class="briga-sidebar__logo-full">BRIGA PRO</div>
                          <div class="briga-sidebar__logo-sub">Bistro Régent</div>
                        </div>
                    </div>
                    <nav class="briga-sidebar__nav">
                        <button class="briga-sidebar__item is-active" data-go="home">
                            <span class="briga-sidebar__icon">📊</span>
                            <span class="briga-sidebar__label">Dashboard</span>
                        </button>
                        <button class="briga-sidebar__item" data-go="caisse">
                            <span class="briga-sidebar__icon">💰</span>
                            <span class="briga-sidebar__label">Caisse</span>
                        </button>
                        <button class="briga-sidebar__item" data-go="taches">
                            <span class="briga-sidebar__icon">✅</span>
                            <span class="briga-sidebar__label">Tâches</span>
                        </button>
                        <button class="briga-sidebar__item" data-go="stock">
                            <span class="briga-sidebar__icon">📦</span>
                            <span class="briga-sidebar__label">Stock</span>
                        </button>
                        <button class="briga-sidebar__item" data-go="stockbar">
                            <span class="briga-sidebar__icon">🍷</span>
                            <span class="briga-sidebar__label">Stock Bar</span>
                        </button>
                        <button class="briga-sidebar__item" data-go="commandes">
                            <span class="briga-sidebar__icon">🚚</span>
                            <span class="briga-sidebar__label">Commandes</span>
                        </button>
                        <button class="briga-sidebar__item" data-go="casse">
                            <span class="briga-sidebar__icon">💥</span>
                            <span class="briga-sidebar__label">Casse & Perte</span>
                        </button>
                        <button class="briga-sidebar__item" data-go="offerts">
                            <span class="briga-sidebar__icon">🎁</span>
                            <span class="briga-sidebar__label">Offerts</span>
                        </button>
                        <button class="briga-sidebar__item" data-go="dlc">
                            <span class="briga-sidebar__icon">⏰</span>
                            <span class="briga-sidebar__label">DLC</span>
                        </button>
                        <button class="briga-sidebar__item" data-go="salle">
                            <span class="briga-sidebar__icon">🗺️</span>
                            <span class="briga-sidebar__label">Plan de salle</span>
                        </button>
                    </nav>
                    <div class="briga-sidebar__footer">
                    <button class="briga-sidebar__item" data-go="demo" style="margin-bottom:8px;opacity:0.7">
                        <span class="briga-sidebar__icon">🧪</span>
                        <span class="briga-sidebar__label">Demo</span>
                    </button>

                        <button class="briga-sidebar__logout" id="brigaLogoutBtn">Déconnexion</button>
                    </div>
                </aside>

                <!-- MAIN -->
                <div class="briga-main">
                    <header class="briga-header">
                        <div id="briga-header-title" class="briga-header__title">Dashboard</div>
                        <div class="briga-header__user" id="briga-header-user">Constantin</div>
                    </header>

                    <div class="briga-content">

                        <!-- HOME / DASHBOARD -->
                        <section id="briga-screen-home" class="briga__screen">
                            <div class="b-ph">
                                <h2 id="briga-dash-greeting">Bonjour 👋</h2>
                                <p id="briga-dash-service">Service du midi</p>
                            </div>
                            <div class="briga-home__panels">
                                <div class="briga-panel briga-panel--gold">
                                    <h3>⚡ Actions à faire</h3>
                                    <ul id="briga-dash-actions"><li>Chargement…</li></ul>
                                </div>
                                <div class="briga-panel">
                                    <h3>👥 Équipe</h3>
                                    <ul id="briga-dash-team"><li>Chargement…</li></ul>
                                </div>
                                <div class="briga-panel briga-panel--alert">
                                    <h3>🚨 Alertes</h3>
                                    <ul id="briga-dash-alerts"><li>Chargement…</li></ul>
                                </div>
                            </div>
                        </section>

                        <!-- CAISSE -->
                        <section id="briga-screen-caisse" class="briga__screen briga__hidden">
                            <div class="briga-caisse">

                                <!-- BARRE DATE -->
                                <div class="briga-caisse__datebar">
                                    <button id="brigaCaissePrevDay" class="briga-caisse__daybtn">‹</button>
                                    <input type="date" id="brigaCaisseDate" class="briga__input briga-caisse__dateinput" />
                                    <button id="brigaCaisseNextDay" class="briga-caisse__daybtn">›</button>
                                    <button id="brigaCaisseToday" class="briga-caisse__daybtn briga-caisse__today">Aujourd'hui</button>
                                </div>

                                <!-- TABS -->
                                <div class="b-tabs">
                                    <button class="b-tab is-active" data-tab="midi">🌞 Midi</button>
                                    <button class="b-tab" data-tab="journee">📋 Journée</button>
                                    <button class="b-tab" data-tab="recap">📊 Récap</button>
                                    <button class="b-tab" data-tab="historique">🗓 Historique</button>
                                </div>

                                <!-- FORMULAIRE MIDI -->
                                <div id="briga-tab-midi" class="briga-caisse__tabcontent">
                                    <div class="b-card">
                                        <div class="briga-caisse__grid">
                                            <div class="briga-caisse__field briga-caisse__field--full">
                                                <label>Couverts midi</label>
                                                <input type="number" id="brigaCouvertsMidi" class="briga__input briga-caisse__big" placeholder="0" min="0" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>TTC 20%</label>
                                                <input type="number" id="brigaTTC20Midi" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>TTC 10%</label>
                                                <input type="number" id="brigaTTC10Midi" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>TVA 5.5% <span class="briga__tag">emporté</span></label>
                                                <input type="number" id="brigaTTC55Midi" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Uber Eats</label>
                                                <input type="number" id="brigaUberMidi" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Deliveroo</label>
                                                <input type="number" id="brigaDeliverooMidi" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Remises</label>
                                                <input type="number" id="brigaRemiseMidi" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Annulations</label>
                                                <input type="number" id="brigaAnnulationsMidi" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Offerts</label>
                                                <input type="number" id="brigaOffertsMidi" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Fonds de caisse</label>
                                                <input type="number" id="brigaFondsCaisseMidi" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                        </div>
                                        <div class="briga-caisse__preview" id="prev-midi">
                                            <div class="briga-caisse__preview-item"><span>CA salle midi</span><strong id="prev-midi-ca">—</strong></div>
                                            <div class="briga-caisse__preview-item"><span>Ticket moyen midi</span><strong id="prev-midi-ticket">—</strong></div>
                                        </div>
                                        <button class="briga__btn briga-caisse__savebtn" data-save="midi">💾 Enregistrer Midi</button>
                                        <div class="briga__msg" id="msg-midi"></div>
                                    </div>
                                </div>

                                <!-- FORMULAIRE JOURNÉE -->
                                <div id="briga-tab-journee" class="briga-caisse__tabcontent briga__hidden">
                                    <div class="b-card">
                                        <p class="briga__muted" style="margin-bottom:12px">Saisir le total de la journée. Le soir est calculé automatiquement.</p>
                                        <div class="briga-caisse__grid">
                                            <div class="briga-caisse__field briga-caisse__field--full">
                                                <label>Couverts journée</label>
                                                <input type="number" id="brigaCouvertsDay" class="briga__input briga-caisse__big" placeholder="0" min="0" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>TTC 20%</label>
                                                <input type="number" id="brigaTTC20Day" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>TTC 10%</label>
                                                <input type="number" id="brigaTTC10Day" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>TVA 5.5% <span class="briga__tag">emporté</span></label>
                                                <input type="number" id="brigaTTC55Day" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Uber Eats</label>
                                                <input type="number" id="brigaUberDay" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Deliveroo</label>
                                                <input type="number" id="brigaDeliverooDay" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Remises</label>
                                                <input type="number" id="brigaRemiseDay" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Annulations</label>
                                                <input type="number" id="brigaAnnulationsDay" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Offerts</label>
                                                <input type="number" id="brigaOffertsDay" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Fonds de caisse</label>
                                                <input type="number" id="brigaFondsCaisseDay" class="b-input" placeholder="0.00" step="0.01" />
                                            </div>
                                        </div>
                                        <div class="briga-caisse__preview" id="prev-day">
                                            <div class="briga-caisse__preview-item"><span>CA salle journée</span><strong id="prev-day-ca">—</strong></div>
                                            <div class="briga-caisse__preview-item"><span>Ticket moyen journée</span><strong id="prev-day-ticket">—</strong></div>
                                            <div class="briga-caisse__preview-item briga-caisse__preview-item--soir"><span>CA salle soir (calculé)</span><strong id="prev-day-casoir">—</strong></div>
                                            <div class="briga-caisse__preview-item briga-caisse__preview-item--soir"><span>Ticket moyen soir</span><strong id="prev-day-ticketsoir">—</strong></div>
                                        </div>
                                        <button class="briga__btn briga-caisse__savebtn" data-save="journee">💾 Enregistrer Journée</button>
                                        <div class="briga__msg" id="msg-journee"></div>
                                    </div>
                                </div>

                                <!-- RÉCAP -->
                                <div id="briga-tab-recap" class="briga-caisse__tabcontent briga__hidden">
                                    <div id="briga-caisse-recap-content">
                                        <div class="briga__muted">Chargement…</div>
                                    </div>
                                </div>

                                <!-- HISTORIQUE -->
                                <div id="briga-tab-historique" class="briga-caisse__tabcontent briga__hidden">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">🗓 30 derniers jours</h3>
                                        <div id="briga-caisse-histo-list"><div class="briga__muted">Chargement…</div></div>
                                    </div>
                                </div>

                            </div>
                        </section>

                        <!-- TACHES -->
                        <section id="briga-screen-taches" class="briga__screen briga__hidden">

                          <!-- TÂCHES HEADER -->
                          <div class="tasks-header">
                            <div class="tasks-header__left">
                              <h2 class="b-ph__title">Tâches</h2>
                              <span class="b-badge" id="tasksDayBadge">Lun–Mer</span>
                              <span id="tasksDayLabel" style="font-size:11px;color:var(--txt3)"></span>
                            </div>
                            <div class="tasks-header__right">
                              <button id="tasksSeedBtn" class="b-btn b-btn--navy b-btn--sm">🔄 Initialiser le jour</button>
                              <button id="tasksAddBtn"  class="b-btn b-btn--ghost b-btn--sm">+ Ajouter</button>
                              <button id="tasksRefreshBtn" class="b-btn b-btn--icon b-btn--sm" title="Actualiser">↺</button>
                            </div>
                          </div>

                          <!-- KPIs tâches -->
                          <div class="tasks-kpis">
                            <div class="tasks-kpi tasks-kpi--blue">
                              <div class="tasks-kpi__val" id="tasksKpiLibre">0</div>
                              <div class="tasks-kpi__lbl">Libres</div>
                            </div>
                            <div class="tasks-kpi tasks-kpi--blue">
                              <div class="tasks-kpi__val" id="tasksKpiPrises">0</div>
                              <div class="tasks-kpi__lbl">Prises</div>
                            </div>
                            <div class="tasks-kpi tasks-kpi--orange">
                              <div class="tasks-kpi__val" id="tasksKpiValider">0</div>
                              <div class="tasks-kpi__lbl">À valider</div>
                            </div>
                            <div class="tasks-kpi tasks-kpi--green">
                              <div class="tasks-kpi__val" id="tasksKpiValidees">0</div>
                              <div class="tasks-kpi__lbl">Validées</div>
                            </div>
                            <div class="tasks-kpi tasks-kpi--red">
                              <div class="tasks-kpi__val" id="tasksKpiRetard">0</div>
                              <div class="tasks-kpi__lbl">En retard</div>
                            </div>
                          </div>

                          <!-- FILTRES RÔLE -->
                          <div class="tasks-filters">
                            <div class="tasks-filters__section">
                              <span class="tasks-filters__lbl">Rôle :</span>
                              <button class="tasks-role-btn is-active" data-role="all">Tous</button>
                              <button class="tasks-role-btn" data-role="salle">Salle / Bar</button>
                              <button class="tasks-role-btn" data-role="cuisine">Cuisine</button>
                              <button class="tasks-role-btn tasks-role-btn--manager" data-role="manager">👔 Manager</button>
                            </div>
                            <div class="tasks-filters__section">
                              <span class="tasks-filters__lbl">Zone :</span>
                              <button class="tasks-zone-btn is-active" data-zone="all">Tout</button>
                              <button class="tasks-zone-btn" data-zone="bar">🍷 Bar</button>
                              <button class="tasks-zone-btn" data-zone="salle">🍽 Salle</button>
                              <button class="tasks-zone-btn" data-zone="vestiaires">👔 Vest.</button>
                              <button class="tasks-zone-btn" data-zone="cuisine">🍳 Cuisine</button>
                            </div>
                          </div>

                          <!-- LÉGENDE WORKFLOW -->
                          <div class="tasks-legend">
                            <span class="tl-step tl-step--libre">○ Libre</span>
                            <span class="tl-arrow">→</span>
                            <span class="tl-step tl-step--prise">◐ Prise</span>
                            <span class="tl-arrow">→</span>
                            <span class="tl-step tl-step--valider">◑ À valider</span>
                            <span class="tl-arrow">→</span>
                            <span class="tl-step tl-step--validee">● Validée</span>
                          </div>

                          <!-- LISTE DES TÂCHES -->
                          <div id="brigaTasksList">
                            <div class="b-loading"><div class="b-spinner"></div><span>Chargement…</span></div>
                          </div>

                        </section>

                        <!-- STOCK -->
                        <section id="briga-screen-stock" class="briga__screen briga__hidden">
                            <div class="briga-stock">

                                <div class="b-tabs">
                                    <button class="briga-stock__tab is-active" data-stab="bar">🍷 Bar</button>
                                    <button class="briga-stock__tab" data-stab="cuisine">🍳 Cuisine</button>
                                    <button class="briga-stock__tab" data-stab="ajouter">➕ Ajouter</button>
                                    <button class="briga-stock__tab" data-stab="historique">📋 Mouvements</button>
                                </div>

                                <!-- STOCK BAR -->
                                <div id="briga-stab-bar" class="briga-stock__tabcontent">
                                    <div id="briga-stock-bar">
                                        <div class="briga__muted">Chargement…</div>
                                    </div>
                                </div>

                                <!-- STOCK CUISINE -->
                                <div id="briga-stab-cuisine" class="briga-stock__tabcontent briga__hidden">
                                    <div id="briga-stock-cuisine">
                                        <div class="briga__muted">Chargement…</div>
                                    </div>
                                </div>

                                <!-- AJOUTER PRODUIT -->
                                <div id="briga-stab-ajouter" class="briga-stock__tabcontent briga__hidden">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">Nouveau produit</h3>
                                        <div class="briga-caisse__grid">
                                            <div class="briga-caisse__field briga-caisse__field--full">
                                                <label>Nom du produit *</label>
                                                <input type="text" id="brigaStockNewName" class="b-input" placeholder="Ex: Ricard 1L" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Catégorie</label>
                                                <select id="brigaStockNewCat" class="b-select">
                                                    <option value="alcool">Alcool</option>
                                                    <option value="vin">Vin</option>
                                                    <option value="champagne">Champagne</option>
                                                    <option value="biere">Bière</option>
                                                    <option value="soft">Soft</option>
                                                    <option value="sirop">Sirop</option>
                                                    <option value="epicerie">Épicerie</option>
                                                    <option value="autre">Autre</option>
                                                </select>
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Unité</label>
                                                <select id="brigaStockNewUnit" class="b-select">
                                                    <option value="bouteille">Bouteille</option>
                                                    <option value="kg">Kg</option>
                                                    <option value="litre">Litre</option>
                                                    <option value="pièce">Pièce</option>
                                                    <option value="colis">Colis</option>
                                                    <option value="carton">Carton</option>
                                                </select>
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Zone</label>
                                                <select id="brigaStockNewZone" class="b-select">
                                                    <option value="bar">Bar</option>
                                                    <option value="cuisine">Cuisine</option>
                                                    <option value="cave">Cave</option>
                                                    <option value="salle">Salle</option>
                                                </select>
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Stock minimum</label>
                                                <input type="number" id="brigaStockNewMin" class="b-input" placeholder="0" min="0" step="0.5" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Stock actuel</label>
                                                <input type="number" id="brigaStockNewCurrent" class="b-input" placeholder="0" min="0" step="0.5" />
                                            </div>
                                        </div>
                                        <button id="brigaStockAddBtn" class="b-btn b-btn--navy" style="margin-top:14px;width:100%">➕ Ajouter au stock</button>
                                        <div id="brigaStockAddMsg" class="briga__msg" style="margin-top:8px;text-align:center"></div>
                                    </div>
                                </div>

                                <!-- HISTORIQUE MOUVEMENTS -->
                                <div id="briga-stab-historique" class="briga-stock__tabcontent briga__hidden">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">📋 Derniers mouvements</h3>
                                        <div id="briga-stock-moves-list"><div class="briga__muted">Chargement…</div></div>
                                    </div>
                                </div>

                            </div>
                        </section>

                        <!-- COMMANDES -->
                        <section id="briga-screen-commandes" class="briga__screen briga__hidden">
                            <div class="briga-commandes">
                                <div class="b-tabs">
                                    <button class="briga-cmd__tab is-active" data-fournisseur="brake">🚛 BRAKE / Sysco</button>
                                    <button class="briga-cmd__tab" data-fournisseur="brasseur">🍺 Brasseur</button>
                                    <button class="briga-cmd__tab" data-fournisseur="historique">📋 Historique</button>
                                </div>
                                <div class="briga-cmd__deadlines">
                                    <div class="briga-cmd__deadline" id="briga-cmd-deadline-brake">🛒 BRAKE : commande samedi · limite <strong>dimanche soir</strong></div>
                                    <div class="briga-cmd__deadline briga-cmd__deadline--brasseur" id="briga-cmd-deadline-brasseur" style="display:none">🍺 BRASSEUR : commande <strong>mercredi avant 14h30</strong></div>
                                </div>
                                <div id="briga-cmd-form-wrap">
                                    <div class="b-card">
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                                            <h3 class="briga-caisse__subtitle" id="briga-cmd-title" style="margin:0">Commande BRAKE</h3>
                                            <button id="briga-cmd-select-all" class="b-btn b-btn--navy b-btn--sm">Suggestions auto</button>
                                        </div>
                                        <div id="briga-cmd-list"></div>
                                        <textarea id="briga-cmd-note" class="b-input" placeholder="Notes commande…" rows="2" style="margin-top:12px"></textarea>
                                        <button id="briga-cmd-save" class="b-btn b-btn--navy" style="margin-top:12px">💾 Enregistrer la commande</button>
                                        <div id="briga-cmd-msg" class="briga__msg"></div>
                                    </div>
                                </div>
                                <div id="briga-cmd-historique" style="display:none">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">📋 Commandes passées</h3>
                                        <div id="briga-cmd-histo-list"><div class="briga__muted">Chargement…</div></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- STOCK BAR QUOTIDIEN -->
                        <section id="briga-screen-stockbar" class="briga__screen briga__hidden">
                            <div class="briga-sb">

                                <!-- BARRE DATE + TABS -->
                                <div class="briga-caisse__datebar">
                                    <button class="briga-caisse__daybtn" id="sbPrevDay">&#8249;</button>
                                    <input type="date" id="sbDateInput" class="briga__input briga-caisse__dateinput" />
                                    <button class="briga-caisse__daybtn" id="sbNextDay">&#8250;</button>
                                    <button class="briga-caisse__daybtn briga-caisse__today" id="sbTodayBtn">Aujourd'hui</button>
                                </div>

                                <div class="b-tabs" id="sbTabs">
                                    <button class="b-tab is-active" data-sbtab="ouverture">☀️ Stock matin</button>
                                    <button class="b-tab" data-sbtab="ventes">🌙 Ventes soir</button>
                                    <button class="b-tab" data-sbtab="recap">📊 Récap jour</button>
                                    <button class="b-tab" data-sbtab="semaine">📅 Semaine</button>
                                </div>

                                <!-- ☀️ STOCK MATIN -->
                                <div id="briga-sbtab-ouverture" class="briga-sb__tabcontent">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">☀️ Stock bar — ouverture</h3>
                                        <p class="briga__muted" style="margin-bottom:12px">Saisir les bouteilles présentes à l'ouverture. Le reliquat verres de la veille est pré-rempli automatiquement.</p>
                                        <div id="briga-sb-ouv-list" class="briga-sb__list"></div>
                                        <button id="brigaSbOuvSave" class="b-btn b-btn--navy" style="margin-top:12px">💾 Enregistrer stock matin</button>
                                        <div id="brigaSbOuvMsg" class="briga__msg" style="margin-top:8px;text-align:center"></div>
                                    </div>
                                </div>

                                <!-- 🌙 VENTES SOIR -->
                                <div id="briga-sbtab-ventes" class="briga-sb__tabcontent briga__hidden">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">🌙 Ventes bar — clôture soir</h3>
                                        <p class="briga__muted" style="margin-bottom:12px">Saisir les ventes, offerts et casse du service. Le reliquat est calculé automatiquement.</p>
                                        <div id="briga-sb-ventes-list" class="briga-sb__list"></div>
                                        <button id="brigaSbVentesSave" class="b-btn b-btn--navy" style="margin-top:12px">💾 Enregistrer ventes</button>
                                        <div id="brigaSbVentesMsg" class="briga__msg" style="margin-top:8px;text-align:center"></div>
                                    </div>
                                </div>

                                <!-- 📊 RÉCAP JOUR -->
                                <div id="briga-sbtab-recap" class="briga-sb__tabcontent briga__hidden">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">📊 Récap du jour</h3>
                                        <div id="briga-sb-recap-list"></div>
                                    </div>
                                </div>

                                <!-- 📅 SEMAINE -->
                                <div id="briga-sbtab-semaine" class="briga-sb__tabcontent briga__hidden">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">📅 Résumé semaine</h3>
                                        <div id="briga-sb-semaine-list"></div>
                                    </div>
                                </div>

                            </div>
                        </section>

                        <!-- CASSE & PERTE -->
                        <section id="briga-screen-casse" class="briga__screen briga__hidden">
                            <div class="briga-casse">

                                <div class="b-tabs">
                                    <button class="briga-casse__tab is-active" data-ctab="saisie">💥 Saisie rapide</button>
                                    <button class="briga-casse__tab" data-ctab="manuel">✏️ Manuel</button>
                                    <button class="briga-casse__tab" data-ctab="historique">📋 Historique</button>
                                </div>

                                <!-- SAISIE RAPIDE -->
                                <div id="briga-ctab-saisie" class="briga-casse__tabcontent">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">Choix rapide</h3>

                                        <!-- FILTRE ZONE -->
                                        <div class="briga-casse__zones">
                                            <button class="briga-casse__zfilter is-active" data-filter="all">Tout</button>
                                            <button class="briga-casse__zfilter" data-filter="bar">🍷 Bar</button>
                                            <button class="briga-casse__zfilter" data-filter="cuisine">🍳 Cuisine</button>
                                            <button class="briga-casse__zfilter" data-filter="salle">🍽️ Salle</button>
                                        </div>

                                        <div id="briga-casse-library" class="briga-casse__library"></div>

                                        <!-- CONFIRMATION QUANTITÉ -->
                                        <div id="briga-casse-confirm" class="briga-casse__confirm" style="display:none">
                                            <div class="briga-casse__confirm-label" id="briga-casse-confirm-label"></div>
                                            <div class="briga-casse__confirm-row">
                                                <label>Quantité</label>
                                                <input type="number" id="brigaCasseQty" class="b-input" value="1" min="1" step="1" style="max-width:100px;margin:0" />
                                            </div>
                                            <div class="briga-casse__confirm-row">
                                                <label>Coût estimé (€)</label>
                                                <input type="number" id="brigaCasseCost" class="b-input" value="0" min="0" step="0.01" style="max-width:120px;margin:0" />
                                            </div>
                                            <input type="text" id="brigaCasseNote" class="b-input" placeholder="Note (optionnel)" style="margin-top:8px" />
                                            <div style="display:flex;gap:8px;margin-top:8px">
                                                <button id="brigaCasseConfirmBtn" class="b-btn b-btn--navy" style="margin:0;flex:1">✅ Enregistrer</button>
                                                <button id="brigaCasseCancelBtn" class="b-btn b-btn--navy" style="margin:0;flex:0 0 auto;background:#6b7280;width:auto;padding:12px 16px">Annuler</button>
                                            </div>
                                            <div id="brigaCasseMsg" class="briga__msg" style="margin-top:8px;text-align:center"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SAISIE MANUELLE -->
                                <div id="briga-ctab-manuel" class="briga-casse__tabcontent briga__hidden">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">Saisie manuelle</h3>
                                        <div class="briga-caisse__grid" style="grid-template-columns:1fr 1fr">
                                            <div class="briga-caisse__field briga-caisse__field--full">
                                                <label>Description *</label>
                                                <input type="text" id="brigaCasseManLabel" class="b-input" placeholder="Ex: bouteille de Ricard renversée" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Zone</label>
                                                <select id="brigaCasseManZone" class="b-select" style="width:100%">
                                                    <option value="bar">Bar</option>
                                                    <option value="cuisine">Cuisine</option>
                                                    <option value="salle">Salle</option>
                                                </select>
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Type</label>
                                                <select id="brigaCasseManType" class="b-select" style="width:100%">
                                                    <option value="casse_materiel">Casse matériel</option>
                                                    <option value="casse_produit">Casse produit</option>
                                                    <option value="perte_produit">Perte produit</option>
                                                    <option value="incident">Incident</option>
                                                </select>
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Quantité</label>
                                                <input type="number" id="brigaCasseManQty" class="b-input" value="1" min="1" />
                                            </div>
                                            <div class="briga-caisse__field">
                                                <label>Coût estimé (€)</label>
                                                <input type="number" id="brigaCasseManCost" class="b-input" value="0" min="0" step="0.01" />
                                            </div>
                                            <div class="briga-caisse__field briga-caisse__field--full">
                                                <label>Note</label>
                                                <input type="text" id="brigaCasseManNote" class="b-input" placeholder="Note complémentaire" />
                                            </div>
                                        </div>
                                        <button id="brigaCasseManSave" class="b-btn b-btn--navy" style="margin-top:4px">💾 Enregistrer</button>
                                        <div id="brigaCasseManMsg" class="briga__msg" style="margin-top:8px;text-align:center"></div>
                                    </div>
                                </div>

                                <!-- HISTORIQUE -->
                                <div id="briga-ctab-historique" class="briga-casse__tabcontent briga__hidden">
                                    <div class="b-card">
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                                            <h3 class="briga-caisse__subtitle" style="margin:0">📋 Historique</h3>
                                            <div id="briga-casse-total" class="briga-casse__total"></div>
                                        </div>
                                        <div id="briga-casse-histo-list"><div class="briga__muted">Chargement…</div></div>
                                    </div>
                                </div>

                            </div>
                        </section>

                        <!-- DLC -->
                        <section id="briga-screen-dlc" class="briga__screen briga__hidden">
                            <div class="b-card">
                                <form id="briga-dlc-form">
                                    <div class="briga__row">
                                        <input id="brigaDlcName" class="b-input" placeholder="Produit" required />
                                        <input id="brigaDlcQty"  class="b-input" placeholder="Quantité" style="max-width:100px" />
                                    </div>
                                    <div class="briga__row">
                                        <input id="brigaDlcDate" class="b-input" type="date" required />
                                        <select id="brigaDlcZone" class="b-select">
                                            <option value="cuisine">Cuisine</option>
                                            <option value="bar">Bar</option>
                                            <option value="cave">Cave</option>
                                        </select>
                                        <button type="submit" class="b-btn b-btn--navy" style="width:auto;padding:12px 20px;margin:0">+ Ajouter</button>
                                    </div>
                                </form>
                            </div>
                            <div id="briga-dlc-list" class="b-card">
                                <div class="briga__muted">Chargement…</div>
                            </div>
                        </section>

                    </div><!-- /briga-content -->
                </div><!-- /briga-main -->
            </div><!-- /briga-layout -->

                        <!-- OFFERTS -->
                        <section id="briga-screen-offerts" class="briga__screen briga__hidden">
                            <div class="briga-offerts">

                                <!-- TABS -->
                                <div class="b-tabs">
                                    <button class="b-tab is-active" data-otab="saisie">➕ Saisir</button>
                                    <button class="b-tab" data-otab="jour">📅 Aujourd'hui</button>
                                    <button class="b-tab" data-otab="resume">📊 Résumé</button>
                                </div>

                                <!-- DATEBAR -->
                                <div class="briga-caisse__datebar" id="offerts-datebar">
                                    <button class="briga-caisse__daybtn" id="oPrevDay">&#8249;</button>
                                    <input type="date" id="oDateInput" class="briga__input briga-caisse__dateinput" />
                                    <button class="briga-caisse__daybtn" id="oNextDay">&#8250;</button>
                                    <button class="briga-caisse__daybtn briga-caisse__today" id="oTodayBtn">Aujourd'hui</button>
                                </div>

                                <!-- ONGLET SAISIE -->
                                <div id="briga-otab-saisie" class="briga-offerts__tab">
                                    <div class="b-card">
                                        <h3 class="briga-caisse__subtitle">🎁 Nouvel offert</h3>

                                        <div class="briga-offerts__form">
                                            <div class="briga__field">
                                                <label class="b-label">Description *</label>
                                                <input type="text" id="oLabel" class="b-input" placeholder="Ex: Verre de vin rouge, Dessert offert..." />
                                            </div>

                                            <div class="briga__field">
                                                <label class="b-label">Bénéficiaire</label>
                                                <div class="briga-offerts__chips">
                                                    <button class="briga-offerts__chip is-active" data-val="client">👤 Client</button>
                                                    <button class="briga-offerts__chip" data-val="personnel">👨‍🍳 Personnel</button>
                                                    <button class="briga-offerts__chip" data-val="commercial">🤝 Commercial</button>
                                                </div>
                                                <input type="hidden" id="oBenef" value="client" />
                                            </div>

                                            <div class="briga__field">
                                                <label class="b-label">Zone</label>
                                                <div class="briga-offerts__chips">
                                                    <button class="briga-offerts__chip is-active" data-zone="bar">🍷 Bar</button>
                                                    <button class="briga-offerts__chip" data-zone="salle">🍽️ Salle</button>
                                                    <button class="briga-offerts__chip" data-zone="cuisine">👨‍🍳 Cuisine</button>
                                                </div>
                                                <input type="hidden" id="oZone" value="bar" />
                                            </div>

                                            <div class="briga-offerts__row2">
                                                <div class="briga__field">
                                                    <label class="b-label">Quantité</label>
                                                    <input type="number" id="oQty" class="b-input" value="1" min="1" step="1" />
                                                </div>
                                                <div class="briga__field">
                                                    <label class="b-label">Coût estimé (€)</label>
                                                    <input type="number" id="oCost" class="b-input" value="0" min="0" step="0.50" />
                                                </div>
                                            </div>

                                            <div class="briga__field">
                                                <label class="b-label">Note (optionnel)</label>
                                                <input type="text" id="oNote" class="b-input" placeholder="Raison, table, contexte..." />
                                            </div>

                                            <button id="oSaveBtn" class="briga__btn briga__btn--primary briga__btn--full">
                                                Enregistrer l'offert
                                            </button>
                                            <div id="oMsg" class="briga-casse__msg"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ONGLET JOUR -->
                                <div id="briga-otab-jour" class="briga-offerts__tab briga__hidden">
                                    <div class="b-card">
                                        <div class="briga-offerts__total" id="oJourTotal"></div>
                                        <div id="oJourList"></div>
                                    </div>
                                </div>

                                <!-- ONGLET RÉSUMÉ -->
                                <div id="briga-otab-resume" class="briga-offerts__tab briga__hidden">
                                    <div class="briga-caisse__datebar" style="margin-bottom:14px">
                                        <input type="date" id="oResumeFrom" class="briga__input briga-caisse__dateinput" />
                                        <span style="color:var(--bp-muted);padding:0 8px">→</span>
                                        <input type="date" id="oResumeTo"   class="briga__input briga-caisse__dateinput" />
                                        <button id="oResumeLoad" class="b-btn b-btn--navy b-btn--sm">Analyser</button>
                                    </div>
                                    <div id="oResumeDatas"></div>
                                </div>

                            </div>
                        </section>

                        <!-- DEMO & SIMULATION -->
                        <section id="briga-screen-demo" class="briga__screen briga__hidden">
                            <div class="briga-demo">
                                <div class="briga-demo__header">
                                    <h2>🧪 Mode Simulation</h2>
                                    <p>Injecte 3 jours de données réalistes pour tester tous les modules.</p>
                                </div>

                                <div id="briga-demo-status" class="briga-demo__status"></div>

                                <div class="briga-demo__grid">
                                    <div class="briga-demo__card">
                                        <div class="briga-demo__card-icon">📅</div>
                                        <div class="briga-demo__card-title">3 jours simulés</div>
                                        <div class="briga-demo__card-desc">Lundi · Mardi · Mercredi (aujourd'hui)</div>
                                    </div>
                                    <div class="briga-demo__card">
                                        <div class="briga-demo__card-icon">🍷</div>
                                        <div class="briga-demo__card-title">21 produits</div>
                                        <div class="briga-demo__card-desc">14 produits bar + 7 produits cuisine</div>
                                    </div>
                                    <div class="briga-demo__card">
                                        <div class="briga-demo__card-icon">💰</div>
                                        <div class="briga-demo__card-title">2 jours de caisse</div>
                                        <div class="briga-demo__card-desc">Lundi + Mardi avec midi et journée</div>
                                    </div>
                                    <div class="briga-demo__card">
                                        <div class="briga-demo__card-icon">💥</div>
                                        <div class="briga-demo__card-title">9 incidents casse</div>
                                        <div class="briga-demo__card-desc">Bar · Cuisine · Salle sur 2 jours</div>
                                    </div>
                                    <div class="briga-demo__card">
                                        <div class="briga-demo__card-icon">🚚</div>
                                        <div class="briga-demo__card-title">2 commandes</div>
                                        <div class="briga-demo__card-desc">Brasseur (mercredi) + BRAKE (dimanche)</div>
                                    </div>
                                    <div class="briga-demo__card">
                                        <div class="briga-demo__card-icon">✅</div>
                                        <div class="briga-demo__card-title">10 tâches</div>
                                        <div class="briga-demo__card-desc">Mercredi ouverture + fermeture</div>
                                    </div>
                                </div>

                                <div class="briga-demo__actions">
                                    <button id="brigaDemoSeed"  class="briga-demo__btn briga-demo__btn--seed">
                                        ▶️ Lancer la simulation
                                    </button>
                                    <button id="brigaDemoReset" class="briga-demo__btn briga-demo__btn--reset">
                                        🗑️ Vider toutes les données
                                    </button>
                                </div>
                                <div id="brigaDemoMsg" class="briga-demo__msg"></div>

                                <div id="briga-demo-log" class="briga-demo__log"></div>
                            </div>
                        </section>

                                                <!-- SECTION PLAN DE SALLE v3 -->
                        <section id="briga-screen-salle" class="briga__screen briga__hidden">
                          <div class="briga-salle">

                            <!-- HEADER compact -->
                            <div class="bs-header">
                              <div class="bs-header__left">
                                <h2 class="bs-title">Plan de salle</h2>
                                <div class="bs-kpis">
                                  <span class="bs-kpi bs-kpi--occupee" id="salleKpiOccupee">0 occupées</span>
                                  <span class="bs-kpi bs-kpi--reservee" id="salleKpiReservees">0 réservées</span>
                                  <span class="bs-kpi bs-kpi--libre" id="salleKpiLibre">0 libres</span>
                                  <span class="bs-kpi bs-kpi--couverts" id="salleKpiCouverts">0 couverts</span>
                                </div>
                              </div>
                              <div class="bs-header__right">
                                <button class="bs-btn bs-btn--ghost bs-btn--sm" id="salleResetBtn">🔄 Nouveau service</button>
                                <button class="bs-btn bs-btn--icon" id="salleRefreshBtn" title="Actualiser">↺</button>
                              </div>
                            </div>

                            <!-- ONGLETS ZONES -->
                            <div class="bs-zones">
                              <button class="bs-zone-tab is-active" data-zone="gilbert">
                                <span class="bs-zone-icon">🏛</span>
                                <span class="bs-zone-label">Salle Gilbert</span>
                                <span class="bs-zone-count" id="zoneCountGilbert">—</span>
                              </button>
                              <button class="bs-zone-tab" data-zone="terrasse">
                                <span class="bs-zone-icon">☀️</span>
                                <span class="bs-zone-label">Terrasse</span>
                                <span class="bs-zone-count" id="zoneCountTerrasse">—</span>
                              </button>
                              <button class="bs-zone-tab" data-zone="cuisine">
                                <span class="bs-zone-icon">🍳</span>
                                <span class="bs-zone-label">Côté cuisine</span>
                                <span class="bs-zone-count" id="zoneCountCuisine">—</span>
                              </button>
                              <button class="bs-zone-tab bs-zone-tab--global" data-zone="all">
                                <span class="bs-zone-icon">⊞</span>
                                <span class="bs-zone-label">Vue globale</span>
                              </button>
                            </div>

                            <!-- LÉGENDE + MODE BANNER -->
                            <div class="bs-toolbar">
                              <div class="bs-legend">
                                <span class="bs-dot bs-dot--libre">Libre</span>
                                <span class="bs-dot bs-dot--reservee">Réservée</span>
                                <span class="bs-dot bs-dot--occupee">Occupée</span>
                                <span class="bs-dot bs-dot--nettoyage">À nettoyer</span>
                              </div>
                              <div id="salleModeBar" class="bs-mode-bar briga__hidden"></div>
                            </div>

                            <!-- PLAN -->
                            <div class="bs-plan-wrap">
                              <div class="bs-plan" id="sallePlan">
                                <div class="bs-loading">
                                  <div class="bs-spinner"></div>
                                  <span>Chargement du plan…</span>
                                </div>
                              </div>
                            </div>

                          </div>
                        </section>

                <!-- BOTTOM NAV MOBILE -->
        <nav id="briga-bottom-nav" class="briga__hidden">
            <button class="briga-bnav__item is-active" data-go="home"><span class="briga-bnav__icon">📊</span>Dash</button>
            <button class="briga-bnav__item" data-go="caisse"><span class="briga-bnav__icon">💰</span>Caisse</button>
            <button class="briga-bnav__item" data-go="stock"><span class="briga-bnav__icon">📦</span>Stock</button>
            <button class="briga-bnav__item" data-go="commandes"><span class="briga-bnav__icon">🚚</span>Commandes</button>
            <button class="briga-bnav__item" data-go="casse"><span class="briga-bnav__icon">💥</span>Casse</button>
            <button class="briga-bnav__item" data-go="taches"><span class="briga-bnav__icon">✅</span>Tâches</button>
        </nav>

        
<!-- BRIGA Debug Runtime -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(function() {
    var fns = ['loadDashboard','initCaisse','tasksInitEvents','tasksLoad',
               'loadStock','loadDlc','initCasse','initStockBar','initDemo',
               'initOfferts','loadCmdForm','salleInitEvents','salleLoad'];
    var miss = fns.filter(function(f){ return typeof window[f] !== 'function'; });
    if (miss.length === 0) {
      console.log('%c[BRIGA] ✅ Tous les modules chargés (' + fns.length + '/'+fns.length+')', 'color:#22c55e;font-weight:bold');
    } else {
      console.error('[BRIGA] ❌ Modules manquants : ' + miss.join(', '));
      miss.forEach(function(f){ console.error('  typeof ' + f + ' =', typeof window[f]); });
    }
  }, 600);
});
</script>
</div><!-- /briga-root -->
        <?php
        return ob_get_clean();
    }
}
