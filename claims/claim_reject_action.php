<?php
require_once __DIR__ . '/../config/app.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE claim_submissions SET status = 'Rejected' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Claim ID $id rejected successfully.";
    } else {
        $_SESSION['error'] = "Failed to reject claim ID $id.";
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid claim ID.";
}

header("Location: " . BASE_URL . "/claims/record_keeping.php");
exit;
