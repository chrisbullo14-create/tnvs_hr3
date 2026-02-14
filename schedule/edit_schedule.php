<?php
require_once __DIR__ . '/../config/app.php';

require_once __DIR__ . '/../config/app.php';
if (isset($_GET['id'])) {
    $scheduleId = $_GET['id'];

    // Fetch the schedule details from the database
    $sql = "SELECT * FROM schedule_management WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo "Error preparing the statement: " . $conn->error;
        exit();
    }

    $stmt->bind_param("i", $scheduleId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the schedule exists
    if ($result->num_rows == 1) {
        $schedule = $result->fetch_assoc();
    } else {
        echo "Schedule not found!";
        exit();
    }
} else {
    echo "Invalid schedule ID!";
    exit();
}

// Handle form submission to update the schedule
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form values and sanitize them
    $vehicleId = htmlspecialchars($_POST['vehicle_id']);
    $driverId = htmlspecialchars($_POST['driver_id']);
    $tripDate = htmlspecialchars($_POST['trip_date']);
    $tripTime = htmlspecialchars($_POST['trip_time']);
    $destination = htmlspecialchars($_POST['destination']);
    $status = htmlspecialchars($_POST['status']);
    $remarks = htmlspecialchars($_POST['remarks']);
    $tripDuration = htmlspecialchars($_POST['trip_duration']);
    $priority = htmlspecialchars($_POST['priority']);

    // Validate required fields
    if (empty($vehicleId) || empty($driverId) || empty($tripDate) || empty($tripTime) || empty($destination) || empty($status) || empty($tripDuration) || empty($priority)) {
        echo "Please fill in all required fields.";
    } else {
        // Update the schedule in the database
        $updateSql = "UPDATE schedule_management SET 
                      vehicle_id = ?, driver_id = ?, trip_date = ?, trip_time = ?, 
                      destination = ?, status = ?, remarks = ?, trip_duration = ?, priority = ? 
                      WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        if ($stmt === false) {
            echo "Error preparing the update statement: " . $conn->error;
            exit();
        }

        $stmt->bind_param("sssssssdsi", $vehicleId, $driverId, $tripDate, $tripTime, $destination, $status, $remarks, $tripDuration, $priority, $scheduleId);

        if ($stmt->execute()) {
            header("Location: " . BASE_URL . "/schedule/schedule_management.php"); // Redirect to the schedule management page after update
            exit();  // Ensure script stops after redirect
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}

$page_title = 'Edit Schedule';
$current_page = 'schedule_management';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<!-- Topbar (same as before) -->

            <!-- Main Content -->
            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Edit Schedule</h1>

                <form action="edit_schedule.php?id=<?php echo urlencode($scheduleId); ?>" method="POST">
                    <div class="form-group">
                        <label for="vehicle_id">Vehicle ID:</label>
                        <input type="text" class="form-control" id="vehicle_id" name="vehicle_id" value="<?php echo htmlspecialchars($schedule['vehicle_id']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="driver_id">Driver ID:</label>
                        <input type="text" class="form-control" id="driver_id" name="driver_id" value="<?php echo htmlspecialchars($schedule['driver_id']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="trip_date">Trip Date:</label>
                        <input type="date" class="form-control" id="trip_date" name="trip_date" value="<?php echo htmlspecialchars($schedule['trip_date']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="trip_time">Trip Time:</label>
                        <input type="time" class="form-control" id="trip_time" name="trip_time" value="<?php echo htmlspecialchars($schedule['trip_time']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="destination">Destination:</label>
                        <input type="text" class="form-control" id="destination" name="destination" value="<?php echo htmlspecialchars($schedule['destination']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="Scheduled" <?php echo ($schedule['status'] == 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="Completed" <?php echo ($schedule['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo ($schedule['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="In Progress" <?php echo ($schedule['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="remarks">Remarks:</label>
                        <textarea class="form-control" id="remarks" name="remarks"><?php echo htmlspecialchars($schedule['remarks']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="trip_duration">Trip Duration (in hours):</label>
                        <input type="number" class="form-control" id="trip_duration" name="trip_duration" value="<?php echo htmlspecialchars($schedule['trip_duration']); ?>" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority:</label>
                        <select class="form-control" id="priority" name="priority" required>
                            <option value="Low" <?php echo ($schedule['priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                            <option value="Medium" <?php echo ($schedule['priority'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="High" <?php echo ($schedule['priority'] == 'High') ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Schedule</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php
// Close the connection
$conn->close();
?>

<?php include BASE_PATH . '/templates/footer.php'; ?>
