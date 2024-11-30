<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/functions.php';

class UserActions {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register($firstName, $lastName, $email, $password) {
        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        if (!validatePassword($password)) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters and contain uppercase, lowercase, and numbers'];
        }

        try {
            // Check if email exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("INSERT INTO users (firstName, lastName, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);

            return ['success' => true, 'message' => 'Registration successful'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT id, firstName, lastName, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
                return ['success' => true, 'message' => 'Login successful'];
            }

            return ['success' => false, 'message' => 'Invalid email or password'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }

    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logout successful'];
    }

    public function getUserProfile($userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.*, 
                       COUNT(DISTINCT up.courseId) as enrolled_courses,
                       SUM(up.wordsLearned) as total_words_learned,
                       SUM(up.totalScore) as total_score
                FROM users u
                LEFT JOIN user_progress up ON u.id = up.userId
                WHERE u.id = ?
                GROUP BY u.id
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>
