<?php
/*
Plugin Name: Auto Delete Post
Description: A comprehensive plugin that provides an administrative interface for configuring automated deletion of posts, taxonomies, comments, and categories with advanced options and logging system.
Version: 2.1.0
Author: DynoWP
Author URI: https://dynowp.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: auto-delete-post
Requires at least: 6.3
Tested up to: 6.8
Requires PHP: 7.4
*/

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ADP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ADP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ADP_VERSION', '2.0.0');

class AutoDeletePost {
    private $settings;
    private $logger;
    private $deleter;
    private $admin;
    
    public function __construct() {
        $this->loadDependencies();
        $this->initializeComponents();
        $this->setupHooks();
    }
    
    private function loadDependencies() {
        require_once ADP_PLUGIN_DIR . 'includes/class-settings.php';
        require_once ADP_PLUGIN_DIR . 'includes/class-logger.php';
        require_once ADP_PLUGIN_DIR . 'includes/class-deleter.php';
        require_once ADP_PLUGIN_DIR . 'includes/class-admin.php';
    }
    
    private function initializeComponents() {
        $this->settings = new AutoDeletePostSettings();
        $this->logger = new AutoDeletePostLogger();
        $this->deleter = new AutoDeletePostDeleter($this->logger, $this->settings);
        
        if (is_admin()) {
            $this->admin = new AutoDeletePostAdmin($this->settings, $this->logger);
        }
    }
    
    private function setupHooks() {
        add_action('wp_loaded', array($this, 'registerCronJob'));
        add_action('remover_post_delete_cron', array($this, 'executeDeleteProcess'));
        add_filter('cron_schedules', array($this, 'addAutoIntervals'));
        
        register_activation_hook(__FILE__, array($this, 'onActivation'));
        register_deactivation_hook(__FILE__, array($this, 'onDeactivation'));
    }
    
    public function registerCronJob() {
        if (!$this->settings->isEnabled()) {
            $this->unscheduleCronJob();
            return;
        }
        
        $interval = $this->settings->getCronInterval();
        
        if (!wp_next_scheduled('remover_post_delete_cron')) {
            wp_schedule_event(time(), $interval, 'remover_post_delete_cron');
            $this->logger->logInfo("Cron job scheduled with interval: {$interval}");
        }
    }
    
    public function unscheduleCronJob() {
        $timestamp = wp_next_scheduled('remover_post_delete_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'remover_post_delete_cron');
            $this->logger->logInfo('Cron job unscheduled');
        }
    }
    
    public function addAutoIntervals($schedules) {
        $schedules['every_minute'] = array(
            'interval' => 60,
            'display'  => __('Every Minute', 'auto-delete-cron'),
        );
        
        $schedules['every_five_minutes'] = array(
            'interval' => 300,
            'display'  => __('Every 5 Minutes', 'auto-delete-cron'),
        );
        
        $schedules['every_fifteen_minutes'] = array(
            'interval' => 900,
            'display'  => __('Every 15 Minutes', 'auto-delete-cron'),
        );
        
        return $schedules;
    }
    
    public function executeDeleteProcess() {
        $this->deleter->executeDeleteProcess();
    }
    
    public function onActivation() {
        $this->logger->logInfo('Auto Delete Post plugin activated');
        
        if (!wp_next_scheduled('remover_post_delete_cron')) {
            wp_schedule_event(time(), 'every_minute', 'remover_post_delete_cron');
        }
    }
    
    public function onDeactivation() {
        $this->logger->logInfo('Auto Delete Post plugin deactivated');
        $this->unscheduleCronJob();
    }
    
    public function getSettings() {
        return $this->settings;
    }
    
    public function getLogger() {
        return $this->logger;
    }
    
    public function getDeleter() {
        return $this->deleter;
    }
}

new AutoDeletePost();