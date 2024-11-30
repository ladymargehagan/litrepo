<?php require_once __DIR__ . '/../php/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost in Translation</title>
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
        <a href="/" class="logo">Lost in Translation</a>
        <nav class="nav-links">
            <?php if (isLoggedIn()): ?>
                <a href="../view/dashboard.php">Dashboard</a>
                <a href="../view/profile.php">Profile</a>
                <a href="../actions/logout.php">Logout</a>
            <?php else: ?>
                <a href="../view/login.php">Login</a>
                <a href="../view/register.php">Register</a>
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