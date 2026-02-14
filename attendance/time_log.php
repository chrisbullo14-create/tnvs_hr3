<?php
require_once __DIR__ . '/../config/app.php';
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Handle insert
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_log'])) {
    if (!hash_equals($_SESSION['token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $stmt = $conn->prepare("INSERT INTO time_logs (employee_id, log_date, time_in, time_out, status, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $_POST['employee_id'], $_POST['log_date'], $_POST['time_in'], $_POST['time_out'], $_POST['status'], $_POST['remarks']);
    $stmt->execute();
    $stmt->close();

    header("Location: " . BASE_URL . "/attendance/time_log.php?success=Log added successfully");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM time_logs WHERE id = $id");
    header("Location: " . BASE_URL . "/attendance/time_log.php?success=Log deleted successfully");
    exit;
}
?>

<?php
$page_title = 'Time Logging';
$current_page = 'time_log';
$extra_css = '<style>
        .tl-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .tl-page-title {
            color: #2b2d42;
            font-weight: 700;
        }
        .tl-card .card-header {
            background: linear-gradient(90deg, #4361ee, #3f37c9);
            color: white;
            font-weight: 600;
        }
        .tl-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        #logTable thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            color: #2b2d42;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-ontime { background-color: rgba(28,200,138,0.15); color: #1cc88a; }
        .status-late   { background-color: rgba(246,194,62,0.15); color: #f6c23e; }
        .status-absent { background-color: rgba(231,74,59,0.15); color: #e74a3b; }
        .status-halfday{ background-color: rgba(133,135,150,0.15); color: #858796; }
        .tl-search { position: relative; margin-bottom: 1rem; }
        .tl-search i { position: absolute; left: 15px; top: 12px; color: #6c757d; }
        .tl-search input { padding-left: 40px; }
        .tl-actions { display: flex; gap: 6px; }
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<div class="container-fluid">
    <div class="tl-page-header">
        <h1 class="h3 tl-page-title">Employee Time Log</h1>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Add New Time Log -->
    <div class="card tl-card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-plus-circle mr-2"></i>Add New Time Log
            </h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['token'] ?>">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">Select Employee</option>
                            <?php
                            $res = $conn->query("SELECT id, full_name FROM user_accounts ORDER BY full_name");
                            while ($row = $res->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['full_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
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
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="On Time">On Time</option>
                            <option value="Late">Late</option>
                            <option value="Absent">Absent</option>
                            <option value="Half Day">Half Day</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Optional notes">
                    </div>
                    <div class="form-group col-md-3 d-flex align-items-end">
                        <button type="submit" name="add_log" class="btn btn-primary w-100">
                            <i class="fas fa-save mr-2"></i>Save Log
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Time Log History -->
    <div class="card tl-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-white">
                    <i class="fas fa-history mr-2"></i>Time Log History
                </h6>
                <button onclick="window.print();" class="btn btn-light btn-sm">
                    <i class="fas fa-print mr-1"></i>Print
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="tl-search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" class="form-control" placeholder="Search logs...">
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="logTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $logs = $conn->query("SELECT t.*, u.full_name FROM time_logs t LEFT JOIN user_accounts u ON t.employee_id = u.id ORDER BY t.log_date DESC, t.id DESC");
                        $n = 1;
                        while ($log = $logs->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $n++ ?></td>
                                <td><?= htmlspecialchars($log['full_name'] ?? 'Unknown') ?></td>
                                <td><?= date('M d, Y', strtotime($log['log_date'])) ?></td>
                                <td><?= $log['time_in'] ? date('h:i A', strtotime($log['time_in'])) : '-' ?></td>
                                <td><?= $log['time_out'] ? date('h:i A', strtotime($log['time_out'])) : '-' ?></td>
                                <td>
                                    <?php
                                    $sc = strtolower(str_replace(' ', '', $log['status']));
                                    echo "<span class='status-badge status-$sc'>" . htmlspecialchars($log['status']) . "</span>";
                                    ?>
                                </td>
                                <td><?= $log['remarks'] ? htmlspecialchars($log['remarks']) : '-' ?></td>
                                <td>
                                    <div class="tl-actions">
                                        <a href="<?= BASE_URL ?>/attendance/edit_time_log.php?id=<?= $log['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/attendance/time_log.php?delete=<?= $log['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this log?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
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

<!-- DataTable + Search JS -->
<script>
$(document).ready(function(){
    $('#logTable').DataTable({
        "order": [[2, "desc"]],
        "pageLength": 10,
        "columnDefs": [{ "orderable": false, "targets": 7 }]
    });
    $('#searchInput').on('keyup', function(){
        $('#logTable').DataTable().search(this.value).draw();
    });
});
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
