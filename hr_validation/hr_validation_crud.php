<?php
require_once __DIR__ . '/../config/app.php';

require_once __DIR__ . '/../config/app.php';
if (isset($_POST['create'])) {
    $employee_id = $_POST['employee_id'];
    $validation_date = $_POST['validation_date'];
    $validation_status = $_POST['validation_status'];
    $comments = $_POST['comments'];

    $stmt = $conn->prepare("INSERT INTO hr_validation (employee_id, validation_date, validation_status, comments) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $employee_id, $validation_date, $validation_status, $comments);
    $stmt->execute();
    header("Location: " . BASE_URL . "/hr_validation/hr_validation_crud.php");
    exit();
}

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['validation_id'];
    $employee_id = $_POST['employee_id'];
    $validation_date = $_POST['validation_date'];
    $validation_status = $_POST['validation_status'];
    $comments = $_POST['comments'];

    $stmt = $conn->prepare("UPDATE hr_validation SET employee_id=?, validation_date=?, validation_status=?, comments=? WHERE validation_id=?");
    $stmt->bind_param("isssi", $employee_id, $validation_date, $validation_status, $comments, $id);
    $stmt->execute();
    header("Location: " . BASE_URL . "/hr_validation/hr_validation_crud.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM hr_validation WHERE validation_id = $id");
    header("Location: " . BASE_URL . "/hr_validation/hr_validation_crud.php");
    exit();
}

// Fetch all records
$result = $conn->query("SELECT * FROM hr_validation ORDER BY validation_date DESC");

// Fetch record for editing
$edit = false;
$edit_row = [];
if (isset($_GET['edit'])) {
    $edit = true;
    $id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM hr_validation WHERE validation_id = $id");
    $edit_row = $edit_result->fetch_assoc();
}

$page_title = 'HR Validation';
$current_page = 'hr_validation';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<h2 class="mb-4"><?= $edit ? 'Edit Validation' : 'Add New Validation' ?></h2>

    <!-- Form -->
    <form method="post" class="mb-5">
        <input type="hidden" name="validation_id" value="<?= $edit ? $edit_row['validation_id'] : '' ?>">

        <div class="mb-3">
            <label>Employee ID</label>
            <input type="number" name="employee_id" class="form-control" required value="<?= $edit ? $edit_row['employee_id'] : '' ?>">
        </div>
        <div class="mb-3">
            <label>Validation Date</label>
            <input type="date" name="validation_date" class="form-control" required value="<?= $edit ? $edit_row['validation_date'] : '' ?>">
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="validation_status" class="form-control" required>
                <option value="">Select status</option>
                <option value="Approved" <?= $edit && $edit_row['validation_status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Pending" <?= $edit && $edit_row['validation_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Rejected" <?= $edit && $edit_row['validation_status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Comments</label>
            <textarea name="comments" class="form-control"><?= $edit ? $edit_row['comments'] : '' ?></textarea>
        </div>

        <button type="submit" name="<?= $edit ? 'update' : 'create' ?>" class="btn btn-<?= $edit ? 'warning' : 'primary' ?>">
            <?= $edit ? 'Update' : 'Create' ?>
        </button>
        <?php if ($edit): ?>
            <a href="<?= BASE_URL ?>/hr_validation/hr_validation_crud.php" class="btn btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <h4>Validation Records</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee ID</th>
                <th>Date</th>
                <th>Status</th>
                <th>Comments</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['validation_id'] ?></td>
                    <td><?= $row['employee_id'] ?></td>
                    <td><?= $row['validation_date'] ?></td>
                    <td><?= $row['validation_status'] ?></td>
                    <td><?= $row['comments'] ?></td>
                    <td>
                        <a href="?edit=<?= $row['validation_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?= $row['validation_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

<?php include BASE_PATH . '/templates/footer.php'; ?>
