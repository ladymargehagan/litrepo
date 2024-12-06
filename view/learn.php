<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../actions/LearningActions.php';
include 'header.php';

requireLogin();

$learningActions = new LearningActions();
$lessonId = $_GET['lesson'] ?? null;
$currentLesson = $learningActions->getLesson($lessonId);
$currentExercise = $learningActions->getCurrentExercise($lessonId, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Learn - Lost in Translation</title>
    <style>
        :root {
            --primary: #58CC02;        /* Duolingo green */
            --secondary: #FFC800;      /* Warm yellow */
            --accent: #FF4B4B;         /* Coral red for wrong answers */
            --correct: #89E219;        /* Light green for correct answers */
            --background: #FFF;
            --text: #3C3C3C;
            --light-gray: #E5E5E5;
        }

        body {
            background-color: var(--background);
            color: var(--text);
            font-family: 'Nunito', sans-serif;
        }

        .learning-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .progress-bar {
            height: 12px;
            background: var(--light-gray);
            border-radius: 6px;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .exercise-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .hearts {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .heart {
            color: var(--accent);
            font-size: 1.5rem;
        }

        .question {
            font-size: 1.8rem;
            margin: 2rem 0;
            font-weight: bold;
        }

        .options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .option {
            padding: 1rem;
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .option:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
        }

        .option.correct {
            background: var(--correct);
            border-color: var(--correct);
            color: white;
        }

        .option.wrong {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
        }

        .feedback {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            padding: 1rem 2rem;
            border-radius: 12px;
            color: white;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feedback.correct {
            background: var(--correct);
        }

        .feedback.wrong {
            background: var(--accent);
        }

        .feedback.visible {
            opacity: 1;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .celebration {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 4rem;
            animation: bounce 0.5s ease infinite;
            display: none;
        }
    </style>
</head>
<body>

<div class="learning-container">
    <div class="progress-bar">
        <div class="progress-fill" style="width: <?php echo $currentExercise['progress']; ?>%"></div>
    </div>

    <div class="hearts">
        <?php for($i = 0; $i < $currentExercise['hearts']; $i++): ?>
            <span class="heart">❤️</span>
        <?php endfor; ?>
    </div>

    <div class="exercise-container">
        <!-- Exercise content will be loaded dynamically -->
    </div>

    <div class="feedback"></div>
    <div class="celebration"></div>
</div>

<script src="/js/learning.js"></script>
</body>
</html> 