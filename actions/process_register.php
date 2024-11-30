<?php
require_once __DIR__ . '/../php/functions.php';
require_once 'UserActions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitizeInput($_POST['firstName']);
    $lastName = sanitizeInput($_POST['lastName']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        flashMessage('Passwords do not match', 'error');
        header('Location: ../view/register.php');
        exit();
    }

    $userActions = new UserActions();
    $result = $userActions->register($firstName, $lastName, $email, $password);

    if ($result['success']) {
        flashMessage('Registration successful! Please log in.', 'info');
        header('Location: ../view/login.php');
    } else {
        flashMessage($result['message'], 'error');
        header('Location: ../view/register.php');
    }
    exit();
} else {
    header('Location: ../view/register.php');
    exit();
}
?>