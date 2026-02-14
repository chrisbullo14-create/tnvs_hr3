<?php
require_once __DIR__ . '/../config/app.php';
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM hr_validation WHERE validation_id = $id");
$data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeId = $_POST['employeeId'];
    $validationDate = $_POST['validationDate'];
    $status = $_POST['status'];
    $comments = $_POST['comments'];

    $stmt = $conn->prepare("UPDATE hr_validation SET employee_id=?, validation_date=?, validation_status=?, comments=? WHERE validation_id=?");
    $stmt->bind_param("ssssi", $employeeId, $validationDate, $status, $comments, $id);

    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "/hr_validation/hr_validation_list.php?message=updated");
    } else {
        echo "Update error: " . $stmt->error;
    }
}
?>

<!-- Form below should be inside your HTML body -->
<form method="POST">
    <input type="text" name="employeeId" value="<?= $data['employee_id'] ?>" required>
    <input type="date" name="validationDate" value="<?= $data['validation_date'] ?>" required>
    <select name="status">
        <option value="Pending" <?= $data['validation_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
        <option value="Approved" <?= $data['validation_status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
        <option value="Rejected" <?= $data['validation_status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
    </select>
    <textarea name="comments"><?= $data['comments'] ?></textarea>
    <button type="submit">Update</button>
</form>
