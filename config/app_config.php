<?php
// Base URL Configuration
$base_url = '';

// Detect if we're in a subdirectory
$subdir = dirname($_SERVER['SCRIPT_NAME']);
if ($subdir !== '/') {
    $base_url = $subdir;
}

// Define constants
define('BASE_URL', $base_url);
define('CSS_URL', BASE_URL . '/public/css');
define('JS_URL', BASE_URL . '/public/js');
define('ASSETS_URL', BASE_URL . '/public/assets'); 