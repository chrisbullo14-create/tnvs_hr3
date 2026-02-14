<?php
require_once __DIR__ . '/../config/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $contact_number = trim($_POST['contact_number']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $license_number = trim($_POST['license_number']);
    $vehicle_plate = trim($_POST['vehicle_plate']);
    $claim_type = $_POST['claim_type'];
    $incident_date = $_POST['incident_date'];
    $incident_location = trim($_POST['incident_location']);
    $claim_description = trim($_POST['claim_description']);
    $amount = $_POST['amount'];

    if (empty($full_name) || empty($contact_number) || empty($claim_type) || empty($incident_date) || empty($incident_location) || empty($claim_description)) {
        $_SESSION['error'] = "Please fill all required fields.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $upload_dir = 'uploads/';
    $supporting_document = null;

    if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['supporting_document']['tmp_name'];
        $file_name = basename($_FILES['supporting_document']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . "." . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $supporting_document = $new_file_name;
            } else {
                $_SESSION['error'] = "Failed to upload file.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Allowed: pdf, jpg, jpeg, png.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    $stmt = $conn->prepare("INSERT INTO claim_submissions (full_name, contact_number, email, address, license_number, vehicle_plate, claim_type, incident_date, incident_location, claim_description, amount, supporting_document, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("ssssssssssds", $full_name, $contact_number, $email, $address, $license_number, $vehicle_plate, $claim_type, $incident_date, $incident_location, $claim_description, $amount, $supporting_document);
    $stmt->execute();

    $_SESSION['success'] = "Claim submitted successfully!";
    // Redirect to approval page after successful submission
    header("Location: " . BASE_URL . "/compliance/approval_process.php");
    exit;
}
?>

<?php
$page_title = 'Claim Submission';
$current_page = 'claim_submission';
$extra_css = '<style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fc;
            --dark-color: #2b2d42;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .sidebar-brand {
            padding: 1.5rem 0;
        }
        
        .sidebar-brand-icon {
            font-size: 1.5rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            color: white;
            font-weight: 600;
            padding: 1.25rem 1.5rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .form-section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
        }
        
        .form-section-title i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-control {
            border-radius: 6px;
            padding: 10px 15px;
            border: 1px solid #e0e3f1;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .required-field::after {
            content: " *";
            color: #e63946;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .page-title {
            color: var(--dark-color);
            font-weight: 700;
        }
        
        .alert {
            border-radius: 6px;
        }
        
        @media (max-width: 768px) {
            .form-section {
                padding-bottom: 1rem;
                margin-bottom: 1.5rem;
            }
        }
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<div class="page-header">
                        <h1 class="page-title">Submit New Claim</h1>
                    </div>

                    <?php if (isset($_SESSION['error'])) : ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?= $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])) : ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?= $_SESSION['success'];
                            unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-file-alt mr-2"></i>Claim Information
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="fas fa-user"></i>Personal Information
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="full_name" class="required-field">Full Name</label>
                                                <input type="text" name="full_name" id="full_name" class="form-control" required />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="contact_number" class="required-field">Contact Number</label>
                                                <input type="text" name="contact_number" id="contact_number" class="form-control" required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email Address</label>
                                                <input type="email" name="email" id="email" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="license_number">License Number</label>
                                                <input type="text" name="license_number" id="license_number" class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="fas fa-car"></i>Vehicle Information (Optional)
                                    </h5>
                                    <div class="form-group">
                                        <label for="vehicle_plate">Vehicle Plate Number</label>
                                        <input type="text" name="vehicle_plate" id="vehicle_plate" class="form-control" />
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="fas fa-exclamation-circle"></i>Incident Details
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="claim_type" class="required-field">Claim Type</label>
                                                <select name="claim_type" id="claim_type" class="form-control" required>
                                                    <option value="">Select Claim Type</option>
                                                    <option value="Accident">Accident</option>
                                                    <option value="Theft">Theft</option>
                                                    <option value="Damage">Damage</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="incident_date" class="required-field">Incident Date</label>
                                                <input type="date" name="incident_date" id="incident_date" class="form-control" required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="incident_location" class="required-field">Incident Location</label>
                                        <input type="text" name="incident_location" id="incident_location" class="form-control" required />
                                    </div>
                                    <div class="form-group">
                                        <label for="claim_description" class="required-field">Claim Description</label>
                                        <textarea name="claim_description" id="claim_description" rows="3" class="form-control" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount">Claim Amount</label>
                                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" placeholder="0.00" />
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="fas fa-paperclip"></i>Supporting Documents
                                    </h5>
                                    <div class="form-group">
                                        <label for="supporting_document">Upload Supporting Documents</label>
                                        <div class="custom-file">
                                            <input type="file" name="supporting_document" id="supporting_document" class="form-control-file" />
                                            <small class="form-text text-muted">Accepted formats: PDF, JPG, JPEG, PNG (Max 5MB)</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane mr-2"></i>Submit Claim
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

<?php include BASE_PATH . '/templates/footer.php'; ?>
