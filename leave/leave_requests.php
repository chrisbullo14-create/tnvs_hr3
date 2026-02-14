<?php
require_once __DIR__ . '/../config/app.php';
// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

// Get all employees
$employees = [];
$sql = "SELECT id AS user_id, full_name FROM user_accounts ORDER BY full_name ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

// Handle Leave Request Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submitLeaveRequest'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF validation failed.");
    }

    $userId = intval($_POST['userId']);
    $leaveType = trim($_POST['leaveType']);
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $reason = htmlspecialchars(trim($_POST['reason']));
    $remarks = htmlspecialchars(trim($_POST['remarks'] ?? ""));

    // Check if user exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user_accounts WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($exists);
    $stmt->fetch();
    $stmt->close();

    if ($exists == 0) {
        $error = "User does not exist.";
    } else {
        // Insert into leave_requests
        $stmt = $conn->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason, remarks, leave_status, request_date) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->bind_param("isssss", $userId, $leaveType, $startDate, $endDate, $reason, $remarks);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Leave request failed: " . $stmt->error;
            $stmt->close();
        }
    }
}

// Fetch leave requests for display
$sql = "
    SELECT lr.id AS leave_id, lr.leave_type, lr.start_date, lr.end_date, lr.reason, lr.remarks, lr.leave_status, lr.request_date,
           u.full_name
    FROM leave_requests lr
    LEFT JOIN user_accounts u ON lr.employee_id = u.id
    ORDER BY lr.request_date DESC
";
$result = $conn->query($sql);

?>

<?php
$page_title = 'Leave Requests';
$current_page = 'leave_requests';
$extra_css = '<style>
        .form-icon { color: #4e73df; }
        .card-header { font-weight: 600; letter-spacing: 0.5px; }
        .form-group label { font-weight: 500; color: #4e73df; }
        .date-input-group { position: relative; }
        .date-input-group i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6e707e;
        }
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Leave Request Form</h1>
    </div>

    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary">
                        <h6 class="m-0 font-weight-bold text-white">New Leave Application</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="userId">Select Employee</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text form-icon">
                                                <i class="fas fa-user-tie"></i>
                                            </span>
                                        </div>
                                        <select name="userId" class="form-control" required>
                                            <option value="">-- Choose Employee --</option>
                                            <?php foreach ($employees as $emp): ?>
                                                <option value="<?= $emp['user_id'] ?>">
                                                    <?= htmlspecialchars($emp['full_name']) ?> (ID: <?= $emp['user_id'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select an employee</div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="leaveType">Leave Type</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text form-icon">
                                                <i class="fas fa-list-alt"></i>
                                            </span>
                                        </div>
                                        <select name="leaveType" class="form-control" required>
                                            <option>Sick Leave</option>
                                            <option>Casual Leave</option>
                                            <option>Annual Leave</option>
                                            <option>Maternity Leave</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Start Date</label>
                                    <div class="date-input-group">
                                        <input type="date" name="startDate" class="form-control" required>
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>End Date</label>
                                    <div class="date-input-group">
                                        <input type="date" name="endDate" class="form-control" required>
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <div class="w-100 text-center">
                                        <span class="badge badge-warning p-2" style="font-size: 1em;">
                                            <i class="fas fa-clock"></i> Pending Approval
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label>Reason for Leave</label>
                                <textarea name="reason" class="form-control" rows="3" placeholder="Enter detailed reason for leave..." required></textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label>Additional Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Any additional comments or notes..."></textarea>
                                <small class="form-text text-muted">Optional information</small>
                            </div>

                            <div class="text-center mt-4">
                                <button name="submitLeaveRequest" class="btn btn-primary btn-icon-split">
                                    <span class="icon text-white-50"><i class="fas fa-paper-plane"></i></span>
                                    <span class="text">Submit Application</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Example: Display leave requests -->
                <div class="card shadow mb-4 mt-4">
                    <div class="card-header py-3 bg-info">
                        <h6 class="m-0 font-weight-bold text-white">Leave Requests</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Reason</th>
                                        <th>Remarks</th>
                                        <th>Status</th>
                                        <th>Requested At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()):
                                            $st = $row['leave_status'];
                                            if ($st === 'Approved') $badge = 'badge-success';
                                            elseif ($st === 'Rejected') $badge = 'badge-danger';
                                            else $badge = 'badge-warning';
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?></td>
                                                <td><?= htmlspecialchars($row['leave_type']) ?></td>
                                                <td><?= date('M d, Y', strtotime($row['start_date'])) ?></td>
                                                <td><?= date('M d, Y', strtotime($row['end_date'])) ?></td>
                                                <td><?= htmlspecialchars($row['reason']) ?></td>
                                                <td><?= $row['remarks'] ? htmlspecialchars($row['remarks']) : '-' ?></td>
                                                <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($st) ?></span></td>
                                                <td><?= date('M d, Y h:i A', strtotime($row['request_date'])) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No leave requests found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- End leave requests table -->

</div><!-- /.container-fluid -->

<script>
$(document).ready(function(){
    $('.table').DataTable({
        "order": [[7, "desc"]],
        "pageLength": 10
    });
});
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
