<?php
/**
 * TNVS Application Entry Point
 * Redirects to the login page.
 */
require_once __DIR__ . '/config/app.php';

header("Location: " . BASE_URL . "/auth/login.php");
exit;
