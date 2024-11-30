<?php 
include 'header.php';
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>

<div style="max-width: 400px; margin: 2rem auto;">
    <h2>Register</h2>
    <form action="../actions/process_register.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
        <div>
            <label for="firstName">First Name:</label>
            <input type="text" id="firstName" name="firstName" required style="width: 100%; padding: 0.5rem;">
        </div>
        <div>
            <label for="lastName">Last Name:</label>
            <input type="text" id="lastName" name="lastName" required style="width: 100%; padding: 0.5rem;">
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required style="width: 100%; padding: 0.5rem;">
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required 
                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                   title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters"
                   style="width: 100%; padding: 0.5rem;">
        </div>
        <div>
            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required style="width: 100%; padding: 0.5rem;">
        </div>
        <button type="submit" style="padding: 0.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Register
        </button>
    </form>
    <p style="margin-top: 1rem;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

<?php include 'footer.php'; ?>
