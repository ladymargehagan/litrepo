<?php
class CourseActions {
    private $db;
    private $logger;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = new LogHandler();
    }

    public function getAllCourses() {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
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
        } finally {
            $this->db->releaseConnection($conn);
        }
    }

    public function getUserProgress($userId, $courseId) {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                SELECT up.*, 
                       (SELECT COUNT(*) FROM learned_words 
                        WHERE userId = up.userId AND courseId = up.courseId) as wordsLearned,
                       (SELECT COUNT(*) FROM words 
                        WHERE courseId = up.courseId) as totalWords
                FROM user_progress up
                WHERE up.userId = ? AND up.courseId = ?
            ");
            $stmt->execute([$userId, $courseId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logger->error("Error getting user progress: " . $e->getMessage());
            return null;
        } finally {
            $this->db->releaseConnection($conn);
        }
    }
}
?>
