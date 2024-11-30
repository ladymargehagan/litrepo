<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/functions.php';

// Ensure user is logged in
requireLogin();

class EnrollmentActions {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();  // Get PDO connection
    }

    /**
     * Enroll user in a course
     */
    public function enrollUser($userId, $courseId) {
        try {
            // Check if user is already enrolled
            $stmt = $this->conn->prepare(
                "SELECT * FROM user_enrollments 
                 WHERE userId = ? AND courseId = ?"
            );
            $stmt->execute([$userId, $courseId]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'You are already enrolled in this course'
                ];
            }

            // Create new enrollment
            $stmt = $this->conn->prepare(
                "INSERT INTO user_enrollments (userId, courseId, enrollmentDate, status) 
                 VALUES (?, ?, NOW(), 'active')"
            );
            $success = $stmt->execute([$userId, $courseId]);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Successfully enrolled in the course'
                ];
            } else {
                throw new Exception('Failed to enroll in course');
            }

        } catch (Exception $e) {
            error_log("Enrollment Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during enrollment'
            ];
        }
    }

    /**
     * Get user's enrolled courses
     */
    public function getEnrolledCourses($userId) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT c.*, ue.enrollmentDate, up.wordsLearned, up.quizzesTaken 
                 FROM courses c 
                 JOIN user_enrollments ue ON c.courseId = ue.courseId 
                 LEFT JOIN user_progress up ON c.courseId = up.courseId AND up.userId = ue.userId
                 WHERE ue.userId = ? AND ue.status = 'active'"
            );
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching enrolled courses: " . $e->getMessage());
            return [];
        }
    }
}

// Handle enrollment request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['courseId'])) {
    $enrollment = new EnrollmentActions();
    $result = $enrollment->enrollUser($_SESSION['user_id'], $_POST['courseId']);
    
    // Set flash message
    $_SESSION['flash'] = [
        'type' => $result['success'] ? 'success' : 'error',
        'message' => $result['message']
    ];
    
    // Redirect back to dashboard
    header('Location: /view/dashboard.php');
    exit;
}
