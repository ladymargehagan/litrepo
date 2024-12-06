<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../actions/TranslateAPI.php';
require_once __DIR__ . '/../actions/CourseActions.php';
require_once __DIR__ . '/../php/LogHandler.php';
require_once __DIR__ . '/../actions/AnalyticsTracker.php';
require_once __DIR__ . '/components/analytics_widgets.php';
include 'header.php';

// Ensure user is logged in
requireLogin();

$translateAPI = new TranslateAPI();
$courseActions = new CourseActions();
$analytics = new AnalyticsTracker($_SESSION['user_id']);

// Track dashboard view
$analytics->trackEvent('dashboard_view');

// Get word of the day with full definition, translation, and examples
$wordOfDay = $translateAPI->getWordOfDay();

// Get user's progress across all courses
$userCourses = [];
$enrolledCourses = [];
$availableCourses = $courseActions->getAllCourses();

// Get progress for enrolled courses
foreach ($availableCourses as $course) {
    $progress = $courseActions->getUserProgress($_SESSION['user_id'], $course['courseId']);
    if ($progress) {
        $totalWordsLearned += (int)($progress['wordsLearned'] ?? 0);
        $course['progress'] = $progress;
        $enrolledCourses[] = $course;
    }
}

// Calculate user level based on total words learned
$userLevel = getUserLevel($totalWordsLearned);
?>

<div class="dashboard-container">
    <!-- Word of the Day Section -->
    <div class="word-of-day">
        <h2>Word of the Day</h2>
        <?php if ($wordOfDay && isset($wordOfDay['word']) && isset($wordOfDay['translation'])): ?>
            <div class="word-display">
                <div class="word-main">
                    <span class="word"><?php echo htmlspecialchars($wordOfDay['word']); ?></span>
                    <span class="arrow">→</span>
                    <span class="translation"><?php echo htmlspecialchars($wordOfDay['translation']); ?></span>
                </div>
                
                <?php if (!empty($wordOfDay['definition'])): ?>
                    <div class="definitions">
                        <?php foreach ($wordOfDay['definition'] as $meaning): ?>
                            <div class="meaning">
                                <h4><?php echo htmlspecialchars($meaning['partOfSpeech'] ?? 'Unknown'); ?></h4>
                                <ul>
                                    <?php foreach ($meaning['definitions'] as $def): ?>
                                        <li><?php echo htmlspecialchars($def['definition']); ?></li>
                                        <?php if (!empty($def['example'])): ?>
                                            <p class="example">"<?php echo htmlspecialchars($def['example']); ?>"</p>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="error">Word of the day is currently unavailable. Please try again later.</p>
        <?php endif; ?>
    </div>

    <!-- Enrolled Courses -->
    <?php if (!empty($enrolledCourses)): ?>
        <div class="enrolled-courses">
            <h2>Your Courses</h2>
            <div class="course-grid">
                <?php foreach ($enrolledCourses as $course): ?>
                    <div class="course-card">
                        <h3><?php echo htmlspecialchars($course['courseName']); ?></h3>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo getProgressPercentage($course['progress']['wordsLearned'] ?? 0, $course['totalWords'] ?? 0); ?>%"></div>
                        </div>
                        <p class="progress-text">
                            <?php echo $course['progress']['wordsLearned'] ?? 0; ?> / <?php echo $course['totalWords'] ?? 0; ?> words
                        </p>
                        <a href="course.php?id=<?php echo $course['courseId']; ?>" class="continue-btn">Continue Learning</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="no-courses">
            <p>You haven't enrolled in any courses yet.</p
        </div>

        <!-- available courses -->
        <div class="available-courses">
            <h2>Available Courses</h2>
            <div class="course-grid">
                <?php foreach ($availableCourses as $course): ?> 
                    <div class="course-card">
                        <h3><?php echo htmlspecialchars($course['courseName']); ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="analytics-section">
        <?php 
        renderLearningStreak($analytics);
        renderProgressChart($analytics);
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>


