<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'study_crew');
define('SQLITE_DB_PATH', __DIR__ . '/study_crew.db');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
session_start();

// Time zone
date_default_timezone_set('UTC'); 