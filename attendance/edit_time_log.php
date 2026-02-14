<?php
require_once __DIR__ . '/../config/app.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID.");
}

$id = intval($_GET['id']);

// Fetch existing log
$stmt = $conn->prepare("SELECT * FROM time_logs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Log not found.");
}
$log = $result->fetch_assoc();
$stmt->close();

// CSRF token
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_log'])) {
    if (!hash_equals($_SESSION['token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $stmt = $conn->prepare("UPDATE time_logs SET log_date = ?, time_in = ?, time_out = ?, status = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $_POST['log_date'], $_POST['time_in'], $_POST['time_out'], $_POST['status'], $_POST['remarks'], $id);
    $stmt->execute();
    $stmt->close();

    header("Location: " . BASE_URL . "/attendance/time_log.php?success=Log updated successfully");
    exit;
}
?>

<?php
$page_title = 'Edit Time Log';
$current_page = 'time_log';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Edit Time Log</h1>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['token'] ?>">

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Employee ID</label>
                                    <input type="text" class="form-control" value="<?= $log['employee_id'] ?>" disabled>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Date</label>
                                    <input type="date" name="log_date" class="form-control" value="<?= $log['log_date'] ?>" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Time In</label>
                                    <input type="time" name="time_in" class="form-control" value="<?= $log['time_in'] ?>">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Time Out</label>
                                    <input type="time" name="time_out" class="form-control" value="<?= $log['time_out'] ?>">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option <?= $log['status'] == 'On Time' ? 'selected' : '' ?>>On Time</option>
                                        <option <?= $log['status'] == 'Late' ? 'selected' : '' ?>>Late</option>
                                        <option <?= $log['status'] == 'Absent' ? 'selected' : '' ?>>Absent</option>
                                        <option <?= $log['status'] == 'Half Day' ? 'selected' : '' ?>>Half Day</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Remarks</label>
                                    <input type="text" name="remarks" class="form-control" value="<?= htmlspecialchars($log['remarks']) ?>">
                                </div>
                            </div>

                            <button type="submit" name="update_log" class="btn btn-success">Update</button>
                            <a href="<?= BASE_URL ?>/attendance/time_log.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
</div><!-- /.container-fluid -->

<?php include BASE_PATH . '/templates/footer.php'; ?>
