<?php
require_once __DIR__ . '/../config/app.php';
if (isset($_GET['id'])) {
    $scheduleId = $_GET['id'];

    // Delete the schedule from the database
    $sql = "DELETE FROM schedule_management WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $scheduleId);

    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "/schedule/schedule_management.php"); // Redirect to the schedule management page after deletion
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
