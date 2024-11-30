<?php
require_once __DIR__ . '/../php/functions.php';
require_once 'UserActions.php';

$userActions = new UserActions();
$result = $userActions->logout();

flashMessage($result['message'], $result['success'] ? 'info' : 'error');
header('Location: ../view/login.php');
exit();
?>