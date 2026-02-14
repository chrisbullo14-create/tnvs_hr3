<?php
require_once __DIR__ . '/../config/app.php';
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Get record
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID");
}
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM timesheet_logs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$log = $result->fetch_assoc();
if (!$log) {
    die("Log not found");
}
$stmt->close();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_log'])) {
    if (!hash_equals($_SESSION['token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $employee_id = $_POST['employee_id'];
    $log_date = $_POST['log_date'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $start = strtotime($time_in);
    $end = strtotime($time_out);
    $total_hours = ($end - $start) / 3600;
    $overtime = $total_hours > 8 ? $total_hours - 8 : 0;

    $night_diff = 0;
    $cur = $start;
    while ($cur < $end) {
        $hour = date('H', $cur);
        if ($hour >= 22 || $hour < 6) {
            $night_diff += 1;
        }
        $cur += 3600;
    }

    $stmt = $conn->prepare("UPDATE timesheet_logs SET employee_id=?, log_date=?, time_in=?, time_out=?, status=?, remarks=?, total_hours=?, overtime_hours=?, night_diff_hours=? WHERE id=?");
    $stmt->bind_param("isssssdddi", $employee_id, $log_date, $time_in, $time_out, $status, $remarks, $total_hours, $overtime, $night_diff, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: " . BASE_URL . "/timesheet/timesheet.php?success=Log updated");
    exit;
}

$page_title = 'Edit Timesheet';
$current_page = 'timesheet';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Edit Timesheet Log</h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['token'] ?>">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">Select</option>
                            <?php
                            $res = $conn->query("SELECT id, full_name FROM user_accounts ORDER BY full_name");
                            while ($row = $res->fetch_assoc()) {
                                $selected = $row['id'] == $log['employee_id'] ? 'selected' : '';
                                echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['full_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Date</label>
                        <input type="date" name="log_date" class="form-control" value="<?= $log['log_date'] ?>" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Time In</label>
                        <input type="time" name="time_in" class="form-control" value="<?= $log['time_in'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Time Out</label>
                        <input type="time" name="time_out" class="form-control" value="<?= $log['time_out'] ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option <?= $log['status'] == 'On Time' ? 'selected' : '' ?>>On Time</option>
                            <option <?= $log['status'] == 'Late' ? 'selected' : '' ?>>Late</option>
                            <option <?= $log['status'] == 'Absent' ? 'selected' : '' ?>>Absent</option>
                            <option <?= $log['status'] == 'Half Day' ? 'selected' : '' ?>>Half Day</option>
                        </select>
                    </div>
                    <div class="form-group col-md-9">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="<?= htmlspecialchars($log['remarks']) ?>">
                    </div>
                </div>
                <button type="submit" name="update_log" class="btn btn-success">Update Log</button>
                <a href="<?= BASE_URL ?>/timesheet/timesheet.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div><!-- /.container-fluid -->

<?php include BASE_PATH . '/templates/footer.php'; ?>
