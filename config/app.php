<?php
/**
 * Application Configuration
 * Include this file at the top of every page.
 * Provides BASE_PATH (filesystem) and BASE_URL (web root) constants.
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (!defined('BASE_URL')) {
    // Adjust this if the project is deployed under a different URL path
    define('BASE_URL', '/TNVS');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once BASE_PATH . '/config/db_connection.php';
