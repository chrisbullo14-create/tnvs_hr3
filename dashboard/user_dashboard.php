<?php
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit();
}

// Simulate a missing username fallback just in case it's not set
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
?>

<?php
$page_title = 'TransGo - User Dashboard';
$current_page = 'dashboard';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">User Dashboard</h1>
    <p>This dashboard is only accessible to users after logging in.</p>
</div>
<!-- End Page Content -->

<?php include BASE_PATH . '/templates/footer.php'; ?>
