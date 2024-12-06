<?php
require_once __DIR__ . '/../config/database.php';

// Session configuration
$sessionPath = __DIR__ . '/../temp/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

function getDatabaseConnection() {
    return Database::getInstance()->getConnection();
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    session_start();
    
    // If user is not logged in, redirect to login page
    if (!isset($_SESSION['user_id'])) {
        header('Location: /view/login.php');
        exit();
    }
}

// Input handling functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

// Progress tracking functions
function getProgressPercentage($wordsLearned, $totalWords) {
    if ($totalWords == 0) return 0;
    return min(100, round(($wordsLearned / $totalWords) * 100));
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function getUserLevel($totalScore) {
    if ($totalScore < 100) return "Beginner";
    if ($totalScore < 500) return "Intermediate";
    if ($totalScore < 1000) return "Advanced";
    return "Expert";
}

// Flash message functions
function flashMessage($message, $type = 'info') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
