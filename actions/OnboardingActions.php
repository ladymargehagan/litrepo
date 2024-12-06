<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/LogHandler.php';

class OnboardingActions {
    private $conn;
    private $logger;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
        $this->logger = new LogHandler();
    }

    public function createUserProfile($userId, $data) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO user_profiles 
                (userId, targetLanguage, proficiencyLevel, learningGoal) 
                VALUES (?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $userId,
                $data['targetLanguage'],
                $data['proficiencyLevel'],
                $data['learningGoal']
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error creating user profile: " . $e->getMessage());
            return false;
        }
    }

    public function getProficiencyQuestions($language) {
        // Sample questions for proficiency test
        return [
            [
                'question' => 'How would you say "Hello" in ' . $language . '?',
                'options' => [
                    'bonjour' => 'Beginner',
                    'salut' => 'Intermediate',
                    'bonsoir' => 'Advanced'
                ]
            ],
            // Add more questions based on language
        ];
    }
} 