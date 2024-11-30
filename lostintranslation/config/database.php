<?php
require_once __DIR__ . '/../php/LogHandler.php';

class Database {
    private static $instance = null;
    private $connections = [];
    private $maxConnections = 10;
    private $logger;
    
    private $config = [
        'host' => 'localhost',
        'db_name' => 'litdb',
        'username' => 'root',
        'password' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_PERSISTENT => true
        ]
    ];

    private function __construct() {
        $this->logger = new LogHandler();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        // Reuse available connection
        foreach ($this->connections as $key => $conn) {
            if ($conn['in_use'] === false) {
                $this->connections[$key]['in_use'] = true;
                return $conn['connection'];
            }
        }

        // Create new connection if under limit
        if (count($this->connections) < $this->maxConnections) {
            try {
                $dsn = "mysql:host={$this->config['host']};dbname={$this->config['db_name']};charset=utf8mb4";
                $this->logger->info("Attempting to connect to database with DSN: " . $dsn);
                
                $pdo = new PDO($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
                
                $this->connections[] = [
                    'connection' => $pdo,
                    'in_use' => true,
                    'created' => time()
                ];
                
                $this->logger->info("Database connection established successfully");
                return $pdo;
            } catch (PDOException $e) {
                $this->logger->error("Connection error: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }
        }

        throw new Exception("Maximum connections reached");
    }

    public function releaseConnection($conn) {
        foreach ($this->connections as $key => $connection) {
            if ($connection['connection'] === $conn) {
                $this->connections[$key]['in_use'] = false;
                break;
            }
        }
    }
}
?>
