<?php

if (!defined('ABSPATH')) {
    exit;
}

class AutoDeletePostAdmin {
    private $settings;
    private $logger;
    
    public function __construct($settings, $logger) {
        $this->settings = $settings;
        $this->logger = $logger;
        
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminAssets'));
        add_action('wp_ajax_adp_clear_logs', array($this, 'ajaxClearLogs'));
        add_action('wp_ajax_adp_download_logs', array($this, 'ajaxDownloadLogs'));
        add_action('wp_ajax_adp_reset_statistics', array($this, 'ajaxResetStatistics'));
    }
    
    public function addAdminMenu() {
        add_management_page(
            'Auto Delete Post Settings',
            'Delete Post',
            'manage_options',
            'auto-delete-cron',
            array($this, 'renderAdminPage')
        );
    }
    
    public function registerSettings() {
        register_setting('auto_delete_post_settings', 'auto_delete_post_settings', array(
            'sanitize_callback' => array($this, 'sanitizeSettings')
        ));
    }
    
    public function sanitizeSettings($input) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($input['enabled']) ? (bool) $input['enabled'] : false;
        
        $sanitized['posts'] = array(
            'enabled' => isset($input['posts']['enabled']) ? (bool) $input['posts']['enabled'] : false,
            'limit' => isset($input['posts']['limit']) ? absint($input['posts']['limit']) : 50,
            'status' => isset($input['posts']['status']) ? sanitize_text_field($input['posts']['status']) : 'any',
            'delete_attachments' => isset($input['posts']['delete_attachments']) ? (bool) $input['posts']['delete_attachments'] : false,
            'date_filter_enabled' => isset($input['posts']['date_filter_enabled']) ? (bool) $input['posts']['date_filter_enabled'] : false,
            'date_filter_mode' => isset($input['posts']['date_filter_mode']) ? sanitize_text_field($input['posts']['date_filter_mode']) : 'exclude',
            'date_start' => isset($input['posts']['date_start']) ? sanitize_text_field($input['posts']['date_start']) : '',
            'date_end' => isset($input['posts']['date_end']) ? sanitize_text_field($input['posts']['date_end']) : '',
            'user_filter_enabled' => isset($input['posts']['user_filter_enabled']) ? (bool) $input['posts']['user_filter_enabled'] : false,
            'user_filter_mode' => isset($input['posts']['user_filter_mode']) ? sanitize_text_field($input['posts']['user_filter_mode']) : 'exclude',
            'selected_users' => isset($input['posts']['selected_users']) && is_array($input['posts']['selected_users']) ? array_map('absint', $input['posts']['selected_users']) : array()
        );
        
        $sanitized['comments'] = array(
            'enabled' => isset($input['comments']['enabled']) ? (bool) $input['comments']['enabled'] : false,
            'limit' => isset($input['comments']['limit']) ? absint($input['comments']['limit']) : 25,
            'status' => isset($input['comments']['status']) ? sanitize_text_field($input['comments']['status']) : 'any'
        );
        
        $sanitized['categories'] = array(
            'enabled' => isset($input['categories']['enabled']) ? (bool) $input['categories']['enabled'] : false,
            'limit' => isset($input['categories']['limit']) ? absint($input['categories']['limit']) : 10,
            'delete_empty_only' => isset($input['categories']['delete_empty_only']) ? (bool) $input['categories']['delete_empty_only'] : false
        );
        
        $sanitized['tags'] = array(
            'enabled' => isset($input['tags']['enabled']) ? (bool) $input['tags']['enabled'] : false,
            'limit' => isset($input['tags']['limit']) ? absint($input['tags']['limit']) : 10,
            'delete_empty_only' => isset($input['tags']['delete_empty_only']) ? (bool) $input['tags']['delete_empty_only'] : false
        );
        
        $sanitized['cron_interval'] = isset($input['cron_interval']) ? sanitize_text_field($input['cron_interval']) : 'every_minute';
        
        $currentSettings = $this->settings->getSettings();
        $sanitized['statistics'] = $currentSettings['statistics'];
        
        $errors = $this->settings->validateSettings($sanitized);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                add_settings_error('auto_delete_post_settings', 'validation_error', $error);
            }
        }
        
        return $sanitized;
    }
    
    public function enqueueAdminAssets($hook) {
        if ($hook !== 'tools_page_auto-delete-cron') {
            return;
        }
        
        wp_enqueue_style(
            'auto-delete-cron-admin',
            plugin_dir_url(dirname(__FILE__)) . 'admin/assets/css/admin.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_script(
            'auto-delete-cron-admin',
            plugin_dir_url(dirname(__FILE__)) . 'admin/assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('auto-delete-cron-admin', 'adpAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('adp_admin_nonce'),
            'confirmClearLogs' => 'Are you sure you want to clear all logs? This action cannot be undone.',
            'confirmResetStats' => 'Are you sure you want to reset all statistics? This action cannot be undone.'
        ));
    }
    
    public function renderAdminPage() {
        if (isset($_POST['submit']) && check_admin_referer('auto_delete_post_settings-options')) {
            $this->settings->updateSettings($_POST['auto_delete_post_settings']);
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
        }
        
        $settings = $this->settings->getSettings();
        $statistics = $this->settings->getStatistics();
        $logs = $this->logger->getLogs(50);
        $nextScheduled = wp_next_scheduled('remover_post_delete_cron');
        
        include plugin_dir_path(dirname(__FILE__)) . 'admin/views/admin-page.php';
    }
    
    public function ajaxClearLogs() {
        check_ajax_referer('adp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $result = $this->logger->clearLogs();
        
        if ($result) {
            wp_send_json_success('Logs cleared successfully');
        } else {
            wp_send_json_error('Failed to clear logs');
        }
    }
    
    public function ajaxDownloadLogs() {
        check_ajax_referer('adp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $this->logger->downloadLogs();
    }
    
    public function ajaxResetStatistics() {
        check_ajax_referer('adp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $this->settings->resetStatistics();
        wp_send_json_success('Statistics reset successfully');
    }
    
    public function getStatusBadge($enabled) {
        if ($enabled) {
            return '<span class="adp-status-badge adp-status-enabled">Enabled</span>';
        } else {
            return '<span class="adp-status-badge adp-status-disabled">Disabled</span>';
        }
    }
    
    public function formatTimestamp($timestamp) {
        if (empty($timestamp)) {
            return 'Never';
        }
        return date('Y-m-d H:i:s', strtotime($timestamp));
    }
    
    public function getCronIntervals() {
        return array(
            'every_minute' => 'Every Minute',
            'hourly' => 'Hourly',
            'twicedaily' => 'Twice Daily',
            'daily' => 'Daily',
            'weekly' => 'Weekly'
        );
    }
}