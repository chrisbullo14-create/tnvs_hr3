<?php
require_once __DIR__ . '/../config/app.php';

$query = "SELECT a.id as audit_id, a.action, a.description, a.ip_address, a.created_at, u.full_name
          FROM audit_logs a
          LEFT JOIN user_accounts u ON a.employee_id = u.id
          ORDER BY a.created_at DESC";
$result = $conn->query($query);
?>

<?php
$page_title = 'Compliance & Auditing';
$current_page = 'compliance_audits';
$extra_css = '<style>
        .audit-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .audit-card .card-header { background: linear-gradient(90deg, #4e73df, #3a5ccc); color: white; border-bottom: none; padding: 1.25rem 1.5rem; }
        .audit-title { color: #5a5c69; font-weight: 700; }
        #auditTable thead th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border: none; padding: 1rem; }
        #auditTable tbody td { vertical-align: middle; padding: 0.85rem 1rem; }
        .audit-badge { display: inline-block; padding: 0.35em 0.65em; font-size: 0.75em; font-weight: 700; border-radius: 0.375rem; }
        .audit-login   { background-color: rgba(28,200,138,0.1);  color: #1cc88a; }
        .audit-logout  { background-color: rgba(133,135,150,0.15); color: #858796; }
        .audit-update  { background-color: rgba(78,115,223,0.1);  color: #4e73df; }
        .audit-delete  { background-color: rgba(231,74,59,0.1);   color: #e74a3b; }
        .audit-create  { background-color: rgba(246,194,62,0.1);  color: #f6c23e; }
        .audit-export  { background-color: rgba(54,185,204,0.1);  color: #36b9cc; }
        .audit-view    { background-color: rgba(133,135,150,0.1); color: #858796; }
        .audit-ts { font-size: 0.85rem; color: #6c757d; font-family: monospace; }
        .audit-avatar { width: 32px; height: 32px; border-radius: 50%; margin-right: 10px; border: 2px solid #f8f9fc; }
        .audit-ip { font-size: 0.8rem; color: #858796; font-family: monospace; }
        .audit-no-data { text-align: center; padding: 2rem; color: #6c757d; }
        .audit-no-data i { font-size: 3rem; color: #dee2e6; margin-bottom: 1rem; }
        .audit-summary .border-left-primary { border-left: 4px solid #4e73df !important; }
        .audit-summary .border-left-success { border-left: 4px solid #1cc88a !important; }
        .audit-summary .border-left-info    { border-left: 4px solid #36b9cc !important; }
        .audit-summary .border-left-danger  { border-left: 4px solid #e74a3b !important; }
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';

// Summary stats
$total_logs = $conn->query("SELECT COUNT(*) as c FROM audit_logs")->fetch_assoc()['c'];
$today_logs = $conn->query("SELECT COUNT(*) as c FROM audit_logs WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
$total_logins = $conn->query("SELECT COUNT(*) as c FROM audit_logs WHERE action='Login'")->fetch_assoc()['c'];
$total_deletes = $conn->query("SELECT COUNT(*) as c FROM audit_logs WHERE action='Delete'")->fetch_assoc()['c'];
?>

<div class="container-fluid">

    <h1 class="h3 mb-3 audit-title"><i class="fas fa-shield-alt mr-2"></i>Compliance & Auditing</h1>

    <!-- Summary Cards -->
    <div class="row mb-4 audit-summary">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-left-primary h-100"><div class="card-body py-3">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Audit Logs</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_logs ?></div>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-left-success h-100"><div class="card-body py-3">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today's Activity</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $today_logs ?></div>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-left-info h-100"><div class="card-body py-3">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Login Events</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_logins ?></div>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-left-danger h-100"><div class="card-body py-3">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Delete Actions</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_deletes ?></div>
            </div></div>
        </div>
    </div>

    <!-- Audit Table -->
    <div class="card audit-card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-clipboard-list mr-2"></i>System Audit Records</h6>
            <button onclick="window.print();" class="btn btn-sm btn-light"><i class="fas fa-print mr-1"></i>Print</button>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="auditTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $n=1; while ($row = $result->fetch_assoc()):
                        $act = strtolower($row['action']);
                        $cls = 'audit-' . $act;
                    ?>
                        <tr>
                            <td class="font-weight-bold"><?= $n++ ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['full_name'] ?? 'Unknown') ?>&background=random&size=32" class="audit-avatar">
                                    <?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?>
                                </div>
                            </td>
                            <td><span class="audit-badge <?= $cls ?>"><?= htmlspecialchars($row['action']) ?></span></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td class="audit-ip"><?= htmlspecialchars($row['ip_address'] ?? '-') ?></td>
                            <td class="audit-ts"><?= date('M j, Y h:i A', strtotime($row['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="audit-no-data">
                    <i class="fas fa-clipboard-list d-block"></i>
                    <h5>No audit logs found</h5>
                    <p class="text-muted">System activity will appear here when available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div><!-- /.container-fluid -->

<script>
$(document).ready(function(){
    $('#auditTable').DataTable({
        "order": [[5, "desc"]],
        "pageLength": 15,
        "language": { "search": "Filter:" }
    });
});
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
