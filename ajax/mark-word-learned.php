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

    echo json_encode([
        'success' => true,
        'wordsLearned' => $progress['wordsLearned']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 