<?php
require_once __DIR__ . '/../config/app.php';

require_once __DIR__ . '/../config/app.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $stmt = $conn->prepare("INSERT INTO test_employees (employee_id, first_name, last_name, contact_number, department, position, employment_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "issssss",
        $_POST['employee_id'],
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['contact_number'],
        $_POST['department'],
        $_POST['position'],
        $_POST['employment_date']
    );
    $stmt->execute();
    $stmt->close();
    header("Location: " . BASE_URL . "/employees/employees.php");
    exit;
}

// Delete employee
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM test_employees WHERE employee_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . BASE_URL . "/employees/employees.php");
    exit;
}

// Fetch all employees
$result = $conn->query("SELECT * FROM test_employees ORDER BY last_name ASC");
$employees = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

$page_title = 'Employees';
$current_page = 'dashboard';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= BASE_URL ?>/dashboard/admin_dashboard.php">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-users"></i></div>
            <div class="sidebar-brand-text mx-3">Employees</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="time_sheet.php"><i class="fas fa-clock"></i><span>Time Sheet</span></a></li>
    </ul>

    
        <div id="content" class="container-fluid pt-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h1 class="h3 mb-0 text-gray-800">Employee Records</h1>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addEmployeeModal">
                    <i class="fas fa-user-plus"></i> Add Employee
                </button>
            </div>

            <div class="card shadow">
                <div class="card-body">
                    <?php if ($employees): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Employment Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($employees as $emp): ?>
                                <tr>
                                    <td><?= $emp['employee_id'] ?></td>
                                    <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                                    <td><?= htmlspecialchars($emp['contact_number']) ?></td>
                                    <td><?= htmlspecialchars($emp['department']) ?></td>
                                    <td><?= htmlspecialchars($emp['position']) ?></td>
                                    <td><?= htmlspecialchars($emp['employment_date']) ?></td>
                                    <td>
                                        <a href="?delete=<?= $emp['employee_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this employee?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">No employees found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Add New Employee</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label>Employee ID</label>
            <input type="number" name="employee_id" class="form-control" required>
        </div>
        <div class="form-group">
            <label>First Name</label>
            <input name="first_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input name="last_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Contact Number</label>
            <input name="contact_number" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Department</label>
            <input name="department" class="form-control">
        </div>
        <div class="form-group">
            <label>Position</label>
            <input name="position" class="form-control">
        </div>
        <div class="form-group">
            <label>Employment Date</label>
            <input type="date" name="employment_date" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_employee" class="btn btn-success">Add Employee</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Scripts -->

<?php include BASE_PATH . '/templates/footer.php'; ?>
