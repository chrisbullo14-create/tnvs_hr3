<?php
require_once __DIR__ . '/../config/app.php';
// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$hrId = 1; // Replace with session user ID if you have login
$error = "";

// Handle validation action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['leave_id'], $_POST['action'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF validation failed.");
    }

    $leaveId = intval($_POST['leave_id']);
    $action = ($_POST['action'] === 'approve') ? 'Approved' : 'Rejected';

    // Update leave_requests status
    $stmt = $conn->prepare("UPDATE leave_requests SET leave_status = ? WHERE leave_id = ?");
    $stmt->bind_param("si", $action, $leaveId);
    $stmt->execute();
    $stmt->close();

    // Update hr_validation record
    $stmt = $conn->prepare("UPDATE hr_validation SET validation_status = ?, validation_date = NOW(), hr_id = ? WHERE leave_id = ?");
    $stmt->bind_param("sii", $action, $hrId, $leaveId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash'] = "Leave request has been $action.";
    header("Location: " . BASE_URL . "/hr_validation/hr_validation.php"); // Refresh the page after approval/rejection
    exit;
}

// Fetch leave requests for validation
$requests = [];
$stmt = $conn->prepare("
    SELECT lr.leave_id, lr.leave_type, lr.start_date, lr.end_date, lr.reason,
           te.first_name, te.last_name, hv.validation_status
    FROM leave_requests lr
    JOIN test_employees te ON lr.employee_id = te.employee_id
    JOIN hr_validation hv ON lr.leave_id = hv.leave_id
    WHERE hv.validation_status = 'Pending'
    ORDER BY lr.request_date DESC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
$stmt->close();
?>

<?php
$page_title = 'HR Validation';
$current_page = 'hr_validation';
$extra_css = '<style>
        :root {
            --primary: #4e73df;
            --secondary: #858796;
            --success: #1cc88a;
            --danger: #e74a3b;
            --warning: #f6c23e;
            --info: #36b9cc;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            background-color: #f5f7fa;
        }
        
        /* Sidebar Styling */
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, #224abe 100%);
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            padding: 1.5rem 1rem;
            background: rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }
        
        .sidebar-brand-icon {
            color: white;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }
        
        .sidebar .nav-item.active .nav-link {
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            margin: 0 10px;
        }
        
        /* Card Styling */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            background: linear-gradient(90deg, var(--primary) 0%, #224abe 100%);
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        /* Table Styling */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            background: linear-gradient(90deg, var(--primary) 0%, #224abe 100%);
            color: white;
            border: none;
            position: sticky;
            top: 0;
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
            transform: scale(1.005);
        }
        
        /* Button Styling */
        .btn-success {
            background: linear-gradient(90deg, var(--success) 0%, #17a673 100%);
            border: none;
            box-shadow: 0 2px 5px rgba(28, 200, 138, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(90deg, var(--danger) 0%, #c0272b 100%);
            border: none;
            box-shadow: 0 2px 5px rgba(231, 74, 59, 0.3);
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 5px;
        }
        
        /* Status Badges */
        .badge-pill {
            padding: 0.5em 0.75em;
            font-weight: 600;
        }
        
        /* Topbar Styling */
        .topbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        
        /* Page Heading */
        h2 {
            color: var(--primary);
            font-weight: 700;
            text-shadow: 0 2px 3px rgba(0,0,0,0.1);
            position: relative;
            padding-bottom: 10px;
        }
        
        h2::after {
            content: \'\';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--info) 100%);
            border-radius: 3px;
        }
        
        /* Empty State */
        .empty-state {
            padding: 2rem;
            text-align: center;
            color: var(--secondary);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--info);
            margin-bottom: 1rem;
        }
        
        /* Form Styling */
        form {
            display: inline-block;
            margin-right: 5px;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar .nav-item .nav-link span {
                display: none;
            }
            
            .sidebar-brand-text {
                display: none;
            }
        }
    </style>
<link href="<?= BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
';
$extra_js = '<script src="<?= BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<?php include BASE_PATH . '/templates/footer.php'; ?>
