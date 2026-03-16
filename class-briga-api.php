<?php
if (!defined('ABSPATH')) exit;

class BRIGA_API {

    public static function register_routes() {
        // ── Endpoint de diagnostic ─────────────────────────────────────────
        register_rest_route('briga/v1', '/status', ['methods'=>'GET','callback'=>[__CLASS__,'get_status'],'permission_callback'=>'__return_true']);
        register_rest_route('briga/v1', '/dashboard',   ['methods'=>'GET','callback'=>[__CLASS__,'get_dashboard'],  'permission_callback'=>'__return_true']);
        // Stock géré par BRIGA_Stock
        register_rest_route('briga/v1', '/dlc',          ['methods'=>'GET','callback'=>[__CLASS__,'get_dlc'],        'permission_callback'=>'__return_true']);
        register_rest_route('briga/v1', '/dlc',          ['methods'=>'POST','callback'=>[__CLASS__,'post_dlc'],      'permission_callback'=>'__return_true']);
        register_rest_route('briga/v1', '/alerts',       ['methods'=>'GET','callback'=>[__CLASS__,'get_alerts'],     'permission_callback'=>'__return_true']);
        // Routes /tasks gérées par BRIGA_Tasks (class-briga-tasks.php)
    }

    // ── TASKS (SQL) ────────────────────────────────────────
    // Fonction get_tasks supprimée — géré par BRIGA_Tasks

    // Fonction post_tasks supprimée — géré par BRIGA_Tasks

    // Fonction post_tasks_toggle supprimée — géré par BRIGA_Tasks

    // Fonction post_tasks_delete supprimée — géré par BRIGA_Tasks

    public static function ensure_defaults() {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $t   = BRIGA_DB::table('stock_items');
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t WHERE restaurant_id=%d", $rid));
        if ($count > 0) return; // Déjà initialisé
        $now = current_time('mysql');
        // [name, category, unit, zone, stock_min, display_order]
        $products = [
            ["Pessac Léognan rouge 75cl","vin_rouge","bouteille","bar",2,1],
            ["Pessac Léognan rouge 37,5cl","vin_rouge","bouteille","bar",6,2],
            ["Verre vin rouge","vin_rouge","verre","bar",0,3],
            ["Merlet rouge 75cl","vin_rouge","bouteille","bar",2,4],
            ["Merlet rouge 37,5cl","vin_rouge","bouteille","bar",6,5],
            ["Verre Merlet rouge","vin_rouge","verre","bar",0,6],
            ["Cotes de Provence 75cl","vin_rose","bouteille","bar",2,7],
            ["Cotes de Provence 37,5cl","vin_rose","bouteille","bar",6,8],
            ["Verre vin rose","vin_rose","verre","bar",0,9],
            ["Merlet rose 75cl","vin_rose","bouteille","bar",2,10],
            ["Merlet rose 37,5cl","vin_rose","bouteille","bar",6,11],
            ["Verre Merlet rose","vin_rose","verre","bar",0,12],
            ["Pessac Leognan blanc 75cl","vin_blanc","bouteille","bar",2,13],
            ["Pessac Leognan blanc 37,5cl","vin_blanc","bouteille","bar",6,14],
            ["Verre vin blanc","vin_blanc","verre","bar",0,15],
            ["Merlet blanc 75cl","vin_blanc","bouteille","bar",2,16],
            ["Merlet blanc 37,5cl","vin_blanc","bouteille","bar",6,17],
            ["Verre Merlet blanc","vin_blanc","verre","bar",0,18],
            ["Colombard Ugni Blanc","vin_blanc","bouteille","bar",3,19],
            ["UBY","vin_blanc","bouteille","bar",1,20],
            ["Layon","vin_blanc","bouteille","bar",1,21],
            ["Castellane 75cl","champagne","bouteille","bar",2,22],
            ["Castellane 37,5cl","champagne","bouteille","bar",4,23],
            ["Coupe champagne","champagne","verre","bar",0,24],
            ["Ricard","alcool","bouteille","bar",1,25],
            ["Get 27","alcool","bouteille","bar",1,26],
            ["Martini blanc","alcool","bouteille","bar",1,27],
            ["Martini rouge","alcool","bouteille","bar",1,28],
            ["Rhum blanc","alcool","bouteille","bar",1,29],
            ["Rhum ambre","alcool","bouteille","bar",1,30],
            ["Whisky Jack Daniels","alcool","bouteille","bar",1,31],
            ["Ballentines","alcool","bouteille","bar",1,32],
            ["Clan Campbell","alcool","bouteille","bar",1,33],
            ["Lillet blanc","alcool","bouteille","bar",1,34],
            ["Lillet rose","alcool","bouteille","bar",1,35],
            ["Calvados","alcool","bouteille","bar",1,36],
            ["Cognac","alcool","bouteille","bar",1,37],
            ["Armagnac","alcool","bouteille","bar",1,38],
            ["Baileys","alcool","bouteille","bar",1,39],
            ["Manzana","alcool","bouteille","bar",1,40],
            ["Kir","alcool","bouteille","bar",1,41],
            ["Aperol","alcool","bouteille","bar",2,42],
            ["Prosecco","alcool","bouteille","bar",2,43],
            ["Biere 1664 0% 33cl","biere","carton 12","bar",1,44],
            ["Abbaye fut","biere","fut","bar",1,45],
            ["Bouteille CO2","biere","piece","bar",1,46],
            ["Orangina 25cl","soft","carton 39","bar",1,47],
            ["Coca Cola 33cl","soft","carton 24","bar",3,48],
            ["Coca Cola Cherry 33cl","soft","carton 24","bar",1,49],
            ["Coca Zero 33cl","soft","carton 24","bar",1,50],
            ["Fuzetea Peche 20cl","soft","carton 24","bar",1,51],
            ["Perrier 33cl","soft","carton 24","bar",1,52],
            ["Limonade 25cl","soft","carton 24","bar",1,53],
            ["Limonade 1L PET","soft","carton 6","bar",2,54],
            ["Schweppes Indian Tonic 25cl","soft","carton 24","bar",1,55],
            ["Sirop Grenadine 1L","sirop","bouteille","bar",1,56],
            ["Sirop Citron 1L","sirop","bouteille","bar",2,57],
            ["Sirop Menthe 1L","sirop","bouteille","bar",2,58],
            ["Sirop Fraise 1L","sirop","bouteille","bar",3,59],
            ["Sirop Peche 1L","sirop","bouteille","bar",3,60],
            ["Pina colada","cocktail","dose","bar",0,61],
            ["Spritz","cocktail","dose","bar",0,62],
            ["Blue Lagoon","cocktail","dose","bar",0,63],
            ["Sunny Vita sans alcool","cocktail","dose","bar",0,64],
            ["TropicalFresh sans alcool","cocktail","dose","bar",0,65],
            ["Vin du mois bouteille","vin_rouge","bouteille","bar",2,66],
            ["Vin du mois verre","vin_rouge","verre","bar",0,67],
            ["The vert menthe","cafe_the","boite","bar",1,68],
            ["Infusion verveine","cafe_the","boite","bar",1,69],
            ["Ceylan Superieur Diamant","cafe_the","boite","bar",1,70],
            ["Cafe Baresto Deca 250g","cafe_the","paquet","bar",1,71],
            ["Cafe Baresto Grano 1kg","cafe_the","paquet","bar",1,72],
            ["Buchettes sucre 1000","cafe_the","carton","bar",1,73],
            ["Frites","feculents","paquet 5kg","cuisine",2,74],
            ["Salade","legumes","paquet 500g","cuisine",2,75],
            ["Saumon Tartare","poissons","filet","cuisine",4,76],
            ["Ananas","fruits","unite","cuisine",2,77],
            ["Pain","boulangerie","sachet","cuisine",2,78],
            ["Tomate","legumes","boite","cuisine",2,79],
            ["Poulet","viandes","blanc","cuisine",6,80],
            ["Magret canard","viandes","piece","cuisine",4,81],
            ["Pieces de boeuf","viandes","piece","cuisine",6,82],
            ["Steak hache burger 125g","viandes","piece","cuisine",10,83],
            ["Steak hache tartare 150g","viandes","piece","cuisine",6,84],
            ["Cheddar","cremerie","paquet","cuisine",2,85],
            ["Pains burgers","boulangerie","sachet 15","cuisine",2,86],
            ["Choux","patisserie","carton","cuisine",1,87],
            ["Entrecote","viandes","carre","cuisine",4,88],
            ["Chevre","cremerie","buche","cuisine",2,89],
            ["Poche de sauce","condiments","piece","cuisine",2,90],
            ["Sauce caramel 1kg","condiments","bidon","cuisine",1,91],
            ["Sauce fruits rouges 1kg","condiments","bidon","cuisine",1,92],
            ["Sauce cafe 1kg","condiments","bidon","cuisine",1,93],
            ["Gateau chocolat","patisserie","piece","cuisine",2,94],
            ["Tiramisu","patisserie","piece","cuisine",4,95],
            ["Creme brulee","patisserie","piece","cuisine",4,96],
            ["Pana cotta","patisserie","piece","cuisine",4,97],
            ["Boeuf","viandes","piece","cave",10,98],
            ["Saumon","poissons","filet","cave",4,99],
            ["Poulet cave","viandes","blanc","cave",6,100],
            ["Magret cave","viandes","piece","cave",4,101],
            ["Entrecote cave","viandes","carre","cave",4,102],
        ];
        foreach ($products as $p) {
            if (!is_array($p)) continue;
            $wpdb->insert($t, [
                'restaurant_id' => $rid,
                'name'          => $p[0],
                'category'      => $p[1],
                'unit'          => $p[2],
                'zone'          => $p[3],
                'stock_min'     => $p[4],
                'stock_current' => 0,
                'display_order' => $p[5],
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    public static function get_stock(WP_REST_Request $r) {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . BRIGA_DB::table('stock_items') . " WHERE restaurant_id=%d AND is_active=1 ORDER BY display_order, id", $rid), ARRAY_A);
        return new WP_REST_Response(['items' => $items ?: [], 'count' => count($items ?: [])], 200);
    }

    public static function post_stock_move(WP_REST_Request $r) {
        global $wpdb;
        $item_id  = intval($r->get_param('item_id'));
        $type     = sanitize_text_field($r->get_param('type')); // in | out | offered | broken
        $quantity = floatval($r->get_param('quantity'));
        $note     = sanitize_text_field($r->get_param('note') ?: '');

        if (!in_array($type, ['in','out','offered','broken','adjustment'], true))
            return new WP_REST_Response(['success'=>false,'message'=>'Type invalide'], 400);

        $t = BRIGA_DB::table('stock_items');
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE id=%d", $item_id), ARRAY_A);
        if (!$item) return new WP_REST_Response(['success'=>false,'message'=>'Produit introuvable'], 404);

        $new_qty = floatval($item['stock_current']);
        if ($type === 'in')    $new_qty += $quantity;
        else                   $new_qty  = max(0, $new_qty - $quantity);

        $wpdb->update($t, ['stock_current'=>$new_qty,'updated_at'=>current_time('mysql')], ['id'=>$item_id]);
        $wpdb->insert(BRIGA_DB::table('stock_moves'), [
            'restaurant_id' => BRIGA_DB::get_restaurant_id(),
            'item_id'       => $item_id,
            'move_type'     => $type,
            'quantity'      => $quantity,
            'note'          => $note,
            'created_at'    => current_time('mysql'),
        ]);
        return new WP_REST_Response(['success'=>true,'stock_current'=>$new_qty], 200);
    }

    // ── DLC (wp_options gardé temporairement) ─────────────
    public static function get_dlc(WP_REST_Request $r) {
        global $wpdb;
        $rid   = BRIGA_DB::get_restaurant_id();
        $rows  = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . BRIGA_DB::table('dlc') . " WHERE restaurant_id=%d ORDER BY expiry_date ASC", $rid), ARRAY_A);
        if ($rows) return new WP_REST_Response(['items'=>$rows,'count'=>count($rows)], 200);
        $items = get_option('briga_dlc_items_UNUSED', [
            ['id'=>1,'product_name'=>'Saumon','quantity'=>1,'expiry_date'=>date('Y-m-d',strtotime('+1 day')),'zone'=>'cuisine'],
            ['id'=>2,'product_name'=>'Crème', 'quantity'=>1,'expiry_date'=>date('Y-m-d',strtotime('+2 days')),'zone'=>'cuisine'],
        ]);
        return new WP_REST_Response(['items'=>$items,'count'=>count($items)], 200);
    }

    public static function post_dlc(WP_REST_Request $r) {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $wpdb->insert(BRIGA_DB::table('dlc'), [
            'restaurant_id' => $rid,
            'product_name'  => sanitize_text_field($r->get_param('product_name')),
            'quantity'      => floatval($r->get_param('quantity') ?: 1),
            'expiry_date'   => sanitize_text_field($r->get_param('expiry_date')),
            'zone'          => sanitize_text_field($r->get_param('zone') ?: 'cuisine'),
            'created_at'    => current_time('mysql'),
        ]);
        return self::get_dlc($r);
    }

    // ── ALERTS ────────────────────────────────────────────
    public static function get_alerts(WP_REST_Request $r) {
        global $wpdb;
        $alerts = [];
        $rid    = BRIGA_DB::get_restaurant_id();
        $items  = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . BRIGA_DB::table('stock_items') . " WHERE restaurant_id=%d AND is_active=1", $rid), ARRAY_A);
        foreach ($items ?: [] as $item) {
            $qty = floatval($item['stock_current']);
            $min = floatval($item['stock_min']);
            if ($qty <= $min / 2) $alerts[] = ['type'=>'stock','level'=>'danger','message'=>"Stock critique : {$item['name']}",'item_id'=>$item['id']];
            elseif ($qty <= $min) $alerts[] = ['type'=>'stock','level'=>'warning','message'=>"Stock faible : {$item['name']}",'item_id'=>$item['id']];
        }
        $dlcs  = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . BRIGA_DB::table('dlc') . " WHERE restaurant_id=%d", $rid), ARRAY_A) ?: [];
        $today = new DateTime(); $today->setTime(0,0,0);
        foreach ($dlcs as $d) {
            $exp  = new DateTime($d['expiry_date']); $exp->setTime(0,0,0);
            $days = intval($today->diff($exp)->days) * ($exp >= $today ? 1 : -1);
            $pname = $d['product_name'] ?? '';
            if ($days < 0)     $alerts[] = ['type'=>'dlc','level'=>'danger', 'message'=>"DLC dépassée : $pname", 'item_id'=>$d['id']];
            elseif ($days <= 1)$alerts[] = ['type'=>'dlc','level'=>'danger', 'message'=>"DLC urgente : $pname",  'item_id'=>$d['id']];
            elseif ($days <= 2)$alerts[] = ['type'=>'dlc','level'=>'warning','message'=>"DLC proche : $pname",   'item_id'=>$d['id']];
        }
        return new WP_REST_Response(['alerts'=>$alerts,'count'=>count($alerts)], 200);
    }

    // ── DASHBOARD ─────────────────────────────────────────
    public static function get_dashboard(WP_REST_Request $r) {
        $hour        = intval(date('H'));
        $service     = $hour < 15 ? 'midi' : 'soir';
        $alerts_data = self::get_alerts($r)->get_data();
        return new WP_REST_Response([
            'user'    => wp_get_current_user()->display_name ?: 'Manager',
            'service' => $service,
            'actions' => ['Charger bar midi','Vérifier DLC cuisine','Contrôler stock cave'],
            'team'    => ['bar'=>'1/2','salle'=>'2/3','cuisine'=>'1/2'],
            'alerts'  => $alerts_data['alerts'] ?? [],
            'stats'   => ['stock_count'=>3,'dlc_count'=>2],
        ], 200);
    }

    // ── Endpoint de diagnostic ────────────────────────────────────────────
    public static function get_status(WP_REST_Request $r) {
        global $wpdb;
        $tables_expected = [
            'briga_restaurants','briga_tasks','briga_stock_items','briga_stock_moves',
            'briga_stock_bar_daily','briga_dlc','briga_caisse','briga_casse',
            'briga_commandes','briga_commande_lines','briga_offerts','briga_reports',
            'briga_table_status','briga_task_logs',
        ];
        $tables_found = [];
        $tables_missing = [];
        foreach ($tables_expected as $t) {
            $full = $wpdb->prefix . $t;
            if ($wpdb->get_var("SHOW TABLES LIKE '$full'") === $full) {
                // Compter les colonnes
                $cols = $wpdb->get_results("SHOW COLUMNS FROM $full", ARRAY_A);
                $tables_found[$t] = count($cols) . ' colonnes';
            } else {
                $tables_missing[] = $t;
            }
        }
        $rid = BRIGA_DB::get_restaurant_id();
        return new WP_REST_Response([
            'ok'             => true,
            'version'        => BRIGA_DB::VERSION,
            'briga_version'  => defined('BRIGA_VERSION') ? BRIGA_VERSION : '?',
            'restaurant_id'  => $rid,
            'tables_found'   => $tables_found,
            'tables_missing' => $tables_missing,
            'wp_db_error'    => $wpdb->last_error ?: null,
            'php_version'    => PHP_VERSION,
            'mysql_version'  => $wpdb->get_var('SELECT VERSION()'),
            'timestamp'      => current_time('mysql'),
        ], 200);
    }

}
