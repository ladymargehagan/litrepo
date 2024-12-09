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

    public function getCurrentStreak() {
        try {
            $stmt = $this->conn->prepare("
                WITH RECURSIVE dates AS (
                    SELECT DISTINCT DATE(created_at) as activity_date
                    FROM user_analytics
                    WHERE userId = ?
                    ORDER BY activity_date DESC
                ),
                streak_calc AS (
                    SELECT 
                        activity_date,
                        ROW_NUMBER() OVER (ORDER BY activity_date DESC) as day_rank,
                        DATEDIFF(
                            activity_date,
                            DATE_SUB(CURRENT_DATE, INTERVAL ROW_NUMBER() OVER (ORDER BY activity_date DESC) - 1 DAY)
                        ) as date_diff
                    FROM dates
                )
                SELECT COUNT(*) as streak
                FROM streak_calc
                WHERE date_diff = 0
                AND day_rank = ROW_NUMBER() OVER (ORDER BY activity_date DESC)
            ");

            $stmt->execute([$this->userId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $this->logger->error("Error calculating streak: " . $e->getMessage());
            return 0;
        }
    }

    public function getLastThirtyDaysActivity() {
        try {
            // First, get all active days in the last 30 days
            $stmt = $this->conn->prepare("
                SELECT DISTINCT DATE(created_at) as active_date
                FROM user_analytics
                WHERE userId = ?
                AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            ");
            $stmt->execute([$this->userId]);
            $activeDays = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Generate array of last 30 days with activity status
            $result = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $result[] = [
                    'date' => $date,
                    'hasActivity' => in_array($date, $activeDays)
                ];
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error("Error getting activity history: " . $e->getMessage());
            return array_fill(0, 30, ['date' => '', 'hasActivity' => false]);
        }
    }

    public function getWeeklyProgress() {
        try {
            $stmt = $this->conn->prepare("
                WITH RECURSIVE dates AS (
                    SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS date
                    FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                    CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                    CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
                    WHERE CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY >= CURDATE() - INTERVAL 7 DAY
                ),
                daily_progress AS (
                    SELECT 
                        DATE(created_at) as date,
                        COUNT(DISTINCT CASE 
                            WHEN event_type = 'word_learned' THEN JSON_EXTRACT(event_data, '$.wordId')
                            ELSE NULL 
                        END) as words_learned
                    FROM user_analytics
                    WHERE userId = ?
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    GROUP BY DATE(created_at)
                )
                SELECT 
                    dates.date,
                    COALESCE(daily_progress.words_learned, 0) as words_learned
                FROM dates
                LEFT JOIN daily_progress ON dates.date = daily_progress.date
                ORDER BY dates.date ASC
            ");

            $stmt->execute([$this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logger->error("Error getting weekly progress: " . $e->getMessage());
            return array_fill(0, 7, ['date' => '', 'words_learned' => 0]);
        }
    }
} 