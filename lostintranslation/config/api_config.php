<?php
// API Endpoints and Keys
define('DICTIONARY_API_ENDPOINT', 'https://api.wordnik.com/v4/word.json/');
define('TRANSLATE_API_ENDPOINT', 'https://api-free.deepl.com/v2/translate');
define('WORDS_API_KEY', getenv('WORDS_API_KEY') ?: '');
define('DEEP_L_API_KEY', getenv('DEEPL_API_KEY') ?: '');

// API Request Settings
define('API_TIMEOUT', 10); // seconds
define('API_MAX_RETRIES', 3);
define('API_RETRY_DELAY', 1); // seconds

// Language Configurations
define('SOURCE_LANG', 'en');
define('SUPPORTED_LANGUAGES', [
    'fr' => [
        'code' => 'fr',
        'name' => 'French',
        'active' => true
    ],
    'es' => [
        'code' => 'es',
        'name' => 'Spanish',
        'active' => true
    ]
]);

// Cache Settings
define('ENABLE_CACHE', true);
define('CACHE_DURATION', 60 * 60 * 24);
define('CACHE_PREFIX', 'lang_');
define('CACHE_MIN_HITS', 3); // Minimum hits before caching

// Rate Limiting
define('MAX_REQUESTS_PER_HOUR', getenv('API_RATE_LIMIT') ?: 1000);
define('RATE_LIMIT_WINDOW', 3600);
define('RATE_LIMIT_BUFFER', 50); // Keep buffer of requests

// Error Messages
define('API_ERROR_MESSAGE', 'An error occurred while fetching data from the API');
define('TRANSLATION_ERROR_MESSAGE', 'Translation service temporarily unavailable');
define('UNSUPPORTED_LANGUAGE_ERROR', 'The requested language is not supported');
define('API_TIMEOUT_MESSAGE', 'The request timed out. Please try again.');

// Wordnik API Configurations
define('WORDNIK_API_RANDOM_WORD', DICTIONARY_API_ENDPOINT . '%s/definitions?api_key=' . WORDS_API_KEY); // Random word API endpoint
define('WORDNIK_API_SYNONYMS', DICTIONARY_API_ENDPOINT . '%s/relatedWords?useCanonical=true&api_key=' . WORDS_API_KEY); // Synonyms and Antonyms endpoint

// Wordnik-specific error handling
define('WORDNIK_RATE_LIMIT_ERROR', 'Rate limit exceeded for Wordnik API');
define('WORDNIK_API_KEY_ERROR', 'Invalid API key for Wordnik');
?>
