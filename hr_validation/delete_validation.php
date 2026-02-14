<?php
require_once __DIR__ . '/../config/app.php';
$id = $_GET['id'];
$conn->query("DELETE FROM hr_validation WHERE validation_id = $id");
header("Location: " . BASE_URL . "/hr_validation/hr_validation_list.php?message=deleted");
