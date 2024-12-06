<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../actions/OnboardingActions.php';
include 'header.php';

$step = $_GET['step'] ?? 1;
$onboardingActions = new OnboardingActions();

// If user completed onboarding, redirect to dashboard
if ($onboardingActions->isOnboardingComplete($_SESSION['user_id'])) {
    header('Location: /view/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Lost in Translation</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/onboarding.css">
</head>
<body>

<div class="onboarding-container">
    <div class="progress-bar">
        <div class="progress" style="width: <?php echo ($step * 20); ?>%"></div>
    </div>

    <?php include "onboarding/step{$step}.php"; ?>

    <div class="navigation-buttons">
        <?php if ($step > 1): ?>
            <button onclick="prevStep()" class="btn-secondary">Back</button>
        <?php endif; ?>
        
        <?php if ($step < 5): ?>
            <button onclick="nextStep()" class="btn-primary">Continue</button>
        <?php else: ?>
            <button onclick="finishOnboarding()" class="btn-success">Start Learning!</button>
        <?php endif; ?>
    </div>
</div>

<script src="/js/onboarding.js"></script>
</body>
</html> 