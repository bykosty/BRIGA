<?php
if (!defined('ABSPATH')) exit;

class BRIGA_DB {

    const VERSION = '2.3.0'; // v2.3.0 : ajout table_status (Plan de salle)

    public static function install() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // ── RESTAURANTS ────────────────────────────────────
        dbDelta("CREATE TABLE {$wpdb->prefix}briga_restaurants (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name         VARCHAR(255)    NOT NULL,
            slug         VARCHAR(100)    NOT NULL UNIQUE,
            address      VARCHAR(500)    DEFAULT '',
            phone        VARCHAR(50)     DEFAULT '',
            manager_name VARCHAR(255)    DEFAULT '',
            wp_user_id   BIGINT UNSIGNED DEFAULT NULL,
            is_active    TINYINT(1)      NOT NULL DEFAULT 1,
            created_at   DATETIME        NOT NULL,
            updated_at   DATETIME        NOT NULL,
            PRIMARY KEY (id),
            KEY slug (slug)
        ) $charset;");

        // ── TASKS ──────────────────────────────────────────
        dbDelta("CREATE TABLE {$wpdb->prefix}briga_tasks (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            restaurant_id   BIGINT UNSIGNED NOT NULL,
            title           VARCHAR(255)    NOT NULL,
            priority        ENUM('low','med','high','urgent') NOT NULL DEFAULT 'med',
            status          ENUM('libre','prise','a_valider','validee','en_retard') NOT NULL DEFAULT 'libre',
            shift           ENUM('midi','soir','journee','') DEFAULT '',
            zone            VARCHAR(50)     DEFAULT '',
            frequency       ENUM('daily','weekly','once') DEFAULT 'daily',
            day_of_week     TINYINT         DEFAULT NULL,
            taken_by        VARCHAR(100)    DEFAULT '',
            taken_at        DATETIME        DEFAULT NULL,
            done_by         VARCHAR(100)    DEFAULT '',
            done_at         DATETIME        DEFAULT NULL,
            validated_by    VARCHAR(100)    DEFAULT '',
            validated_at    DATETIME        DEFAULT NULL,
            target_date     DATE            DEFAULT NULL,
            employee_role   ENUM('manager','salle','bar','cuisine','all') DEFAULT 'all',
            note            TEXT            ,
            created_at      DATETIME        NOT NULL,
            PRIMARY KEY (id),
            KEY restaurant_id (restaurant_id),
            KEY status (status),
            KEY zone (zone)
        ) $charset;");

        // ── STOCK ITEMS ────────────────────────────────────
        dbDelta("CREATE TABLE {$wpdb->prefix}briga_stock_items (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            restaurant_id   BIGINT UNSIGNED NOT NULL,
            name            VARCHAR(255)    NOT NULL,
            category        VARCHAR(100)    DEFAULT '',
            supplier_type   ENUM('brake','brasseur','none') DEFAULT 'none',
            unit            VARCHAR(50)     DEFAULT 'bouteille',
            stock_min       DECIMAL(10,2)   DEFAULT 0,
            stock_current   DECIMAL(10,2)   DEFAULT 0,
            zone            VARCHAR(50)     DEFAULT '',
            has_verre       TINYINT(1)      DEFAULT 0,
            verres_par_btl  TINYINT UNSIGNED DEFAULT 6,
            is_active       TINYINT(1)      DEFAULT 1,
            display_order   INT             DEFAULT 0,
            created_at      DATETIME        NOT NULL,
            updated_at      DATETIME        NOT NULL,
            PRIMARY KEY (id),
            KEY restaurant_id (restaurant_id)
        ) $charset;");

        // ── STOCK MOVES ────────────────────────────────────
        dbDelta("CREATE TABLE {$wpdb->prefix}briga_stock_moves (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            restaurant_id   BIGINT UNSIGNED NOT NULL,
            item_id         BIGINT UNSIGNED NOT NULL,
            move_type       ENUM('in','out','offered','broken','adjustment') NOT NULL DEFAULT 'in',
            quantity        DECIMAL(10,2)   NOT NULL DEFAULT 0,
            note            TEXT            ,
            created_at      DATETIME        NOT NULL,
            PRIMARY KEY (id),
            KEY restaurant_id (restaurant_id),
            KEY item_id (item_id)
        ) $charset;");

        // ── DLC ────────────────────────────────────────────
        dbDelta("CREATE TABLE {$wpdb->prefix}briga_dlc (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            restaurant_id   BIGINT UNSIGNED NOT NULL,
            product_name    VARCHAR(255)    NOT NULL,
            quantity        DECIMAL(10,2)   DEFAULT 1,
            expiry_date     DATE            NOT NULL,
            zone            VARCHAR(50)     DEFAULT 'cuisine',
            created_at      DATETIME        NOT NULL,
            PRIMARY KEY (id),
            KEY restaurant_id (restaurant_id)
        ) $charset;");

        // ── CAISSE ─────────────────────────────────────────
        dbDelta("CREATE TABLE {$wpdb->prefix}briga_caisse (
            id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            restaurant_id       BIGINT UNSIGNED NOT NULL,
            report_date         DATE            NOT NULL,
            status              ENUM('draft','validated','closed') DEFAULT 'draft',
            covers_midi         INT             DEFAULT 0,
            ttc20_midi          DECIMAL(10,2)   DEFAULT 0,
            ttc10_midi          DECIMAL(10,2)   DEFAULT 0,
            ttc55_midi          DECIMAL(10,2)   DEFAULT 0,
            uber_midi           DECIMAL(10,2)   DEFAULT 0,
            deliveroo_midi      DECIMAL(10,2)   DEFAULT 0,
            remise_midi         DECIMAL(10,2)   DEFAULT 0,
            annulations_midi    DECIMAL(10,2)   DEFAULT 0,
            offerts_midi        DECIMAL(10,2)   DEFAULT 0,
            fonds_caisse_midi   DECIMAL(10,2)   DEFAULT 0,
            covers_day          INT             DEFAULT 0,
            ttc20_day           DECIMAL(10,2)   DEFAULT 0,
            ttc10_day           DECIMAL(10,2)   DEFAULT 0,
            ttc55_day           DECIMAL(10,2)   DEFAULT 0,
            uber_day            DECIMAL(10,2)   DEFAULT 0,
            deliveroo_day       DECIMAL(10,2)   DEFAULT 0,
            remise_day          DECIMAL(10,2)   DEFAULT 0,
            annulations_day     DECIMAL(10,2)   DEFAULT 0,
            offerts_day         DECIMAL(10,2)   DEFAULT 0,
            fonds_caisse_day    DECIMAL(10,2)   DEFAULT 0,
            ca_salle_midi       DECIMAL(10,2)   DEFAULT 0,
            ca_salle_day        DECIMAL(10,2)   DEFAULT 0,
            ticket_avg_midi     DECIMAL(10,2)   DEFAULT 0,
            ticket_avg_day      DECIMAL(10,2)   DEFAULT 0,
            note                TEXT            ,
            created_at          DATETIME        NOT NULL,
            updated_at          DATETIME        NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY restaurant_date (restaurant_id, report_date),
            KEY restaurant_id (restaurant_id)
        ) $charset;");

        // ── COMMANDES ──────────────────────────────────────
        dbDelta("CREATE TABLE {$wpdb->prefix}briga_commandes (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            restaurant_id   BIGINT UNSIGNED NOT NULL,
            supplier        ENUM('brake','brasseur') NOT NULL,
            status          ENUM('draft','validated','sent','received') DEFAULT 'draft',
            note            TEXT            ,
            created_at      DATETIME        NOT NULL,
            updated_at      DATETIME        NOT NULL,
            PRIMARY KEY (id),
            KEY restaurant_id (restaurant_id)
        ) $charset;");

        dbDelta("CREATE TABLE {$wpdb->prefix}briga_commande_lines (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            commande_id     BIGINT UNSIGNED NOT NULL,
            product_ref     VARCHAR(50)     DEFAULT '',
            product_name    VARCHAR(255)    NOT NULL,
            unit            VARCHAR(50)     DEFAULT '',
            quantity        DECIMAL(10,2)   DEFAULT 0,
            note            TEXT            ,
            PRIMARY KEY (id),
            KEY commande_id (commande_id)
        ) $charset;");

        // ── RAPPORTS PDF ───────────────────────────────────
        dbDelta("CREATE TABLE {$wpdb->prefix}briga_reports (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            restaurant_id   BIGINT UNSIGNED NOT NULL,
            report_date     DATE            NOT NULL,
            caisse_id       BIGINT UNSIGNED DEFAULT NULL,
            pdf_path        VARCHAR(500)    DEFAULT '',
            archive_status  ENUM('active','archived') DEFAULT 'active',
            generated_at    DATETIME        NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY restaurant_date (restaurant_id, report_date),
            KEY restaurant_id (restaurant_id)
        ) $charset;");

        // Colonnes verre/bouteille sur stock_items (ALTER si pas encore là)
        $wpdb->query("ALTER TABLE {$wpdb->prefix}briga_stock_items
            ADD COLUMN IF NOT EXISTS has_verre      TINYINT(1)     DEFAULT 0 AFTER zone,
            ADD COLUMN IF NOT EXISTS verres_par_btl TINYINT        DEFAULT 6 AFTER has_verre,
            ADD COLUMN IF NOT EXISTS stock_verres   DECIMAL(10,2)  DEFAULT 0 AFTER verres_par_btl
        ");

        // Table stock bar quotidien
        $sql_bar_daily = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}briga_stock_bar_daily (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            restaurant_id   BIGINT UNSIGNED NOT NULL DEFAULT 1,
            item_id         BIGINT UNSIGNED NOT NULL,
            report_date     DATE            NOT NULL,
            stock_matin_btl DECIMAL(10,2)   DEFAULT 0 COMMENT 'Stock bouteilles ouverture',
            stock_matin_verre DECIMAL(10,2) DEFAULT 0 COMMENT 'Reliquat verres ouverture',
            vendu_btl       DECIMAL(10,2)   DEFAULT 0,
            vendu_verre     INT             DEFAULT 0,
            offert_btl      DECIMAL(10,2)   DEFAULT 0,
            offert_verre    INT             DEFAULT 0,
            casse_btl       DECIMAL(10,2)   DEFAULT 0,
            casse_verre     INT             DEFAULT 0,
            stock_soir_btl  DECIMAL(10,2)   DEFAULT 0 COMMENT 'Calcule auto',
            stock_soir_verre INT            DEFAULT 0 COMMENT 'Reliquat calcule auto',
            note            TEXT,
            created_at      DATETIME        NOT NULL,
            updated_at      DATETIME,
            PRIMARY KEY (id),
            UNIQUE KEY item_date (item_id, report_date),
            KEY restaurant_id (restaurant_id),
            KEY report_date (report_date)
        ) $charset_collate;";
        dbDelta($sql_bar_daily);

        // Table Casse & Pertes

        $sql_casse = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}briga_casse (
            id              bigint(20) NOT NULL AUTO_INCREMENT,
            restaurant_id   int(11)    NOT NULL DEFAULT 1,
            label           varchar(255) NOT NULL,
            zone            varchar(50)  NOT NULL DEFAULT 'bar',
            type            varchar(50)  NOT NULL DEFAULT 'casse_materiel',
            quantity        decimal(10,2) NOT NULL DEFAULT 1,
            cost            decimal(10,2) NOT NULL DEFAULT 0,
            note            text,
            report_date     date         NOT NULL,
            created_at      datetime     NOT NULL,
            PRIMARY KEY (id),
            KEY restaurant_id (restaurant_id),
            KEY report_date (report_date),
            KEY zone (zone)
        ) $charset_collate;";
        dbDelta($sql_casse);


        // ── OFFERTS ────────────────────────────────────────────
        $sql_offerts = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}briga_offerts (
            id              bigint(20) NOT NULL AUTO_INCREMENT,
            restaurant_id   int(11)    NOT NULL DEFAULT 1,
            label           varchar(255) NOT NULL,
            beneficiaire    varchar(50)  NOT NULL DEFAULT 'client',
            zone            varchar(50)  NOT NULL DEFAULT 'bar',
            quantity        decimal(10,2) NOT NULL DEFAULT 1,
            cost            decimal(10,2) NOT NULL DEFAULT 0,
            note            text,
            report_date     date         NOT NULL,
            created_at      datetime     NOT NULL,
            PRIMARY KEY (id),
            KEY restaurant_id (restaurant_id),
            KEY report_date (report_date)
        ) $charset;";
        $wpdb->query($sql_offerts);

        // ── TABLE STATUS (Plan de salle — statuts temps réel) ──────────────────
        dbDelta("CREATE TABLE {$wpdb->prefix}briga_table_status (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            restaurant_id   BIGINT UNSIGNED NOT NULL DEFAULT 1,
            table_number    SMALLINT UNSIGNED NOT NULL,
            service_date    DATE            NOT NULL,
            status          ENUM('libre','reservee','occupee','nettoyage') NOT NULL DEFAULT 'libre',
            client_name     VARCHAR(255)    DEFAULT '',
            persons         TINYINT UNSIGNED DEFAULT 0,
            reservation_id  BIGINT UNSIGNED DEFAULT 0,
            merged_with     VARCHAR(100)    DEFAULT '' COMMENT 'ex: 8,9,10',
            group_code      VARCHAR(10)     DEFAULT '' COMMENT 'ex: G1, T2, C3',
            note            TEXT            ,
            created_at      DATETIME        NOT NULL,
            updated_at      DATETIME        NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY restaurant_table_date (restaurant_id, table_number, service_date),
            KEY restaurant_id (restaurant_id),
            KEY service_date (service_date),
            KEY status (status)
        ) $charset;");

        update_option('briga_db_version', self::VERSION);

        // Créer le restaurant par défaut si pas encore fait
        self::ensure_default_restaurant();
    }

    // ── RESTAURANT PAR DÉFAUT ──────────────────────────────
    public static function ensure_default_restaurant() {
        global $wpdb;
        $t = $wpdb->prefix . 'briga_restaurants';
        // Vérifier que la table existe avant de faire un SELECT
        if ($wpdb->get_var("SHOW TABLES LIKE '$t'") !== $t) return;
        $exists = (int)$wpdb->get_var("SELECT COUNT(*) FROM $t");
        if ($exists > 0) return;
        $wpdb->insert($t, [
            'name'         => 'Bistro Régent Caen',
            'slug'         => 'bistro-regent-caen',
            'address'      => '32 bis quai Vendeuvre, Caen',
            'manager_name' => 'Constantin',
            'is_active'    => 1,
            'created_at'   => current_time('mysql'),
            'updated_at'   => current_time('mysql'),
        ]);
    }

    // ── GET RESTAURANT ID ──────────────────────────────────
    // Pour l'instant toujours 1 — sera dynamique plus tard
    public static function get_restaurant_id() {
        return (int) get_option('briga_current_restaurant_id', 1);
    }

    // ── MIGRATION depuis wp_options ────────────────────────
    public static function migrate_from_options() {
        global $wpdb;
        $rid = self::get_restaurant_id();
        $now = current_time('mysql');

        // Tasks
        $old = get_option('briga_tasks', []);
        if (!empty($old) && is_array($old)) {
            foreach ($old as $t) {
                $wpdb->insert("{$wpdb->prefix}briga_tasks", [
                    'restaurant_id' => $rid,
                    'title'         => $t['title'] ?? '',
                    'priority'      => $t['priority'] ?? 'med',
                    'status'        => $t['status'] ?? 'todo',
                    'created_at'    => $now,
                ]);
            }
            delete_option('briga_tasks');
        }

        // Stock
        $old = get_option('briga_stock_items', []);
        if (!empty($old) && is_array($old)) {
            foreach ($old as $s) {
                $wpdb->insert("{$wpdb->prefix}briga_stock_items", [
                    'restaurant_id' => $rid,
                    'name'          => $s['name'] ?? '',
                    'unit'          => $s['unit'] ?? 'bouteille',
                    'stock_min'     => $s['threshold'] ?? 0,
                    'stock_current' => $s['quantity'] ?? 0,
                    'zone'          => $s['zone'] ?? '',
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
            delete_option('briga_stock_items');
            delete_option('briga_stock_moves');
        }

        // DLC
        $old = get_option('briga_dlc_items', []);
        if (!empty($old) && is_array($old)) {
            foreach ($old as $d) {
                $wpdb->insert("{$wpdb->prefix}briga_dlc", [
                    'restaurant_id' => $rid,
                    'product_name'  => $d['product_name'] ?? '',
                    'quantity'      => $d['quantity'] ?? 1,
                    'expiry_date'   => $d['expiry_date'] ?? date('Y-m-d'),
                    'zone'          => $d['zone'] ?? 'cuisine',
                    'created_at'    => $now,
                ]);
            }
            delete_option('briga_dlc_items');
        }

        // Caisse
        $old = get_option('briga_caisse_records', []);
        if (!empty($old) && is_array($old)) {
            foreach ($old as $r) {
                $midi = $r['midi'] ?? [];
                $day  = $r['journee'] ?? [];
                $c    = $r['calculs'] ?? [];
                $wpdb->insert("{$wpdb->prefix}briga_caisse", [
                    'restaurant_id'    => $rid,
                    'report_date'      => $r['date'] ?? date('Y-m-d'),
                    'covers_midi'      => $midi['couverts'] ?? 0,
                    'ttc20_midi'       => $midi['ttc20'] ?? 0,
                    'ttc10_midi'       => $midi['ttc10'] ?? 0,
                    'ttc55_midi'       => $midi['ttc55'] ?? 0,
                    'uber_midi'        => $midi['uber'] ?? 0,
                    'deliveroo_midi'   => $midi['deli'] ?? 0,
                    'remise_midi'      => $midi['remises'] ?? 0,
                    'annulations_midi' => $midi['annulations'] ?? 0,
                    'offerts_midi'     => $midi['offerts'] ?? 0,
                    'covers_day'       => $day['couverts'] ?? 0,
                    'ttc20_day'        => $day['ttc20'] ?? 0,
                    'ttc10_day'        => $day['ttc10'] ?? 0,
                    'ttc55_day'        => $day['ttc55'] ?? 0,
                    'uber_day'         => $day['uber'] ?? 0,
                    'deliveroo_day'    => $day['deli'] ?? 0,
                    'remise_day'       => $day['remises'] ?? 0,
                    'annulations_day'  => $day['annulations'] ?? 0,
                    'offerts_day'      => $day['offerts'] ?? 0,
                    'ca_salle_midi'    => $c['ca_midi'] ?? 0,
                    'ca_salle_day'     => $c['ca_salle'] ?? 0,
                    'ticket_avg_midi'  => $c['ticket_midi'] ?? 0,
                    'ticket_avg_day'   => $c['ticket_moyen'] ?? 0,
                    'created_at'       => $r['created_at'] ?? $now,
                    'updated_at'       => $now,
                ]);
            }
            delete_option('briga_caisse_records');
        }

        // Commandes
        $old = get_option('briga_commandes', []);
        if (!empty($old) && is_array($old)) {
            foreach ($old as $o) {
                $wpdb->insert("{$wpdb->prefix}briga_commandes", [
                    'restaurant_id' => $rid,
                    'supplier'      => $o['supplier'] ?? 'brake',
                    'status'        => $o['status'] ?? 'draft',
                    'note'          => $o['note'] ?? '',
                    'created_at'    => $o['created_at'] ?? $now,
                    'updated_at'    => $now,
                ]);
                $cmd_id = $wpdb->insert_id;
                foreach (($o['lines'] ?? []) as $l) {
                    $wpdb->insert("{$wpdb->prefix}briga_commande_lines", [
                        'commande_id'  => $cmd_id,
                        'product_name' => $l['name'] ?? '',
                        'unit'         => $l['unit'] ?? '',
                        'quantity'     => $l['qty'] ?? 0,
                    ]);
                }
            }
            delete_option('briga_commandes');
        }

        update_option('briga_migrated', '2.0');
    }

    // ── MIGRATIONS COLONNES ──────────────────────────────
    public static function run_column_migrations() {
        global $wpdb;

        // Recréer task_logs si elle existe mais est vide/invalide (erreur SQL TEXT DEFAULT)
        $tl = $wpdb->prefix . 'briga_task_logs';
        if ($wpdb->get_var("SHOW TABLES LIKE '$tl'") === $tl) {
            // Vérifier que la table a bien la colonne 'note'
            $tl_cols = $wpdb->get_col("SHOW COLUMNS FROM $tl");
            if (empty($tl_cols)) {
                // Table corrompue — la supprimer et recréer
                $wpdb->query("DROP TABLE IF EXISTS $tl");
                self::create_task_logs_table();
            }
        } else {
            self::create_task_logs_table();
        }

        // verres_par_btl dans stock_items
        $cols = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}briga_stock_items", ARRAY_A);
        $col_names = array_column($cols, 'Field');
        if (!in_array('has_verre', $col_names)) {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}briga_stock_items ADD COLUMN has_verre TINYINT(1) DEFAULT 0 AFTER zone");
        }
        if (!in_array('verres_par_btl', $col_names)) {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}briga_stock_items ADD COLUMN verres_par_btl TINYINT UNSIGNED DEFAULT 6 AFTER has_verre");
        }

        // Colonnes tâches v3 (workflow employé)
        $tcols_raw = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}briga_tasks", ARRAY_A);
        $tcols = array_column($tcols_raw ?: [], 'Field');
        $task_migrations = [
            "ALTER TABLE {$wpdb->prefix}briga_tasks MODIFY COLUMN status ENUM('libre','prise','a_valider','validee','en_retard') NOT NULL DEFAULT 'libre'",
            "ALTER TABLE {$wpdb->prefix}briga_tasks ADD COLUMN frequency ENUM('daily','weekly','once') DEFAULT 'daily' AFTER zone",
            "ALTER TABLE {$wpdb->prefix}briga_tasks ADD COLUMN day_of_week TINYINT DEFAULT NULL AFTER frequency",
            "ALTER TABLE {$wpdb->prefix}briga_tasks ADD COLUMN taken_by VARCHAR(100) DEFAULT '' AFTER day_of_week",
            "ALTER TABLE {$wpdb->prefix}briga_tasks ADD COLUMN taken_at DATETIME DEFAULT NULL AFTER taken_by",
            "ALTER TABLE {$wpdb->prefix}briga_tasks ADD COLUMN done_by VARCHAR(100) DEFAULT '' AFTER taken_at",
            "ALTER TABLE {$wpdb->prefix}briga_tasks ADD COLUMN validated_by VARCHAR(100) DEFAULT '' AFTER done_by",
            "ALTER TABLE {$wpdb->prefix}briga_tasks ADD COLUMN validated_at DATETIME DEFAULT NULL AFTER validated_by",
            "ALTER TABLE {$wpdb->prefix}briga_tasks ADD COLUMN target_date DATE DEFAULT NULL AFTER validated_at",
            "ALTER TABLE {$wpdb->prefix}briga_tasks ADD COLUMN employee_role ENUM('manager','salle','bar','cuisine','all') DEFAULT 'all' AFTER target_date",
        ];
        $new_cols = ['frequency','day_of_week','taken_by','taken_at','done_by','validated_by','validated_at','target_date','employee_role'];
        foreach ($new_cols as $i => $col) {
            if (!in_array($col, $tcols)) {
                $wpdb->query($task_migrations[$i + 1]); // +1 car index 0 = MODIFY status
            }
        }
        // Toujours tenter le MODIFY status (idempotent via dbDelta ne le fait pas)
        if (in_array('status', $tcols)) {
            $wpdb->query($task_migrations[0]);
        }
    }

    // ── TABLE TASK LOGS ───────────────────────────────────
    public static function create_task_logs_table() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $tl = $wpdb->prefix . 'briga_task_logs';

        // Vérifier si la table existe déjà avec la bonne structure
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$tl'");
        if ($exists) return; // Déjà créée

        // Utiliser query directe plutôt que dbDelta (évite l'erreur TEXT DEFAULT)
        $wpdb->query("CREATE TABLE IF NOT EXISTS $tl (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            restaurant_id   BIGINT UNSIGNED NOT NULL DEFAULT 1,
            task_id         BIGINT UNSIGNED NOT NULL,
            task_title      VARCHAR(255) NOT NULL DEFAULT '',
            zone            VARCHAR(50) NOT NULL DEFAULT '',
            employee_name   VARCHAR(100) NOT NULL DEFAULT '',
            employee_role   VARCHAR(50) NOT NULL DEFAULT '',
            action          ENUM('taken','done','validated','rejected') NOT NULL,
            note            TEXT,
            log_date        DATE NOT NULL,
            log_at          DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY task_id (task_id),
            KEY restaurant_id (restaurant_id),
            KEY log_date (log_date),
            KEY employee_name (employee_name)
        ) $charset");
    }

    // ── HELPER ────────────────────────────────────────────
    public static function table($name) {
        global $wpdb;
        return $wpdb->prefix . 'briga_' . $name;
    }
}
