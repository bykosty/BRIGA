<?php
if (!defined('ABSPATH')) exit;

class BRIGA_Shortcode {

    public static function init() {
        add_shortcode('briga_app', [__CLASS__, 'render']);
    }

    public static function render() {
        ob_start(); ?>
        <div id="briga-root" class="briga">

            <!-- LOGIN -->
            <section id="briga-screen-login" class="briga__screen briga__screen--login">
                <div class="briga-login__box">
                    <div class="briga-login__logo">BRIGA PRO</div>
                    <p class="briga-login__muted">Entre ton PIN pour accéder.</p>
                    <input id="brigaPinInput" class="briga__input" type="password" inputmode="numeric" placeholder="PIN" />
                    <button id="brigaPinBtn" class="briga__btn">Connexion</button>
                    <div id="brigaLoginMsg" class="briga__msg"></div>
                </div>
            </section>

            <!-- APP LAYOUT -->
            <div id="briga-app-layout" class="briga-layout briga__hidden">

                <!-- SIDEBAR -->
                <aside class="briga-sidebar">
                    <div class="briga-sidebar__logo"><span class="briga-sidebar__logo-full">BRIGA PRO</span><span class="briga-sidebar__logo-short">B</span></div>
                    <nav class="briga-sidebar__nav">
                        <button class="briga-sidebar__item is-active" data-go="home"><span class="briga-sidebar__icon">📊</span><span class="briga-sidebar__label">Dashboard</span></button>
                        <button class="briga-sidebar__item" data-go="taches"><span class="briga-sidebar__icon">✅</span><span class="briga-sidebar__label">Tâches</span></button>
                        <button class="briga-sidebar__item" data-go="stock"><span class="briga-sidebar__icon">📦</span><span class="briga-sidebar__label">Stock</span></button>
                        <button class="briga-sidebar__item" data-go="hygiene"><span class="briga-sidebar__icon">🧼</span><span class="briga-sidebar__label">Hygiène</span></button>
                        <button class="briga-sidebar__item" data-go="dlc"><span class="briga-sidebar__icon">⏰</span><span class="briga-sidebar__label">DLC</span></button>
                        <button class="briga-sidebar__item" data-go="commandes"><span class="briga-sidebar__icon">🚚</span><span class="briga-sidebar__label">Commandes</span></button>
                        <button class="briga-sidebar__item" data-go="reservations"><span class="briga-sidebar__icon">🍽️</span><span class="briga-sidebar__label">Réservations</span></button>
                        <button class="briga-sidebar__item" data-go="journal"><span class="briga-sidebar__icon">📝</span><span class="briga-sidebar__label">Journal</span></button>
                    </nav>
                    <div class="briga-sidebar__footer">
                        <button class="briga-sidebar__logout" id="brigaLogoutBtn">Déconnexion</button>
                    </div>
                </aside>

                <!-- MAIN -->
                <div class="briga-main">

                    <!-- HEADER -->
                    <header class="briga-header">
                        <div id="briga-header-title" class="briga-header__title">Dashboard</div>
                        <div class="briga-header__user">Constantin</div>
                    </header>

                    <!-- SCREENS -->
                    <div class="briga-content">

                        <!-- HOME -->
                        <section id="briga-screen-home" class="briga__screen">
                            <div class="briga-home__greeting">
                                <h2>Bonjour Constantin 👋</h2>
                                <p>Service du midi</p>
                            </div>
                            <div class="briga-home__panels">
                                <div class="briga-panel briga-panel--gold">
                                    <h3>⚡ Actions à faire</h3>
                                    <ul>
                                        <li>Charger bar midi</li>
                                        <li>Vérifier DLC cuisine</li>
                                        <li>Contrôler stock cave</li>
                                    </ul>
                                </div>
                                <div class="briga-panel">
                                    <h3>👥 Équipe midi</h3>
                                    <ul>
                                        <li>Bar : 1 / 2</li>
                                        <li>Salle : 2 / 3</li>
                                        <li>Cuisine : 1 / 2</li>
                                    </ul>
                                </div>
                                <div class="briga-panel briga-panel--alert">
                                    <h3>🚨 Alertes</h3>
                                    <ul>
                                        <li>Stock faible : citron</li>
                                        <li>DLC proche : saumon</li>
                                        <li>Machine chantilly à contrôler</li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        <!-- TACHES -->
                        <section id="briga-screen-taches" class="briga__screen briga__hidden">
                            <div class="briga__card">
                                <div class="briga__row">
                                    <input id="brigaTaskTitle" class="briga__input" placeholder="Nouvelle tâche" />
                                    <select id="brigaTaskPriority" class="briga__select">
                                        <option value="low">Low</option>
                                        <option value="med" selected>Med</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <button id="brigaTaskAddBtn" class="briga__btn">+ Ajouter</button>
                            </div>
                            <div class="briga__card">
                                <div class="briga__muted">Liste des tâches</div>
                                <div id="brigaTasksList"></div>
                            </div>
                        </section>

                        <!-- STOCK -->
                        <section id="briga-screen-stock" class="briga__screen briga__hidden">
                            <div class="briga__card">Module STOCK (à reconnecter ensuite)</div>
                        </section>

                        <!-- HYGIENE -->
                        <section id="briga-screen-hygiene" class="briga__screen briga__hidden">
                            <div class="briga__card">Module HYGIÈNE (à reconnecter ensuite)</div>
                        </section>

                        <!-- DLC -->
                        <section id="briga-screen-dlc" class="briga__screen briga__hidden">
                            <div class="briga__card">Module DLC (à reconnecter ensuite)</div>
                        </section>

                        <!-- COMMANDES -->
                        <section id="briga-screen-commandes" class="briga__screen briga__hidden">
                            <div class="briga__card">Module COMMANDES (à reconnecter ensuite)</div>
                        </section>

                        <!-- RESERVATIONS -->
                        <section id="briga-screen-reservations" class="briga__screen briga__hidden">
                            <div class="briga__card">Module RÉSERVATIONS (à reconnecter ensuite)</div>
                        </section>

                        <!-- JOURNAL -->
                        <section id="briga-screen-journal" class="briga__screen briga__hidden">
                            <div class="briga__card">Module JOURNAL (à reconnecter ensuite)</div>
                        </section>

                    </div><!-- /briga-content -->
                </div><!-- /briga-main -->
            </div><!-- /briga-layout -->

        </div><!-- /briga-root -->
        <?php
        return ob_get_clean();
    }
}
