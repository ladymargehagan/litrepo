<?php
require_once __DIR__ . '/config/api_config.php';
require_once __DIR__ . '/utils/APIClient.php';
require_once __DIR__ . '/actions/TranslateAPI.php';

try {
    $translateAPI = new TranslateAPI();
    
    // Test a simple translation
    $word = "hello";
    $targetLang = "fr";
    
    echo "Translating '{$word}' to {$targetLang}...\n";
    $result = $translateAPI->translateWord($word, $targetLang);
    
    if ($result) {
        echo "Translation successful!\n";
        echo "Translated text: " . $result . "\n";
        
        // Get alternatives from cache if available
        $cacheKey = "trans_alt_{$word}_{$targetLang}";
        $alternatives = $translateAPI->cache->get($cacheKey);
        if ($alternatives) {
            echo "Alternatives:\n";
            foreach ($alternatives as $alt) {
                echo "- " . $alt . "\n";
            }
        }
    } else {
        echo "Translation failed.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 