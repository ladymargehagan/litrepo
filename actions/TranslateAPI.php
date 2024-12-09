<?php
require_once __DIR__ . '/../utils/Cache.php';
require_once __DIR__ . '/../utils/APIClient.php';
require_once __DIR__ . '/../php/LogHandler.php';
require_once __DIR__ . '/../config/api_config.php';
require_once __DIR__ . '/../config/database.php';

class TranslateAPI {
    private $cache;
    private $apiClient;
    private $logger;
    private $db;

    public function __construct() {
        $this->cache = new Cache();
        $this->apiClient = new APIClient();
        $this->logger = new LogHandler();
        $this->db = Database::getInstance();
    }

    public function translateWord($word, $fromLang = 'en', $toLang = 'fr') {
        try {
            // Use predefined translations similar to getWordOfDay
            $dailyWords = [
                ['word' => 'hello', 'translation' => 'bonjour', 'definition' => [
                    ['partOfSpeech' => 'interjection', 
                     'definitions' => [['definition' => 'Used as a greeting', 'example' => 'Hello, how are you?']]]
                ]],
                ['word' => 'world', 'translation' => 'monde', 'definition' => [
                    ['partOfSpeech' => 'noun', 
                     'definitions' => [['definition' => 'The earth or globe', 'example' => 'He traveled around the world.']]]
                ]],
                ['word' => 'book', 'translation' => 'livre', 'definition' => [
                    ['partOfSpeech' => 'noun', 
                     'definitions' => [['definition' => 'A written work', 'example' => 'I love reading books.']]]
                ]]
                // ... add more words as needed
            ];

            // Find the translation for the given word
            foreach ($dailyWords as $dailyWord) {
                if ($dailyWord['word'] === strtolower($word)) {
                    return $dailyWord;
                }
            }
            
            // If word not found, return null
            return null;
            
        } catch (Exception $e) {
            $this->logger->error("Translation error: " . $e->getMessage());
            return null;
        }
    }

    private function storeTranslation($word, $translation, $targetLang) {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                INSERT INTO translations (word, translation, target_language, created_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                translation = VALUES(translation),
                created_at = NOW()
            ");
            $stmt->execute([$word, $translation, $targetLang]);
        } catch (Exception $e) {
            $this->logger->error("Failed to store translation: " . $e->getMessage());
        }
    }

    private function getFallbackTranslation($word) {
        // Try to get from database first
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                SELECT translation 
                FROM translations 
                WHERE word = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$word]);
            $result = $stmt->fetch();
            if ($result) {
                return $result['translation'];
            }
        } catch (Exception $e) {
            $this->logger->error("Fallback translation lookup failed: " . $e->getMessage());
        }
        
        return null;
    }

    public function getWordOfDay() {
        try {
            // Predefined list of words with translations and definitions
            $dailyWords = [
                ['word' => 'hello', 'translation' => 'bonjour', 'definition' => [
                    ['partOfSpeech' => 'interjection', 
                     'definitions' => [['definition' => 'Used as a greeting', 'example' => 'Hello, how are you?']]]
                ]],
                ['word' => 'world', 'translation' => 'monde', 'definition' => [
                    ['partOfSpeech' => 'noun', 
                     'definitions' => [['definition' => 'The earth or globe', 'example' => 'He traveled around the world.']]]
                ]],
                ['word' => 'book', 'translation' => 'livre', 'definition' => [
                    ['partOfSpeech' => 'noun', 
                     'definitions' => [['definition' => 'A written work', 'example' => 'I love reading books.']]]
                ]]
                // ... add more words as needed
            ];

            // Get a random word from the list
            $randomIndex = array_rand($dailyWords);
            return $dailyWords[$randomIndex];
            
        } catch (Exception $e) {
            $this->logger->error("Word of day error: " . $e->getMessage());
            return null;
        }
    }
}

?> 