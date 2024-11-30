<?php
require_once __DIR__ . '/php/LogHandler.php';

// Initialize the logger
$logger = new LogHandler();

// Test each type of log
$logger->info("This is an info test message");
$logger->error("This is an error test message");
$logger->debug("This is a debug test message");
$logger->warning("This is a warning test message");

echo "Log file should be created at: " . $logger->getLogPath() . "/app_" . date('Y-m-d') . ".log";
?> 