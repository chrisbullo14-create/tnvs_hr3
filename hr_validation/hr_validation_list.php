<?php
require_once __DIR__ . '/../config/app.php';
$result = $conn->query("SELECT * FROM hr_validation ORDER BY validation_date DESC");
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Employee ID</th>
            <th>Date</th>
            <th>Status</th>
            <th>Comments</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['validation_id'] ?></td>
            <td><?= $row['employee_id'] ?></td>
            <td><?= $row['validation_date'] ?></td>
            <td><?= $row['validation_status'] ?></td>
            <td><?= $row['comments'] ?></td>
            <td>
                <a href="<?= BASE_URL ?>/hr_validation/edit_validation.php?id=<?= $row['validation_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="<?= BASE_URL ?>/hr_validation/delete_validation.php?id=<?= $row['validation_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?');">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
