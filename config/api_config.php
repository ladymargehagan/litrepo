<?php
require_once __DIR__ . '/env.php';

// Dictionary API for English definitions
define('DICTIONARY_API_ENDPOINT', 'https://api.dictionaryapi.dev/api/v2/entries/en/');

// API Settings
define('API_TIMEOUT', 5);
define('API_MAX_RETRIES', 2);

// Language Settings
define('SOURCE_LANGUAGE', 'en');
define('TARGET_LANGUAGE', 'fr');

// Error Messages
define('API_ERROR_MESSAGE', 'An error occurred while fetching data from the API');
define('UNSUPPORTED_LANGUAGE_ERROR', 'The requested language is not supported');
define('API_TIMEOUT_MESSAGE', 'The request timed out. Please try again.');
