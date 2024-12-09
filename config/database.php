<?php
require_once __DIR__ . '/../php/LogHandler.php';

class Database {
    private static $instance = null;
    private $conn = null;
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=localhost;dbname=litdb;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true
                ]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
        self::$instance = null;
    }

    public function ensureConnection() {
        if (!$this->conn || !$this->ping()) {
            self::$instance = null;
            return self::getInstance()->getConnection();
        }
        return $this->conn;
    }

    private function ping() {
        try {
            $this->conn->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn = null;
        }
    }
}
?>
