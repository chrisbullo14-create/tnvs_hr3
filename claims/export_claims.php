<?php
require_once __DIR__ . '/../config/app.php';

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM claim_submissions";
if ($status_filter && in_array($status_filter, ['Pending', 'Approved', 'Rejected'])) {
    $sql .= " WHERE status = '" . $conn->real_escape_string($status_filter) . "'";
}
$sql .= " ORDER BY date_submitted DESC";

$result = $conn->query($sql);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="claim_records.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Full Name', 'Contact Number', 'Claim Type', 'Incident Date', 'Amount', 'Status', 'Date Submitted']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['full_name'],
        $row['contact_number'],
        $row['claim_type'],
        $row['incident_date'],
        number_format($row['amount'], 2),
        $row['status'],
        $row['date_submitted'],
    ]);
}

fclose($output);
exit;
