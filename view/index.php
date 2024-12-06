<?php

session_start();

// If user is logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /view/dashboard.php');
    exit();
}

// Include header (remove redirect to login since this is our landing page)
include 'header.php'; 
?>

<div style="text-align: center; padding: 4rem 0;">
    <h1>Want to learn a new language?</h1>
    <p style="font-size: 1.2rem; margin: 2rem 0;">
        Start your journey to mastering French with our interactive learning platform.
    </p>
    <div style="margin: 2rem 0;">
        <a href="../view/register.php" style="display: inline-block; padding: 1rem 2rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
            Get Started
        </a>
    </div>
</div>

<div style="padding: 2rem 0;">
    <h2>About Lost in Translation</h2>
    <p>
        Lost in Translation is a language learning platform that makes learning French easy and fun. 
        Our features include:
    </p>
    <ul>
        <li>Interactive lessons for all skill levels</li>
        <li>Daily word challenges</li>
        <li>Progress tracking</li>
        <li>Fun language games</li>
        <li>Leaderboard competition</li>
    </ul>
</div>

<?php 
include 'footer.php'; 
?>

