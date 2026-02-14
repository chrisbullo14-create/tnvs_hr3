<?php
require_once __DIR__ . '/../config/app.php';
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_shift'])) {
    if (!hash_equals($_SESSION['token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    // Loop through each work day entry and insert
    if (isset($_POST['work_days'], $_POST['start_times'], $_POST['end_times'])) {
        $employee_id = $_POST['employee_id'];
        $status = $_POST['status'];
        $remarks = $_POST['remarks'];

        $all_success = true;
        foreach ($_POST['work_days'] as $i => $work_day) {
            $start_time = $_POST['start_times'][$i];
            $end_time = $_POST['end_times'][$i];

            $stmt = $conn->prepare("INSERT INTO schedule_management (employee_id, work_day, start_time, end_time, status, remarks) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param(
                    "isssss",
                    $employee_id,
                    $work_day,
                    $start_time,
                    $end_time,
                    $status,
                    $remarks
                );
                if (!$stmt->execute()) {
                    $all_success = false;
                    break;
                }
            } else {
                $all_success = false;
                break;
            }
        }

        if ($all_success) {
            header("Location: " . BASE_URL . "/schedule/schedule_management.php?success=" . urlencode("Shift successfully added!"));
            exit;
        } else {
            $error_message = "Error adding shift. Please try again.";
        }
    } else {
        $error_message = "Incomplete form data.";
    }
}
?>

<?php
$page_title = 'Shift Creation';
$current_page = 'shift_creation';
$extra_css = '<style>

        /*
        
        :root {
            --primary: #4361ee;
            --primary-light: #e0e7ff;
            --secondary: #3a0ca3;
            --accent: #4895ef;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --gray-100: #f8f9fc;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
        }
        
        body {
            font-family: \'Poppins\', sans-serif;
            background-color: #f8fafc;
            color: #2d3748;
        }
        
        .shift-form-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(67, 97, 238, 0.1);
            padding: 2.5rem;
            border: 1px solid var(--gray-200);
            background-image: radial-gradient(circle at 100% 0%, var(--primary-light) 0%, transparent 20%);
            transition: all 0.3s ease;
            overflow: visible; 
            position: relative;
        }
        
        .shift-form-container:hover {
            box-shadow: 0 12px 40px rgba(67, 97, 238, 0.15);
            transform: translateY(-3px);
        }
        
        .form-section-title {
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 2rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary-light);
            display: flex;
            align-items: center;
            position: relative;
            font-size: 1.25rem;
        }
        
        .form-section-title i {
            margin-right: 1rem;
            font-size: 1.5rem;
            color: var(--accent);
            background: var(--primary-light);
            padding: 12px;
            border-radius: 12px;
        }
        
     
        .dropdown-container {
            position: relative;
            z-index: 1000;
        }
        
        .form-control, .custom-select {
            border-radius: 10px;
            padding: 0.875rem 1.25rem;
            border: 2px solid var(--gray-200);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
         Enhanced select dropdown styling 
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' fill=\'%234361ee\' viewBox=\'0 0 16 16\'%3E%3Cpath d=\'M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z\'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1.25rem center;
            background-size: 14px;
            overflow: visible;
            z-index: 1001;
        }
        
         Ensure dropdown options are fully visible 
        select.form-control option {
            position: static;
            background: white;
            z-index: 1002;
        }
        
        .form-control:focus, .custom-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(72, 149, 239, 0.15);
        }
        
        .btn {
            border-radius: 10px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            font-size: 0.95rem;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
        }
        
        .btn-light {
            background-color: white;
            border: 2px solid var(--gray-300);
            color: #4a5568;
        }
        
        .btn-light:hover {
            background-color: var(--gray-100);
            color: var(--secondary);
            border-color: var(--accent);
        }
        
        .time-input-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .time-separator {
            color: var(--accent);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .topbar {
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid var(--gray-200);
        }
        
        .page-header {
            display: flex;
            align-items: center;
            padding: 1rem 0;
        }
        
        .page-header i {
            font-size: 1.75rem;
            color: var(--accent);
            margin-right: 1.25rem;
            background: var(--primary-light);
            padding: 12px;
            border-radius: 12px;
        }
        
        .page-header h4 {
            font-weight: 700;
            margin-bottom: 0;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.75rem;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--secondary);
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 8px;
            color: var(--accent);
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--gray-200);
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-group:after {
            content: \'\';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary-light);
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .form-group:hover:after {
            width: 80px;
            background: var(--accent);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        @media (max-width: 768px) {
            .shift-form-container {
                padding: 1.75rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
            }
            
            .page-header h4 {
                font-size: 1.5rem;
            }
        }
        
         Decorative elements 
        .form-decoration {
            position: absolute;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--primary-light) 0%, transparent 70%);
            z-index: 0;
        }
        
        .decoration-1 {
            top: -30px;
            right: -30px;
        }
        
        .decoration-2 {
            bottom: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            opacity: 0.3;
        }
            */
    </style>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<!-- Page Content -->
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus-circle mr-2"></i>Create Shift</h1>
        <a href="<?= BASE_URL ?>/schedule/schedule_management.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Schedules
        </a>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error_message) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

<div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="shift-form-container">
                <div class="form-decoration decoration-1"></div>
                <div class="form-decoration decoration-2"></div>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['token'] ?>">

                    <h5 class="form-section-title">
                        <i class="fas fa-user-clock"></i>Shift Details
                    </h5>

                    <div class="form-row">
                        <div class="form-group col-md-6 dropdown-container">
                            <label for="employee_id" class="form-label">
                                <i class="fas fa-user-tie"></i>Employee
                            </label>
                            <select name="employee_id" id="employee_id" class="form-control" required style="overflow: visible;">
                                <option value="">Select Employee</option>
                                <?php
                                $emp = $conn->query("
                                    SELECT id, full_name
                                    FROM user_accounts
                                    ORDER BY full_name
                                ");
                                if ($emp) {
                                    while ($e = $emp->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($e['id']) . "'>" . htmlspecialchars($e['full_name']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3 dropdown-container">
                            <label for="status" class="form-label">
                                <i class="fas fa-info-circle"></i>Status
                            </label>
                            <select name="status" id="status" class="form-control" required style="overflow: visible;">
                                <option value="Scheduled" selected>Scheduled</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-calendar-week"></i>Work Days & Shift Time</label>
                        <div class="table-responsive">
                            <table class="table table-bordered workdays-table w-100" id="workdaysTable" style="width:100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 40%;">Work Day</th>
                                        <th style="width: 30%;">Start Time</th>
                                        <th style="width: 30%;">End Time</th>
                                        <th style="width: 60px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="work_days[]" class="form-control" required>
                                                <option value="">Select Day</option>
                                                <option value="Monday">Monday</option>
                                                <option value="Tuesday">Tuesday</option>
                                                <option value="Wednesday">Wednesday</option>
                                                <option value="Thursday">Thursday</option>
                                                <option value="Friday">Friday</option>
                                                <option value="Saturday">Saturday</option>
                                                <option value="Sunday">Sunday</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="time" name="start_times[]" class="form-control" required>
                                        </td>
                                        <td>
                                            <input type="time" name="end_times[]" class="form-control" required>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="add-row-btn" title="Add Row"><i class="fas fa-plus-circle"></i></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="remarks" class="form-label">
                            <i class="fas fa-comment-alt"></i>Remarks
                        </label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3" 
                                  placeholder="Additional notes..."></textarea>
                    </div>

                    <div class="action-buttons">
                        <button type="reset" class="btn btn-light">
                            <i class="fas fa-undo mr-2"></i>Reset Form
                        </button>
                        <button type="submit" name="add_shift" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Save Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<script>
$(document).ready(function() {
    // Add row
    $(document).on('click', '.add-row-btn', function() {
        var newRow = `<tr>
            <td>
                <select name="work_days[]" class="form-control" required>
                    <option value="">Select Day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </td>
            <td><input type="time" name="start_times[]" class="form-control" required></td>
            <td><input type="time" name="end_times[]" class="form-control" required></td>
            <td class="text-center align-middle">
                <span class="remove-row-btn text-danger" title="Remove Row" style="cursor:pointer;font-size:1.2rem;">
                    <i class="fas fa-minus-circle"></i>
                </span>
            </td>
        </tr>`;
        $('#workdaysTable tbody').append(newRow);
    });

    // Remove row
    $(document).on('click', '.remove-row-btn', function() {
        $(this).closest('tr').remove();
    });
});
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
