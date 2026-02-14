<?php
/**
 * Shared Sidebar Partial
 * Included by all pages that need the sidebar navigation.
 * Uses $current_page variable to highlight the active nav item.
 */
$current_page = $current_page ?? '';
?>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= BASE_URL ?>/dashboard/admin_dashboard.php">
        <div class="sidebar-brand-icon">
            <img src="<?= BASE_URL ?>/transparent%20logo.png" alt="TNVS" style="width: 120px; height: auto;">
        </div>
        <div class="sidebar-brand-text mx-3"></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?= ($current_page === 'dashboard') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/dashboard/admin_dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">Interface</div>

    <!-- Time & Attendance -->
    <li class="nav-item <?= (in_array($current_page, ['attendance_time_log', 'attendance_record', 'attendance_report'])) ? 'active' : '' ?>">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAttendance"
            aria-expanded="<?= (in_array($current_page, ['attendance_time_log', 'attendance_record', 'attendance_report'])) ? 'true' : 'false' ?>"
            aria-controls="collapseAttendance">
            <i class="fas fa-fw fa-cog"></i>
            <span>Time & Attendance</span>
        </a>
        <div id="collapseAttendance" class="collapse <?= (in_array($current_page, ['attendance_time_log', 'attendance_record', 'attendance_report'])) ? 'show' : '' ?>"
            aria-labelledby="headingAttendance" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Time & Attendance:</h6>
                <a class="collapse-item <?= ($current_page === 'attendance_time_log') ? 'active' : '' ?>" href="<?= BASE_URL ?>/attendance/attendance_time_log.php">Time in & out</a>
                <a class="collapse-item <?= ($current_page === 'attendance_record') ? 'active' : '' ?>" href="<?= BASE_URL ?>/attendance/attendance_record.php">Attendance Record</a>
                <a class="collapse-item <?= ($current_page === 'attendance_report') ? 'active' : '' ?>" href="<?= BASE_URL ?>/attendance/attendance_report.php">Attendance Reports</a>
            </div>
        </div>
    </li>

    <!-- Shift & Scheduling -->
    <li class="nav-item <?= (in_array($current_page, ['shift_creation', 'sheet_management', 'schedule_management'])) ? 'active' : '' ?>">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseScheduling"
            aria-expanded="<?= (in_array($current_page, ['shift_creation', 'sheet_management', 'schedule_management'])) ? 'true' : 'false' ?>"
            aria-controls="collapseScheduling">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Shift & Scheduling</span>
        </a>
        <div id="collapseScheduling" class="collapse <?= (in_array($current_page, ['shift_creation', 'sheet_management', 'schedule_management'])) ? 'show' : '' ?>"
            aria-labelledby="headingScheduling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Shift & Scheduling:</h6>
                <a class="collapse-item <?= ($current_page === 'shift_creation') ? 'active' : '' ?>" href="<?= BASE_URL ?>/schedule/shift_creation.php">Shift Creation</a>
                <a class="collapse-item <?= ($current_page === 'sheet_management') ? 'active' : '' ?>" href="<?= BASE_URL ?>/timesheet/sheet_management.php">Sheet Management</a>
                <a class="collapse-item <?= ($current_page === 'schedule_management') ? 'active' : '' ?>" href="<?= BASE_URL ?>/schedule/schedule_management.php">Schedule Management</a>
            </div>
        </div>
    </li>

    <!-- Time Sheet -->
    <li class="nav-item <?= (in_array($current_page, ['time_log', 'timesheet', 'compliance_audits'])) ? 'active' : '' ?>">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTimeSheet"
            aria-expanded="<?= (in_array($current_page, ['time_log', 'timesheet', 'compliance_audits'])) ? 'true' : 'false' ?>"
            aria-controls="collapseTimeSheet">
            <i class="fas fa-fw fa-folder"></i>
            <span>Time Sheet</span>
        </a>
        <div id="collapseTimeSheet" class="collapse <?= (in_array($current_page, ['time_log', 'timesheet', 'compliance_audits'])) ? 'show' : '' ?>"
            aria-labelledby="headingTimeSheet" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Time Sheet:</h6>
                <a class="collapse-item <?= ($current_page === 'time_log') ? 'active' : '' ?>" href="<?= BASE_URL ?>/attendance/time_log.php">Time Logging</a>
                <a class="collapse-item <?= ($current_page === 'timesheet') ? 'active' : '' ?>" href="<?= BASE_URL ?>/timesheet/timesheet.php">Timesheet</a>
                <a class="collapse-item <?= ($current_page === 'compliance_audits') ? 'active' : '' ?>" href="<?= BASE_URL ?>/compliance/compliance_audits.php">Compliance and Auditing</a>
            </div>
        </div>
    </li>

    <!-- Leave Management -->
    <li class="nav-item <?= (in_array($current_page, ['leave_requests', 'leave_approval', 'hr_validation'])) ? 'active' : '' ?>">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLeave"
            aria-expanded="<?= (in_array($current_page, ['leave_requests', 'leave_approval', 'hr_validation'])) ? 'true' : 'false' ?>"
            aria-controls="collapseLeave">
            <i class="fas fa-fw fa-calendar-minus"></i>
            <span>Leave Management</span>
        </a>
        <div id="collapseLeave" class="collapse <?= (in_array($current_page, ['leave_requests', 'leave_approval', 'hr_validation'])) ? 'show' : '' ?>"
            aria-labelledby="headingLeave" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Leave Management:</h6>
                <a class="collapse-item <?= ($current_page === 'leave_requests') ? 'active' : '' ?>" href="<?= BASE_URL ?>/leave/leave_requests.php">Leave Requests</a>
                <a class="collapse-item <?= ($current_page === 'leave_approval') ? 'active' : '' ?>" href="<?= BASE_URL ?>/leave/leave_approval.php">Leave Approval</a>
            
            </div>
        </div>
    </li>

    <!-- Claim & Reimbursement -->
    <li class="nav-item <?= (in_array($current_page, ['expenses_insured', 'claim_submission', 'approval_process', 'record_keeping'])) ? 'active' : '' ?>">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseClaims"
            aria-expanded="<?= (in_array($current_page, ['expenses_insured', 'claim_submission', 'approval_process', 'record_keeping'])) ? 'true' : 'false' ?>"
            aria-controls="collapseClaims">
            <i class="fas fa-fw fa-file-invoice-dollar"></i>
            <span>Claim & Reimbursement</span>
        </a>
        <div id="collapseClaims" class="collapse <?= (in_array($current_page, ['expenses_insured', 'claim_submission', 'approval_process', 'record_keeping'])) ? 'show' : '' ?>"
            aria-labelledby="headingClaims" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Claim & Reimbursement:</h6>
                <a class="collapse-item <?= ($current_page === 'expenses_insured') ? 'active' : '' ?>" href="<?= BASE_URL ?>/expenses/expenses_insured.php">Expenses Insured</a>
                <a class="collapse-item <?= ($current_page === 'claim_submission') ? 'active' : '' ?>" href="<?= BASE_URL ?>/claims/claim_submission.php">Claim Submission</a>
                <a class="collapse-item <?= ($current_page === 'approval_process') ? 'active' : '' ?>" href="<?= BASE_URL ?>/compliance/approval_process.php">Approval And Process</a>
                
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">Addons</div>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
