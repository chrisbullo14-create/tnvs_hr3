<?php
require_once __DIR__ . '/../config/app.php';
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM schedule_management WHERE id = $id");
    header("Location: " . BASE_URL . "/schedule/schedule_management.php?deleted=1");
    exit;
}

// Export to CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=tnvs_schedule.csv');

    $output = fopen("php://output", "w");
    fputcsv($output, ['ID', 'Employee', 'Work Day', 'Start Time', 'End Time', 'Status', 'Remarks']);

    $rows = $conn->query("
        SELECT s.*, u.full_name as employee 
        FROM schedule_management s 
        LEFT JOIN user_accounts u ON s.employee_id = u.id
    ");
    while ($row = $rows->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['employee'],
            $row['work_day'],
            $row['start_time'],
            $row['end_time'],
            $row['status'],
            $row['remarks']
        ]);
    }
    fclose($output);
    exit;
}

// Fetch all shifts
$shifts = $conn->query("
    SELECT s.*, u.full_name as employee 
    FROM schedule_management s 
    LEFT JOIN user_accounts u ON s.employee_id = u.id
    ORDER BY s.id DESC
");
?>

<?php
$page_title = 'Schedule Management';
$current_page = 'schedule_management';
$extra_css = '<style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #ff6b6b;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
        }
        
        body {
            background-color: #f5f7fa;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 10px;
            margin: 10px;
        }
        
        .sidebar-brand-icon {
            color: #fff;
            font-size: 1.5rem;
            animation: pulse 2s infinite;
        }
        
        .sidebar-brand-text {
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .nav-item {
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
        }
        
        .nav-link i {
            margin-right: 8px;
            color: rgba(255,255,255,0.6);
        }
        
        .topbar {
            background: linear-gradient(90deg, #ffffff 0%, #f8f9fc 100%) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 10px;
            margin: 10px;
        }
        
        .topbar h4 {
            color: var(--primary-color);
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .container-fluid {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin: 10px;
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table thead {
            background: linear-gradient(90deg, var(--primary-color) 0%, #3a5ccc 100%) !important;
            color: white;
        }
        
        .table th {
            border: none !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        
        .table td {
            vertical-align: middle;
            border-color: #f1f3f9;
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #17a673 100%) !important;
            border: none;
            box-shadow: 0 2px 5px rgba(28, 200, 138, 0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3a5ccc 100%) !important;
            border: none;
            box-shadow: 0 2px 5px rgba(78, 115, 223, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--accent-color) 0%, #e84c3d 100%) !important;
            border: none;
            box-shadow: 0 2px 5px rgba(255, 107, 107, 0.3);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .status-pending {
            color: var(--warning-color);
            font-weight: 600;
        }
        
        .status-approved {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .status-rejected {
            color: var(--accent-color);
            font-weight: 600;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .export-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(28, 200, 138, 0.4) !important;
        }
        
        .export-btn i {
            margin-right: 8px;
        }
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-calendar-alt mr-2"></i>Schedule Management</h1>
        <a href="<?= BASE_URL ?>/schedule/shift_creation.php" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Create Shift
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> Shift updated successfully.
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> Shift successfully deleted.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <a href="?export=1" class="export-btn btn btn-success mb-3"><i class="fas fa-file-export"></i> Export CSV</a>

                <div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Employee</th>
                <th>Work Day</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($shifts && $shifts->num_rows > 0): ?>
            <?php while ($row = $shifts->fetch_assoc()): 
                $statusClass = '';
                $badgeClass = 'badge-secondary';
                if ($row['status'] == 'Scheduled') { $statusClass = 'status-pending'; $badgeClass = 'badge-warning'; }
                if ($row['status'] == 'Completed') { $statusClass = 'status-approved'; $badgeClass = 'badge-success'; }
                if ($row['status'] == 'Cancelled') { $statusClass = 'status-rejected'; $badgeClass = 'badge-danger'; }
            ?>
            <tr>
                <td class="font-weight-bold">#<?= htmlspecialchars($row['id']) ?></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar mr-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.8rem;">
                            <?= substr(htmlspecialchars($row['employee'] ?? 'U'), 0, 1) ?>
                        </div>
                        <?= htmlspecialchars($row['employee'] ?? 'Unknown') ?>
                    </div>
                </td>
                <td><?= htmlspecialchars($row['work_day']) ?></td>
                <td><?= date('h:i A', strtotime($row['start_time'])) ?></td>
                <td><?= date('h:i A', strtotime($row['end_time'])) ?></td>
                <td>
                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['status']) ?></span>
                </td>
                <td><?= !empty($row['remarks']) ? htmlspecialchars($row['remarks']) : '<span class="text-muted">None</span>' ?></td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="<?= BASE_URL ?>/schedule/shift_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this shift?')"><i class="fas fa-trash-alt"></i></a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    No shifts found. <a href="<?= BASE_URL ?>/schedule/shift_creation.php">Create one</a>.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include BASE_PATH . '/templates/footer.php'; ?>
