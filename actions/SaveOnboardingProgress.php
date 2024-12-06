<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/LogHandler.php';

class SaveOnboardingProgress {
    private $conn;
    private $logger;
    private $userId;

    public function __construct($userId) {
        $this->conn = Database::getInstance()->getConnection();
        $this->logger = new LogHandler();
        $this->userId = $userId;
    }

    public function save($data) {
        try {
            $this->conn->beginTransaction();

            // Update user profile
            $stmt = $this->conn->prepare("
                INSERT INTO user_profiles 
                (userId, targetLanguage, proficiencyLevel, learningGoal, created_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                targetLanguage = VALUES(targetLanguage),
                proficiencyLevel = VALUES(proficiencyLevel),
                learningGoal = VALUES(learningGoal),
                updated_at = NOW()
            ");

            $stmt->execute([
                $this->userId,
                $data['language'],
                $data['level'],
                $data['dailyGoal']
            ]);

            // Save preferences
            $this->savePreferences($data['preferences']);

            // Track analytics
            $this->trackOnboardingAnalytics($data);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->logger->error("Error saving onboarding progress: " . $e->getMessage());
            return false;
        }
    }

    private function savePreferences($preferences) {
        $stmt = $this->conn->prepare("
            INSERT INTO user_preferences 
            (userId, preference_key, preference_value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
            preference_value = VALUES(preference_value)
        ");

        foreach ($preferences as $key => $values) {
            foreach ($values as $value) {
                $stmt->execute([$this->userId, $key, $value]);
            }
        }
    }

    private function trackOnboardingAnalytics($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO user_analytics 
            (userId, event_type, event_data, created_at)
            VALUES (?, 'onboarding_complete', ?, NOW())
        ");

        $analyticsData = json_encode([
            'language' => $data['language'],
            'level' => $data['level'],
            'goal' => $data['dailyGoal'],
            'quiz_score' => $this->calculateQuizScore($data['quizAnswers']),
            'preferences' => $data['preferences']
        ]);

        $stmt->execute([$this->userId, $analyticsData]);
    }

    private function calculateQuizScore($answers) {
        // Implementation depends on quiz scoring logic
        return count(array_filter($answers, fn($a) => $a === 'correct')) / count($answers) * 100;
    }
} 