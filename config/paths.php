// Add this new file to define consistent paths
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('STORAGE_PATH', BASE_PATH . '/storage');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');
define('IMAGES_PATH', ASSETS_PATH . '/images');
define('CACHE_PATH', STORAGE_PATH . '/cache');
define('LOGS_PATH', STORAGE_PATH . '/logs'); 