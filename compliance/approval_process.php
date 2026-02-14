<?php
require_once __DIR__ . '/../config/app.php';

$query = "SELECT * FROM claim_submissions ORDER BY id DESC";
$result = $conn->query($query);

/* summary counts */
$total   = $result ? $result->num_rows : 0;
$pending = $approved = $rejected = 0;
$rows = [];
if ($result && $total > 0) {
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
        if ($r['status'] === 'Approved') $approved++;
        elseif ($r['status'] === 'Rejected') $rejected++;
        else $pending++;
    }
}
?>
<?php
$page_title = 'Approval Process';
$current_page = 'approval_process';
$extra_css = '<style>
    /* ── scoped: approval-process page ── */
    .ap-page .ap-card          { border:none;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,.08);overflow:hidden; }
    .ap-page .ap-card-header   { background:linear-gradient(90deg,#4361ee,#3f37c9);color:#fff;font-weight:600;padding:1.25rem 1.5rem; }
    .ap-page .ap-table thead th{ background:#f8f9fc;border-bottom:2px solid #e3e6f0;color:#2b2d42;font-weight:600;text-transform:uppercase;font-size:.75rem;letter-spacing:.5px; }
    .ap-page .ap-table tbody tr{ transition:all .2s; }
    .ap-page .ap-table tbody tr:hover{ background:rgba(67,97,238,.05); }
    .ap-page .ap-status        { padding:5px 10px;border-radius:20px;font-size:.75rem;font-weight:600;text-transform:uppercase; }
    .ap-page .ap-status-pending  { background:rgba(248,150,30,.1);color:#f8961e; }
    .ap-page .ap-status-approved { background:rgba(40,167,69,.1);color:#28a745; }
    .ap-page .ap-status-rejected { background:rgba(247,37,133,.1);color:#f72585; }
    .ap-page .ap-summary       { border-left:4px solid; border-radius:8px; }
    .ap-page .ap-summary.border-primary  { border-left-color:#4361ee!important; }
    .ap-page .ap-summary.border-warning  { border-left-color:#f8961e!important; }
    .ap-page .ap-summary.border-success  { border-left-color:#28a745!important; }
    .ap-page .ap-summary.border-danger   { border-left-color:#f72585!important; }
    .ap-page .ap-empty         { text-align:center;padding:2rem;color:#6c757d; }
    .ap-page .ap-empty i       { font-size:3rem;color:#dee2e6;margin-bottom:1rem; }
</style>';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<div class="container-fluid ap-page">

    <!-- Flash messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <h1 class="h3 mb-4 text-gray-800">Claim Approval Dashboard</h1>

    <!-- Summary cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card ap-summary border-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Claims</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-folder-open fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card ap-summary border-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pending ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card ap-summary border-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $approved ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card ap-summary border-danger shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $rejected ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Claims table -->
    <div class="card ap-card shadow mb-4">
        <div class="card-header ap-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-white">
                    <i class="fas fa-tasks mr-2"></i>Claims Review
                </h6>
                <span class="badge badge-light"><?= $total ?> Total</span>
            </div>
        </div>
        <div class="card-body">
            <?php if ($total > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover ap-table" id="claimsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Claim ID</th>
                            <th>Claimant</th>
                            <th>Contact</th>
                            <th>Type</th>
                            <th>Incident Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td>
                                <div class="font-weight-bold"><?= htmlspecialchars($row['full_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['contact_number']) ?></td>
                            <td><span class="badge badge-light"><?= htmlspecialchars($row['claim_type']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($row['incident_date'])) ?></td>
                            <td class="font-weight-bold">&#8369;<?= number_format($row['amount'], 2) ?></td>
                            <td>
                                <?php if (empty($row['status']) || $row['status'] === 'Pending'): ?>
                                    <span class="ap-status ap-status-pending"><i class="fas fa-clock mr-1"></i>Pending</span>
                                <?php elseif ($row['status'] === 'Approved'): ?>
                                    <span class="ap-status ap-status-approved"><i class="fas fa-check mr-1"></i>Approved</span>
                                <?php else: ?>
                                    <span class="ap-status ap-status-rejected"><i class="fas fa-times mr-1"></i>Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (empty($row['status']) || $row['status'] === 'Pending'): ?>
                                    <a href="<?= BASE_URL ?>/claims/claim_approve_action.php?id=<?= $row['id'] ?>"
                                       class="btn btn-success btn-sm mr-1"
                                       onclick="return confirm('Approve this claim?');">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </a>
                                    <a href="<?= BASE_URL ?>/claims/claim_reject_action.php?id=<?= $row['id'] ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Reject this claim?');">
                                        <i class="fas fa-times mr-1"></i>Reject
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="ap-empty">
                    <i class="fas fa-inbox fa-4x"></i>
                    <h4>No claims submitted yet</h4>
                    <p>When claims are submitted, they will appear here for review.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /.container-fluid -->

<!-- DataTable init -->
<script>
$(document).ready(function(){
    $('#claimsTable').DataTable({
        order:[[0,'desc']],
        pageLength:10,
        responsive:true,
        language:{ search:'', searchPlaceholder:'Search claims...' }
    });
});
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
