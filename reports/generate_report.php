<?php
require_once __DIR__ . '/../config/app.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/app.php';
$start_date = date('Y-m-01');
$end_date = date('Y-m-t');
$employee_id = null;
$error = null;
$results = [];

try {
    // Validate and sanitize inputs
   // Validate and sanitize inputs
$start_date = filter_input(INPUT_GET, 'start_date', FILTER_DEFAULT) ?: date('Y-m-01');
$end_date = filter_input(INPUT_GET, 'end_date', FILTER_DEFAULT) ?: date('Y-m-t');
$employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);

// Additional validation for dates
if (!DateTime::createFromFormat('Y-m-d', $start_date) || 
    !DateTime::createFromFormat('Y-m-d', $end_date)) {
    die("Invalid date format");
}

if (strtotime($start_date) > strtotime($end_date)) {
    die("Start date cannot be after end date");
}

    // Get employee list
    $stmt = $conn->prepare("SELECT id, name FROM employees ORDER BY name");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build main query
    $sql = "SELECT a.*, e.name AS employee_name 
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            WHERE a.record_date BETWEEN :start AND :end";

    $params = [':start' => $start_date, ':end' => $end_date];

    if ($employee_id) {
        $sql .= " AND a.employee_id = :employee_id";
        $params[':employee_id'] = $employee_id;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}

$page_title = 'Report Generator';
$current_page = 'dashboard';
$extra_css = '<style>
        .report-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .chart-container {
            position: relative;
            height: 400px;
        }
        .avatar {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
        }
    </style>
';
$extra_js = '<script src="<?= BASE_URL ?>/vendor/chart.js/Chart.min.js"></script>
<script>
document.addEventListener(\'DOMContentLoaded\', function () {
    <?php if (!empty($results)): ?>
    const dates = <?= json_encode(array_column($results, \'record_date\')) ?>;
    const ctx = document.getElementById(\'attendanceChart\').getContext(\'2d\');
    
    new Chart(ctx, {
        type: \'line\',
        data: {
            labels: [...new Set(dates)].sort(),
            datasets: [{
                label: \'Daily Attendance\',
                data: dates.reduce((acc, date) => {
                    acc[date] = (acc[date] || 0) + 1;
                    return acc;
                }, {}),
                borderColor: \'#2c3e50\',
                backgroundColor: \'rgba(44, 62, 80, 0.1)\',
                tension: 0.3,
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: \'bottom\' },
                tooltip: { mode: \'index\' }
            },
            scales: {
                x: { 
                    grid: { display: false },
                    ticks: { autoSkip: true, maxRotation: 0 }
                },
                y: { 
                    beginAtZero: true,
                    title: { display: true, text: \'Number of Entries\' }
                }
            }
        }
    });
    <?php endif; ?>
});

async function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF(\'p\', \'pt\', \'a4\');
    const element = document.getElementById(\'reportSection\');
    const loading = document.createElement(\'div\');
    
    // Show loading indicator
    loading.style.position = \'fixed\';
    loading.style.top = \'0\';
    loading.style.left = \'0\';
    loading.style.width = \'100%\';
    loading.style.height = \'100%\';
    loading.style.background = \'rgba(255, 255, 255, 0.8)\';
    loading.style.display = \'flex\';
    loading.style.alignItems = \'center\';
    loading.style.justifyContent = \'center\';
    loading.innerHTML = \'<div class="spinner-border text-primary"></div>\';
    document.body.appendChild(loading);

    try {
        const canvas = await html2canvas(element, { scale: 2 });
        const imgData = canvas.toDataURL(\'image/png\');
        const imgProps = doc.getImageProperties(imgData);
        const pdfWidth = doc.internal.pageSize.getWidth() - 40;
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

        doc.setFontSize(18);
        doc.text(\'Attendance Report\', 20, 30);
        doc.addImage(imgData, \'PNG\', 20, 50, pdfWidth, pdfHeight);
        doc.save("attendance_report_<?= date(\'Ymd_His\') ?>.pdf");
    } catch (error) {
        alert(\'Error generating PDF: \' + error.message);
    } finally {
        document.body.removeChild(loading);
    }
}
</script>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<div class="container py-5">
    <?php if ($error): ?>
        <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="report-card card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-funnel me-2"></i>Report Filters
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" 
                           value="<?= htmlspecialchars($start_date) ?>" 
                           class="form-control" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" 
                           value="<?= htmlspecialchars($end_date) ?>" 
                           class="form-control" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= htmlspecialchars($emp['id']) ?>" 
                                <?= $employee_id == $emp['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-repeat me-2"></i>Generate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($results)): ?>
    <div id="reportSection">
        <div class="report-card card mb-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-bar-chart-line me-2"></i>Attendance Overview
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>

        <div class="report-card card mb-4">
            <div class="card-header bg-info text-white">
                <i class="bi bi-table me-2"></i>Detailed Records
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): 
                                $time_in = strtotime($row['time_in']);
                                $time_out = strtotime($row['time_out']);
                                $duration = $time_out - $time_in;
                            ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($row['record_date'])) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary text-white rounded-circle me-2">
                                            <?= strtoupper(substr($row['employee_name'], 0, 1)) ?>
                                        </div>
                                        <?= htmlspecialchars($row['employee_name']) ?>
                                    </div>
                                </td>
                                <td><?= date('h:i A', $time_in) ?></td>
                                <td><?= $time_out ? date('h:i A', $time_out) : 'N/A' ?></td>
                                <td>
                                    <?php if($duration > 0): ?>
                                        <?= floor($duration/3600) ?>h <?= floor(($duration%3600)/60) ?>m
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge bg-<?= 
                                        $row['status'] === 'Present' ? 'success' : 
                                        ($row['status'] === 'Late' ? 'warning' : 'danger') 
                                    ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <button onclick="exportPDF()" class="btn btn-danger w-100">
            <i class="bi bi-file-pdf me-2"></i>Export as PDF
        </button>
    </div>
    <?php elseif (!isset($error)): ?>
        <div class="alert alert-info text-center py-4">
            <i class="bi bi-info-circle me-2"></i>
            No attendance records found for the selected criteria
        </div>
    <?php endif; ?>
</div>



<?php include BASE_PATH . '/templates/footer.php'; ?>
