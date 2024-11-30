<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$_SESSION['current_word_index'] = $data['index'];

echo json_encode(['success' => true]); 