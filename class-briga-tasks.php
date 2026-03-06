<?php
if (!defined('ABSPATH')) exit;

class BRIGA_Tasks {

    public static function init() {
        add_action('wp_ajax_briga_tasks_list',           [__CLASS__, 'tasks_list']);
        add_action('wp_ajax_nopriv_briga_tasks_list',    [__CLASS__, 'tasks_list']);
        add_action('wp_ajax_briga_tasks_add',            [__CLASS__, 'tasks_add']);
        add_action('wp_ajax_nopriv_briga_tasks_add',     [__CLASS__, 'tasks_add']);
        add_action('wp_ajax_briga_tasks_toggle',         [__CLASS__, 'tasks_toggle']);
        add_action('wp_ajax_nopriv_briga_tasks_toggle',  [__CLASS__, 'tasks_toggle']);
        add_action('wp_ajax_briga_tasks_delete',         [__CLASS__, 'tasks_delete']);
        add_action('wp_ajax_nopriv_briga_tasks_delete',  [__CLASS__, 'tasks_delete']);
    }

    private static function get_tasks() {
        $tasks = get_option('briga_tasks', []);
        return is_array($tasks) ? array_values($tasks) : [];
    }

    private static function save_tasks($tasks) {
        update_option('briga_tasks', array_values($tasks), false);
    }

    public static function tasks_list() {
        check_ajax_referer('briga_nonce', 'nonce');
        wp_send_json_success(self::get_tasks());
    }

    public static function tasks_add() {
        check_ajax_referer('briga_nonce', 'nonce');
        $title    = isset($_POST['title'])    ? sanitize_text_field($_POST['title'])    : '';
        $priority = isset($_POST['priority']) ? sanitize_text_field($_POST['priority']) : 'med';
        if (!$title) wp_send_json_error(['message' => 'Titre obligatoire']);
        if (!in_array($priority, ['low', 'med', 'high'], true)) $priority = 'med';
        $tasks = self::get_tasks();
        $tasks[] = ['id' => time() . rand(10, 99), 'title' => $title, 'priority' => $priority, 'status' => 'todo'];
        self::save_tasks($tasks);
        wp_send_json_success(['created' => true]);
    }

    public static function tasks_toggle() {
        check_ajax_referer('briga_nonce', 'nonce');
        $id     = isset($_POST['id'])     ? (string) $_POST['id']                       : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status'])        : 'todo';
        if (!in_array($status, ['todo', 'done'], true)) $status = 'todo';
        $tasks = self::get_tasks();
        foreach ($tasks as &$task) {
            if ((string)($task['id'] ?? '') === $id) $task['status'] = $status;
        }
        unset($task);
        self::save_tasks($tasks);
        wp_send_json_success(['updated' => true]);
    }

    public static function tasks_delete() {
        check_ajax_referer('briga_nonce', 'nonce');
        $id    = isset($_POST['id']) ? (string) $_POST['id'] : '';
        $tasks = self::get_tasks();
        $tasks = array_values(array_filter($tasks, function ($t) use ($id) {
            return (string)($t['id'] ?? '') !== $id;
        }));
        self::save_tasks($tasks);
        wp_send_json_success(['deleted' => true]);
    }
}
