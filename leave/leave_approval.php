<?php
require_once __DIR__ . '/../config/app.php';
// Approve leave via POST
if (isset($_POST['approve_id'])) {
    $leaveId = $_POST['approve_id'];
    $status = 'Approved';

    $stmt = $conn->prepare("UPDATE leave_requests SET leave_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $leaveId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash'] = "Leave request has been Approved.";
    header("Location: " . BASE_URL . "/leave/leave_approval.php");
    exit;
}

// Reject leave via GET
if (isset($_GET['reject_id'])) {
    $leaveId = $_GET['reject_id'];
    $status = 'Rejected';

    $stmt = $conn->prepare("UPDATE leave_requests SET leave_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $leaveId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash'] = "Leave request has been Rejected.";
    header("Location: " . BASE_URL . "/leave/leave_approval.php");
    exit;
}

// Fetch leave requests grouped by status
$statuses = ['Pending', 'Approved', 'Rejected'];
$leaveRequests = [];

foreach ($statuses as $status) {
    $stmt = $conn->prepare("
        SELECT l.id AS leave_id, u.full_name, l.leave_type, l.start_date, l.end_date, l.leave_status, l.reason, l.request_date
        FROM leave_requests l
        LEFT JOIN user_accounts u ON l.employee_id = u.id
        WHERE l.leave_status = ?
        ORDER BY l.request_date DESC
    ");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $leaveRequests[$status] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<?php
$page_title = 'Leave Approval';
$current_page = 'leave_approval';
$extra_css = '<style>
        .status-badge { 
            padding: 0.5em 0.8em;
            font-size: 0.85em;
            border-radius: 20px;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            color: #4e73df;
            font-weight: 600;
        }
        .action-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .card-header {
            font-size: 1.1rem;
        }
        .table thead th {
            border-bottom: 2px solid #e3e6f0;
        }
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';

$pendingCount = count($leaveRequests['Pending']);
$approvedCount = count($leaveRequests['Approved']);
$rejectedCount = count($leaveRequests['Rejected']);
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-clipboard-check mr-2"></i>Leave Approval</h1>
    </div>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars(is_array($_SESSION['flash']) ? implode(', ', $_SESSION['flash']) : $_SESSION['flash']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Summary Row -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="card border-left-warning shadow-sm h-100"><div class="card-body py-3">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingCount ?></div>
            </div></div>
        </div>
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="card border-left-success shadow-sm h-100"><div class="card-body py-3">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $approvedCount ?></div>
            </div></div>
        </div>
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="card border-left-danger shadow-sm h-100"><div class="card-body py-3">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $rejectedCount ?></div>
            </div></div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#pending" role="tab">
                        <i class="fas fa-hourglass-half mr-1"></i>Pending <span class="badge badge-warning ml-1"><?= $pendingCount ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#approved" role="tab">
                        <i class="fas fa-check-circle mr-1"></i>Approved <span class="badge badge-success ml-1"><?= $approvedCount ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#rejected" role="tab">
                        <i class="fas fa-times-circle mr-1"></i>Rejected <span class="badge badge-danger ml-1"><?= $rejectedCount ?></span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">

                <!-- Pending Tab -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <?php if (empty($leaveRequests['Pending'])): ?>
                        <div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block"></i>No pending leave requests</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $n=1; foreach ($leaveRequests['Pending'] as $row):
                                    $days = (strtotime($row['end_date']) - strtotime($row['start_date'])) / 86400;
                                ?>
                                <tr>
                                    <td><?= $n++ ?></td>
                                    <td><?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($row['leave_type']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['start_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['end_date'])) ?></td>
                                    <td><span class="badge badge-info"><?= $days ?> day<?= $days != 1 ? 's' : '' ?></span></td>
                                    <td><?= htmlspecialchars($row['reason']) ?></td>
                                    <td class="small text-muted"><?= date('M d, Y', strtotime($row['request_date'])) ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="approve_id" value="<?= $row['leave_id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm action-btn" title="Approve" onclick="return confirm('Approve this request?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <a href="<?= BASE_URL ?>/leave/leave_approval.php?reject_id=<?= $row['leave_id'] ?>" class="btn btn-danger btn-sm action-btn" title="Reject" onclick="return confirm('Reject this request?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Approved Tab -->
                <div class="tab-pane fade" id="approved" role="tabpanel">
                    <?php if (empty($leaveRequests['Approved'])): ?>
                        <div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block"></i>No approved leave requests</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Requested</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $n=1; foreach ($leaveRequests['Approved'] as $row):
                                    $days = (strtotime($row['end_date']) - strtotime($row['start_date'])) / 86400;
                                ?>
                                <tr>
                                    <td><?= $n++ ?></td>
                                    <td><?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($row['leave_type']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['start_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['end_date'])) ?></td>
                                    <td><span class="badge badge-info"><?= $days ?> day<?= $days != 1 ? 's' : '' ?></span></td>
                                    <td><?= htmlspecialchars($row['reason']) ?></td>
                                    <td class="small text-muted"><?= date('M d, Y', strtotime($row['request_date'])) ?></td>
                                    <td><span class="badge badge-success">Approved</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Rejected Tab -->
                <div class="tab-pane fade" id="rejected" role="tabpanel">
                    <?php if (empty($leaveRequests['Rejected'])): ?>
                        <div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block"></i>No rejected leave requests</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Requested</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $n=1; foreach ($leaveRequests['Rejected'] as $row):
                                    $days = (strtotime($row['end_date']) - strtotime($row['start_date'])) / 86400;
                                ?>
                                <tr>
                                    <td><?= $n++ ?></td>
                                    <td><?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($row['leave_type']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['start_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['end_date'])) ?></td>
                                    <td><span class="badge badge-info"><?= $days ?> day<?= $days != 1 ? 's' : '' ?></span></td>
                                    <td><?= htmlspecialchars($row['reason']) ?></td>
                                    <td class="small text-muted"><?= date('M d, Y', strtotime($row['request_date'])) ?></td>
                                    <td><span class="badge badge-danger">Rejected</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

            </div><!-- /.tab-content -->
        </div>
    </div>

</div><!-- /.container-fluid -->

<?php include BASE_PATH . '/templates/footer.php'; ?>
