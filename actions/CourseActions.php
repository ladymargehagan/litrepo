<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/LogHandler.php';

class CourseActions {
    private $conn;
    private $logger;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
        $this->logger = new LogHandler();
    }

    public function getAllCourses() {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, COUNT(w.wordId) as totalWords 
                FROM courses c 
                LEFT JOIN words w ON c.courseId = w.courseId 
                GROUP BY c.courseId 
                ORDER BY c.level
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logger->error("Error getting courses: " . $e->getMessage());
            return [];
        }
    }

    public function getUserProgress($userId, $courseId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT up.*, 
                       COUNT(lw.id) as wordsLearned
                FROM user_progress up
                LEFT JOIN learned_words lw ON up.userId = lw.user_id
                WHERE up.userId = ? AND up.courseId = ?
                GROUP BY up.progressId
            ");
            $stmt->execute([$userId, $courseId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logger->error("Error getting user progress: " . $e->getMessage());
            return null;
        }
    }

    public function enrollUserInCourse($userId, $courseId) {
        try {
            // Check if already enrolled
            $stmt = $this->conn->prepare("
                SELECT enrollmentId FROM user_enrollments 
                WHERE userId = ? AND courseId = ?
            ");
            $stmt->execute([$userId, $courseId]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Already enrolled in this course'];
            }

            // Create enrollment
            $stmt = $this->conn->prepare("
                INSERT INTO user_enrollments (userId, courseId, status)
                VALUES (?, ?, 'active')
            ");
            $stmt->execute([$userId, $courseId]);

            // Initialize progress
            $stmt = $this->conn->prepare("
                INSERT INTO user_progress (userId, courseId)
                VALUES (?, ?)
            ");
            $stmt->execute([$userId, $courseId]);

            return ['success' => true, 'message' => 'Successfully enrolled in course'];
        } catch (Exception $e) {
            $this->logger->error("Error enrolling user in course: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to enroll in course'];
        }
    }

    public function updateProgress($userId, $courseId, $wordsLearned = 0, $quizScore = null) {
        try {
            $updates = [];
            $params = [$userId, $courseId];
            
            if ($wordsLearned > 0) {
                $updates[] = "wordsLearned = wordsLearned + ?";
                $params[] = $wordsLearned;
            }
            
            if ($quizScore !== null) {
                $updates[] = "quizzesTaken = quizzesTaken + 1";
                $updates[] = "totalScore = totalScore + ?";
                $params[] = $quizScore;
            }

            if (empty($updates)) {
                return ['success' => false, 'message' => 'No updates provided'];
            }

            $sql = "UPDATE user_progress SET " . implode(", ", $updates) . 
                   ", lastAccessed = CURRENT_TIMESTAMP WHERE userId = ? AND courseId = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'message' => 'Progress updated successfully'];
        } catch (Exception $e) {
            $this->logger->error("Error updating progress: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update progress'];
        }
    }
}
?>
