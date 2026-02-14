<?php
require_once __DIR__ . '/../config/app.php';
// Fetch employees for dropdown
$employees = [];
$result = $conn->query("SELECT id, full_name FROM user_accounts ORDER BY full_name ASC");
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

// Handle form submissions (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $expense_name = trim($_POST['expense_name']);
    $description = trim($_POST['description']);
    $employee_id = $_POST['employee_id'];
    $edit_id = $_POST['edit_id'] ?? null;

    if (!$employee_id || !$expense_name) {
        $_SESSION['flash'] = "Please fill all required fields.";
        header("Location: " . BASE_URL . "/expenses/expenses_insured.php");
        exit;
    }

    if ($edit_id) {
        $stmt = $conn->prepare("UPDATE expenses_insured SET employee_id = ?, expense_name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("issi", $employee_id, $expense_name, $description, $edit_id);
        $stmt->execute();
        $_SESSION['flash'] = "Expense updated successfully.";
    } else {
        $stmt = $conn->prepare("INSERT INTO expenses_insured (employee_id, expense_name, description, status, date_added)
                                VALUES (?, ?, ?, 'Active', NOW())");
        $stmt->bind_param("iss", $employee_id, $expense_name, $description);
        $stmt->execute();
        $_SESSION['flash'] = "Expense added successfully.";
    }
    header("Location: " . BASE_URL . "/expenses/expenses_insured.php");
    exit;
}

// Archive expense
if (isset($_GET['archive_id'])) {
    $stmt = $conn->prepare("UPDATE expenses_insured SET status = 'Archived' WHERE id = ?");
    $stmt->bind_param("i", $_GET['archive_id']);
    $stmt->execute();
    $_SESSION['flash'] = "Expense archived.";
    header("Location: " . BASE_URL . "/expenses/expenses_insured.php");
    exit;
}

// Restore expense
if (isset($_GET['restore_id'])) {
    $stmt = $conn->prepare("UPDATE expenses_insured SET status = 'Active' WHERE id = ?");
    $stmt->bind_param("i", $_GET['restore_id']);
    $stmt->execute();
    $_SESSION['flash'] = "Expense restored.";
    header("Location: " . BASE_URL . "/expenses/expenses_insured.php");
    exit;
}

// Clear all archived expenses permanently
if (isset($_GET['clear_archived'])) {
    $stmt = $conn->prepare("DELETE FROM expenses_insured WHERE status = 'Archived'");
    $stmt->execute();
    $_SESSION['flash'] = "All archived expenses have been permanently deleted.";
    header("Location: " . BASE_URL . "/expenses/expenses_insured.php");
    exit;
}

// Fetch Active and Archived expenses
$statuses = ['Active', 'Archived'];
$expenses = [];
foreach ($statuses as $status) {
    $stmt = $conn->prepare("
        SELECT ei.*, u.full_name 
        FROM expenses_insured ei
        LEFT JOIN user_accounts u ON ei.employee_id = u.id
        WHERE ei.status = ? ORDER BY ei.date_added DESC
    ");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $expenses[$status] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<?php
$page_title = 'Expenses Insured';
$current_page = 'expenses_insured';
$extra_css = '<style>
    .exp-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
    .exp-card .card-header { background: linear-gradient(90deg, #4e73df, #3a5ccc); color: white; border-bottom: none; padding: 1.25rem 1.5rem; font-weight: 600; }
    .exp-title { color: #5a5c69; font-weight: 700; }
    #activeTable thead th, #archivedTable thead th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border: none; padding: 1rem; }
    .exp-avatar { width: 32px; height: 32px; border-radius: 50%; margin-right: 10px; border: 2px solid #f8f9fc; }
    .exp-no-data { text-align: center; padding: 2rem; color: #6c757d; }
    .exp-no-data i { font-size: 3rem; color: #dee2e6; margin-bottom: 1rem; }
    .exp-summary .border-left-primary { border-left: 4px solid #4e73df !important; }
    .exp-summary .border-left-success { border-left: 4px solid #1cc88a !important; }
    .exp-summary .border-left-warning { border-left: 4px solid #f6c23e !important; }
    .exp-tabs .nav-link { color: #5a5c69; font-weight: 600; border: none; padding: 0.75rem 1.25rem; border-radius: 8px 8px 0 0; }
    .exp-tabs .nav-link.active { background: linear-gradient(135deg, #4e73df, #3a5ccc); color: white; border: none; }
    .exp-actions { display: flex; gap: 5px; }
</style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';

$activeCount = count($expenses['Active']);
$archivedCount = count($expenses['Archived']);
$totalCount = $activeCount + $archivedCount;
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 exp-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Expenses Insured</h1>
    </div>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars(is_array($_SESSION['flash']) ? implode(', ', $_SESSION['flash']) : $_SESSION['flash']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-4 exp-summary">
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="card shadow-sm border-left-primary h-100"><div class="card-body py-3">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Expenses</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalCount ?></div>
            </div></div>
        </div>
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="card shadow-sm border-left-success h-100"><div class="card-body py-3">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $activeCount ?></div>
            </div></div>
        </div>
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="card shadow-sm border-left-warning h-100"><div class="card-body py-3">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Archived</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $archivedCount ?></div>
            </div></div>
        </div>
    </div>

    <!-- Add / Edit Form -->
    <div class="card exp-card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-white" id="formTitle"><i class="fas fa-plus-circle mr-2"></i>Add New Expense</h6>
        </div>
        <div class="card-body">
            <form method="POST" id="expenseForm">
                <input type="hidden" name="edit_id" id="edit_id" value="">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="font-weight-bold">Employee</label>
                        <select name="employee_id" id="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="font-weight-bold">Expense Name</label>
                        <input type="text" name="expense_name" id="expense_name" class="form-control" placeholder="e.g. Hospital Bills" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Description</label>
                        <input type="text" name="description" id="description" class="form-control" placeholder="Brief description">
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save mr-1"></i>Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs: Active / Archived -->
    <div class="card exp-card">
        <div class="card-body">
            <ul class="nav nav-tabs exp-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#activeTab"><i class="fas fa-check-circle mr-1"></i>Active <span class="badge badge-success ml-1"><?= $activeCount ?></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#archivedTab"><i class="fas fa-archive mr-1"></i>Archived <span class="badge badge-warning ml-1"><?= $archivedCount ?></span></a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Active Tab -->
                <div class="tab-pane fade show active" id="activeTab">
                    <?php if (empty($expenses['Active'])): ?>
                        <div class="exp-no-data"><i class="fas fa-folder-open d-block"></i><h5>No active expenses</h5></div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="activeTable" width="100%" cellspacing="0">
                            <thead>
                                <tr><th>#</th><th>Employee</th><th>Expense Name</th><th>Description</th><th>Date Added</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php $n=1; foreach ($expenses['Active'] as $row): ?>
                                <tr>
                                    <td><?= $n++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['full_name'] ?? 'U') ?>&background=random&size=32" class="exp-avatar">
                                            <?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['expense_name']) ?></td>
                                    <td><?= htmlspecialchars($row['description'] ?? '-') ?></td>
                                    <td class="small text-muted"><?= date('M d, Y h:i A', strtotime($row['date_added'])) ?></td>
                                    <td>
                                        <div class="exp-actions">
                                            <button class="btn btn-sm btn-warning" title="Edit" onclick="editExpense(<?= $row['id'] ?>, <?= $row['employee_id'] ?>, '<?= addslashes($row['expense_name']) ?>', '<?= addslashes($row['description'] ?? '') ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>/expenses/expenses_insured.php?archive_id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary" title="Archive" onclick="return confirm('Archive this expense?')">
                                                <i class="fas fa-archive"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Archived Tab -->
                <div class="tab-pane fade" id="archivedTab">
                    <?php if (empty($expenses['Archived'])): ?>
                        <div class="exp-no-data"><i class="fas fa-archive d-block"></i><h5>No archived expenses</h5></div>
                    <?php else: ?>
                    <?php if ($archivedCount > 0): ?>
                        <div class="mb-3 text-right">
                            <a href="<?= BASE_URL ?>/expenses/expenses_insured.php?clear_archived=1" class="btn btn-sm btn-danger" onclick="return confirm('Permanently delete ALL archived expenses?')">
                                <i class="fas fa-trash mr-1"></i>Clear All Archived
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="archivedTable" width="100%" cellspacing="0">
                            <thead>
                                <tr><th>#</th><th>Employee</th><th>Expense Name</th><th>Description</th><th>Date Added</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php $n=1; foreach ($expenses['Archived'] as $row): ?>
                                <tr>
                                    <td><?= $n++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['full_name'] ?? 'U') ?>&background=random&size=32" class="exp-avatar">
                                            <?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['expense_name']) ?></td>
                                    <td><?= htmlspecialchars($row['description'] ?? '-') ?></td>
                                    <td class="small text-muted"><?= date('M d, Y h:i A', strtotime($row['date_added'])) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/expenses/expenses_insured.php?restore_id=<?= $row['id'] ?>" class="btn btn-sm btn-success" title="Restore" onclick="return confirm('Restore this expense?')">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    </td>
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

<script>
function editExpense(id, empId, name, desc) {
    document.getElementById('edit_id').value = id;
    document.getElementById('employee_id').value = empId;
    document.getElementById('expense_name').value = name;
    document.getElementById('description').value = desc;
    document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Expense #' + id;
    window.scrollTo({top: 0, behavior: 'smooth'});
}
$(document).ready(function(){
    $('#activeTable').DataTable({ "order": [[4, "desc"]], "pageLength": 10 });
    $('#archivedTable').DataTable({ "order": [[4, "desc"]], "pageLength": 10 });
});
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
