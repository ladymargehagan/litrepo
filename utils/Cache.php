<?php
require_once __DIR__ . '/../php/LogHandler.php';

class Cache {
    private $cacheDir;
    private $logger;

    public function __construct() {
        $this->cacheDir = __DIR__ . '/../cache/';
        $this->logger = new LogHandler();
        
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function exists($key) {
        $filename = $this->cacheDir . md5($key);
        if (!file_exists($filename)) {
            return false;
        }
        
        try {
            $content = file_get_contents($filename);
            $data = unserialize($content);
            
            if ($data['expires'] < time()) {
                unlink($filename);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            $this->logger->error("Cache check failed: " . $e->getMessage());
            return false;
        }
    }

    public function get($key) {
        $filename = $this->cacheDir . md5($key);
        try {
            if (!$this->exists($key)) {
                return null;
            }
            
            $content = file_get_contents($filename);
            $data = unserialize($content);
            return $data['value'];
        } catch (Exception $e) {
            $this->logger->error("Cache get failed: " . $e->getMessage());
            return null;
        }
    }

    public function set($key, $value, $ttl = 3600) {
        $filename = $this->cacheDir . md5($key);
        try {
            $data = [
                'value' => $value,
                'expires' => time() + $ttl
            ];
            
            file_put_contents($filename, serialize($data));
            return true;
        } catch (Exception $e) {
            $this->logger->error("Cache set failed: " . $e->getMessage());
            return false;
        }
    }

    public function delete($key) {
        $filename = $this->cacheDir . md5($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    public function clear() {
        $files = glob($this->cacheDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
} 