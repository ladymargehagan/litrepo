<?php 
include 'header.php';
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>

<div style="max-width: 400px; margin: 2rem auto;">
    <h2>Login</h2>
    <form action="../actions/process_login.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required style="width: 100%; padding: 0.5rem;">
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required style="width: 100%; padding: 0.5rem;">
        </div>
        <button type="submit" style="padding: 0.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Login
        </button>
    </form>
    <p style="margin-top: 1rem;">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
</div>

<?php include 'footer.php'; ?>
