<?php
require_once __DIR__ . '/../config/app.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leave_id = $_POST['leave_id'];
    $employee_id = $_POST['employee_id'];
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];
    $remarks = $_POST['remarks'];
    $action = $_POST['action']; // approve o reject

    $status = $action == 'approve' ? 'Approved' : 'Rejected';
    $hr_id = 1; // static for now, depende kung may login system ka
    $now = date("Y-m-d H:i:s");

    // 1. Update sa leave_requests
    $update = $conn->prepare("UPDATE leave_requests SET leave_status = ? WHERE leave_id = ?");
    $update->bind_param("si", $status, $leave_id);
    $update->execute();

    // 2. Insert sa hr_validation
    $insertValidation = $conn->prepare("INSERT INTO hr_validation (leave_id, hr_id, validation_status, validation_date) VALUES (?, ?, ?, ?)");
    $insertValidation->bind_param("iiss", $leave_id, $hr_id, $status, $now);
    $insertValidation->execute();

    // 3. Insert sa leave_approval
    $insertApproval = $conn->prepare("INSERT INTO leave_approval (leave_id, employee_id, leave_type, start_date, end_date, leave_status, reason, remarks, request_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertApproval->bind_param("iisssssss", $leave_id, $employee_id, $leave_type, $start_date, $end_date, $status, $reason, $remarks, $now);
    $insertApproval->execute();

    header("Location: your_hr_page.php?success=1");
    exit();
}
?>
