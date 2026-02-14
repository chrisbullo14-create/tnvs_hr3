<?php
require_once __DIR__ . '/../config/app.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="hr_validation_export.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Employee ID', 'Date', 'Status', 'Comments']);

$sql = "SELECT * FROM hr_validation ORDER BY validation_date DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'], 
        $row['employee_id'], 
        $row['validation_date'], 
        $row['validation_status'], 
        $row['comments']
    ]);
}

fclose($output);
exit;
?>
