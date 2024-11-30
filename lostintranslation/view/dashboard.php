<?php 
include 'header.php';
require_once __DIR__ . '/../actions/TranslateAPI.php';
require_once __DIR__ . '/../actions/CourseActions.php';

requireLogin();

$translateAPI = new TranslateAPI();
$courseActions = new CourseActions();

// Get word of the day with full definition, translation, and examples
$wordOfDay = $translateAPI->getWordOfDay();

// Get user's progress across all courses
$userCourses = [];
$enrolledCourses = [];
$availableCourses = $courseActions->getAllCourses();
$totalWordsLearned = 0;

// Get progress for enrolled courses
foreach ($availableCourses as $course) {
    $progress = $courseActions->getUserProgress($_SESSION['user_id'], $course['courseId']);
    if ($progress) {
        $totalWordsLearned += $progress['wordsLearned'];
        $userCourses[$course['courseId']] = array_merge($course, ['progress' => $progress]);
        $enrolledCourses[] = array_merge($course, ['progress' => $progress]);
    }
}

// Calculate user level based on total words learned
$userLevel = getUserLevel($totalWordsLearned);
?>

<div class="dashboard-container">
    <!-- User Progress Summary -->
    <div class="progress-summary">
        <h2>Your Progress</h2>
        <div class="stats">
            <div class="stat-item">
                <span class="stat-label">Level</span>
                <span class="stat-value"><?php echo htmlspecialchars($userLevel); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Words Learned</span>
                <span class="stat-value"><?php echo $totalWordsLearned; ?></span>
            </div>
        </div>
    </div>

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
                        <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo getProgressPercentage($course['progress']['wordsLearned'], $course['totalWords']); ?>%"></div>
                        </div>
                        <p class="progress-text">
                            <?php echo $course['progress']['wordsLearned']; ?> / <?php echo $course['totalWords']; ?> words
                        </p>
                        <a href="course.php?id=<?php echo $course['courseId']; ?>" class="continue-btn">Continue Learning</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>


<?php include 'footer.php'; ?>


