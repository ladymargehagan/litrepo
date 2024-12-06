<?php
function renderLearningStreak($analytics) {
    $streak = $analytics->getCurrentStreak($_SESSION['user_id']);
    ?>
    <div class="analytics-widget streak-widget">
        <div class="widget-header">
            <h3>Learning Streak</h3>
            <span class="streak-count">🔥 <?php echo $streak; ?> days</span>
        </div>
        <div class="streak-calendar">
            <?php 
            $lastThirtyDays = $analytics->getLastThirtyDaysActivity();
            foreach ($lastThirtyDays as $day): ?>
                <div class="day-marker <?php echo $day['hasActivity'] ? 'active' : ''; ?>"
                     title="<?php echo $day['date']; ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function renderProgressChart($analytics) {
    $weeklyProgress = $analytics->getWeeklyProgress($_SESSION['user_id']);
    ?>
    <div class="analytics-widget progress-widget">
        <div class="widget-header">
            <h3>Weekly Progress</h3>
        </div>
        <canvas id="progressChart"></canvas>
    </div>
    <script>
        const ctx = document.getElementById('progressChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($weeklyProgress, 'date')); ?>,
                datasets: [{
                    label: 'Words Learned',
                    data: <?php echo json_encode(array_column($weeklyProgress, 'words_learned')); ?>,
                    borderColor: '#58CC02',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
    <?php
}
?> 