<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/LogHandler.php';

class LearningActions {
    private $conn;
    private $logger;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
        $this->logger = new LogHandler();
    }

    public function getLesson($lessonId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT l.*, COUNT(e.exerciseId) as totalExercises
                FROM lessons l
                LEFT JOIN exercises e ON l.lessonId = e.lessonId
                WHERE l.lessonId = ?
                GROUP BY l.lessonId
            ");
            $stmt->execute([$lessonId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logger->error("Error getting lesson: " . $e->getMessage());
            return null;
        }
    }

    public function getCurrentExercise($lessonId, $userId) {
        try {
            // Get next unanswered exercise
            $stmt = $this->conn->prepare("
                SELECT e.*, et.name as exerciseType
                FROM exercises e
                JOIN exercise_types et ON e.typeId = et.typeId
                LEFT JOIN user_exercise_progress uep 
                    ON e.exerciseId = uep.exerciseId 
                    AND uep.userId = ?
                WHERE e.lessonId = ? 
                AND (uep.status IS NULL OR uep.status = 'incorrect')
                ORDER BY e.orderIndex
                LIMIT 1
            ");
            $stmt->execute([$userId, $lessonId]);
            $exercise = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($exercise) {
                // Add options for multiple choice
                if ($exercise['exerciseType'] === 'multiple_choice') {
                    $exercise['options'] = json_decode($exercise['options'], true);
                }
            }

            return $exercise;
        } catch (Exception $e) {
            $this->logger->error("Error getting exercise: " . $e->getMessage());
            return null;
        }
    }

    public function checkAnswer($exerciseId, $userId, $answer) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM exercises WHERE exerciseId = ?
            ");
            $stmt->execute([$exerciseId]);
            $exercise = $stmt->fetch(PDO::FETCH_ASSOC);

            $isCorrect = $this->validateAnswer($exercise, $answer);

            // Record attempt
            $this->recordAttempt($exerciseId, $userId, $isCorrect);

            return [
                'correct' => $isCorrect,
                'feedback' => $this->getFeedback($isCorrect),
                'correctAnswer' => $exercise['correct_answer']
            ];
        } catch (Exception $e) {
            $this->logger->error("Error checking answer: " . $e->getMessage());
            return null;
        }
    }

    private function validateAnswer($exercise, $answer) {
        switch ($exercise['typeId']) {
            case 1: // Multiple choice
                return strtolower(trim($answer)) === strtolower(trim($exercise['correct_answer']));
            case 2: // Word matching
                return strtolower(trim($answer)) === strtolower(trim($exercise['correct_answer']));
            case 3: // Type translation
                // More flexible matching for typed answers
                return $this->fuzzyMatch($answer, $exercise['correct_answer']);
            default:
                return false;
        }
    }

    private function fuzzyMatch($answer, $correct) {
        // Remove accents, spaces, and make case-insensitive
        $normalize = function($str) {
            return strtolower(trim(str_replace(' ', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))));
        };
        return $normalize($answer) === $normalize($correct);
    }

    private function getFeedback($isCorrect) {
        $correctResponses = [
            "Excellent! 🎉",
            "Perfect! ⭐",
            "Amazing work! 🌟",
            "You're on fire! 🔥"
        ];
        $incorrectResponses = [
            "Not quite! Try again! 💪",
            "Almost there! 🎯",
            "Keep going! 🚀",
            "You can do it! 💫"
        ];
        
        $responses = $isCorrect ? $correctResponses : $incorrectResponses;
        return $responses[array_rand($responses)];
    }
} 