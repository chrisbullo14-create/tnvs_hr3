<?php
require_once __DIR__ . '/../config/app.php';

$success = '';
$error = '';

// Handle AJAX requests for edit and delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Unknown error'];

    if ($_POST['action'] === 'edit') {
        // Edit attendance record
        $id = intval($_POST['id']);
        $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_SANITIZE_STRING);
        $recordDate = filter_input(INPUT_POST, 'recordDate', FILTER_SANITIZE_STRING);
        $totalWorkHours = filter_input(INPUT_POST, 'totalWorkHours', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        $remarks = filter_input(INPUT_POST, 'remarks', FILTER_SANITIZE_STRING);

        if ($totalWorkHours < 0 || $totalWorkHours > 24) {
            $response['message'] = "Work hours must be between 0 and 24";
            echo json_encode($response);
            exit;
        }

        $stmt = $conn->prepare("UPDATE attendance_reports SET employee_id = ?, record_date = ?, total_work_hours = ?, status = ?, remarks = ? WHERE id = ?");
        $stmt->bind_param("isdssi", $employeeId, $recordDate, $totalWorkHours, $status, $remarks, $id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Record updated successfully.';
        } else {
            $response['message'] = 'Failed to update record.';
        }
        $stmt->close();

        echo json_encode($response);
        exit;
    }

    if ($_POST['action'] === 'delete') {
        // Delete attendance record
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM attendance_reports WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Record deleted successfully.';
        } else {
            $response['message'] = 'Failed to delete record.';
        }
        $stmt->close();

        echo json_encode($response);
        exit;
    }
}

// Handle new record creation (normal form POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['action'])) {
    $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_SANITIZE_STRING);
    $recordDate = filter_input(INPUT_POST, 'recordDate', FILTER_SANITIZE_STRING);
    $totalWorkHours = filter_input(INPUT_POST, 'totalWorkHours', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $remarks = filter_input(INPUT_POST, 'remarks', FILTER_SANITIZE_STRING);

    if ($totalWorkHours < 0 || $totalWorkHours > 24) {
        $error = "Work hours must be between 0 and 24";
    } else {
        $stmt = $conn->prepare("INSERT INTO attendance_reports (employee_id, record_date, total_work_hours, status, remarks) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $employeeId, $recordDate, $totalWorkHours, $status, $remarks);
        if ($stmt->execute()) {
            $success = "Attendance record added successfully!";
            $_POST = array(); // clear form
        } else {
            $error = "Failed to add record.";
        }
        $stmt->close();
    }
}
?>

<?php
/*require_once __DIR__ . '/../config/app.php';

$success = '';
$error = '';

// Handle AJAX requests for edit and delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Unknown error'];

    if ($_POST['action'] === 'edit') {
        // Edit attendance record
        $id = intval($_POST['id']);
        $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_SANITIZE_STRING);
        $recordDate = filter_input(INPUT_POST, 'recordDate', FILTER_SANITIZE_STRING);
        $totalWorkHours = filter_input(INPUT_POST, 'totalWorkHours', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        $remarks = filter_input(INPUT_POST, 'remarks', FILTER_SANITIZE_STRING);

        if ($totalWorkHours < 0 || $totalWorkHours > 24) {
            $response['message'] = "Work hours must be between 0 and 24";
            echo json_encode($response);
            exit;
        }

        $stmt = $conn->prepare("UPDATE attendance_reports SET employee_id = ?, record_date = ?, total_work_hours = ?, status = ?, remarks = ? WHERE id = ?");
        $stmt->bind_param("isdssi", $employeeId, $recordDate, $totalWorkHours, $status, $remarks, $id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Record updated successfully.';
        } else {
            $response['message'] = 'Failed to update record.';
        }
        $stmt->close();

        echo json_encode($response);
        exit;
    }

    if ($_POST['action'] === 'delete') {
        // Delete attendance record
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM attendance_reports WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Record deleted successfully.';
        } else {
            $response['message'] = 'Failed to delete record.';
        }
        $stmt->close();

        echo json_encode($response);
        exit;
    }
}

// Handle new record creation (normal form POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['action'])) {
    $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_SANITIZE_STRING);
    $recordDate = filter_input(INPUT_POST, 'recordDate', FILTER_SANITIZE_STRING);
    $totalWorkHours = filter_input(INPUT_POST, 'totalWorkHours', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $remarks = filter_input(INPUT_POST, 'remarks', FILTER_SANITIZE_STRING);

    if ($totalWorkHours < 0 || $totalWorkHours > 24) {
        $error = "Work hours must be between 0 and 24";
    } else {
        $stmt = $conn->prepare("INSERT INTO attendance_reports (employee_id, record_date, total_work_hours, status, remarks) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $employeeId, $recordDate, $totalWorkHours, $status, $remarks);
        if ($stmt->execute()) {
            $success = "Attendance record added successfully!";
            $_POST = array(); // clear form
        } else {
            $error = "Failed to add record.";
        }
        $stmt->close();
    }
}*/
?>

<?php
$page_title = 'Attendance Reports';
$current_page = 'attendance_report';
$extra_css = '<style>
        .form-label { font-weight: 600; color: #4e73df; }
        .status-indicator {
            width: 12px; height: 12px; border-radius: 50%; display: inline-block;
            margin-right: 5px;
        }
        .status-present { background-color: #1cc88a; }
        .status-absent { background-color: #e74a3b; }
        .status-onleave { background-color: #36b9cc; }
        .status-halfday { background-color: #f6c23e; }
        
        /* Enhanced action buttons */
        .edit-btn {
            background-color: #4e73df;
            border-color: #4e73df;
            transition: all 0.3s;
        }
        .edit-btn:hover {
            background-color: #2e59d9;
            transform: scale(1.05);
        }
        .delete-btn {
            transition: all 0.3s;
        }
        .delete-btn:hover {
            transform: scale(1.05);
        }
        
        /* Card enhancements */
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: none;
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
        }
        
        /* Table enhancements */
        #attendanceTable thead th {
            background-color: #f8f9fc;
            color: #4e73df;
            border-bottom: 2px solid #e3e6f0;
        }
        #attendanceTable tbody tr:hover {
            background-color: #f8f9fc;
        }
        
        /* Form inputs */
        .form-control, .form-control:focus {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
        }
        .form-control:focus {
            border-color: #bac8f3;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        /* Buttons */
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            transform: translateY(-1px);
        }
        .btn-outline-secondary {
            border-radius: 8px;
            padding: 8px 20px;
            transition: all 0.3s;
        }
        .btn-outline-secondary:hover {
            transform: translateY(-1px);
        }
        
        /* Modal enhancements */
        .modal-content {
            border-radius: 15px;
        }
        .modal-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
        .close {
            color: white;
            text-shadow: none;
            opacity: 0.8;
        }
        .close:hover {
            color: white;
            opacity: 1;
        }
        
        /* Alert messages */
        .alert {
            border-radius: 8px;
        }
        
        /* Sidebar active item highlight */
        .nav-item.active .nav-link {
            color: white !important;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
        }
        
        /* Welcome message */
        .welcome-message {
            background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
            border-left: 4px solid #4e73df;
        }
    </style>
<link href="<?= BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
';
$extra_js = '<script src="<?= BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';

// Fetch attendance reports
$reports = $conn->query("SELECT * FROM attendance_reports ORDER BY record_date DESC, employee_id ASC");
$all_reports = [];
$total = 0;
$present_count = 0;
$absent_count = 0;
$leave_count = 0;
$halfday_count = 0;

if ($reports) {
    while ($row = $reports->fetch_assoc()) {
        $all_reports[] = $row;
        $total++;
        switch ($row['status']) {
            case 'Present': $present_count++; break;
            case 'Absent': $absent_count++; break;
            case 'On Leave': $leave_count++; break;
            case 'Half Day': $halfday_count++; break;
        }
    }
}

$conn->close();
?>

<!-- Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar mr-2"></i>Attendance Reports
        </h1>
        <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addRecordModal">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Record
        </button>
    </div>

    <!-- Alerts -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Records</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Present</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $present_count ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-check fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Absent</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $absent_count ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-times fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">On Leave / Half Day</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $leave_count + $halfday_count ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-minus fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Reports Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-table mr-2"></i>Attendance Reports
            </h6>
            <span class="badge badge-light"><?= $total ?> record<?= $total !== 1 ? 's' : '' ?></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="attendanceTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Date</th>
                            <th>Work Hours</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($all_reports) > 0): ?>
                            <?php foreach ($all_reports as $i => $rpt): ?>
                                <?php
                                switch ($rpt['status']) {
                                    case 'Present':  $dot = 'status-present';  $badge = 'badge-success'; break;
                                    case 'Absent':   $dot = 'status-absent';   $badge = 'badge-danger';  break;
                                    case 'On Leave': $dot = 'status-onleave';  $badge = 'badge-info';    break;
                                    case 'Half Day': $dot = 'status-halfday';  $badge = 'badge-warning'; break;
                                    default:         $dot = '';                $badge = 'badge-secondary';
                                }
                                ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><span style="color:#4e73df;font-weight:600;">#<?= htmlspecialchars($rpt['employee_id']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($rpt['record_date'])) ?></td>
                                    <td><?= number_format($rpt['total_work_hours'], 2) ?> hrs</td>
                                    <td>
                                        <span class="status-indicator <?= $dot ?>"></span>
                                        <span class="badge <?= $badge ?>"><?= htmlspecialchars($rpt['status']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($rpt['remarks'] ?? '—') ?></td>
                                    <td>
                                        <button class="btn btn-sm edit-btn text-white mr-1"
                                            onclick="openEditModal(<?= $rpt['id'] ?>, <?= $rpt['employee_id'] ?>, '<?= $rpt['record_date'] ?>', <?= $rpt['total_work_hours'] ?>, '<?= htmlspecialchars($rpt['status']) ?>', '<?= htmlspecialchars(addslashes($rpt['remarks'] ?? '')) ?>')">
                                            <i class="fas fa-edit fa-sm"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-btn"
                                            onclick="deleteRecord(<?= $rpt['id'] ?>)">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No attendance reports found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<!-- Add Record Modal -->
<div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Add Attendance Record</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Employee ID</label>
                        <input type="number" name="employeeId" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Record Date</label>
                        <input type="date" name="recordDate" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Work Hours</label>
                        <input type="number" name="totalWorkHours" class="form-control" step="0.01" min="0" max="24" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Half Day">Half Day</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Record Modal -->
<div class="modal fade" id="editRecordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Attendance Record</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="form-group">
                    <label class="form-label">Employee ID</label>
                    <input type="number" id="editEmployeeId" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Record Date</label>
                    <input type="date" id="editRecordDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Total Work Hours</label>
                    <input type="number" id="editWorkHours" class="form-control" step="0.01" min="0" max="24" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="editStatus" class="form-control" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="On Leave">On Leave</option>
                        <option value="Half Day">Half Day</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Remarks</label>
                    <textarea id="editRemarks" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveEdit()"><i class="fas fa-save mr-1"></i>Update Record</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#attendanceTable').DataTable({
        "order": [[2, "desc"]],
        "pageLength": 10,
        "language": {
            "emptyTable": "No attendance reports found."
        }
    });
});

function openEditModal(id, empId, date, hours, status, remarks) {
    $('#editId').val(id);
    $('#editEmployeeId').val(empId);
    $('#editRecordDate').val(date);
    $('#editWorkHours').val(hours);
    $('#editStatus').val(status);
    $('#editRemarks').val(remarks);
    $('#editRecordModal').modal('show');
}

function saveEdit() {
    $.post(window.location.href, {
        action: 'edit',
        id: $('#editId').val(),
        employeeId: $('#editEmployeeId').val(),
        recordDate: $('#editRecordDate').val(),
        totalWorkHours: $('#editWorkHours').val(),
        status: $('#editStatus').val(),
        remarks: $('#editRemarks').val()
    }, function(res) {
        if (res.success) {
            location.reload();
        } else {
            alert(res.message);
        }
    }, 'json');
}

function deleteRecord(id) {
    if (!confirm('Are you sure you want to delete this record?')) return;
    $.post(window.location.href, { action: 'delete', id: id }, function(res) {
        if (res.success) {
            location.reload();
        } else {
            alert(res.message);
        }
    }, 'json');
}
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
