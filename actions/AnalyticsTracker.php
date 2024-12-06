<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/LogHandler.php';

class AnalyticsTracker {
    private $conn;
    private $logger;
    private $userId;

    public function __construct($userId = null) {
        $this->conn = Database::getInstance()->getConnection();
        $this->logger = new LogHandler();
        $this->userId = $userId;
    }

    public function trackEvent($eventType, $eventData = []) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO user_analytics 
                (userId, event_type, event_data, created_at)
                VALUES (?, ?, ?, NOW())
            ");

            $stmt->execute([
                $this->userId,
                $eventType,
                json_encode($eventData)
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error("Error tracking analytics: " . $e->getMessage());
            return false;
        }
    }

    public function getUserInsights() {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_events,
                    COUNT(DISTINCT event_type) as unique_events
                FROM user_analytics
                WHERE userId = ?
                GROUP BY DATE(created_at)
                ORDER BY date DESC
                LIMIT 30
            ");

            $stmt->execute([$this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logger->error("Error getting user insights: " . $e->getMessage());
            return [];
        }
    }
} 