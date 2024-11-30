<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/LogHandler.php';
require_once 'UserActions.php';

$logger = new LogHandler();
$logger->info("Login attempt initiated");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    $userActions = new UserActions();
    $result = $userActions->login($email, $password);

    if ($result['success']) {
        $logger->info("Successful login for user: " . $email);
        flashMessage('Welcome back!', 'info');
        header('Location: ../view/dashboard.php');
    } else {
        $logger->warning("Failed login attempt for user: " . $email);
        flashMessage($result['message'], 'error');
        header('Location: ../view/login.php');
    }
    exit();
} else {
    $logger->warning("Invalid request method for login");
    header('Location: ../view/login.php');
    exit();
}
?>