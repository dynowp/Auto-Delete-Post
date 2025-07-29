<?php

if (!defined('ABSPATH')) {
    exit;
}

class AutoDeletePostLogger {
    private $logFile;
    private $maxLogSize = 5242880; // 5MB
    
    public function __construct() {
        $this->logFile = plugin_dir_path(dirname(__FILE__)) . 'logs/deletion-logs.txt';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            wp_mkdir_p($logDir);
        }
    }
    
    public function log($message, $level = 'info') {
        $timestamp = current_time('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        $this->rotateLogIfNeeded();
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public function logInfo($message) {
        $this->log($message, 'INFO');
    }
    
    public function logWarning($message) {
        $this->log($message, 'WARNING');
    }
    
    public function logError($message) {
        $this->log($message, 'ERROR');
    }
    
    public function logSuccess($message) {
        $this->log($message, 'SUCCESS');
    }
    
    private function rotateLogIfNeeded() {
        if (file_exists($this->logFile) && filesize($this->logFile) > $this->maxLogSize) {
            $backupFile = $this->logFile . '.backup';
            if (file_exists($backupFile)) {
                unlink($backupFile);
            }
            rename($this->logFile, $backupFile);
        }
    }
    
    public function getLogs($lines = 100) {
        if (!file_exists($this->logFile)) {
            return array();
        }
        
        $content = file_get_contents($this->logFile);
        $logLines = explode(PHP_EOL, trim($content));
        
        return array_slice(array_reverse($logLines), 0, $lines);
    }
    
    public function clearLogs() {
        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
            return true;
        }
        return false;
    }
    
    public function getLogFile() {
        return $this->logFile;
    }
    
    public function downloadLogs() {
        if (!file_exists($this->logFile)) {
            return false;
        }
        
        $filename = 'deletion-logs-' . date('Y-m-d-H-i-s') . '.txt';
        
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($this->logFile));
        
        readfile($this->logFile);
        exit;
    }
}