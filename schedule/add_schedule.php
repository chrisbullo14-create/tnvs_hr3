<?php
require_once __DIR__ . '/../config/app.php';

require_once __DIR__ . '/../config/app.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form values
    $vehicleId = $_POST['vehicle_id'];
    $driverId = $_POST['driver_id'];
    $tripDate = $_POST['trip_date'];
    $tripTime = $_POST['trip_time'];
    $destination = $_POST['destination'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];
    $tripDuration = $_POST['trip_duration'];
    $priority = $_POST['priority'];

    // Insert schedule into database
    $sql = "INSERT INTO schedule_management (vehicle_id, driver_id, trip_date, trip_time, destination, status, remarks, trip_duration, priority)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssdsd", $vehicleId, $driverId, $tripDate, $tripTime, $destination, $status, $remarks, $tripDuration, $priority);
    
    if ($stmt->execute()) {
        echo "New schedule added successfully!";
        header("Location: " . BASE_URL . "/schedule/schedule_management.php"); // Redirect to the schedule management page
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

$page_title = 'Add Schedule';
$current_page = 'schedule_management';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<!-- Topbar -->
            

            <!-- Main Content -->
            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Add New Schedule</h1>

                <form action="add_schedule.php" method="POST">
                    <div class="form-group">
                        <label for="vehicle_id">Vehicle ID:</label>
                        <input type="text" class="form-control" id="vehicle_id" name="vehicle_id" required>
                    </div>
                    <div class="form-group">
                        <label for="driver_id">Driver ID:</label>
                        <input type="text" class="form-control" id="driver_id" name="driver_id" required>
                    </div>
                    <div class="form-group">
                        <label for="trip_date">Trip Date:</label>
                        <input type="date" class="form-control" id="trip_date" name="trip_date" required>
                    </div>
                    <div class="form-group">
                        <label for="trip_time">Trip Time:</label>
                        <input type="time" class="form-control" id="trip_time" name="trip_time" required>
                    </div>
                    <div class="form-group">
                        <label for="destination">Destination:</label>
                        <input type="text" class="form-control" id="destination" name="destination" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="In Progress">In Progress</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="remarks">Remarks:</label>
                        <textarea class="form-control" id="remarks" name="remarks"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="trip_duration">Trip Duration:</label>
                        <input type="number" class="form-control" id="trip_duration" name="trip_duration" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority:</label>
                        <select class="form-control" id="priority" name="priority" required>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Schedule</button>
                </form>

<?php include BASE_PATH . '/templates/footer.php'; ?>
