<?php
if (!defined('ABSPATH')) exit;

class BRIGA_Commandes {

    private static function get_catalogue() {
        return [
            'brake' => [
                ['id'=>'b1', 'name'=>'Pain lingotin',          'ref'=>'71839','unit'=>'carton de 160','stock_min'=>2, 'debit'=>3],
                ['id'=>'b2', 'name'=>'Choux Delif',             'ref'=>'72638','unit'=>'carton de 96', 'stock_min'=>3, 'debit'=>2],
                ['id'=>'b3', 'name'=>'Sauce dessert café',      'ref'=>'17902','unit'=>'unité',         'stock_min'=>2, 'debit'=>'1/mois'],
                ['id'=>'b4', 'name'=>'Sauce dessert caramel',   'ref'=>'14931','unit'=>'unité',         'stock_min'=>6, 'debit'=>3],
                ['id'=>'b5', 'name'=>'Coulis fruits rouges',    'ref'=>'',     'unit'=>'carton de 6',  'stock_min'=>6, 'debit'=>3],
                ['id'=>'b6', 'name'=>'Rumsteak',                'ref'=>'40604','unit'=>'poche 2x5p',   'stock_min'=>10,'debit'=>5],
                ['id'=>'b7', 'name'=>'Magret canard 380/400g',  'ref'=>'40378','unit'=>'unité',         'stock_min'=>40,'debit'=>10],
                ['id'=>'b8', 'name'=>'Tartare bœuf 180g',      'ref'=>'45251','unit'=>'carton de 8',  'stock_min'=>3, 'debit'=>2],
                ['id'=>'b9', 'name'=>'Poulet',                  'ref'=>'',     'unit'=>'poche de 10',  'stock_min'=>4, 'debit'=>2],
                ['id'=>'b10','name'=>'Nems légumes taboulé',   'ref'=>'75494','unit'=>'unité',         'stock_min'=>2, 'debit'=>1],
                ['id'=>'b11','name'=>'Salade',                  'ref'=>'',     'unit'=>'poche 500g',   'stock_min'=>6, 'debit'=>4],
                ['id'=>'b12','name'=>'Frites',                  'ref'=>'',     'unit'=>'poche 5kg',    'stock_min'=>5, 'debit'=>3],
                ['id'=>'b13','name'=>'Saumon filet 2kg',        'ref'=>'',     'unit'=>'colis 10kg',   'stock_min'=>2, 'debit'=>1],
                ['id'=>'b14','name'=>'Fraises',                 'ref'=>'',     'unit'=>'barquette 500g','stock_min'=>4,'debit'=>3],
                ['id'=>'b15','name'=>'Ananas',                  'ref'=>'',     'unit'=>'pièce',        'stock_min'=>3, 'debit'=>2],
                ['id'=>'b16','name'=>'Œufs bio',               'ref'=>'',     'unit'=>'colis 90p',    'stock_min'=>1, 'debit'=>1],
                ['id'=>'b17','name'=>'Oignons',                 'ref'=>'',     'unit'=>'kg',           'stock_min'=>2, 'debit'=>2],
                ['id'=>'b18','name'=>'Citron / Orange',         'ref'=>'',     'unit'=>'selon besoins','stock_min'=>0, 'debit'=>0],
                ['id'=>'b19','name'=>'Carrés entrecôte',       'ref'=>'',     'unit'=>'unité',        'stock_min'=>2, 'debit'=>2],
                ['id'=>'b20','name'=>'Poche sauce 4kg',         'ref'=>'',     'unit'=>'poche',        'stock_min'=>4, 'debit'=>2],
                ['id'=>'b21','name'=>'Pot sauce 10cl',          'ref'=>'',     'unit'=>'pot',          'stock_min'=>6, 'debit'=>3],
                ['id'=>'b22','name'=>'Glace',                   'ref'=>'',     'unit'=>'unité',        'stock_min'=>4, 'debit'=>2],
                ['id'=>'b23','name'=>'Carton serviettes',       'ref'=>'',     'unit'=>'carton',       'stock_min'=>1, 'debit'=>1],
                ['id'=>'b24','name'=>'Sets de table',           'ref'=>'',     'unit'=>'paquet',       'stock_min'=>1, 'debit'=>1],
            ],
            'brasseur' => [
                ['id'=>'r1', 'name'=>'Coca-Cola 33cl',          'ref'=>'','unit'=>'carton 24','stock_min'=>3,'debit'=>2],
                ['id'=>'r2', 'name'=>'Coca-Cola 1.5L',          'ref'=>'','unit'=>'carton 6', 'stock_min'=>2,'debit'=>1],
                ['id'=>'r3', 'name'=>'Fanta Orange',            'ref'=>'','unit'=>'carton 24','stock_min'=>2,'debit'=>1],
                ['id'=>'r4', 'name'=>'Limonade',                'ref'=>'','unit'=>'carton 24','stock_min'=>2,'debit'=>1],
                ['id'=>'r5', 'name'=>'Schweppes Tonic',         'ref'=>'','unit'=>'carton 24','stock_min'=>2,'debit'=>1],
                ['id'=>'r6', 'name'=>'Schweppes Agrumes',       'ref'=>'','unit'=>'carton 24','stock_min'=>2,'debit'=>1],
                ['id'=>'r7', 'name'=>'Eau plate 50cl',          'ref'=>'','unit'=>'palette',  'stock_min'=>1,'debit'=>1],
                ['id'=>'r8', 'name'=>'Eau gazeuse 50cl',        'ref'=>'','unit'=>'palette',  'stock_min'=>1,'debit'=>1],
                ['id'=>'r9', 'name'=>'Bière 1664 25cl',         'ref'=>'','unit'=>'carton 24','stock_min'=>3,'debit'=>2],
                ['id'=>'r10','name'=>'Bière Abbey',             'ref'=>'','unit'=>'carton 24','stock_min'=>2,'debit'=>1],
                ['id'=>'r11','name'=>'Sirop menthe',            'ref'=>'','unit'=>'bouteille','stock_min'=>4,'debit'=>2],
                ['id'=>'r12','name'=>'Sirop fraise',            'ref'=>'','unit'=>'bouteille','stock_min'=>4,'debit'=>2],
                ['id'=>'r13','name'=>'Sirop citron',            'ref'=>'','unit'=>'bouteille','stock_min'=>4,'debit'=>2],
                ['id'=>'r14','name'=>'Sirop grenadine',         'ref'=>'','unit'=>'bouteille','stock_min'=>4,'debit'=>2],
                ['id'=>'r15','name'=>'Ricard 1L',               'ref'=>'','unit'=>'bouteille','stock_min'=>4,'debit'=>2],
                ['id'=>'r16','name'=>'Vodka',                   'ref'=>'','unit'=>'bouteille','stock_min'=>2,'debit'=>1],
                ['id'=>'r17','name'=>'Rhum blanc',              'ref'=>'','unit'=>'bouteille','stock_min'=>2,'debit'=>1],
                ['id'=>'r18','name'=>'Rhum ambré',              'ref'=>'','unit'=>'bouteille','stock_min'=>2,'debit'=>1],
                ['id'=>'r19','name'=>'Gin',                     'ref'=>'','unit'=>'bouteille','stock_min'=>2,'debit'=>1],
                ['id'=>'r20','name'=>'Champagne Castellane 75cl','ref'=>'','unit'=>'bouteille','stock_min'=>6,'debit'=>3],
                ['id'=>'r21','name'=>'Champagne Castellane 37.5cl','ref'=>'','unit'=>'bouteille','stock_min'=>4,'debit'=>2],
                ['id'=>'r22','name'=>'Prosecco',                'ref'=>'','unit'=>'bouteille','stock_min'=>4,'debit'=>2],
                ['id'=>'r23','name'=>'Apérol',                  'ref'=>'','unit'=>'bouteille','stock_min'=>3,'debit'=>1],
            ],
        ];
    }

    public static function register_routes() {
        register_rest_route('briga/v1', '/commandes',             ['methods'=>'GET', 'callback'=>[__CLASS__,'get_commandes'],     'permission_callback'=>'__return_true']);
        register_rest_route('briga/v1', '/commandes/catalogue',   ['methods'=>'GET', 'callback'=>[__CLASS__,'get_catalogue_api'],'permission_callback'=>'__return_true']);
        register_rest_route('briga/v1', '/commandes',             ['methods'=>'POST','callback'=>[__CLASS__,'post_commande'],    'permission_callback'=>'__return_true']);
        register_rest_route('briga/v1', '/commandes/(?P<id>\d+)', ['methods'=>'POST','callback'=>[__CLASS__,'update_commande'],  'permission_callback'=>'__return_true']);
    }

    public static function get_catalogue_api(WP_REST_Request $r) {
        $type = sanitize_text_field($r->get_param('type') ?: 'all');
        $cat  = self::get_catalogue();
        if ($type === 'brake')    return new WP_REST_Response(['items'=>$cat['brake'],   'count'=>count($cat['brake'])],   200);
        if ($type === 'brasseur') return new WP_REST_Response(['items'=>$cat['brasseur'],'count'=>count($cat['brasseur'])],200);
        return new WP_REST_Response(['brake'=>$cat['brake'],'brasseur'=>$cat['brasseur']], 200);
    }

    public static function get_commandes(WP_REST_Request $r) {
        global $wpdb;
        $rid = BRIGA_DB::get_restaurant_id();
        $tc = BRIGA_DB::table('commandes');
        $tl = BRIGA_DB::table('commande_lines');
        $orders = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tc WHERE restaurant_id=%d ORDER BY created_at DESC LIMIT 50", $rid), ARRAY_A);
        foreach ($orders as &$o) {
            $o['lines'] = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tl WHERE commande_id=%d", $o['id']), ARRAY_A);
        }
        return new WP_REST_Response(['items'=>$orders,'count'=>count($orders)], 200);
    }

    public static function post_commande(WP_REST_Request $r) {
        global $wpdb;
        $supplier = sanitize_text_field($r->get_param('supplier'));
        $lines    = $r->get_param('lines') ?: [];
        $note     = sanitize_text_field($r->get_param('note') ?: '');
        if (!in_array($supplier, ['brake','brasseur'], true))
            return new WP_REST_Response(['success'=>false,'message'=>'Fournisseur invalide'], 400);

        $now = current_time('mysql');
        $rid = BRIGA_DB::get_restaurant_id();
        $wpdb->insert(BRIGA_DB::table('commandes'), ['restaurant_id'=>$rid,'supplier'=>$supplier,'status'=>'draft','note'=>$note,'created_at'=>$now,'updated_at'=>$now]);
        $cmd_id = $wpdb->insert_id;

        foreach ((array)$lines as $l) {
            if (floatval($l['qty']??0) <= 0) continue;
            $wpdb->insert(BRIGA_DB::table('commande_lines'), [
                'commande_id'  => $cmd_id,
                'product_ref'  => sanitize_text_field($l['ref'] ?? ''),
                'product_name' => sanitize_text_field($l['name'] ?? ''),
                'unit'         => sanitize_text_field($l['unit'] ?? ''),
                'quantity'     => floatval($l['qty']),
                'note'         => sanitize_text_field($l['note'] ?? ''),
            ]);
        }
        return new WP_REST_Response(['success'=>true,'id'=>$cmd_id], 200);
    }

    public static function update_commande(WP_REST_Request $r) {
        global $wpdb;
        $id     = intval($r->get_param('id'));
        $status = sanitize_text_field($r->get_param('status') ?: '');
        if ($status && in_array($status, ['draft','validated','sent','received'], true)) {
            $wpdb->update(BRIGA_DB::table('commandes'), ['status'=>$status,'updated_at'=>current_time('mysql')], ['id'=>$id]);
        }
        return new WP_REST_Response(['success'=>true], 200);
    }
}
