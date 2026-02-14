<?php
require_once __DIR__ . '/../config/app.php';
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Handle insert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_log'])) {
    if (!hash_equals($_SESSION['token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
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

    $stmt = $conn->prepare("INSERT INTO timesheet_logs (employee_id, log_date, time_in, time_out, status, remarks, total_hours, overtime_hours, night_diff_hours) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssddd", $employee_id, $log_date, $time_in, $time_out, $status, $remarks, $total_hours, $overtime, $night_diff);
    $stmt->execute();
    $stmt->close();
    header("Location: " . BASE_URL . "/timesheet/timesheet.php?success=Log added");
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM timesheet_logs WHERE id = $id");
    header("Location: " . BASE_URL . "/timesheet/timesheet.php?success=Deleted");
    exit;
}
?>

<?php
$page_title = 'Timesheet';
$current_page = 'timesheet';
$extra_css = '<style>
        .ts-card { border: none; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .ts-card .card-header { background: linear-gradient(90deg, #4361ee, #3f37c9); color: white; font-weight: 600; }
        .ts-page-title { color: #2b2d42; font-weight: 700; }
        #timesheetTable thead th { background-color: #f8f9fc; border-bottom: 2px solid #e3e6f0; color: #2b2d42; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        .ts-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .ts-ontime  { background-color: rgba(28,200,138,0.15); color: #1cc88a; }
        .ts-late    { background-color: rgba(246,194,62,0.15); color: #f6c23e; }
        .ts-absent  { background-color: rgba(231,74,59,0.15); color: #e74a3b; }
        .ts-halfday { background-color: rgba(133,135,150,0.15); color: #858796; }
        .ts-hours   { font-weight: 600; text-align: center; }
        .ts-ot      { font-weight: 600; text-align: center; color: #f6c23e; }
        .ts-nd      { font-weight: 600; text-align: center; color: #4e73df; }
        .ts-time    { font-family: monospace; font-weight: 600; }
        .ts-actions { display: flex; gap: 6px; }
        .ts-search  { position: relative; margin-bottom: 1rem; }
        .ts-search i { position: absolute; left: 15px; top: 12px; color: #6c757d; }
        .ts-search input { padding-left: 40px; }
        .ts-summary .card { border: none; border-radius: 10px; }
        .ts-summary .card .card-body { padding: 1.25rem; }
        .ts-summary .border-left-primary { border-left: 4px solid #4e73df !important; }
        .ts-summary .border-left-success { border-left: 4px solid #1cc88a !important; }
        .ts-summary .border-left-warning { border-left: 4px solid #f6c23e !important; }
        .ts-summary .border-left-danger  { border-left: 4px solid #e74a3b !important; }
        @media print { .no-print { display: none; } }
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';

// --- Gather summary stats ---
$total_logs  = $conn->query("SELECT COUNT(*) as c FROM timesheet_logs")->fetch_assoc()['c'];
$total_hours = $conn->query("SELECT COALESCE(SUM(total_hours),0) as s FROM timesheet_logs")->fetch_assoc()['s'];
$total_ot    = $conn->query("SELECT COALESCE(SUM(overtime_hours),0) as s FROM timesheet_logs")->fetch_assoc()['s'];
$total_late  = $conn->query("SELECT COUNT(*) as c FROM timesheet_logs WHERE status='Late'")->fetch_assoc()['c'];
?>

<div class="container-fluid">

    <h1 class="h3 mb-3 ts-page-title">Timesheet</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-4 ts-summary">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-left-primary h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Entries</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_logs ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-left-success h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Work Hours</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_hours, 1) ?> hrs</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-left-warning h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Overtime Hours</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_ot, 1) ?> hrs</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-left-danger h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Late Arrivals</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_late ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Log Form -->
    <div class="card ts-card mb-4 no-print">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-plus-circle mr-2"></i>Add New Timesheet Entry</h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['token'] ?>">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">Select Employee</option>
                            <?php
                            $emps = $conn->query("SELECT id, full_name FROM user_accounts ORDER BY full_name");
                            while ($e = $emps->fetch_assoc()):
                            ?>
                                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['full_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Date</label>
                        <input type="date" name="log_date" class="form-control" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Time In</label>
                        <input type="time" name="time_in" class="form-control">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Time Out</label>
                        <input type="time" name="time_out" class="form-control">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="On Time">On Time</option>
                            <option value="Late">Late</option>
                            <option value="Absent">Absent</option>
                            <option value="Half Day">Half Day</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-9">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Optional notes">
                    </div>
                    <div class="form-group col-md-3 d-flex align-items-end">
                        <button type="submit" name="add_log" class="btn btn-primary w-100">
                            <i class="fas fa-save mr-2"></i>Save Entry
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Timesheet Table -->
    <div class="card ts-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-clock mr-2"></i>Timesheet Records</h6>
                <button onclick="window.print();" class="btn btn-light btn-sm no-print"><i class="fas fa-print mr-1"></i>Print</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="timesheetTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Total Hrs</th>
                            <th>OT Hrs</th>
                            <th>Night Diff</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $logs = $conn->query("
                            SELECT t.*, u.full_name
                            FROM timesheet_logs t
                            LEFT JOIN user_accounts u ON t.employee_id = u.id
                            ORDER BY t.log_date DESC, t.id DESC
                        ");
                        $n = 1;
                        while ($row = $logs->fetch_assoc()):
                            $sc = strtolower(str_replace(' ', '', $row['status']));
                        ?>
                        <tr>
                            <td><?= $n++ ?></td>
                            <td><?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?></td>
                            <td><?= date('M d, Y', strtotime($row['log_date'])) ?></td>
                            <td class="ts-time"><?= $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '-' ?></td>
                            <td class="ts-time"><?= $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-' ?></td>
                            <td class="ts-hours"><?= number_format($row['total_hours'], 2) ?></td>
                            <td class="ts-ot"><?= $row['overtime_hours'] > 0 ? number_format($row['overtime_hours'], 2) : '-' ?></td>
                            <td class="ts-nd"><?= $row['night_diff_hours'] > 0 ? number_format($row['night_diff_hours'], 2) : '-' ?></td>
                            <td><span class="ts-badge ts-<?= $sc ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                            <td><?= $row['remarks'] ? htmlspecialchars($row['remarks']) : '-' ?></td>
                            <td class="no-print">
                                <div class="ts-actions">
                                    <a href="<?= BASE_URL ?>/timesheet/edit_timesheet.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= BASE_URL ?>/timesheet/timesheet.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this entry?')" title="Delete"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- /.container-fluid -->

<script>
$(document).ready(function(){
    $('#timesheetTable').DataTable({
        "order": [[2, "desc"]],
        "pageLength": 15,
        "columnDefs": [{ "orderable": false, "targets": 10 }]
    });
});
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
