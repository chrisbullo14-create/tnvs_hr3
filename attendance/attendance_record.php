<?php
require_once __DIR__ . '/../config/app.php';

$error = '';
$success = '';

// Check for success query parameter
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = "Time log recorded successfully!";
}

// Fetch attendance records
$query = "SELECT * FROM attendance_time_log ORDER BY record_date DESC, time_in DESC";
$result = $conn->query($query);

if (!$result) {
    $error = "Error fetching records: " . $conn->error;
}

$conn->close();
?>

<?php
$page_title = 'Attendance Records';
$current_page = 'attendance_record';
$extra_css = '<style>
        .table-enhanced thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
        }
        
        .table-enhanced tbody tr {
            transition: all 0.2s ease;
        }

        .table-enhanced tbody tr:hover {
            background-color: #fbfcfe;
            transform: translateX(4px);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .time-in-dot { background-color: #1cc88a; }
        .time-out-dot { background-color: #e74a3b; }

        .card-header-custom {
            background: linear-gradient(90deg, #4e73df 0%, #224abe 100%);
            color: white;
        }

        .employee-id {
            color: #4e73df;
            font-weight: 600;
        }
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<!-- Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-list mr-2"></i>Attendance Records
        </h1>
        <a href="<?= BASE_URL ?>/attendance/attendance_time_log.php" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Log Time
        </a>
    </div>

    <!-- Alerts -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Summary Cards Row -->
    <?php
    $total_records = 0;
    $today_records = 0;
    $clocked_in = 0;
    $today = date('Y-m-d');
    $all_rows = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $all_rows[] = $row;
            $total_records++;
            if ($row['record_date'] === $today) {
                $today_records++;
                if (empty($row['time_out'])) {
                    $clocked_in++;
                }
            }
        }
    }
    ?>
    <div class="row mb-4">
        <!-- Total Records -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Records</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_records ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Entries -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today's Entries</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $today_records ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currently Clocked In -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Currently Clocked In</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $clocked_in ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header card-header-custom py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-table mr-2"></i>Attendance Time Log
            </h6>
            <span class="badge badge-light"><?= $total_records ?> record<?= $total_records !== 1 ? 's' : '' ?></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-enhanced table-bordered" id="attendanceTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($all_rows) > 0): ?>
                            <?php foreach ($all_rows as $index => $row): ?>
                                <?php
                                // Calculate duration
                                $duration_str = '—';
                                if (!empty($row['time_in']) && !empty($row['time_out'])) {
                                    $diff = strtotime($row['time_out']) - strtotime($row['time_in']);
                                    if ($diff > 0) {
                                        $hours = floor($diff / 3600);
                                        $minutes = floor(($diff % 3600) / 60);
                                        $duration_str = $hours . 'h ' . $minutes . 'm';
                                    }
                                }

                                // Determine status
                                if (empty($row['time_out'])) {
                                    $status = 'Clocked In';
                                    $status_class = 'badge-warning';
                                } elseif (!empty($row['time_in']) && !empty($row['time_out'])) {
                                    $status = 'Complete';
                                    $status_class = 'badge-success';
                                } else {
                                    $status = 'Incomplete';
                                    $status_class = 'badge-secondary';
                                }
                                ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><span class="employee-id">#<?= htmlspecialchars($row['employee_id']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($row['record_date'])) ?></td>
                                    <td>
                                        <span class="status-dot time-in-dot"></span>
                                        <?= !empty($row['time_in']) ? date('h:i A', strtotime($row['time_in'])) : '—' ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['time_out'])): ?>
                                            <span class="status-dot time-out-dot"></span>
                                            <?= date('h:i A', strtotime($row['time_out'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted font-italic">— Not yet —</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $duration_str ?></td>
                                    <td><span class="badge <?= $status_class ?>"><?= $status ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No attendance records found.
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

<?php include BASE_PATH . '/templates/footer.php'; ?>
