<?php
require_once __DIR__ . '/../config/app.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . BASE_URL . "/schedule/schedule_management.php");
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM schedule_management WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$shift = $result->fetch_assoc();
$stmt->close();

if (!$shift) {
    header("Location: " . BASE_URL . "/schedule/schedule_management.php");
    exit;
}

$error = '';

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = intval($_POST['employee_id']);
    $work_day = $_POST['work_day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $stmt = $conn->prepare("UPDATE schedule_management SET employee_id=?, work_day=?, start_time=?, end_time=?, status=?, remarks=? WHERE id=?");
    $stmt->bind_param("isssssi", $employee_id, $work_day, $start_time, $end_time, $status, $remarks, $id);
    
    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "/schedule/schedule_management.php?updated=1");
        exit;
    } else {
        $error = "Failed to update shift. Please try again.";
    }
    $stmt->close();
}

// Fetch employees for the dropdown
$employees = $conn->query("SELECT id, full_name FROM user_accounts ORDER BY full_name");
?>

<?php
$page_title = 'Edit Shift';
$current_page = 'schedule_management';
$extra_css = '<style>
    .edit-form-card {
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: none;
    }
    .edit-form-card .card-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        border-radius: 15px 15px 0 0 !important;
    }
    .form-control {
        border-radius: 8px;
    }
    .form-control:focus {
        border-color: #bac8f3;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
</style>';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<!-- Page Content -->
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit mr-2"></i>Edit Shift #<?= $id ?></h1>
        <a href="<?= BASE_URL ?>/schedule/schedule_management.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Schedules
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card edit-form-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-user-clock mr-2"></i>Shift Details</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold text-primary">Employee</label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">-- Select Employee --</option>
                                    <?php while ($emp = $employees->fetch_assoc()): ?>
                                        <option value="<?= $emp['id'] ?>" <?= $emp['id'] == $shift['employee_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($emp['full_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold text-primary">Work Day</label>
                                <select name="work_day" class="form-control" required>
                                    <?php
                                    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                                    foreach ($days as $day):
                                    ?>
                                        <option value="<?= $day ?>" <?= $shift['work_day'] == $day ? 'selected' : '' ?>><?= $day ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold text-primary">Start Time</label>
                                <input type="time" name="start_time" class="form-control" value="<?= htmlspecialchars($shift['start_time']) ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold text-primary">End Time</label>
                                <input type="time" name="end_time" class="form-control" value="<?= htmlspecialchars($shift['end_time']) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold text-primary">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Scheduled" <?= $shift['status'] == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="Completed" <?= $shift['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancelled" <?= $shift['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold text-primary">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"><?= htmlspecialchars($shift['remarks'] ?? '') ?></textarea>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="<?= BASE_URL ?>/schedule/schedule_management.php" class="btn btn-outline-secondary mr-2">
                                <i class="fas fa-times mr-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Update Shift
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<?php include BASE_PATH . '/templates/footer.php'; ?>
