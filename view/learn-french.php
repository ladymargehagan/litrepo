<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../actions/TranslateAPI.php';
require_once __DIR__ . '/../actions/LearningActions.php';
include 'header.php';

// Ensure user is logged in
requireLogin();

$translateAPI = new TranslateAPI();
$learningActions = new LearningActions();

// Basic vocabulary words for learning
$basicVocabulary = [
    'hello', 'goodbye', 'please', 'thank you',
    'yes', 'no', 'good', 'bad',
    'morning', 'evening', 'water', 'food'
];

// Get translations for initial words
$initialWords = array_slice($basicVocabulary, 0, 5);
$wordPairs = [];

foreach ($initialWords as $word) {
    $translation = $translateAPI->translateWord($word, 'en', 'fr');
    if ($translation) {
        $wordPairs[] = [
            'english' => $word,
            'french' => $translation['translation'],
            'definition' => $translation['definition']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learn French - Lost in Translation</title>
</head>
<body>
    <div class="container">
        <h1>Learn French</h1>

        <!-- Simple Flashcard -->
        <div id="flashcard">
            <div class="word-front"></div>
            <div class="word-back" style="display: none;"></div>
        </div>

        <!-- Basic Controls -->
        <div>
            <button onclick="flipCard()">Flip Card</button>
            <button onclick="nextWord()">Next Word</button>
        </div>

        <!-- Progress -->
        <div>
            <p>Words Learned: <span id="wordsLearned">0</span></p>
        </div>
    </div>

    <script>
        // Initialize with translated words
        const words = <?php echo json_encode($wordPairs); ?>;
        let currentIndex = 0;

        // Basic flashcard functionality
        function showCurrentWord() {
            const word = words[currentIndex];
            document.querySelector('.word-front').textContent = word.english;
            document.querySelector('.word-back').textContent = word.french;
            document.querySelector('.word-back').style.display = 'none';
        }

        function flipCard() {
            const back = document.querySelector('.word-back');
            back.style.display = back.style.display === 'none' ? 'block' : 'none';
        }

        function nextWord() {
            currentIndex = (currentIndex + 1) % words.length;
            showCurrentWord();
        }

        // Show first word on load
        showCurrentWord();
    </script>
</body>
</html> 