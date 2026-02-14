<?php
require_once __DIR__ . '/../config/app.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeId = $_POST['employeeId'];
    $validationDate = $_POST['validationDate'];
    $comments = $_POST['comments'];

    $sql = "INSERT INTO hr_validation (employee_id, validation_date, comments) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $employeeId, $validationDate, $comments);
    
    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "/hr_validation/hr_validation_list.php?message=added");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
