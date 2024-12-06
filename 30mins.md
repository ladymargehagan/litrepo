Here's a 30-minute roadmap for completing essential pages and functionality:

### 1. Fix Database Issues (5 mins)
- Create missing `learned_words` table:
```sql
CREATE TABLE IF NOT EXISTS `learned_words` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `userId` INT NOT NULL,
    `wordId` INT NOT NULL,
    `courseId` INT NOT NULL,
    `learned_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_learned` (`userId`, `wordId`),
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`),
    FOREIGN KEY (`wordId`) REFERENCES `words`(`wordId`),
    FOREIGN KEY (`courseId`) REFERENCES `courses`(`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

- Fix translation column length issue:
```sql
ALTER TABLE translations 
MODIFY COLUMN translation TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
```

### 2. Authentication Pages (10 mins)
- Login page (`view/login.php`)
  - Email/password form
  - Remember me checkbox
  - Forgot password link
  - Register link

- Register page (`view/register.php`)
  - Name fields
  - Email field
  - Password with confirmation
  - Terms acceptance checkbox

### 3. Course Pages (10 mins)
- Course listing (`view/courses.php`)
  - Grid of available courses
  - Progress indicators for enrolled courses
  - Enroll button for non-enrolled courses

- Course detail (`view/course-detail.php`)
  - Course information
  - Word list with learning progress
  - Practice exercises section
  - Progress statistics

### 4. Learning Interface (5 mins)
- Word practice page (`view/practice.php`)
  - Word display with translation
  - Mark as learned button
  - Next word button
  - Progress indicator

References from provided code:
- Dashboard structure:

```1:34:view/dashboard.php
<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../actions/TranslateAPI.php';
require_once __DIR__ . '/../actions/CourseActions.php';
require_once __DIR__ . '/../php/LogHandler.php';
include 'header.php';

// Ensure user is logged in
requireLogin();

$translateAPI = new TranslateAPI();
$courseActions = new CourseActions();

// Get word of the day with full definition, translation, and examples
$wordOfDay = $translateAPI->getWordOfDay();

// Get user's progress across all courses
$userCourses = [];
$enrolledCourses = [];
$availableCourses = $courseActions->getAllCourses();

// Get progress for enrolled courses
foreach ($availableCourses as $course) {
    $progress = $courseActions->getUserProgress($_SESSION['user_id'], $course['courseId']);
    if ($progress) {
        $totalWordsLearned += (int)($progress['wordsLearned'] ?? 0);
        $course['progress'] = $progress;
        $enrolledCourses[] = $course;
    }
}

// Calculate user level based on total words learned
$userLevel = getUserLevel($totalWordsLearned);
?>
```


- Course actions:

```1:60:actions/CourseActions.php
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
```


- Word learning AJAX:

```1:38:ajax/mark-word-learned.php
<?php
require_once('../config/database.php');
session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];
$wordId = $data['wordId'];
$courseId = $data['courseId'];

$database = new Database();
$conn = $database->getConnection();

try {
    // Mark word as learned
    $stmt = $conn->prepare("
        INSERT INTO learned_words (userId, wordId, courseId, learned_date)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE learned_date = NOW()
    ");
    $stmt->execute([$userId, $wordId, $courseId]);

    // Update user progress
    $stmt = $conn->prepare("
        UPDATE user_progress 
        SET wordsLearned = wordsLearned + 1
        WHERE userId = ? AND courseId = ?
    ");
    $stmt->execute([$userId, $courseId]);

    // Get updated word count
    $stmt = $conn->prepare("
        SELECT wordsLearned FROM user_progress 
        WHERE userId = ? AND courseId = ?
    ");
    $stmt->execute([$userId, $courseId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
```


This roadmap focuses on core functionality and can be completed in 30 minutes by leveraging existing code and database structure. The main goal is to get a working MVP with user authentication, course management, and basic word learning features.
