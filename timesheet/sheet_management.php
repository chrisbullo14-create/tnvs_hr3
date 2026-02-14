<?php
require_once __DIR__ . '/../config/app.php';
if (!isset($_SESSION['flash'])) {
    $_SESSION['flash'] = ['success' => '', 'error' => ''];
}

// CSRF token
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Handle actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION['token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token");
    }

    function validate_input($data) {
        return trim(htmlspecialchars($data));
    }

    $employee_id = intval($_POST['employee_id'] ?? 0);
    $route = validate_input($_POST['route'] ?? '');
    $assignment_date = $_POST['assignment_date'] ?? '';
    $vehicle_id = validate_input($_POST['vehicle_id'] ?? '');
    $remarks = validate_input($_POST['remarks'] ?? '');

    if (isset($_POST['add_sheet'])) {
        if ($employee_id > 0 && $route && $assignment_date && $vehicle_id) {
            $stmt = $conn->prepare("INSERT INTO sheet_management (employee_id, route, assignment_date, vehicle_id, remarks) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $employee_id, $route, $assignment_date, $vehicle_id, $remarks);
            $_SESSION['flash']['success'] = $stmt->execute() ? "Sheet added successfully." : "Failed to add sheet.";
            $stmt->close();
        } else {
            $_SESSION['flash']['error'] = "Please fill in all required fields.";
        }
    }

    if (isset($_POST['edit_sheet'])) {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && $employee_id > 0 && $route && $assignment_date && $vehicle_id) {
            $stmt = $conn->prepare("UPDATE sheet_management SET employee_id=?, route=?, assignment_date=?, vehicle_id=?, remarks=? WHERE id=?");
            $stmt->bind_param("issssi", $employee_id, $route, $assignment_date, $vehicle_id, $remarks, $id);
            $_SESSION['flash']['success'] = $stmt->execute() ? "Sheet updated successfully." : "Failed to update sheet.";
            $stmt->close();
        } else {
            $_SESSION['flash']['error'] = "Please fill in all required fields for update.";
        }
    }

    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        if ($delete_id > 0) {
            $stmt = $conn->prepare("DELETE FROM sheet_management WHERE id=?");
            $stmt->bind_param("i", $delete_id);
            $_SESSION['flash']['success'] = $stmt->execute() ? "Sheet deleted successfully." : "Failed to delete sheet.";
            $stmt->close();
        } else {
            $_SESSION['flash']['error'] = "Invalid delete request.";
        }
    }

    header("Location: " . BASE_URL . "/timesheet/sheet_management.php");
    exit;
}
?>

<?php
$page_title = 'Sheet Management';
$current_page = 'sheet_management';
$extra_css = '<style>
        .sheet-page-title {
            color: #3a0ca3;
            font-weight: 700;
        }

        .sheet-form {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
        }

        .sheet-table-wrapper {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }

        #sheetTable thead th {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            border: none;
            padding: 0.85rem 1rem;
            font-size: 0.85rem;
        }

        #sheetTable tbody tr:hover {
            background-color: #eef2ff;
        }

        #sheetTable tbody td {
            vertical-align: middle;
            padding: 0.85rem 1rem;
        }
    </style>
<link href="<?= BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">';
$extra_js = '<script src="<?= BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<!-- Page Content -->
<div class="container-fluid">

    <h1 class="h3 mb-4 sheet-page-title"><i class="fas fa-list mr-2"></i>TNVS Sheet Management</h1>

            <?php if ($_SESSION['flash']['success']): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= $_SESSION['flash']['success'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
                </div>
                <?php $_SESSION['flash']['success'] = ''; endif; ?>

            <?php if ($_SESSION['flash']['error']): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= $_SESSION['flash']['error'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
                </div>
                <?php $_SESSION['flash']['error'] = ''; endif; ?>

            <form method="POST" class="sheet-form mb-3">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['token'] ?>">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-row">
                    <!-- Employee Dropdown - Now fully visible -->
                    <div class="col-md-3">
                        <label class="font-weight-bold"><i class="fas fa-user-tie mr-2"></i>Employee</label>
                        <select name="employee_id" class="form-control" required id="edit_employee" style="height: auto; min-height: 45px;">
                            <option value="">Select Employee</option>
                            <?php
                            $emp = $conn->query("SELECT id, full_name FROM user_accounts ORDER BY full_name");
                            if ($emp) {
                                while ($e = $emp->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($e['id']) . "'>" . htmlspecialchars($e['full_name']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold"><i class="fas fa-route mr-2"></i>Route</label>
                        <input type="text" name="route" class="form-control" required id="edit_route">
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold"><i class="fas fa-calendar-day mr-2"></i>Date</label>
                        <input type="date" name="assignment_date" class="form-control" required id="edit_date">
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold"><i class="fas fa-car mr-2"></i>Vehicle ID</label>
                        <input type="text" name="vehicle_id" class="form-control" required id="edit_vehicle">
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold"><i class="fas fa-comment-alt mr-2"></i>Remarks</label>
                        <input type="text" name="remarks" class="form-control" id="edit_remarks">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" name="add_sheet" id="addBtn" class="btn btn-success">
                        <i class="fas fa-plus mr-2"></i>Add Sheet
                    </button>
                    <button type="submit" name="edit_sheet" id="updateBtn" class="btn btn-warning d-none">
                        <i class="fas fa-save mr-2"></i>Update
                    </button>
                    <button type="button" class="btn btn-secondary d-none" id="cancelEdit">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                </div>
            </form>

            <div class="sheet-table-wrapper">
                <table class="table table-bordered" id="sheetTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Route</th>
                        <th>Date</th>
                        <th>Vehicle ID</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $result = $conn->query("SELECT s.*, u.full_name as employee_name FROM sheet_management s LEFT JOIN user_accounts u ON s.employee_id = u.id ORDER BY s.id DESC");
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($row['route']) ?></td>
                        <td><?= date('M d, Y', strtotime($row['assignment_date'])) ?></td>
                        <td><span class="badge badge-primary"><?= htmlspecialchars($row['vehicle_id']) ?></span></td>
                        <td><?= !empty($row['remarks']) ? htmlspecialchars($row['remarks']) : '<span class="text-muted">None</span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-info editBtn mr-1"
                                data-id="<?= $row['id'] ?>"
                                data-employee="<?= $row['employee_id'] ?>"
                                data-route="<?= htmlspecialchars($row['route'], ENT_QUOTES) ?>"
                                data-date="<?= $row['assignment_date'] ?>"
                                data-vehicle="<?= htmlspecialchars($row['vehicle_id'], ENT_QUOTES) ?>"
                                data-remarks="<?= htmlspecialchars($row['remarks'] ?? '', ENT_QUOTES) ?>">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['token'] ?>">
                                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            No sheets found. Add one using the form above.
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

</div>
<!-- /.container-fluid -->

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#sheetTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 10
        });
    }

    // Edit button click
    $(document).on('click', '.editBtn', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_employee').val($(this).data('employee'));
        $('#edit_route').val($(this).data('route'));
        $('#edit_date').val($(this).data('date'));
        $('#edit_vehicle').val($(this).data('vehicle'));
        $('#edit_remarks').val($(this).data('remarks'));

        $('#addBtn').addClass('d-none');
        $('#updateBtn').removeClass('d-none');
        $('#cancelEdit').removeClass('d-none');

        $('html, body').animate({ scrollTop: $('form').offset().top - 20 }, 300);
    });

    // Cancel edit
    $('#cancelEdit').on('click', function() {
        $('#edit_id').val('');
        $('#edit_employee').val('');
        $('#edit_route').val('');
        $('#edit_date').val('');
        $('#edit_vehicle').val('');
        $('#edit_remarks').val('');

        $('#addBtn').removeClass('d-none');
        $('#updateBtn').addClass('d-none');
        $('#cancelEdit').addClass('d-none');
    });
});
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
