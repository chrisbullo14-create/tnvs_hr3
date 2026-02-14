<?php
require_once __DIR__ . '/../config/app.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $employeeId = $_POST['employee_id'];
    $claimType = $_POST['claim_type'];
    $claimAmount = $_POST['claim_amount'];
    $claimDescription = $_POST['claim_description'];
    $claimDate = date('Y-m-d'); // Set current date as the claim date

    // Prepare and execute query to insert the claim into the database
    $stmt = $conn->prepare("INSERT INTO claim_submissions (employee_id, claim_type, claim_amount, claim_description, claim_date, status) 
                            VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("ssdss", $employeeId, $claimType, $claimAmount, $claimDescription, $claimDate);
    $stmt->execute();
    $stmt->close();

    // Redirect to a page (e.g., claim confirmation page or claim list page)
    header("Location: " . BASE_URL . "/claims/submit_claim.php?status=success");
    exit;
}
?>

<?php
$page_title = 'Submit Claim';
$current_page = 'claim_submission';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<!-- Submit Claim Form -->
                <h1 class="h3 mb-4 text-gray-800">Submit a New Claim</h1>
                <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <div class="alert alert-success" role="alert">
                        Claim has been successfully submitted!
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="submit_claim.php">
                    <div class="form-group">
                        <label for="employee_id">Employee ID</label>
                        <input type="text" class="form-control" id="employee_id" name="employee_id" required>
                    </div>
                    <div class="form-group">
                        <label for="claim_type">Claim Type</label>
                        <input type="text" class="form-control" id="claim_type" name="claim_type" required>
                    </div>
                    <div class="form-group">
                        <label for="claim_amount">Claim Amount</label>
                        <input type="number" class="form-control" id="claim_amount" name="claim_amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="claim_description">Claim Description</label>
                        <textarea class="form-control" id="claim_description" name="claim_description" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Claim</button>
                </form>
                
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap and SB2 JS-->

<?php include BASE_PATH . '/templates/footer.php'; ?>
