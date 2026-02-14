<?php
require_once __DIR__ . '/../config/app.php';
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_SANITIZE_NUMBER_INT);
    $recordDate = filter_input(INPUT_POST, 'recordDate', FILTER_SANITIZE_STRING);
    $timeIn = filter_input(INPUT_POST, 'timeIn', FILTER_SANITIZE_STRING);
    $timeOut = filter_input(INPUT_POST, 'timeOut', FILTER_SANITIZE_STRING);

    // Validate employee exists in onboarding_contracts via applications
    $check_sql = "
        SELECT u.id 
        FROM onboarding_contracts oc
        INNER JOIN applications a ON oc.application_id = a.id
        INNER JOIN users u ON a.user_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ";
    if ($check_stmt = $conn->prepare($check_sql)) {
        $check_stmt->bind_param("i", $employeeId);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows === 0) {
            $error = "Error: Employee is not onboarded.";
        }
        $check_stmt->close();
    } else {
        $error = "Error checking employee: " . $conn->error;
    }

    if (empty($error)) {
        if (strtotime($timeIn) >= strtotime($timeOut)) {
            $error = "Error: Time Out must be after Time In.";
        } else {
            $sql = "INSERT INTO attendance_time_log (employee_id, record_date, time_in, time_out) 
                    VALUES (?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("isss", $employeeId, $recordDate, $timeIn, $timeOut);
                if ($stmt->execute()) {
                    $success = "Time log entry saved successfully!";
                    $_POST = array(); // Clear form
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<?php
$page_title = 'Time In & Out';
$current_page = 'attendance_time_log';
$extra_css = '<style>
        :root {
            --primary: #5c6bc0;
            --primary-light: #e8eaf6;
            --primary-dark: #3949ab;
            --secondary: #26c6da;
            --success: #66bb6a;
            --success-light: #e8f5e9;
            --warning: #ffee58;
            --danger: #ef5350;
            --danger-light: #ffebee;
            --purple: #ab47bc;
            --orange: #ffa726;
            --gray-100: #f5f5f5;
            --gray-200: #eeeeee;
            --gray-300: #e0e0e0;
        }
        
        body {
            font-family: \'Inter\', sans-serif;
            background-color: #f5f7fa;
            color: #455a64;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 12px;
            transition: transform 0.3s ease;
            background-color: white;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(38, 198, 218, 0.2);
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--purple) 100%);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #9c27b0 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .btn-outline-secondary {
            border: 2px solid var(--gray-300);
            color: #607d8b;
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--gray-100);
            border-color: var(--gray-300);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.5rem;
        }
        
        .alert-success {
            background-color: var(--success-light);
            color: #2e7d32;
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: var(--danger-light);
            color: #c62828;
            border-left: 4px solid var(--danger);
        }
        
        .page-header {
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-header h1 {
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
        }
        
        .page-header h1 i {
            margin-right: 12px;
            color: var(--secondary);
        }
        
        .page-header p {
            color: #78909c;
            font-size: 1.05rem;
        }
        
        .input-group-text {
            background-color: var(--primary-light);
            border: 2px solid var(--gray-200);
            color: var(--primary-dark);
            font-weight: 500;
        }
        
        .topbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            background-color: white;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 8px;
            color: var(--secondary);
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .time-input-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .time-separator {
            color: var(--secondary);
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
            }
        }
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Header -->
    <div class="page-header">
        <h1 class="h3"><i class="fas fa-clock"></i> Time In &amp; Out</h1>
        <p class="mb-0">Record employee attendance time logs</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header">
            <i class="fas fa-user-clock mr-2"></i> New Time Log Entry
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-id-badge"></i> Employee ID</label>
                        <input type="number" class="form-control" name="employeeId" placeholder="Enter Employee ID" required value="<?= isset($_POST['employeeId']) ? htmlspecialchars($_POST['employeeId']) : '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-calendar-alt"></i> Record Date</label>
                        <input type="date" class="form-control" name="recordDate" required value="<?= isset($_POST['recordDate']) ? htmlspecialchars($_POST['recordDate']) : date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-sign-in-alt"></i> Time In</label>
                        <input type="time" class="form-control" name="timeIn" required value="<?= isset($_POST['timeIn']) ? htmlspecialchars($_POST['timeIn']) : '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-sign-out-alt"></i> Time Out</label>
                        <input type="time" class="form-control" name="timeOut" required value="<?= isset($_POST['timeOut']) ? htmlspecialchars($_POST['timeOut']) : '' ?>">
                    </div>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Time Log</button>
                    <button type="reset" class="btn btn-outline-secondary"><i class="fas fa-undo mr-1"></i> Reset</button>
                </div>
            </form>
        </div>
    </div>

</div>
<!-- End Page Content -->

<?php include BASE_PATH . '/templates/footer.php'; ?>
