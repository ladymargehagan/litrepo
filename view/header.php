<?php 
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../config/api_config.php';  // Updated to use existing config
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost in Translation</title>
    
    <!-- Base CSS -->
    <link rel="stylesheet" href="../public/css/main.css">
    <link rel="stylesheet" href="../public/css/layout.css">
    <link rel="stylesheet" href="../public/css/components.css">
    
    <!-- Page specific CSS -->
    <?php
    $currentPage = basename($_SERVER['PHP_SELF'], '.php');
    if (file_exists(__DIR__ . "/../public/css/pages/{$currentPage}.css")) {
        echo "<link rel='stylesheet' href='/public/css/pages/{$currentPage}.css'>";
    }
    ?>
    
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Minimal CSS for testing */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header { background: #f8f9fa; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: bold; text-decoration: none; color: #333; }
        .nav-links { display: flex; gap: 1rem; }
        .nav-links a { text-decoration: none; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 1rem; }
        .flash { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        .flash.info { background: #d1ecf1; }
        .flash.error { background: #f8d7da; }
    </style>
</head>
<body>
    <header class="header">
        <a href="<?php echo BASE_URL; ?>/" class="logo">Lost in Translation</a>
        <nav class="nav-links">
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>../view/dashboard.php">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>../view/profile.php">Profile</a>
                <a href="<?php echo BASE_URL; ?>../actions/logout.php">Logout</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>../view/login.php">Login</a>
                <a href="<?php echo BASE_URL; ?>../view/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">
        <?php
        $flash = getFlashMessage();
        if ($flash): ?>
            <div class="flash <?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?> 