<?php
class LogHandler {
    private $logFile;
    private $logPath;
    
    public function __construct($logPath = '../logs') {
        // Create logs directory if it doesn't exist
        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
        }
        
        $this->logPath = $logPath;
        $this->logFile = $logPath . '/app_' . date('Y-m-d') . '.log';
    }
    
    public function log($message, $level = 'INFO') {
        // Format the log entry
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        
        // Write to log file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    public function error($message) {
        $this->log($message, 'ERROR');
    }
    
    public function info($message) {
        $this->log($message, 'INFO');
    }
    
    public function debug($message) {
        $this->log($message, 'DEBUG');
    }
    
    public function warning($message) {
        $this->log($message, 'WARNING');
    }
    
    public function getLogPath() {
        return $this->logPath;
    }
    
    public function clearLogs($daysToKeep = 30) {
        $files = glob($this->logPath . '/app_*.log');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $daysToKeep) {
                    unlink($file);
                }
            }
        }
    }
}
?> 