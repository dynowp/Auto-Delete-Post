<?php

if (!defined('ABSPATH')) {
    exit;
}

class AutoDeletePostSettings {
    private $optionName = 'auto_delete_post_settings';
    private $defaultSettings;
    
    public function __construct() {
        $this->defaultSettings = array(
            'enabled' => false,
            'posts' => array(
                'enabled' => false,
                'limit' => 50,
                'status' => array('any'),
                'delete_attachments' => false,
                'date_filter_enabled' => false,
                'date_filter_mode' => 'include',
                'date_start' => '',
                'date_end' => '',
                'user_filter_enabled' => false,
                'user_filter_mode' => 'include',
                'selected_users' => array()
            ),
            'comments' => array(
                'enabled' => false,
                'limit' => 25,
                'status' => 'any'
            ),
            'categories' => array(
                'enabled' => false,
                'limit' => 10,
                'delete_empty_only' => false
            ),
            'tags' => array(
                'enabled' => false,
                'limit' => 10,
                'delete_empty_only' => false
            ),
            'cron_interval' => 'every_minute',
            'last_execution' => '',
            'statistics' => array(
                'posts_deleted' => 0,
                'comments_deleted' => 0,
                'categories_deleted' => 0,
                'tags_deleted' => 0,
                'total_executions' => 0,
                'last_execution_time' => ''
            )
        );
    }
    
    public function getSettings() {
        $settings = get_option($this->optionName, $this->defaultSettings);
        return wp_parse_args($settings, $this->defaultSettings);
    }
    
    public function getSetting($key, $default = null) {
        $settings = $this->getSettings();
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    public function updateSettings($newSettings) {
        $currentSettings = $this->getSettings();
        $updatedSettings = wp_parse_args($newSettings, $currentSettings);
        return update_option($this->optionName, $updatedSettings);
    }
    
    public function updateSetting($key, $value) {
        $settings = $this->getSettings();
        $settings[$key] = $value;
        return update_option($this->optionName, $settings);
    }
    
    public function isEnabled() {
        return $this->getSetting('enabled', false);
    }
    
    public function isPostDeletionEnabled() {
        $posts = $this->getSetting('posts', array());
        return isset($posts['enabled']) ? $posts['enabled'] : false;
    }
    
    public function isCommentDeletionEnabled() {
        $comments = $this->getSetting('comments', array());
        return isset($comments['enabled']) ? $comments['enabled'] : false;
    }
    
    public function isCategoryDeletionEnabled() {
        $categories = $this->getSetting('categories', array());
        return isset($categories['enabled']) ? $categories['enabled'] : false;
    }
    
    public function isTagDeletionEnabled() {
        $tags = $this->getSetting('tags', array());
        return isset($tags['enabled']) ? $tags['enabled'] : false;
    }
    
    public function shouldDeleteAttachments() {
        $posts = $this->getSetting('posts', array());
        return isset($posts['delete_attachments']) ? $posts['delete_attachments'] : false;
    }
    
    public function getPostLimit() {
        $posts = $this->getSetting('posts', array());
        return isset($posts['limit']) ? intval($posts['limit']) : 50;
    }
    
    public function getCommentLimit() {
        $comments = $this->getSetting('comments', array());
        return isset($comments['limit']) ? intval($comments['limit']) : 25;
    }
    
    public function getCategoryLimit() {
        $categories = $this->getSetting('categories', array());
        return isset($categories['limit']) ? intval($categories['limit']) : 10;
    }
    
    public function getTagLimit() {
        $tags = $this->getSetting('tags', array());
        return isset($tags['limit']) ? intval($tags['limit']) : 10;
    }
    
    public function shouldDeleteEmptyCategoriesOnly() {
        $categories = $this->getSetting('categories', array());
        return isset($categories['delete_empty_only']) ? $categories['delete_empty_only'] : false;
    }
    
    public function shouldDeleteEmptyTagsOnly() {
        $tags = $this->getSetting('tags', array());
        return isset($tags['delete_empty_only']) ? $tags['delete_empty_only'] : false;
    }
    
    public function getCronInterval() {
        return $this->getSetting('cron_interval', 'every_minute');
    }
    
    public function updateStatistics($type, $count) {
        $statistics = $this->getSetting('statistics', array());
        
        if (isset($statistics[$type . '_deleted'])) {
            $statistics[$type . '_deleted'] += $count;
        }
        
        $statistics['total_executions']++;
        $statistics['last_execution_time'] = current_time('Y-m-d H:i:s');
        
        $this->updateSetting('statistics', $statistics);
    }
    
    public function getStatistics() {
        return $this->getSetting('statistics', $this->defaultSettings['statistics']);
    }
    
    public function resetSettings() {
        return update_option($this->optionName, $this->defaultSettings);
    }
    
    public function resetStatistics() {
        $this->updateSetting('statistics', $this->defaultSettings['statistics']);
    }
    
    public function isDateFilterEnabled() {
        $posts = $this->getSetting('posts', array());
        return isset($posts['date_filter_enabled']) ? $posts['date_filter_enabled'] : false;
    }
    
    public function getDateFilterMode() {
        $posts = $this->getSetting('posts', array());
        return isset($posts['date_filter_mode']) ? $posts['date_filter_mode'] : 'include';
    }
    
    public function getDateStart() {
        $posts = $this->getSetting('posts', array());
        return isset($posts['date_start']) ? $posts['date_start'] : '';
    }
    
    public function getDateEnd() {
        $posts = $this->getSetting('posts', array());
        return isset($posts['date_end']) ? $posts['date_end'] : '';
    }
    
    public function isUserFilterEnabled() {
        $posts = $this->getSetting('posts', array());
        return isset($posts['user_filter_enabled']) ? $posts['user_filter_enabled'] : false;
    }
    
    public function getUserFilterMode() {
        $posts = $this->getSetting('posts', array());
        return isset($posts['user_filter_mode']) ? $posts['user_filter_mode'] : 'include';
    }
    
    public function getSelectedUsers() {
        $posts = $this->getSetting('posts', array());
        return isset($posts['selected_users']) ? $posts['selected_users'] : array();
    }
    
    public function getPostStatuses() {
        $posts = $this->getSetting('posts', array());
        $statuses = isset($posts['status']) ? $posts['status'] : array('any');
        
        // Ensure backward compatibility with old single status format
        if (is_string($statuses)) {
            $statuses = array($statuses);
        }
        
        return $statuses;
    }
    
    public function getAvailablePostStatuses() {
        $statuses = array(
            'any' => 'Any Status',
            'publish' => 'Published',
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'private' => 'Private',
            'future' => 'Scheduled',
            'trash' => 'Trash',
            'auto-draft' => 'Auto Draft',
            'inherit' => 'Inherit'
        );
        
        // Get custom post statuses
        $customStatuses = get_post_stati(array('_builtin' => false), 'objects');
        foreach ($customStatuses as $status => $statusObj) {
            $statuses[$status] = $statusObj->label;
        }
        
        return $statuses;
    }
    
    public function validateSettings($settings) {
        $errors = array();
        
        if (isset($settings['posts']['limit']) && (!is_numeric($settings['posts']['limit']) || $settings['posts']['limit'] < 1)) {
            $errors[] = 'Post limit must be a positive number';
        }
        
        if (isset($settings['comments']['limit']) && (!is_numeric($settings['comments']['limit']) || $settings['comments']['limit'] < 1)) {
            $errors[] = 'Comment limit must be a positive number';
        }
        
        if (isset($settings['categories']['limit']) && (!is_numeric($settings['categories']['limit']) || $settings['categories']['limit'] < 1)) {
            $errors[] = 'Category limit must be a positive number';
        }
        
        if (isset($settings['tags']['limit']) && (!is_numeric($settings['tags']['limit']) || $settings['tags']['limit'] < 1)) {
            $errors[] = 'Tag limit must be a positive number';
        }
        
        if (isset($settings['posts']['date_filter_enabled']) && $settings['posts']['date_filter_enabled']) {
            if (isset($settings['posts']['date_start']) && isset($settings['posts']['date_end'])) {
                $startDate = $settings['posts']['date_start'];
                $endDate = $settings['posts']['date_end'];
                
                if (!empty($startDate) && !empty($endDate)) {
                    if (strtotime($startDate) > strtotime($endDate)) {
                        $errors[] = 'Start date must be before end date';
                    }
                }
            }
        }
        
        return $errors;
    }
}