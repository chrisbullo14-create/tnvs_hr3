<?php
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit();
}

/* ── Module 1: Time & Attendance ── */
$att_total   = $conn->query("SELECT COUNT(*) AS c FROM attendance_time_log")->fetch_assoc()['c'] ?? 0;
$att_present = $conn->query("SELECT COUNT(*) AS c FROM attendance_reports WHERE status='Present'")->fetch_assoc()['c'] ?? 0;
$att_late    = $conn->query("SELECT COUNT(*) AS c FROM attendance_reports WHERE status='Late'")->fetch_assoc()['c'] ?? 0;
$att_absent  = $conn->query("SELECT COUNT(*) AS c FROM attendance_reports WHERE status='Absent'")->fetch_assoc()['c'] ?? 0;

/* ── Module 2: Shift & Scheduling ── */
$sch_total  = $conn->query("SELECT COUNT(*) AS c FROM schedule_management")->fetch_assoc()['c'] ?? 0;
$sch_active = $conn->query("SELECT COUNT(*) AS c FROM schedule_management WHERE status='Active'")->fetch_assoc()['c'] ?? 0;

/* ── Module 3: Time Sheet ── */
$ts_total = $conn->query("SELECT COUNT(*) AS c FROM timesheet_logs")->fetch_assoc()['c'] ?? 0;
$ts_hours = $conn->query("SELECT COALESCE(SUM(total_hours),0) AS h FROM timesheet_logs")->fetch_assoc()['h'] ?? 0;
$ts_ot    = $conn->query("SELECT COALESCE(SUM(overtime_hours),0) AS h FROM timesheet_logs")->fetch_assoc()['h'] ?? 0;

/* ── Module 4: Leave Management ── */
$lv_total    = $conn->query("SELECT COUNT(*) AS c FROM leave_requests")->fetch_assoc()['c'] ?? 0;
$lv_pending  = $conn->query("SELECT COUNT(*) AS c FROM leave_requests WHERE leave_status='Pending'")->fetch_assoc()['c'] ?? 0;
$lv_approved = $conn->query("SELECT COUNT(*) AS c FROM leave_requests WHERE leave_status='Approved'")->fetch_assoc()['c'] ?? 0;
$lv_rejected = $conn->query("SELECT COUNT(*) AS c FROM leave_requests WHERE leave_status='Rejected'")->fetch_assoc()['c'] ?? 0;

/* ── Module 5: Claim & Reimbursement ── */
$cl_total    = $conn->query("SELECT COUNT(*) AS c FROM claim_submissions")->fetch_assoc()['c'] ?? 0;
$cl_pending  = $conn->query("SELECT COUNT(*) AS c FROM claim_submissions WHERE status='Pending'")->fetch_assoc()['c'] ?? 0;
$cl_approved = $conn->query("SELECT COUNT(*) AS c FROM claim_submissions WHERE status='Approved'")->fetch_assoc()['c'] ?? 0;
$cl_amount   = $conn->query("SELECT COALESCE(SUM(amount),0) AS a FROM claim_submissions")->fetch_assoc()['a'] ?? 0;

/* ── Chart data: leave by type ── */
$lv_types_q = $conn->query("SELECT leave_type, COUNT(*) AS c FROM leave_requests GROUP BY leave_type ORDER BY c DESC");
$lv_labels = []; $lv_data = [];
if ($lv_types_q) { while ($r = $lv_types_q->fetch_assoc()) { $lv_labels[] = $r['leave_type']; $lv_data[] = (int)$r['c']; } }

/* ── Chart data: claims by type ── */
$cl_types_q = $conn->query("SELECT claim_type, COUNT(*) AS c FROM claim_submissions GROUP BY claim_type ORDER BY c DESC");
$cl_labels = []; $cl_data = [];
if ($cl_types_q) { while ($r = $cl_types_q->fetch_assoc()) { $cl_labels[] = $r['claim_type']; $cl_data[] = (int)$r['c']; } }

/* ── Recent activity table (latest 10 across modules) ── */
$recent_sql = "
    (SELECT 'Attendance' AS module, CONCAT(u.full_name) AS detail, a.record_date AS event_date, 'Time Log' AS type
     FROM attendance_time_log a LEFT JOIN user_accounts u ON a.employee_id=u.id ORDER BY a.record_date DESC LIMIT 5)
    UNION ALL
    (SELECT 'Leave' AS module, CONCAT(u.full_name,' - ',l.leave_type) AS detail, l.request_date AS event_date, l.leave_status AS type
     FROM leave_requests l LEFT JOIN user_accounts u ON l.employee_id=u.id ORDER BY l.request_date DESC LIMIT 5)
    UNION ALL
    (SELECT 'Claim' AS module, CONCAT(c.full_name,' - ',c.claim_type) AS detail, c.created_at AS event_date, c.status AS type
     FROM claim_submissions c ORDER BY c.created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'Timesheet' AS module, CONCAT(u.full_name) AS detail, t.log_date AS event_date, t.status AS type
     FROM timesheet_logs t LEFT JOIN user_accounts u ON t.employee_id=u.id ORDER BY t.log_date DESC LIMIT 5)
    UNION ALL
    (SELECT 'Schedule' AS module, CONCAT(u.full_name,' - ',s.destination) AS detail, s.date AS event_date, s.status AS type
     FROM schedule_management s LEFT JOIN user_accounts u ON s.employee_id=u.id ORDER BY s.created_at DESC LIMIT 5)
    ORDER BY event_date DESC LIMIT 10
";
$recent_res = $conn->query($recent_sql);
$recent_rows = [];
if ($recent_res) { while ($r = $recent_res->fetch_assoc()) $recent_rows[] = $r; }

$page_title = 'TransGo - Dashboard';
$current_page = 'dashboard';
$extra_css = '<style>
    .db-card{border:none;border-radius:.75rem;overflow:hidden;transition:transform .2s,box-shadow .2s;}
    .db-card:hover{transform:translateY(-4px);box-shadow:0 8px 25px rgba(0,0,0,.12)!important;}
    .db-card .db-icon{width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;}
    .db-card .db-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#a0aec0;}
    .db-card .db-value{font-size:1.6rem;font-weight:800;color:#2d3748;}
    .db-card .db-sub{font-size:.75rem;color:#718096;}
    .db-badge{padding:4px 10px;border-radius:20px;font-size:.7rem;font-weight:600;}
    .db-badge-pending{background:rgba(246,194,62,.15);color:#d69e2e;}
    .db-badge-approved,.db-badge-present,.db-badge-active,.db-badge-time.log{background:rgba(72,187,120,.15);color:#38a169;}
    .db-badge-rejected,.db-badge-absent{background:rgba(245,101,101,.15);color:#e53e3e;}
    .db-badge-attendance,.db-badge-late{background:rgba(66,153,225,.15);color:#3182ce;}
    .db-badge-leave{background:rgba(159,122,234,.15);color:#805ad5;}
    .db-badge-claim{background:rgba(237,137,54,.15);color:#dd6b20;}
    .db-badge-timesheet{background:rgba(56,178,172,.15);color:#319795;}
    .db-badge-schedule{background:rgba(99,179,237,.15);color:#3182ce;}
    .db-table th{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#a0aec0;border-top:none;}
    .db-table td{font-size:.85rem;vertical-align:middle;}
</style>';
$extra_js = '';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Dashboard</h1>
            <p class="mb-0 text-muted small">Welcome back — here's what's happening across all modules.</p>
        </div>
        <span class="d-none d-sm-inline text-muted small"><i class="fas fa-calendar-alt mr-1"></i><?= date('l, F j, Y') ?></span>
    </div>

    <!-- ===== 5 MODULE SUMMARY CARDS ===== -->
    <div class="row mb-4">

        <!-- Card 1: Time & Attendance -->
        <div class="col-xl col-md-6 mb-3">
            <div class="card db-card shadow h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="db-icon mr-3" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <div class="db-label">Attendance</div>
                        <div class="db-value"><?= $att_total ?></div>
                        <div class="db-sub"><span class="text-success"><?= $att_present ?> present</span> &middot; <span class="text-warning"><?= $att_late ?> late</span> &middot; <span class="text-danger"><?= $att_absent ?> absent</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Shift & Scheduling -->
        <div class="col-xl col-md-6 mb-3">
            <div class="card db-card shadow h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="db-icon mr-3" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="db-label">Schedules</div>
                        <div class="db-value"><?= $sch_total ?></div>
                        <div class="db-sub"><span class="text-success"><?= $sch_active ?> active</span> &middot; <?= $sch_total - $sch_active ?> inactive</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Time Sheet -->
        <div class="col-xl col-md-6 mb-3">
            <div class="card db-card shadow h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="db-icon mr-3" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <div class="db-label">Timesheets</div>
                        <div class="db-value"><?= $ts_total ?></div>
                        <div class="db-sub"><?= number_format($ts_hours,1) ?> hrs logged &middot; <?= number_format($ts_ot,1) ?> OT</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Leave Management -->
        <div class="col-xl col-md-6 mb-3">
            <div class="card db-card shadow h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="db-icon mr-3" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                        <i class="fas fa-calendar-minus"></i>
                    </div>
                    <div>
                        <div class="db-label">Leave Requests</div>
                        <div class="db-value"><?= $lv_total ?></div>
                        <div class="db-sub"><span class="text-warning"><?= $lv_pending ?> pending</span> &middot; <span class="text-success"><?= $lv_approved ?> approved</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 5: Claims & Reimbursement -->
        <div class="col-xl col-md-6 mb-3">
            <div class="card db-card shadow h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="db-icon mr-3" style="background:linear-gradient(135deg,#fa709a,#fee140);">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <div class="db-label">Claims</div>
                        <div class="db-value"><?= $cl_total ?></div>
                        <div class="db-sub">&#8369;<?= number_format($cl_amount,0) ?> total &middot; <span class="text-warning"><?= $cl_pending ?> pending</span></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ===== 2 CHARTS SIDE-BY-SIDE ===== -->
    <div class="row mb-4">
        <!-- Chart 1: Leave by Type (Doughnut) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow" style="border:none;border-radius:.75rem;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-radius:.75rem .75rem 0 0;">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie mr-2"></i>Leave Requests by Type</h6>
                    <span class="badge badge-primary badge-pill"><?= $lv_total ?> total</span>
                </div>
                <div class="card-body">
                    <div style="height:300px;position:relative;">
                        <canvas id="leaveChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 2: Claims by Type (Bar) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow" style="border:none;border-radius:.75rem;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-radius:.75rem .75rem 0 0;">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar mr-2"></i>Claims by Type</h6>
                    <span class="badge badge-primary badge-pill"><?= $cl_total ?> total</span>
                </div>
                <div class="card-body">
                    <div style="height:300px;position:relative;">
                        <canvas id="claimChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== RECENT ACTIVITY TABLE ===== -->
    <div class="card shadow mb-4" style="border:none;border-radius:.75rem;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-radius:.75rem .75rem 0 0;">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-stream mr-2"></i>Recent Activity</h6>
            <span class="text-muted small">Latest records across all modules</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover db-table mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Module</th>
                            <th>Detail</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_rows) > 0): ?>
                            <?php foreach ($recent_rows as $row): ?>
                            <tr>
                                <td class="pl-4">
                                    <?php
                                    $mod = $row['module'];
                                    $mod_lower = strtolower($mod);
                                    $icons = ['attendance'=>'fa-user-clock','leave'=>'fa-calendar-minus','claim'=>'fa-file-invoice-dollar','timesheet'=>'fa-file-alt','schedule'=>'fa-calendar-check'];
                                    $icon = $icons[$mod_lower] ?? 'fa-circle';
                                    ?>
                                    <span class="db-badge db-badge-<?= $mod_lower ?>">
                                        <i class="fas <?= $icon ?> mr-1"></i><?= $mod ?>
                                    </span>
                                </td>
                                <td class="font-weight-medium"><?= htmlspecialchars($row['detail'] ?? '—') ?></td>
                                <td class="text-muted"><?= $row['event_date'] ? date('M d, Y', strtotime($row['event_date'])) : '—' ?></td>
                                <td>
                                    <?php
                                    $st = $row['type'] ?? '';
                                    $st_lower = strtolower($st);
                                    $st_class = 'db-badge-pending';
                                    if (in_array($st_lower, ['approved','present','active','time log'])) $st_class = 'db-badge-approved';
                                    elseif (in_array($st_lower, ['rejected','absent'])) $st_class = 'db-badge-rejected';
                                    elseif (in_array($st_lower, ['late'])) $st_class = 'db-badge-attendance';
                                    ?>
                                    <span class="db-badge <?= $st_class ?>"><?= htmlspecialchars($st ?: 'N/A') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No recent activity found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- /.container-fluid -->

<!-- Chart.js loaded before init -->
<script src="<?= BASE_URL ?>/vendor/chart.js/Chart.min.js"></script>
<script>
Chart.defaults.global.defaultFontFamily='Nunito,-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto';
Chart.defaults.global.defaultFontColor='#858796';

// Leave Doughnut
new Chart(document.getElementById('leaveChart'),{
    type:'doughnut',
    data:{
        labels:<?= json_encode($lv_labels) ?>,
        datasets:[{
            data:<?= json_encode($lv_data) ?>,
            backgroundColor:['#667eea','#48bb78','#ed8936','#fc8181','#4fd1c5','#b794f4','#f6ad55'],
            borderWidth:2, borderColor:'#fff',
            hoverBorderColor:'#fff'
        }]
    },
    options:{
        maintainAspectRatio:false,
        cutoutPercentage:65,
        legend:{position:'right',labels:{padding:15,usePointStyle:true,fontSize:12}},
        tooltips:{callbacks:{label:function(t,d){var v=d.datasets[0].data[t.index];var total=d.datasets[0].data.reduce(function(a,b){return a+b},0);return d.labels[t.index]+': '+v+' ('+(100*v/total).toFixed(1)+'%)';}}}
    }
});

// Claims Bar
new Chart(document.getElementById('claimChart'),{
    type:'bar',
    data:{
        labels:<?= json_encode($cl_labels) ?>,
        datasets:[{
            label:'Claims',
            data:<?= json_encode($cl_data) ?>,
            backgroundColor:['#667eea','#48bb78','#ed8936','#fc8181','#4fd1c5','#b794f4','#f6ad55'],
            borderRadius:6,
            barPercentage:.6
        }]
    },
    options:{
        maintainAspectRatio:false,
        legend:{display:false},
        scales:{
            xAxes:[{gridLines:{display:false},ticks:{fontSize:11}}],
            yAxes:[{ticks:{beginAtZero:true,stepSize:1,fontSize:11},gridLines:{color:'rgba(0,0,0,.05)',zeroLineColor:'rgba(0,0,0,.1)'}}]
        },
        tooltips:{callbacks:{label:function(t){return t.value+' claim(s)';}}}
    }
});
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
