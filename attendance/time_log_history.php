<?php
require_once __DIR__ . '/../config/app.php';

require_once __DIR__ . '/../config/app.php';
$logs = $conn->query("SELECT * FROM attendance_time_log ORDER BY record_date DESC");

$page_title = 'Time Log History';
$current_page = 'time_log';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<!-- Topbar -->
        

        <!-- Page Content -->
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Time Log History</h1>
            </div>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($log = $logs->fetch_assoc()): 
                                    $duration = strtotime($log['time_out']) - strtotime($log['time_in']);
                                    $hours = floor($duration/3600);
                                    $minutes = floor(($duration%3600)/60);
                                ?>
                                <tr>
                                    <td><?= $log['employee_id'] ?></td>
                                    <td><?= date('m/d/Y', strtotime($log['record_date'])) ?></td>
                                    <td><?= date('h:i A', strtotime($log['time_in'])) ?></td>
                                    <td><?= date('h:i A', strtotime($log['time_out'])) ?></td>
                                    <td><?= $hours ?>h <?= $minutes ?>m</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

<?php include BASE_PATH . '/templates/footer.php'; ?>
