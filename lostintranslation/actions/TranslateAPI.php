<?php
require_once __DIR__ . '/../config/api_config.php';
require_once __DIR__ . '/../utils/Cache.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/APIClient.php';

class TranslateAPI {
    private $cache;
    private $db;
    private $apiClient;
    
    public function __construct() {
        if (ENABLE_CACHE) {
            $this->cache = new Cache();
        }
        $this->db = Database::getInstance();
        $this->apiClient = new APIClient();
    }

    public function getWordOfDay() {
        $conn = $this->db->getConnection();
        $today = date('Y-m-d');
        $cacheKey = CACHE_PREFIX . 'word_of_day_' . $today;
        
        // Check cache first
        if (ENABLE_CACHE && $this->cache->exists($cacheKey)) {
            return $this->cache->get($cacheKey);
        }
        
        try {
            // Check if we already have today's word
            $stmt = $conn->prepare("
                SELECT w.*, t.translation 
                FROM word_of_day wd
                JOIN words w ON wd.wordId = w.wordId
                WHERE DATE(wd.dateShown) = ?
            ");
            $stmt->execute([$today]);
            $word = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($word) {
                $result = $this->enrichWordData($word);
                if (ENABLE_CACHE) {
                    $this->cache->set($cacheKey, $result, 24 * 60 * 60);
                }
                return $result;
            }

            // Get a new random word from the API
            $randomWord = $this->getRandomWord();
            if (!$randomWord) {
                throw new Exception("Failed to get random word");
            }

            // Store the word and its translation
            $stmt = $conn->prepare("
                INSERT INTO words (word, sourceLanguage, targetLanguage, translation)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $randomWord['word'],
                SOURCE_LANG,
                DEFAULT_TARGET_LANG,
                $randomWord['translation']
            ]);
            
            $wordId = $conn->lastInsertId();

            // Set as word of day
            $stmt = $conn->prepare("
                INSERT INTO word_of_day (wordId, dateShown)
                VALUES (?, ?)
            ");
            $stmt->execute([$wordId, $today]);

            if (ENABLE_CACHE) {
                $this->cache->set($cacheKey, $randomWord, 24 * 60 * 60);
            }
            return $randomWord;

        } catch (Exception $e) {
            error_log("Error in getWordOfDay: " . $e->getMessage());
            return null;
        }
    }

    private function getRandomWord() {
        try {
            // First try to get a random word from our database
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                SELECT w.* 
                FROM words w 
                WHERE w.courseId IS NOT NULL 
                ORDER BY RAND() 
                LIMIT 1
            ");
            $stmt->execute();
            $dbWord = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($dbWord) {
                return [
                    'word' => $dbWord['word'],
                    'translation' => $dbWord['translation'],
                    'definition' => $this->getDefinitions($dbWord['word'])
                ];
            }

            // If no words in database, try API
            $url = sprintf(DICTIONARY_API_ENDPOINT . 'en/random');
            $context = stream_context_create(['http' => [
                'timeout' => 5,
                'header' => 'User-Agent: PHP'
            ]]);
            
            $response = @file_get_contents($url, false, $context);
            
            if (!$response) {
                error_log("API Error: Failed to get random word from " . $url);
                // Fallback to a basic word if API fails
                return [
                    'word' => 'hello',
                    'translation' => 'bonjour',
                    'definition' => [['partOfSpeech' => 'exclamation', 'definitions' => [['definition' => 'Used as a greeting']]]]
                ];
            }

            $data = json_decode($response, true);
            if (empty($data)) {
                error_log("API Error: Empty response from dictionary API");
                return null;
            }

            $word = $data[0]['word'];
            $translation = $this->translateWord($word, DEFAULT_TARGET_LANG);

            if (!$translation) {
                error_log("API Error: Failed to translate word: " . $word);
                return null;
            }

            return [
                'word' => $word,
                'translation' => $translation,
                'definition' => $this->formatDefinition($data)
            ];
        } catch (Exception $e) {
            error_log("Error in getRandomWord: " . $e->getMessage());
            return null;
        }
    }

    private function enrichWordData($word) {
        return [
            'word' => $word['word'],
            'translation' => $word['translation'],
            'definition' => $this->getDefinitions($word['word'])
        ];
    }

    public function translateWord($word, $targetLang) {
        $cacheKey = "trans_{$word}_{$targetLang}";
        
        // Check cache first
        if (ENABLE_CACHE && $this->cache->exists($cacheKey)) {
            $this->logger->info("Retrieved cached translation for: " . $word);
            return $this->cache->get($cacheKey);
        }

        try {
            $url = sprintf(
                '%s?auth_key=%s&text=%s&source_lang=%s&target_lang=%s',
                TRANSLATE_API_ENDPOINT,
                DEEP_L_API_KEY,
                urlencode($word),
                SOURCE_LANG,
                $targetLang
            );

            $response = $this->apiClient->request($url);
            if (!$response) {
                throw new Exception("Translation API request failed");
            }

            $data = json_decode($response, true);
            if (!isset($data['translations'][0]['text'])) {
                throw new Exception("Invalid translation response format");
            }

            $translation = $data['translations'][0]['text'];
            
            // Store in database for persistence
            $this->storeTranslation($word, $translation, $targetLang);
            
            // Cache the result
            if (ENABLE_CACHE) {
                $this->cache->set($cacheKey, $translation, CACHE_DURATION);
            }

            return $translation;
        } catch (Exception $e) {
            $this->logger->error("Translation error: " . $e->getMessage());
            return $this->getFallbackTranslation($word);
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

    private function getDefinitions($word) {
        $cacheKey = CACHE_PREFIX . "def_" . strtolower($word);
        
        // Check cache first
        if (ENABLE_CACHE && $this->cache->exists($cacheKey)) {
            $this->logger->info("Retrieved cached definition for: " . $word);
            return $this->cache->get($cacheKey);
        }

        try {
            $url = DICTIONARY_API_ENDPOINT . SOURCE_LANG . '/' . urlencode($word);
            $response = $this->apiClient->request($url, 'GET', null, [
                'Authorization: Bearer ' . WORDS_API_KEY
            ]);
            
            if (!$response) {
                throw new Exception("Failed to fetch word definitions");
            }

            $data = json_decode($response, true);
            $definitions = $this->formatDefinition($data);

            // Cache successful responses
            if (ENABLE_CACHE && $definitions) {
                $this->cache->set($cacheKey, $definitions, CACHE_DURATION);
            }

            return $definitions;
        } catch (Exception $e) {
            $this->logger->error("Definition lookup failed for word '{$word}': " . $e->getMessage());
            return $this->getFallbackDefinition($word);
        }
    }

    private function getFallbackDefinition($word) {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                SELECT definition 
                FROM word_definitions 
                WHERE word = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$word]);
            $result = $stmt->fetch();
            
            if ($result) {
                $this->logger->info("Using fallback definition for: " . $word);
                return json_decode($result['definition'], true);
            }
            
            return null;
        } catch (Exception $e) {
            $this->logger->error("Fallback definition lookup failed: " . $e->getMessage());
            return null;
        } finally {
            $this->db->releaseConnection($conn);
        }
    }

    private function formatDefinition($data) {
        if (empty($data) || !is_array($data)) {
            return null;
        }

        $formatted = [];
        foreach ($data[0]['meanings'] ?? [] as $meaning) {
            $formatted[] = [
                'partOfSpeech' => $meaning['partOfSpeech'] ?? 'unknown',
                'definitions' => array_map(function($def) {
                    return [
                        'definition' => $def['definition'] ?? '',
                        'example' => $def['example'] ?? null
                    ];
                }, $meaning['definitions'] ?? [])
            ];
        }
        
        return $formatted;
    }
}
?> 