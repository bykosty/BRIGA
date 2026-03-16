<?php
/**
 * BRIGA PRO — Module Tâches v3.0
 * Workflow: libre → prise → a_valider → validee / en_retard
 * Multi-logs par tâche, filtrage par rôle, couleurs jour de semaine
 * Constantin BARNA — Bistro Régent Caen
 */
if (!defined('ABSPATH')) exit;

class BRIGA_Tasks {

    // ── Statuts workflow ──────────────────────────────────────────────────
    const S_LIBRE      = 'libre';
    const S_PRISE      = 'prise';
    const S_A_VALIDER  = 'a_valider';
    const S_VALIDEE    = 'validee';
    const S_EN_RETARD  = 'en_retard';

    // ── Zones visibles par rôle ───────────────────────────────────────────
    const ROLE_ZONES = [
        'manager' => ['bar','salle','vestiaires','cuisine','general'],
        'salle'   => ['salle','bar','vestiaires','general'],
        'bar'     => ['bar','salle','vestiaires','general'],
        'cuisine' => ['cuisine','general'],
        'all'     => ['bar','salle','vestiaires','cuisine','general'],
    ];

    // ── Catalogue officiel des tâches BRIGA ──────────────────────────────
    const CATALOGUE = [
        // BAR
        ['title'=>'Machine à glace',              'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Machine chantilly',             'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Frigo dessert',                 'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Frigo boissons',                'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Dessus machine café',           'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Porte cave à vin',              'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Tapis verre bière + plan travail','zone'=>'bar','frequency'=>'daily'],
        ['title'=>'Tapis verre soft + sirop',      'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Portes placards',               'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Sous évier',                    'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Étagères sous bar',             'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Vitres + étagères',             'zone'=>'bar', 'frequency'=>'weekly'],
        ['title'=>'Étagère au-dessus machine glace','zone'=>'bar','frequency'=>'weekly'],
        ['title'=>'Devanture bar',                 'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Placard vins',                  'zone'=>'bar', 'frequency'=>'weekly'],
        ['title'=>'Poubelles bar',                 'zone'=>'bar', 'frequency'=>'daily'],
        ['title'=>'Dessus cave à vins',            'zone'=>'bar', 'frequency'=>'weekly'],
        ['title'=>'Luminaires bar',                'zone'=>'bar', 'frequency'=>'weekly'],
        ['title'=>'Dessous machine chantilly',     'zone'=>'bar', 'frequency'=>'weekly'],
        // SALLE
        ['title'=>'Consoles + écrans',             'zone'=>'salle','frequency'=>'daily'],
        ['title'=>'Vestiaires',                    'zone'=>'salle','frequency'=>'daily'],
        ['title'=>'SAS',                           'zone'=>'salle','frequency'=>'daily'],
        ['title'=>'Réchauds',                      'zone'=>'salle','frequency'=>'daily'],
        ['title'=>'Pieds tables + chaises',        'zone'=>'salle','frequency'=>'weekly'],
        ['title'=>'Banquettes',                    'zone'=>'salle','frequency'=>'weekly'],
        ['title'=>'Plinthes salle',                'zone'=>'salle','frequency'=>'weekly'],
        ['title'=>'Aérations',                     'zone'=>'salle','frequency'=>'weekly'],
        ['title'=>'Tiroirs couverts',              'zone'=>'salle','frequency'=>'daily'],
        ['title'=>'Pots fleurs',                   'zone'=>'salle','frequency'=>'daily'],
        ['title'=>'Poubelles consoles',            'zone'=>'salle','frequency'=>'daily'],
        ['title'=>'Lustres',                       'zone'=>'salle','frequency'=>'weekly'],
        ['title'=>'Chaises enfants',               'zone'=>'salle','frequency'=>'weekly'],
        ['title'=>'Salières / poivrières',         'zone'=>'salle','frequency'=>'daily'],
        ['title'=>'Affichages',                    'zone'=>'salle','frequency'=>'weekly'],
        ['title'=>'Porte manteau',                 'zone'=>'salle','frequency'=>'daily'],
        ['title'=>'Extincteurs',                   'zone'=>'salle','frequency'=>'weekly'],
        ['title'=>'Poubelles toilettes clients',   'zone'=>'salle','frequency'=>'daily'],
        // VESTIAIRES
        ['title'=>'Bancs vestiaires',              'zone'=>'vestiaires','frequency'=>'daily'],
        ['title'=>'Casiers',                       'zone'=>'vestiaires','frequency'=>'daily'],
        ['title'=>'Portes casiers',                'zone'=>'vestiaires','frequency'=>'weekly'],
        ['title'=>'Poignées',                      'zone'=>'vestiaires','frequency'=>'weekly'],
        ['title'=>'Sol vestiaires',                'zone'=>'vestiaires','frequency'=>'daily'],
        ['title'=>'Poubelles vestiaires',          'zone'=>'vestiaires','frequency'=>'daily'],
        ['title'=>'Plinthes vestiaires',           'zone'=>'vestiaires','frequency'=>'weekly'],
        ['title'=>'Luminaires vestiaires',         'zone'=>'vestiaires','frequency'=>'weekly'],
        // CUISINE (quotidien)
        ['title'=>'Plans de travail',              'zone'=>'cuisine','frequency'=>'daily'],
        ['title'=>'Tables préparation',            'zone'=>'cuisine','frequency'=>'daily'],
        ['title'=>'Machines cuisine',              'zone'=>'cuisine','frequency'=>'daily'],
        ['title'=>'Frigos cuisine',                'zone'=>'cuisine','frequency'=>'daily'],
        ['title'=>'Chambres froides',              'zone'=>'cuisine','frequency'=>'daily'],
        ['title'=>'Hotte',                         'zone'=>'cuisine','frequency'=>'daily'],
        ['title'=>'Plonge',                        'zone'=>'cuisine','frequency'=>'daily'],
        ['title'=>'Évier cuisine',                 'zone'=>'cuisine','frequency'=>'daily'],
        ['title'=>'Sol cuisine',                   'zone'=>'cuisine','frequency'=>'daily'],
        ['title'=>'Sous meubles cuisine',          'zone'=>'cuisine','frequency'=>'weekly'],
        ['title'=>'Poubelles cuisine',             'zone'=>'cuisine','frequency'=>'daily'],
        ['title'=>'Vérification DLC',              'zone'=>'cuisine','frequency'=>'daily'],
        // TÂCHES PLANIFIÉES
        ['title'=>'Sortir consignes Coca',         'zone'=>'bar', 'frequency'=>'weekly','day_of_week'=>1], // Lundi
        ['title'=>'Sortir fûts bière 1664',        'zone'=>'bar', 'frequency'=>'weekly','day_of_week'=>1],
        ['title'=>'Nettoyage égouts cuisine',      'zone'=>'cuisine','frequency'=>'weekly','day_of_week'=>1],
        ['title'=>'Sortir eau gazeuse',            'zone'=>'bar', 'frequency'=>'weekly','day_of_week'=>2], // Mardi
        ['title'=>'Sortir bouteilles Evian',       'zone'=>'bar', 'frequency'=>'weekly','day_of_week'=>2],
        ['title'=>'Sortir fûts Metro',             'zone'=>'bar', 'frequency'=>'weekly','day_of_week'=>2],
    ];

    public static function register_routes() {
        $ns = 'briga/v1';
        register_rest_route($ns, '/tasks',                     ['methods'=>'GET',   'callback'=>[__CLASS__,'get_tasks'],    'permission_callback'=>'__return_true']);
        register_rest_route($ns, '/tasks/seed',                ['methods'=>'POST',  'callback'=>[__CLASS__,'seed_tasks'],   'permission_callback'=>'__return_true']);
        register_rest_route($ns, '/tasks/(?P<id>\d+)/take',    ['methods'=>'POST',  'callback'=>[__CLASS__,'take_task'],    'permission_callback'=>'__return_true']);
        register_rest_route($ns, '/tasks/(?P<id>\d+)/done',    ['methods'=>'POST',  'callback'=>[__CLASS__,'done_task'],    'permission_callback'=>'__return_true']);
        register_rest_route($ns, '/tasks/(?P<id>\d+)/validate',['methods'=>'POST',  'callback'=>[__CLASS__,'validate_task'],'permission_callback'=>'__return_true']);
        register_rest_route($ns, '/tasks/(?P<id>\d+)/reject',  ['methods'=>'POST',  'callback'=>[__CLASS__,'reject_task'],  'permission_callback'=>'__return_true']);
        register_rest_route($ns, '/tasks/(?P<id>\d+)/reset',   ['methods'=>'POST',  'callback'=>[__CLASS__,'reset_task'],   'permission_callback'=>'__return_true']);
        register_rest_route($ns, '/tasks/(?P<id>\d+)',         ['methods'=>'DELETE','callback'=>[__CLASS__,'delete_task'],  'permission_callback'=>'__return_true']);
        register_rest_route($ns, '/tasks/create',              ['methods'=>'POST',  'callback'=>[__CLASS__,'create_task'],  'permission_callback'=>'__return_true']);
        register_rest_route($ns, '/tasks/logs',                ['methods'=>'GET',   'callback'=>[__CLASS__,'get_logs'],     'permission_callback'=>'__return_true']);
        register_rest_route($ns, '/tasks/summary',             ['methods'=>'GET',   'callback'=>[__CLASS__,'get_summary'],  'permission_callback'=>'__return_true']);
    }

    // ── GET /tasks ────────────────────────────────────────────────────────
    public static function get_tasks($request) {
        global $wpdb;
        $rid   = BRIGA_DB::get_restaurant_id();
        $t     = $wpdb->prefix.'briga_tasks';
        $today = current_time('Y-m-d');
        $dow   = (int)date('N', current_time('timestamp')); // 1=Lun…7=Dim

        $zone_filter = sanitize_text_field($request->get_param('zone') ?? '');
        $role_filter = sanitize_text_field($request->get_param('role') ?? 'all');
        $freq_filter = sanitize_text_field($request->get_param('frequency') ?? '');

        // Auto-marquer en retard
        $wpdb->query($wpdb->prepare(
            "UPDATE $t SET status='en_retard'
             WHERE restaurant_id=%d AND status IN ('libre','prise')
             AND target_date IS NOT NULL AND target_date < %s",
            $rid, $today
        ));

        $where = $wpdb->prepare("WHERE restaurant_id=%d", $rid);

        // Filtre zone selon rôle
        $allowed_zones = self::ROLE_ZONES[$role_filter] ?? self::ROLE_ZONES['all'];
        $zone_placeholders = implode(',', array_fill(0, count($allowed_zones), '%s'));
        $where .= $wpdb->prepare(" AND zone IN ($zone_placeholders)", ...$allowed_zones);

        if ($zone_filter) $where .= $wpdb->prepare(" AND zone=%s", $zone_filter);
        if ($freq_filter) $where .= $wpdb->prepare(" AND frequency=%s", $freq_filter);

        $tasks = $wpdb->get_results("SELECT * FROM $t $where ORDER BY zone, frequency, title", ARRAY_A);
        if ($wpdb->last_error) return new WP_Error('db',$wpdb->last_error,['status'=>500]);

        // Enrichir avec couleur jour de semaine
        foreach ($tasks as &$task) {
            $task['day_color'] = self::day_color($dow);
            $task['is_overdue'] = ($task['status'] === self::S_EN_RETARD) ? 1 : 0;
        }
        unset($task);

        // Stats rapides
        $counts = ['libre'=>0,'prise'=>0,'a_valider'=>0,'validee'=>0,'en_retard'=>0];
        foreach ($tasks as $t2) $counts[$t2['status']] = ($counts[$t2['status']]??0)+1;

        return rest_ensure_response([
            'tasks'   => $tasks,
            'counts'  => $counts,
            'today'   => $today,
            'dow'     => $dow,
            'day_color' => self::day_color($dow),
        ]);
    }

    // ── POST /tasks/(?P<id>\d+)/take ─────────────────────────────────────
    public static function take_task($request) {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $id  = (int)$request['id'];
        $p   = $request->get_json_params();
        $emp = sanitize_text_field($p['employee_name'] ?? 'Inconnu');
        $rol = sanitize_text_field($p['employee_role'] ?? 'salle');
        $now = current_time('mysql');

        $task = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}briga_tasks WHERE id=%d AND restaurant_id=%d", $id, $rid
        ), ARRAY_A);
        if (!$task) return new WP_Error('not_found','Tâche introuvable',['status'=>404]);

        // Autoriser reprise si libre ou en_retard
        if (!in_array($task['status'], [self::S_LIBRE, self::S_EN_RETARD])) {
            return new WP_Error('conflict','Tâche déjà prise',['status'=>409]);
        }

        $wpdb->update($wpdb->prefix.'briga_tasks',
            ['status'=>self::S_PRISE, 'taken_by'=>$emp, 'taken_at'=>$now, 'done_by'=>'', 'done_at'=>null],
            ['id'=>$id, 'restaurant_id'=>$rid]
        );

        self::log($rid, $id, $task['title'], $task['zone'], $emp, $rol, 'taken');
        return rest_ensure_response(['success'=>true,'status'=>self::S_PRISE,'taken_by'=>$emp]);
    }

    // ── POST /tasks/(?P<id>\d+)/done ─────────────────────────────────────
    public static function done_task($request) {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $id  = (int)$request['id'];
        $p   = $request->get_json_params();
        $emp = sanitize_text_field($p['employee_name'] ?? 'Inconnu');
        $rol = sanitize_text_field($p['employee_role'] ?? 'salle');
        $now = current_time('mysql');

        $task = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}briga_tasks WHERE id=%d AND restaurant_id=%d", $id, $rid
        ), ARRAY_A);
        if (!$task) return new WP_Error('not_found','Tâche introuvable',['status'=>404]);
        if (!in_array($task['status'], [self::S_PRISE, self::S_EN_RETARD])) {
            return new WP_Error('conflict','Marquer d\'abord comme prise',['status'=>409]);
        }

        $wpdb->update($wpdb->prefix.'briga_tasks',
            ['status'=>self::S_A_VALIDER, 'done_by'=>$emp, 'done_at'=>$now],
            ['id'=>$id, 'restaurant_id'=>$rid]
        );

        self::log($rid, $id, $task['title'], $task['zone'], $emp, $rol, 'done');
        return rest_ensure_response(['success'=>true,'status'=>self::S_A_VALIDER,'done_by'=>$emp]);
    }

    // ── POST /tasks/(?P<id>\d+)/validate ─────────────────────────────────
    public static function validate_task($request) {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $id  = (int)$request['id'];
        $p   = $request->get_json_params();
        $mgr = sanitize_text_field($p['manager_name'] ?? 'Manager');
        $now = current_time('mysql');

        $task = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}briga_tasks WHERE id=%d AND restaurant_id=%d", $id, $rid
        ), ARRAY_A);
        if (!$task) return new WP_Error('not_found','Tâche introuvable',['status'=>404]);
        if ($task['status'] !== self::S_A_VALIDER) {
            return new WP_Error('conflict','Tâche pas encore marquée comme faite',['status'=>409]);
        }

        $wpdb->update($wpdb->prefix.'briga_tasks',
            ['status'=>self::S_VALIDEE, 'validated_by'=>$mgr, 'validated_at'=>$now],
            ['id'=>$id, 'restaurant_id'=>$rid]
        );

        self::log($rid, $id, $task['title'], $task['zone'], $mgr, 'manager', 'validated');

        // Si tâche quotidienne/hebdomadaire → remettre en libre pour demain
        if (in_array($task['frequency'] ?? 'daily', ['daily','weekly'])) {
            // On garde la trace dans logs, la tâche sera remise libre à minuit (ou au prochain seed)
        }

        return rest_ensure_response(['success'=>true,'status'=>self::S_VALIDEE,'validated_by'=>$mgr]);
    }

    // ── POST /tasks/(?P<id>\d+)/reject ───────────────────────────────────
    public static function reject_task($request) {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $id  = (int)$request['id'];
        $p   = $request->get_json_params();
        $mgr = sanitize_text_field($p['manager_name'] ?? 'Manager');
        $note= sanitize_textarea_field($p['note'] ?? '');
        $now = current_time('mysql');

        $task = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}briga_tasks WHERE id=%d AND restaurant_id=%d", $id, $rid
        ), ARRAY_A);
        if (!$task) return new WP_Error('not_found','Tâche introuvable',['status'=>404]);

        $wpdb->update($wpdb->prefix.'briga_tasks',
            ['status'=>self::S_LIBRE, 'taken_by'=>'', 'taken_at'=>null, 'done_by'=>'', 'done_at'=>null, 'validated_by'=>'', 'validated_at'=>null, 'note'=>$note],
            ['id'=>$id, 'restaurant_id'=>$rid]
        );

        self::log($rid, $id, $task['title'], $task['zone'], $mgr, 'manager', 'rejected', $note);
        return rest_ensure_response(['success'=>true,'status'=>self::S_LIBRE,'note'=>$note]);
    }

    // ── POST /tasks/(?P<id>\d+)/reset ────────────────────────────────────
    public static function reset_task($request) {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $id  = (int)$request['id'];

        $wpdb->update($wpdb->prefix.'briga_tasks',
            ['status'=>self::S_LIBRE,'taken_by'=>'','taken_at'=>null,'done_by'=>'','done_at'=>null,'validated_by'=>'','validated_at'=>null],
            ['id'=>$id,'restaurant_id'=>$rid]
        );
        return rest_ensure_response(['success'=>true,'status'=>self::S_LIBRE]);
    }

    // ── POST /tasks/create ────────────────────────────────────────────────
    public static function create_task($request) {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $p   = $request->get_json_params();
        $now = current_time('mysql');

        $data = [
            'restaurant_id' => $rid,
            'title'         => sanitize_text_field($p['title'] ?? ''),
            'zone'          => sanitize_text_field($p['zone'] ?? 'general'),
            'priority'      => sanitize_text_field($p['priority'] ?? 'med'),
            'frequency'     => sanitize_text_field($p['frequency'] ?? 'daily'),
            'status'        => self::S_LIBRE,
            'shift'         => sanitize_text_field($p['shift'] ?? ''),
            'note'          => sanitize_textarea_field($p['note'] ?? ''),
            'target_date'   => sanitize_text_field($p['target_date'] ?? ''),
            'employee_role' => sanitize_text_field($p['employee_role'] ?? 'all'),
            'created_at'    => $now,
            'done_at'       => null,
        ];
        if (empty($data['title'])) return new WP_Error('invalid','Titre requis',['status'=>400]);

        $wpdb->insert($wpdb->prefix.'briga_tasks', $data);
        $id = $wpdb->insert_id;
        return rest_ensure_response(['success'=>true,'id'=>$id]);
    }

    // ── DELETE /tasks/(?P<id>\d+) ─────────────────────────────────────────
    public static function delete_task($request) {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $id  = (int)$request['id'];
        $wpdb->delete($wpdb->prefix.'briga_tasks', ['id'=>$id,'restaurant_id'=>$rid]);
        return rest_ensure_response(['success'=>true]);
    }

    // ── POST /tasks/seed ──────────────────────────────────────────────────
    // Initialiser/régénérer les tâches du catalogue pour aujourd'hui
    public static function seed_tasks($request) {
        global $wpdb;
        $rid   = BRIGA_DB::get_restaurant_id();
        $t     = $wpdb->prefix.'briga_tasks';
        $today = current_time('Y-m-d');
        $now   = current_time('mysql');
        $dow   = (int)date('N', current_time('timestamp'));
        $inserted = 0;

        foreach (self::CATALOGUE as $cat) {
            $freq = $cat['frequency'] ?? 'daily';
            $cat_dow = $cat['day_of_week'] ?? null;

            // Tâches hebdomadaires planifiées : seulement si c'est le bon jour
            if ($freq === 'weekly' && $cat_dow !== null && $cat_dow !== $dow) continue;

            // Vérifier si la tâche existe déjà aujourd'hui (même titre + zone)
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $t WHERE restaurant_id=%d AND title=%s AND zone=%s
                 AND (target_date=%s OR target_date IS NULL)
                 AND status NOT IN ('validee') LIMIT 1",
                $rid, $cat['title'], $cat['zone'], $today
            ));
            if ($exists) continue;

            // Remettre en libre si validée hier
            $old = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $t WHERE restaurant_id=%d AND title=%s AND zone=%s LIMIT 1",
                $rid, $cat['title'], $cat['zone']
            ));

            if ($old) {
                $wpdb->update($t,
                    ['status'=>self::S_LIBRE,'taken_by'=>'','taken_at'=>null,'done_by'=>'','done_at'=>null,'validated_by'=>'','validated_at'=>null,'target_date'=>$today,'updated_at'=>$now],
                    ['id'=>$old,'restaurant_id'=>$rid]
                );
            } else {
                $wpdb->insert($t, [
                    'restaurant_id' => $rid,
                    'title'         => $cat['title'],
                    'zone'          => $cat['zone'],
                    'frequency'     => $freq,
                    'day_of_week'   => $cat_dow,
                    'priority'      => 'med',
                    'status'        => self::S_LIBRE,
                    'shift'         => '',
                    'note'          => '',
                    'target_date'   => $today,
                    'employee_role' => 'all',
                    'created_at'    => $now,
                    'done_at'       => null,
                ]);
                $inserted++;
            }
        }
        return rest_ensure_response(['success'=>true,'inserted'=>$inserted,'today'=>$today,'dow'=>$dow]);
    }

    // ── GET /tasks/logs ───────────────────────────────────────────────────
    public static function get_logs($request) {
        global $wpdb;
        $rid   = BRIGA_DB::get_restaurant_id();
        $tl    = $wpdb->prefix.'briga_task_logs';
        $days  = absint($request->get_param('days') ?? 7);
        $emp   = sanitize_text_field($request->get_param('employee') ?? '');
        $zone  = sanitize_text_field($request->get_param('zone') ?? '');
        $from  = date('Y-m-d', strtotime("-{$days} days", current_time('timestamp')));

        $where = $wpdb->prepare("WHERE restaurant_id=%d AND log_date>=%s", $rid, $from);
        if ($emp)  $where .= $wpdb->prepare(" AND employee_name LIKE %s", '%'.$emp.'%');
        if ($zone) $where .= $wpdb->prepare(" AND zone=%s", $zone);

        $logs = $wpdb->get_results("SELECT * FROM $tl $where ORDER BY log_at DESC LIMIT 200", ARRAY_A);
        return rest_ensure_response(['logs'=>$logs,'from'=>$from,'days'=>$days]);
    }

    // ── GET /tasks/summary ────────────────────────────────────────────────
    public static function get_summary($request) {
        global $wpdb;
        $rid  = BRIGA_DB::get_restaurant_id();
        $t    = $wpdb->prefix.'briga_tasks';
        $tl   = $wpdb->prefix.'briga_task_logs';
        $today= current_time('Y-m-d');

        $counts = $wpdb->get_results($wpdb->prepare(
            "SELECT status, COUNT(*) as cnt FROM $t WHERE restaurant_id=%d GROUP BY status", $rid
        ), ARRAY_A);
        $c = array_column($counts,'cnt','status');

        $top = $wpdb->get_results($wpdb->prepare(
            "SELECT employee_name, COUNT(*) as total
             FROM $tl WHERE restaurant_id=%d AND action='validated' AND log_date>=%s
             GROUP BY employee_name ORDER BY total DESC LIMIT 5",
            $rid, date('Y-m-d', strtotime('-30 days', current_time('timestamp')))
        ), ARRAY_A);

        return rest_ensure_response([
            'today'        => $today,
            'libre'        => (int)($c['libre']??0),
            'prise'        => (int)($c['prise']??0),
            'a_valider'    => (int)($c['a_valider']??0),
            'validee'      => (int)($c['validee']??0),
            'en_retard'    => (int)($c['en_retard']??0),
            'top_employees'=> $top,
        ]);
    }

    // ── HELPER : couleur jour de semaine ─────────────────────────────────
    public static function day_color(int $dow): string {
        if ($dow <= 3) return 'blue';    // Lun–Mer : normal
        if ($dow <= 5) return 'yellow';  // Jeu–Ven : priorité
        return 'red';                    // Sam–Dim : critique
    }

    // ── HELPER : log ─────────────────────────────────────────────────────
    private static function log(int $rid, int $task_id, string $title, string $zone,
                                 string $employee, string $role, string $action, string $note='') {
        global $wpdb;
        $tl = $wpdb->prefix.'briga_task_logs';
        // Vérifier que la table existe
        if ($wpdb->get_var("SHOW TABLES LIKE '$tl'") !== $tl) return;
        $wpdb->insert($tl, [
            'restaurant_id' => $rid,
            'task_id'       => $task_id,
            'task_title'    => $title,
            'zone'          => $zone,
            'employee_name' => $employee,
            'employee_role' => $role,
            'action'        => $action,
            'note'          => $note,
            'log_date'      => current_time('Y-m-d'),
            'log_at'        => current_time('mysql'),
        ]);
    }
}
