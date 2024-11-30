<?php
require_once __DIR__ . '/../php/functions.php';
require_once 'UserActions.php';

$userActions = new UserActions();
$result = $userActions->logout();

flashMessage('You have been logged out successfully', 'info');
header('Location: ../view/login.php');
exit();
?>