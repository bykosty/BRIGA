<?php
if (!defined('ABSPATH')) exit;

class BRIGA_Caisse {

    public static function register_routes() {
        register_rest_route('briga/v1', '/caisse',
            ['methods'=>'GET',  'callback'=>[__CLASS__,'get_caisse'],         'permission_callback'=>'__return_true']);
        register_rest_route('briga/v1', '/caisse',
            ['methods'=>'POST', 'callback'=>[__CLASS__,'post_caisse'],        'permission_callback'=>'__return_true']);
        register_rest_route('briga/v1', '/caisse/(?P<date>[0-9]{4}-[0-9]{2}-[0-9]{2})',
            ['methods'=>'GET',  'callback'=>[__CLASS__,'get_caisse_by_date'], 'permission_callback'=>'__return_true']);
        register_rest_route('briga/v1', '/caisse/(?P<date>[0-9]{4}-[0-9]{2}-[0-9]{2})',
            ['methods'=>'DELETE','callback'=>[__CLASS__,'delete_caisse'],     'permission_callback'=>'__return_true']);
    }

    // ── CALCULS ────────────────────────────────────────────
    private static function compute($row) {
        // Soir = Journée − Midi
        $fields = ['covers','ttc20','ttc10','ttc55','uber','deliveroo','remise','annulations','offerts'];
        $soir   = [];
        foreach ($fields as $f) {
            $soir[$f] = round(floatval($row[$f.'_day'] ?? 0) - floatval($row[$f.'_midi'] ?? 0), 2);
        }

        // CA salle = TTC20 + TTC10 (on exclut TTC55=emporté, Uber, Deliveroo)
        $ca_salle_midi = round(floatval($row['ttc20_midi']) + floatval($row['ttc10_midi']), 2);
        $ca_salle_day  = round(floatval($row['ttc20_day'])  + floatval($row['ttc10_day']),  2);
        $ca_salle_soir = round($ca_salle_day - $ca_salle_midi, 2);

        // CA total (incluant tout)
        $ca_total_midi = round(floatval($row['ttc20_midi']) + floatval($row['ttc10_midi']) + floatval($row['ttc55_midi']), 2);
        $ca_total_day  = round(floatval($row['ttc20_day'])  + floatval($row['ttc10_day'])  + floatval($row['ttc55_day']),  2);

        // Ticket moyen sur salle uniquement
        $c_midi = intval($row['covers_midi'] ?? 0);
        $c_day  = intval($row['covers_day']  ?? 0);
        $c_soir = max(0, $c_day - $c_midi);

        return [
            'soir'           => $soir,
            'covers_soir'    => $c_soir,
            'ca_salle_midi'  => $ca_salle_midi,
            'ca_salle_day'   => $ca_salle_day,
            'ca_salle_soir'  => $ca_salle_soir,
            'ca_total_midi'  => $ca_total_midi,
            'ca_total_day'   => $ca_total_day,
            'ca_total_soir'  => round($ca_total_day - $ca_total_midi, 2),
            'ticket_midi'    => $c_midi > 0 ? round($ca_salle_midi / $c_midi, 2) : 0,
            'ticket_day'     => $c_day  > 0 ? round($ca_salle_day  / $c_day,  2) : 0,
            'ticket_soir'    => $c_soir > 0 ? round($ca_salle_soir / $c_soir, 2) : 0,
        ];
    }

    private static function format_row($row) {
        if (!$row) return null;
        $row['calculs'] = self::compute($row);
        return $row;
    }

    // ── GET LIST ───────────────────────────────────────────
    public static function get_caisse(WP_REST_Request $r) {
        global $wpdb;
        $rid   = BRIGA_DB::get_restaurant_id();
        $limit = intval($r->get_param('limit') ?: 30);
        $rows  = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM " . BRIGA_DB::table('caisse') . " WHERE restaurant_id=%d ORDER BY report_date DESC LIMIT %d", $rid, $limit),
            ARRAY_A
        );
        $items = array_map([__CLASS__, 'format_row'], $rows ?: []);
        return new WP_REST_Response(['items' => $items, 'count' => count($items)], 200);
    }

    // ── GET BY DATE ────────────────────────────────────────
    public static function get_caisse_by_date(WP_REST_Request $r) {
        global $wpdb;
        $rid  = BRIGA_DB::get_restaurant_id();
        $date = sanitize_text_field($r->get_param('date'));
        $row  = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM " . BRIGA_DB::table('caisse') . " WHERE restaurant_id=%d AND report_date=%s", $rid, $date),
            ARRAY_A
        );
        if (!$row) return new WP_REST_Response(['found' => false, 'date' => $date], 200);
        return new WP_REST_Response(['found' => true, 'record' => self::format_row($row)], 200);
    }

    // ── POST (upsert midi ou journee) ──────────────────────
    public static function post_caisse(WP_REST_Request $r) {
        global $wpdb;
        $rid     = BRIGA_DB::get_restaurant_id();
        $date    = sanitize_text_field($r->get_param('date') ?: date('Y-m-d'));
        $service = sanitize_text_field($r->get_param('service') ?: 'midi'); // midi | journee
        if (!in_array($service, ['midi','journee'], true)) $service = 'midi';

        $suffix = $service === 'midi' ? '_midi' : '_day';

        // Champs attendus → colonnes SQL
        $map = [
            'covers'      => 'covers',
            'ttc20'       => 'ttc20',
            'ttc10'       => 'ttc10',
            'ttc55'       => 'ttc55',
            'uber'        => 'uber',
            'deliveroo'   => 'deliveroo',
            'remise'      => 'remise',
            'annulations' => 'annulations',
            'offerts'     => 'offerts',
            'fonds_caisse'=> 'fonds_caisse',
        ];

        $data = [];
        foreach ($map as $param => $col) {
            $val = $r->get_param($param);
            $data[$col . $suffix] = ($val !== null) ? floatval($val) : null;
        }
        // couverts = entier
        if (isset($data['covers'.$suffix])) $data['covers'.$suffix] = intval($data['covers'.$suffix]);

        $note = sanitize_text_field($r->get_param('note') ?: '');
        $now  = current_time('mysql');
        $t    = BRIGA_DB::table('caisse');

        $existing_id = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM $t WHERE restaurant_id=%d AND report_date=%s", $rid, $date)
        );

        // Filtrer les null (ne pas écraser les champs non envoyés)
        $data_clean = array_filter($data, fn($v) => $v !== null);
        $data_clean['note']       = $note;
        $data_clean['updated_at'] = $now;

        if ($existing_id) {
            $wpdb->update($t, $data_clean, ['id' => $existing_id]);
        } else {
            $data_clean['restaurant_id'] = $rid;
            $data_clean['report_date']   = $date;
            $data_clean['created_at']    = $now;
            $wpdb->insert($t, $data_clean);
        }

        // Recalculer et sauvegarder les colonnes calculées
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $t WHERE restaurant_id=%d AND report_date=%s", $rid, $date),
            ARRAY_A
        );
        $calc = self::compute($row);
        $wpdb->update($t, [
            'ca_salle_midi'   => $calc['ca_salle_midi'],
            'ca_salle_day'    => $calc['ca_salle_day'],
            'ticket_avg_midi' => $calc['ticket_midi'],
            'ticket_avg_day'  => $calc['ticket_day'],
            'updated_at'      => $now,
        ], ['id' => $row['id']]);

        $row['calculs'] = $calc;
        return new WP_REST_Response(['success' => true, 'record' => $row], 200);
    }

    // ── DELETE ─────────────────────────────────────────────
    public static function delete_caisse(WP_REST_Request $r) {
        global $wpdb;
        $rid  = BRIGA_DB::get_restaurant_id();
        $date = sanitize_text_field($r->get_param('date'));
        $wpdb->delete(BRIGA_DB::table('caisse'), ['restaurant_id' => $rid, 'report_date' => $date]);
        return new WP_REST_Response(['success' => true], 200);
    }
}
