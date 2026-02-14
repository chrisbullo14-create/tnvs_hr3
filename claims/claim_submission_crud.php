<?php
require_once __DIR__ . '/../config/app.php';

require_once __DIR__ . '/../config/app.php';
$employees = $conn->query("SELECT employee_id, first_name, last_name FROM test_employees");

// Handle Claim Submission
if (isset($_POST['submit_claim'])) {
    $employee_id = $_POST['employee_id'] ?? null;
    $claim_date = $_POST['claim_date'] ?? null;
    $claim_description = $_POST['claim_description'] ?? '';

    if (empty($employee_id) || empty($claim_date) || empty($claim_description)) {
        echo "<script>alert('Please fill all required fields.');</script>";
    } else {
        // Insert claim
        $stmt = $conn->prepare("INSERT INTO claims (employee_id, claim_date, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $employee_id, $claim_date, $claim_description);
        $stmt->execute();

        $claim_id = $stmt->insert_id;
        $stmt->close();

        // Insert approval process linked to claim
        $process_name = "Claim Approval for Claim #$claim_id";
        $process_date = date('Y-m-d');
        $status = 'Pending';

        $stmt2 = $conn->prepare("INSERT INTO approval_process (process_name, initiated_by, process_date, status, claim_id) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("ssssi", $process_name, $employee_id, $process_date, $status, $claim_id);
        $stmt2->execute();
        $stmt2->close();

        echo "<script>alert('Claim submitted successfully and sent for approval.'); window.location.href='claim_submission.php';</script>";
        exit;
    }
}

$page_title = 'Claim Submission';
$current_page = 'claim_submission';
$extra_js = '<script>alert(\'Please fill all required fields.\');</script>
<script>alert(\'Claim submitted successfully and sent for approval.\'); window.location.href=\'claim_submission.php\';</script>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-file-alt"></i></div>
            <div class="sidebar-brand-text mx-3">Claim Submission</div>
        </a>
        <li class="nav-item active">
            <a class="nav-link" href="<?= BASE_URL ?>/claims/claim_submission.php"><i class="fas fa-fw fa-clipboard-list"></i><span>Submit Claim</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/compliance/approval_process.php"><i class="fas fa-fw fa-tasks"></i><span>Approval & Process</span></a>
        </li>
    </ul>

    
        
            

            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Claim Submission Form</h1>
                <form method="POST" class="mb-5">
                    <div class="form-group">
                        <label for="employee_id">Claimant (Employee) *</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            <?php while ($emp = $employees->fetch_assoc()): ?>
                                <option value="<?= $emp['employee_id'] ?>">
                                    <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="claim_date">Claim Date *</label>
                        <input type="date" name="claim_date" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="claim_description">Claim Description *</label>
                        <textarea name="claim_description" class="form-control" rows="3" required></textarea>
                    </div>

                    <button type="submit" name="submit_claim" class="btn btn-primary">Submit Claim</button>
                </form>

<?php include BASE_PATH . '/templates/footer.php'; ?>
