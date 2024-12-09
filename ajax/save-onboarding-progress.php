<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/LogHandler.php';

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $db = Database::getInstance()->getConnection();
    
    // Start transaction
    $db->beginTransaction();

    // Update user preferences
    $stmt = $db->prepare("
        INSERT INTO user_preferences 
        (userId, language, proficiency_level, daily_goal, updated_at)
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        language = VALUES(language),
        proficiency_level = VALUES(proficiency_level),
        daily_goal = VALUES(daily_goal),
        updated_at = NOW()
    ");

    $stmt->execute([
        $userId,
        $data['language'] ?? null,
        $data['level'] ?? null,
        $data['dailyGoal'] ?? null
    ]);

    // Track analytics
    Reference to SaveOnboardingProgress.php:
    startLine: 71
    endLine: 87

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
} 