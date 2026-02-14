<?php
require_once __DIR__ . '/../config/app.php';

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM claim_submissions";
if ($status_filter && in_array($status_filter, ['Pending', 'Approved', 'Rejected'])) {
    $sql .= " WHERE status = '" . $conn->real_escape_string($status_filter) . "'";
}
$sql .= " ORDER BY date_submitted DESC";

$result = $conn->query($sql);
?>

<?php
$page_title = 'Record Keeping & Reporting';
$current_page = 'record_keeping';
$extra_css = '<style>
        :root {
            --primary: #4e73df;
            --primary-light: #e8f0fe;
            --success: #1cc88a;
            --success-light: #e6f8f1;
            --warning: #f6c23e;
            --warning-light: #fdf6e8;
            --danger: #e74a3b;
            --danger-light: #fce8e6;
            --gray-100: #f8f9fc;
            --gray-200: #eaecf4;
        }
        
        body {
            font-family: \'Inter\', sans-serif;
            background-color: #f5f7ff;
            color: #2d3748;
        }
        
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 1rem rgba(58, 59, 69, 0.05);
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .table-responsive {
            border-radius: 0.75rem;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--primary);
            color: white;
            border: none;
            font-weight: 500;
            padding: 1rem 1.5rem;
        }
        
        .table tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-top: 1px solid var(--gray-200);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: var(--warning-light);
            color: #8d6a0a;
        }
        
        .status-approved {
            background-color: var(--success-light);
            color: #0d6832;
        }
        
        .status-rejected {
            background-color: var(--danger-light);
            color: #8f2a1b;
        }
        
        .btn {
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: #3a5bc7;
            border-color: #3a5bc7;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .btn-success:hover {
            background-color: #17a673;
            border-color: #17a673;
            transform: translateY(-1px);
        }
        
        .form-control, .custom-select {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid #e0e0e0;
        }
        
        .alert {
            border-radius: 0.5rem;
            border: none;
        }
        
        .alert-success {
            background-color: var(--success-light);
            color: #0d6832;
        }
        
        .alert-danger {
            background-color: var(--danger-light);
            color: #8f2a1b;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .page-title {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .page-description {
            color: var(--secondary);
            font-size: 1rem;
        }
        
        .filter-container {
            background-color: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: white;
            border-radius: 0.75rem;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .amount-cell {
            font-weight: 600;
            color: #2d3748;
        }
        
        .topbar {
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
        }
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<div class="page-header">
                    <div>
                        <h1 class="page-title">Record Keeping & Reporting</h1>
                        <p class="page-description">View and manage all claim submissions</p>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="filter-container">
                    <form method="GET" class="form-inline align-items-center">
                        <div class="form-group mr-3 mb-2">
                            <label for="status" class="mr-2">Filter by Status:</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Claims</option>
                                <option value="Pending" <?= ($status_filter == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="Approved" <?= ($status_filter == 'Approved') ? 'selected' : '' ?>>Approved</option>
                                <option value="Rejected" <?= ($status_filter == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mr-3 mb-2">
                            <i class="fas fa-filter mr-2"></i>Apply Filter
                        </button>
                        <a href="<?= BASE_URL ?>/claims/export_claims.php?status=<?= urlencode($status_filter) ?>" class="btn btn-success mb-2">
                            <i class="fas fa-file-export mr-2"></i>Export CSV
                        </a>
                    </form>
                </div>

                <?php if ($result->num_rows > 0): ?>
                <div class="card shadow mb-4">
                    <div class="table-responsive">
                        <table class="table" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Contact</th>
                                <th>Claim Type</th>
                                <th>Incident Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td><?= htmlspecialchars($row['contact_number']) ?></td>
                                    <td><?= htmlspecialchars($row['claim_type']) ?></td>
                                    <td><?= htmlspecialchars($row['incident_date']) ?></td>
                                    <td class="amount-cell">₱<?= number_format($row['amount'], 2) ?></td>
                                    <td>
                                        <?php if (empty($row['status']) || $row['status'] == 'Pending'): ?>
                                            <span class="status-badge status-pending">
                                                <i class="fas fa-clock mr-1"></i> Pending
                                            </span>
                                        <?php elseif ($row['status'] == 'Approved'): ?>
                                            <span class="status-badge status-approved">
                                                <i class="fas fa-check-circle mr-1"></i> Approved
                                            </span>
                                        <?php elseif ($row['status'] == 'Rejected'): ?>
                                            <span class="status-badge status-rejected">
                                                <i class="fas fa-times-circle mr-1"></i> Rejected
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['date_submitted']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                    <div class="card shadow">
                        <div class="empty-state">
                            <i class="fas fa-table"></i>
                            <h4>No Claim Records Found</h4>
                            <p class="text-muted">There are currently no claims matching your criteria.</p>
                            <a href="<?= BASE_URL ?>/claims/record_keeping.php" class="btn btn-primary mt-3">
                                <i class="fas fa-sync-alt mr-2"></i>Reset Filters
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

<?php include BASE_PATH . '/templates/footer.php'; ?>
