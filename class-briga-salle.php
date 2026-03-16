<?php
/**
 * BRIGA PRO — Module Plan de Salle v3.0
 * 3 zones + vue globale — disposition validée par Constantin BARNA
 */
if (!defined('ABSPATH')) exit;

class BRIGA_Salle {

    const S_LIBRE     = 'libre';
    const S_RESERVEE  = 'reservee';
    const S_OCCUPEE   = 'occupee';
    const S_NETTOYAGE = 'nettoyage';

    // ─────────────────────────────────────────────────────────────────────
    // SALLE GILBERT — 19 tables (pas de 13)
    // L1: 1 2 3 4 5 | L2: 7(ronde) 6 | L3: 10 9 8
    // L4: 11 12 · 14 15(collées) | L5: 16 17 18 19 20
    // ─────────────────────────────────────────────────────────────────────
    const TABLES_GILBERT = [
        ['number'=>1,  'capacity'=>2, 'shape'=>'rect',  'col'=>1,'row'=>1, 'groups'=>[]],
        ['number'=>2,  'capacity'=>2, 'shape'=>'rect',  'col'=>2,'row'=>1, 'groups'=>[]],
        ['number'=>3,  'capacity'=>2, 'shape'=>'rect',  'col'=>3,'row'=>1, 'groups'=>[]],
        ['number'=>4,  'capacity'=>2, 'shape'=>'rect',  'col'=>4,'row'=>1, 'groups'=>[]],
        ['number'=>5,  'capacity'=>4, 'shape'=>'rect',  'col'=>5,'row'=>1, 'groups'=>[]],
        ['number'=>7,  'capacity'=>4, 'shape'=>'round', 'col'=>4,'row'=>2, 'groups'=>[]],
        ['number'=>6,  'capacity'=>2, 'shape'=>'rect',  'col'=>5,'row'=>2, 'groups'=>[]],
        ['number'=>10, 'capacity'=>2, 'shape'=>'rect',  'col'=>1,'row'=>3, 'groups'=>['G1']],
        ['number'=>9,  'capacity'=>2, 'shape'=>'rect',  'col'=>2,'row'=>3, 'groups'=>['G1']],
        ['number'=>8,  'capacity'=>2, 'shape'=>'rect',  'col'=>3,'row'=>3, 'groups'=>['G1']],
        ['number'=>11, 'capacity'=>2, 'shape'=>'rect',  'col'=>1,'row'=>4, 'groups'=>['G2']],
        ['number'=>12, 'capacity'=>2, 'shape'=>'rect',  'col'=>2,'row'=>4, 'groups'=>['G2']],
        ['number'=>14, 'capacity'=>2, 'shape'=>'rect',  'col'=>4,'row'=>4, 'groups'=>['G3'], 'adjacent'=>true],
        ['number'=>15, 'capacity'=>2, 'shape'=>'rect',  'col'=>5,'row'=>4, 'groups'=>['G3'], 'adjacent'=>true],
        ['number'=>16, 'capacity'=>2, 'shape'=>'rect',  'col'=>2,'row'=>5, 'groups'=>[]],
        ['number'=>17, 'capacity'=>2, 'shape'=>'rect',  'col'=>3,'row'=>5, 'groups'=>[]],
        ['number'=>18, 'capacity'=>2, 'shape'=>'rect',  'col'=>4,'row'=>5, 'groups'=>[]],
        ['number'=>19, 'capacity'=>2, 'shape'=>'rect',  'col'=>5,'row'=>5, 'groups'=>[]],
        ['number'=>20, 'capacity'=>2, 'shape'=>'rect',  'col'=>6,'row'=>5, 'groups'=>[]],
    ];

    // ─────────────────────────────────────────────────────────────────────
    // TERRASSE — 9 tables
    // L1: 107 · 108 109 110(collées) | L2: 106 · · · · · 102
    // L3: 105 · 104(centre) · · 103
    // ─────────────────────────────────────────────────────────────────────
    const TABLES_TERRASSE = [
        // Ligne 1
        ['number'=>107, 'capacity'=>2, 'shape'=>'rect', 'col'=>1,'row'=>1, 'groups'=>[]],
        ['number'=>108, 'capacity'=>2, 'shape'=>'rect', 'col'=>3,'row'=>1, 'groups'=>['T1'], 'adjacent'=>true],
        ['number'=>109, 'capacity'=>2, 'shape'=>'rect', 'col'=>4,'row'=>1, 'groups'=>['T1'], 'adjacent'=>true],
        ['number'=>110, 'capacity'=>2, 'shape'=>'rect', 'col'=>5,'row'=>1, 'groups'=>['T1'], 'adjacent'=>true],
        // Ligne 2
        ['number'=>106, 'capacity'=>2, 'shape'=>'rect', 'col'=>1,'row'=>2, 'groups'=>[]],
        ['number'=>102, 'capacity'=>2, 'shape'=>'rect', 'col'=>6,'row'=>2, 'groups'=>[]],
        // Ligne 3
        ['number'=>105, 'capacity'=>2, 'shape'=>'rect', 'col'=>1,'row'=>3, 'groups'=>[]],
        ['number'=>104, 'capacity'=>2, 'shape'=>'rect', 'col'=>3,'row'=>3, 'groups'=>[]],
        ['number'=>103, 'capacity'=>2, 'shape'=>'rect', 'col'=>6,'row'=>3, 'groups'=>[]],
    ];

    // ─────────────────────────────────────────────────────────────────────
    // CÔTÉ CUISINE — 20 tables (pas de 213)
    // L1: 200 201 202(collées) · 203 204(collées)
    // L2: 207 · 206(ronde) · · 205
    // L3: 208 209(collées) · 210 211(collées) · 212 214(collées)
    // L4: 220 · · · · · · · · · 215
    // L5: 219 218 217 216
    // ─────────────────────────────────────────────────────────────────────
    const TABLES_CUISINE = [
        // Ligne 1
        ['number'=>200, 'capacity'=>2, 'shape'=>'rect', 'col'=>1,'row'=>1, 'groups'=>['C1'], 'adjacent'=>true],
        ['number'=>201, 'capacity'=>2, 'shape'=>'rect', 'col'=>2,'row'=>1, 'groups'=>['C1'], 'adjacent'=>true],
        ['number'=>202, 'capacity'=>2, 'shape'=>'rect', 'col'=>3,'row'=>1, 'groups'=>['C1'], 'adjacent'=>true],
        ['number'=>203, 'capacity'=>2, 'shape'=>'rect', 'col'=>5,'row'=>1, 'groups'=>['C2'], 'adjacent'=>true],
        ['number'=>204, 'capacity'=>2, 'shape'=>'rect', 'col'=>6,'row'=>1, 'groups'=>['C2'], 'adjacent'=>true],
        // Ligne 2
        ['number'=>207, 'capacity'=>2, 'shape'=>'rect',  'col'=>1,'row'=>2, 'groups'=>[]],
        ['number'=>206, 'capacity'=>2, 'shape'=>'round', 'col'=>3,'row'=>2, 'groups'=>[]],
        ['number'=>205, 'capacity'=>2, 'shape'=>'rect',  'col'=>6,'row'=>2, 'groups'=>[]],
        // Ligne 3
        ['number'=>208, 'capacity'=>2, 'shape'=>'rect', 'col'=>1,'row'=>3, 'groups'=>['C3'], 'adjacent'=>true],
        ['number'=>209, 'capacity'=>2, 'shape'=>'rect', 'col'=>2,'row'=>3, 'groups'=>['C3'], 'adjacent'=>true],
        ['number'=>210, 'capacity'=>2, 'shape'=>'rect', 'col'=>4,'row'=>3, 'groups'=>['C4'], 'adjacent'=>true],
        ['number'=>211, 'capacity'=>2, 'shape'=>'rect', 'col'=>5,'row'=>3, 'groups'=>['C4'], 'adjacent'=>true],
        ['number'=>212, 'capacity'=>2, 'shape'=>'rect', 'col'=>7,'row'=>3, 'groups'=>['C5'], 'adjacent'=>true],
        ['number'=>214, 'capacity'=>2, 'shape'=>'rect', 'col'=>8,'row'=>3, 'groups'=>['C5'], 'adjacent'=>true],
        // Ligne 4
        ['number'=>220, 'capacity'=>2, 'shape'=>'rect', 'col'=>1,'row'=>4, 'groups'=>[]],
        ['number'=>215, 'capacity'=>2, 'shape'=>'rect', 'col'=>8,'row'=>4, 'groups'=>[]],
        // Ligne 5
        ['number'=>219, 'capacity'=>2, 'shape'=>'rect', 'col'=>1,'row'=>5, 'groups'=>[]],
        ['number'=>218, 'capacity'=>2, 'shape'=>'rect', 'col'=>2,'row'=>5, 'groups'=>[]],
        ['number'=>217, 'capacity'=>2, 'shape'=>'rect', 'col'=>3,'row'=>5, 'groups'=>[]],
        ['number'=>216, 'capacity'=>2, 'shape'=>'rect', 'col'=>4,'row'=>5, 'groups'=>[]],
    ];

    // Capacités groupes fusionnables
    const GROUPS_CAP = [
        'G1'=>6, 'G2'=>4, 'G3'=>4,
        'T1'=>6,
        'C1'=>6, 'C2'=>4, 'C3'=>4, 'C4'=>4, 'C5'=>4,
    ];

    public static function all_tables(): array {
        $all = [];
        foreach (self::TABLES_GILBERT  as $t) { $t['zone']='gilbert';  $all[]=$t; }
        foreach (self::TABLES_TERRASSE as $t) { $t['zone']='terrasse'; $all[]=$t; }
        foreach (self::TABLES_CUISINE  as $t) { $t['zone']='cuisine';  $all[]=$t; }
        return $all;
    }

    public static function register_routes() {
        $ns = 'briga/v1';
        register_rest_route($ns,'/salle/map',           ['methods'=>'GET', 'callback'=>[__CLASS__,'get_map'],      'permission_callback'=>'__return_true']);
        register_rest_route($ns,'/salle/status/(?P<table_number>\d+)',['methods'=>'POST','callback'=>[__CLASS__,'update_status'],'permission_callback'=>'__return_true']);
        register_rest_route($ns,'/salle/move/(?P<table_number>\d+)',  ['methods'=>'POST','callback'=>[__CLASS__,'move_table'],   'permission_callback'=>'__return_true']);
        register_rest_route($ns,'/salle/merge',         ['methods'=>'POST','callback'=>[__CLASS__,'merge_tables'], 'permission_callback'=>'__return_true']);
        register_rest_route($ns,'/salle/split/(?P<table_number>\d+)', ['methods'=>'POST','callback'=>[__CLASS__,'split_table'],  'permission_callback'=>'__return_true']);
        register_rest_route($ns,'/salle/reset',         ['methods'=>'POST','callback'=>[__CLASS__,'reset_service'],'permission_callback'=>'__return_true']);
        register_rest_route($ns,'/salle/suggest',       ['methods'=>'GET', 'callback'=>[__CLASS__,'suggest_table'],'permission_callback'=>'__return_true']);
        register_rest_route($ns,'/salle/summary',       ['methods'=>'GET', 'callback'=>[__CLASS__,'get_summary'],  'permission_callback'=>'__return_true']);
    }

    public static function get_map($request) {
        global $wpdb;
        self::maybe_create_table();
        $rid   = BRIGA_DB::get_restaurant_id();
        $t     = $wpdb->prefix.'briga_table_status';
        $today = current_time('Y-m-d');

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT table_number,status,client_name,persons,reservation_id,merged_with,group_code,note,pos_col,pos_row
             FROM $t WHERE restaurant_id=%d AND service_date=%s",
            $rid, $today
        ), ARRAY_A);
        if ($wpdb->last_error) return new WP_Error('db_error',$wpdb->last_error,['status'=>500]);

        $live=[];
        foreach ($rows as $r) $live[(int)$r['table_number']]=$r;

        $tables=[];
        foreach (self::all_tables() as $def) {
            $n=$def['number']; $lv=$live[$n]??null;
            $tables[]=[
                'number'        => $n,
                'zone'          => $def['zone'],
                'capacity'      => $def['capacity'],
                'shape'         => $def['shape'],
                'groups'        => $def['groups'],
                'adjacent'      => $def['adjacent'] ?? false,
                'col'           => ($lv&&(int)$lv['pos_col']>0)?(int)$lv['pos_col']:$def['col'],
                'row'           => ($lv&&(int)$lv['pos_row']>0)?(int)$lv['pos_row']:$def['row'],
                'default_col'   => $def['col'],
                'default_row'   => $def['row'],
                'status'        => $lv['status']        ?? self::S_LIBRE,
                'client_name'   => $lv['client_name']   ?? '',
                'persons'       => (int)($lv['persons'] ?? 0),
                'reservation_id'=> (int)($lv['reservation_id']??0),
                'merged_with'   => $lv['merged_with']   ?? '',
                'group_code'    => $lv['group_code']    ?? '',
                'note'          => $lv['note']          ?? '',
            ];
        }
        return rest_ensure_response(['tables'=>$tables,'groups_cap'=>self::GROUPS_CAP,'service_date'=>$today]);
    }

    public static function update_status($request) {
        global $wpdb;
        self::maybe_create_table();
        $rid=BRIGA_DB::get_restaurant_id(); $t=$wpdb->prefix.'briga_table_status';
        $n=(int)$request['table_number']; $today=current_time('Y-m-d'); $now=current_time('mysql');
        $p=$request->get_json_params();
        if (!self::table_exists($n)) return new WP_Error('not_found',"Table $n inexistante",['status'=>404]);
        $allowed=[self::S_LIBRE,self::S_RESERVEE,self::S_OCCUPEE,self::S_NETTOYAGE];
        $status=sanitize_text_field($p['status']??self::S_OCCUPEE);
        if (!in_array($status,$allowed)) return new WP_Error('invalid_status','Statut invalide',['status'=>400]);
        $data=['restaurant_id'=>$rid,'table_number'=>$n,'service_date'=>$today,'status'=>$status,
               'client_name'=>sanitize_text_field($p['client_name']??''),
               'persons'=>absint($p['persons']??0),'reservation_id'=>absint($p['reservation_id']??0),
               'note'=>sanitize_textarea_field($p['note']??''),'updated_at'=>$now];
        if ($status===self::S_LIBRE){$data['client_name']='';$data['persons']=0;$data['reservation_id']=0;$data['note']='';$data['merged_with']='';$data['group_code']='';}
        $exists=$wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE restaurant_id=%d AND table_number=%d AND service_date=%s",$rid,$n,$today));
        if ($exists){$wpdb->update($t,$data,['restaurant_id'=>$rid,'table_number'=>$n,'service_date'=>$today]);}
        else{$data['created_at']=$now;$wpdb->insert($t,$data);}
        return rest_ensure_response(['success'=>true,'table_number'=>$n,'status'=>$status]);
    }

    public static function move_table($request) {
        global $wpdb;
        self::maybe_create_table();
        $rid=BRIGA_DB::get_restaurant_id(); $t=$wpdb->prefix.'briga_table_status';
        $n=(int)$request['table_number']; $today=current_time('Y-m-d'); $now=current_time('mysql');
        $p=$request->get_json_params(); $col=absint($p['col']??1); $row=absint($p['row']??1);
        if (!self::table_exists($n)) return new WP_Error('not_found',"Table $n inexistante",['status'=>404]);
        $exists=$wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE restaurant_id=%d AND table_number=%d AND service_date=%s",$rid,$n,$today));
        if ($exists){$wpdb->update($t,['pos_col'=>$col,'pos_row'=>$row,'updated_at'=>$now],['restaurant_id'=>$rid,'table_number'=>$n,'service_date'=>$today]);}
        else{$wpdb->insert($t,['restaurant_id'=>$rid,'table_number'=>$n,'service_date'=>$today,'status'=>self::S_LIBRE,'pos_col'=>$col,'pos_row'=>$row,'created_at'=>$now,'updated_at'=>$now]);}
        return rest_ensure_response(['success'=>true,'table_number'=>$n,'col'=>$col,'row'=>$row]);
    }

    public static function merge_tables($request) {
        global $wpdb;
        self::maybe_create_table();
        $rid=BRIGA_DB::get_restaurant_id(); $t=$wpdb->prefix.'briga_table_status';
        $today=current_time('Y-m-d'); $now=current_time('mysql');
        $p=$request->get_json_params(); $nums=array_map('absint',$p['tables']??[]);
        if (count($nums)<2) return new WP_Error('invalid','2 tables minimum',['status'=>400]);
        $group=self::common_group($nums); $merged=implode(',',$nums);
        foreach ($nums as $n){
            $exists=$wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE restaurant_id=%d AND table_number=%d AND service_date=%s",$rid,$n,$today));
            $data=['merged_with'=>$merged,'group_code'=>($group??''),'updated_at'=>$now];
            if ($exists){$wpdb->update($t,$data,['restaurant_id'=>$rid,'table_number'=>$n,'service_date'=>$today]);}
            else{$wpdb->insert($t,array_merge(['restaurant_id'=>$rid,'table_number'=>$n,'service_date'=>$today,'status'=>self::S_LIBRE,'created_at'=>$now],$data));}
        }
        return rest_ensure_response(['success'=>true,'merged'=>$nums,'group'=>$group]);
    }

    public static function split_table($request) {
        global $wpdb;
        self::maybe_create_table();
        $rid=BRIGA_DB::get_restaurant_id(); $t=$wpdb->prefix.'briga_table_status';
        $n=(int)$request['table_number']; $today=current_time('Y-m-d'); $now=current_time('mysql');
        $row=$wpdb->get_row($wpdb->prepare("SELECT merged_with FROM $t WHERE restaurant_id=%d AND table_number=%d AND service_date=%s",$rid,$n,$today),ARRAY_A);
        $nums=($row&&$row['merged_with'])?array_map('intval',explode(',',$row['merged_with'])):[$n];
        foreach ($nums as $tn){$wpdb->update($t,['merged_with'=>'','group_code'=>'','status'=>self::S_LIBRE,'updated_at'=>$now],['restaurant_id'=>$rid,'table_number'=>$tn,'service_date'=>$today]);}
        return rest_ensure_response(['success'=>true,'split'=>$nums]);
    }

    public static function reset_service($request) {
        global $wpdb;
        self::maybe_create_table();
        $rid=BRIGA_DB::get_restaurant_id(); $t=$wpdb->prefix.'briga_table_status';
        $today=current_time('Y-m-d'); $now=current_time('mysql');
        $wpdb->delete($t,['restaurant_id'=>$rid,'service_date'=>$today]);
        foreach (self::all_tables() as $def){$wpdb->insert($t,['restaurant_id'=>$rid,'table_number'=>$def['number'],'service_date'=>$today,'status'=>self::S_LIBRE,'created_at'=>$now,'updated_at'=>$now]);}
        return rest_ensure_response(['success'=>true,'reset'=>count(self::all_tables())]);
    }

    public static function suggest_table($request) {
        global $wpdb;
        self::maybe_create_table();
        $rid=BRIGA_DB::get_restaurant_id(); $t=$wpdb->prefix.'briga_table_status'; $today=current_time('Y-m-d');
        $persons=absint($request->get_param('persons')??2);
        $zone=sanitize_text_field($request->get_param('zone')??'');
        $busy=array_map('intval',$wpdb->get_col($wpdb->prepare("SELECT table_number FROM $t WHERE restaurant_id=%d AND service_date=%s AND status!='libre'",$rid,$today)));
        $candidates=[];
        foreach (self::all_tables() as $def){
            if (in_array($def['number'],$busy)) continue;
            if ($zone&&$def['zone']!==$zone) continue;
            if ($def['capacity']>=$persons) $candidates[]=['number'=>$def['number'],'zone'=>$def['zone'],'capacity'=>$def['capacity'],'type'=>'single','tables'=>[$def['number']],'waste'=>$def['capacity']-$persons];
        }
        usort($candidates,fn($a,$b)=>$a['waste']<=>$b['waste']);
        return rest_ensure_response(['suggestion'=>$candidates[0]??null,'alternatives'=>array_slice($candidates,1,3),'persons'=>$persons]);
    }

    public static function get_summary($request) {
        global $wpdb;
        self::maybe_create_table();
        $rid=BRIGA_DB::get_restaurant_id(); $t=$wpdb->prefix.'briga_table_status'; $today=current_time('Y-m-d');
        $rows=$wpdb->get_results($wpdb->prepare("SELECT status,COUNT(*) as cnt,SUM(persons) as persons FROM $t WHERE restaurant_id=%d AND service_date=%s GROUP BY status",$rid,$today),ARRAY_A);
        $s=['libre'=>0,'reservee'=>0,'occupee'=>0,'nettoyage'=>0]; $couverts=0;
        foreach ($rows as $r){$s[$r['status']]=(int)$r['cnt'];if($r['status']==='occupee')$couverts=(int)$r['persons'];}
        $total=count(self::all_tables());
        return rest_ensure_response(['total'=>$total,'libres'=>$s['libre'],'reservees'=>$s['reservee'],'occupees'=>$s['occupee'],'nettoyage'=>$s['nettoyage'],'couverts'=>$couverts,'taux_occupation'=>$total?round($s['occupee']/$total*100):0]);
    }

    private static function table_exists(int $n):bool{foreach(self::all_tables() as $t){if($t['number']===$n)return true;}return false;}
    private static function find_def(int $n):?array{foreach(self::all_tables() as $t){if($t['number']===$n)return $t;}return null;}
    private static function common_group(array $nums):?string{$sets=[];foreach(self::all_tables() as $t){if(in_array($t['number'],$nums))$sets[]=$t['groups'];}if(empty($sets))return null;$common=array_shift($sets);foreach($sets as $s)$common=array_intersect($common,$s);return!empty($common)?reset($common):null;}

    public static function maybe_create_table() {
        // Table créée par BRIGA_DB::install() à l'activation
        // Vérification légère avec transient (1h) pour éviter dbDelta à chaque requête
        if ( get_transient('briga_salle_table_ok') ) return;
        global $wpdb;
        $t = $wpdb->prefix . 'briga_table_status';
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$t'");
        if ( $exists ) {
            set_transient('briga_salle_table_ok', 1, HOUR_IN_SECONDS);
            return;
        }
        // Table absente → créer via BRIGA_DB
        BRIGA_DB::install();
        set_transient('briga_salle_table_ok', 1, HOUR_IN_SECONDS);
    }
}
