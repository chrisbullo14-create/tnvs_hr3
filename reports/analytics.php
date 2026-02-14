<?php
require_once __DIR__ . '/../config/app.php';

// Redirect if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$analytics_data = [];

try {
    // Get all analytics data in single execution
    $conn->begin_transaction();

    // Total reports
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM reports WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $analytics_data['total'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Report statistics
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) AS total,
            report_name AS top_report,
            COUNT(*) AS top_count,
            DATE(generated_at) AS report_date
        FROM reports 
        WHERE user_id = ? 
        GROUP BY report_name, report_date
        ORDER BY top_count DESC, report_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $daily_reports = [];
    $report_counts = [];
    $top_report = ['name' => 'N/A', 'count' => 0];
    
    while ($row = $result->fetch_assoc()) {
        // Process daily reports
        $date = $row['report_date'];
        if (!isset($daily_reports[$date])) {
            $daily_reports[$date] = 0;
        }
        $daily_reports[$date] += $row['total'];
        
        // Track top report
        if ($row['top_count'] > $top_report['count']) {
            $top_report = [
                'name' => $row['top_report'],
                'count' => $row['top_count']
            ];
        }
    }
    $stmt->close();
    
    // Format daily reports for last 7 days
    $end_date = new DateTime();
    $start_date = (clone $end_date)->modify('-6 days');
    $date_range = new DatePeriod($start_date, new DateInterval('P1D'), $end_date);
    
    $formatted_daily = [];
    foreach ($date_range as $date) {
        $date_str = $date->format('Y-m-d');
        $formatted_daily[] = [
            'date' => $date->format('M j'),
            'count' => $daily_reports[$date_str] ?? 0
        ];
    }

    $conn->commit();

} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    error_log("Analytics Error: " . $e->getMessage());
    die("<div class='alert alert-danger'>Error loading analytics. Please try again later.</div>");
}

$page_title = 'Analytics';
$current_page = 'dashboard';
$extra_css = '<style>
        .analytics-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .analytics-card:hover {
            transform: translateY(-5px);
        }
        .metric-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
';
$extra_js = '<script src="<?= BASE_URL ?>/vendor/chart.js/Chart.min.js"></script>
<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    // Activity Chart
    const ctx = document.getElementById(\'activityChart\').getContext(\'2d\');
    new Chart(ctx, {
        type: \'line\',
        data: {
            labels: <?= json_encode(array_column($formatted_daily, \'date\')) ?>,
            datasets: [{
                label: \'Reports Generated\',
                data: <?= json_encode(array_column($formatted_daily, \'count\')) ?>,
                borderColor: \'#0d6efd\',
                backgroundColor: \'rgba(13, 110, 253, 0.05)\',
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: \'#f8f9fa\' },
                    ticks: { stepSize: 1 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>



<?php include BASE_PATH . '/templates/footer.php'; ?>
