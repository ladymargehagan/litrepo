<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../actions/UserActions.php';
require_once __DIR__ . '/../actions/AnalyticsTracker.php';
include 'header.php';

// Ensure user is logged in
requireLogin();

$userActions = new UserActions();
$analytics = new AnalyticsTracker($_SESSION['user_id']);

// Get user profile data
$profile = $userActions->getUserProfile($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Lost in Translation</title>
    <link rel="stylesheet" href="/css/profile.css">
</head>
<body>
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($profile['firstName'], 0, 1) . substr($profile['lastName'], 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($profile['firstName'] . ' ' . $profile['lastName']); ?></h1>
                <p class="member-since">Member since <?php echo formatDate($profile['joinDate']); ?></p>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($profile['total_words_learned'] ?? 0); ?></div>
                <div class="stat-label">Words Learned</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $analytics->getCurrentStreak(); ?></div>
                <div class="stat-label">Day Streak</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($profile['enrolled_courses'] ?? 0); ?></div>
                <div class="stat-label">Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo getUserLevel($profile['total_score'] ?? 0); ?></div>
                <div class="stat-label">Level</div>
            </div>
        </div>

        <!-- Activity Calendar -->
        <div class="activity-section">
            <h2>Activity History</h2>
            <div class="activity-calendar">
                <?php 
                $lastThirtyDays = $analytics->getLastThirtyDaysActivity();
                foreach ($lastThirtyDays as $day): ?>
                    <div class="calendar-day <?php echo $day['hasActivity'] ? 'active' : ''; ?>"
                         title="<?php echo $day['date']; ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Learning Progress -->
        <div class="progress-section">
            <h2>Learning Progress</h2>
            <canvas id="progressChart"></canvas>
        </div>

        <!-- Password Change Section -->
        <div class="password-section">
            <h2>Security Settings</h2>
            <form id="passwordChangeForm" class="password-form">
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="currentPassword" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required 
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                           title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
                <div id="passwordError" class="error-message"></div>
                <button type="submit" class="btn-change-password">Update Password</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Password change form handler
        document.getElementById('passwordChangeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const errorDiv = document.getElementById('passwordError');
            
            if (newPassword !== confirmPassword) {
                errorDiv.textContent = 'New passwords do not match';
                return;
            }
            
            try {
                const response = await fetch('/ajax/change_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        currentPassword: document.getElementById('currentPassword').value,
                        newPassword: newPassword
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    errorDiv.textContent = 'Password updated successfully';
                    errorDiv.style.color = 'green';
                    this.reset();
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.style.color = 'red';
                }
            } catch (error) {
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.style.color = 'red';
            }
        });

        // Initialize progress chart
        const ctx = document.getElementById('progressChart').getContext('2d');
        const weeklyProgress = <?php echo json_encode($analytics->getWeeklyProgress()); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: weeklyProgress.map(day => day.date),
                datasets: [{
                    label: 'Words Learned',
                    data: weeklyProgress.map(day => day.words_learned),
                    borderColor: '#58CC02',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(88, 204, 2, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php include 'footer.php'; ?>
