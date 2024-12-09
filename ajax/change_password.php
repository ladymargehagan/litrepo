<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../actions/UserActions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['currentPassword']) || !isset($data['newPassword'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$userActions = new UserActions();
$result = $userActions->changePassword(
    $_SESSION['user_id'],
    $data['currentPassword'],
    $data['newPassword']
);

echo json_encode($result); 