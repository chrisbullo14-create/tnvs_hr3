<?php
// Include the database connection file
require_once __DIR__ . '/../config/app.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form values
    $vehicleId = $_POST['vehicleId'];
    $driverId = $_POST['driverId'];
    $tripDate = $_POST['tripDate'];
    $tripTime = $_POST['tripTime'];
    $destination = $_POST['destination'];
    $tripStatus = $_POST['tripStatus'];
    $remarks = $_POST['remarks'];
    $tripDuration = $_POST['tripDuration'];
    $priority = $_POST['priority'];

    // Prepare the SQL statement to insert the TNVS sheet record
    $sql = "INSERT INTO tnvs_sheet_management (vehicle_id, driver_id, trip_date, trip_time, destination, trip_status, remarks, trip_duration, priority) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters to the statement (Updated: bind priority as string "s")
        $stmt->bind_param("ssssssssd", $vehicleId, $driverId, $tripDate, $tripTime, $destination, $tripStatus, $remarks, $tripDuration, $priority);

        // Execute the query
        if ($stmt->execute()) {
            echo "TNVS sheet successfully created!";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>

<?php
$page_title = 'TNVS Sheet Management';
$current_page = 'sheet_management';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>

<h1 class="h3 mb-4 text-gray-800">Create TNVS Sheet</h1>
                    <form id="tnvsSheetForm" action="tnvs_sheet_management.php" method="POST">
                        <div class="form-group">
                            <label for="vehicleId">Vehicle ID:</label>
                            <input type="text" class="form-control" id="vehicleId" name="vehicleId" required>
                        </div>
                        <div class="form-group">
                            <label for="driverId">Driver ID:</label>
                            <input type="text" class="form-control" id="driverId" name="driverId" required>
                        </div>
                        <div class="form-group">
                            <label for="tripDate">Trip Date:</label>
                            <input type="date" class="form-control" id="tripDate" name="tripDate" required>
                        </div>
                        <div class="form-group">
                            <label for="tripTime">Trip Time:</label>
                            <input type="time" class="form-control" id="tripTime" name="tripTime" required>
                        </div>
                        <div class="form-group">
                            <label for="destination">Destination:</label>
                            <input type="text" class="form-control" id="destination" name="destination" required>
                        </div>
                        <div class="form-group">
                            <label for="tripStatus">Trip Status:</label>
                            <select class="form-control" id="tripStatus" name="tripStatus" required>
                                <option value="Scheduled">Scheduled</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="remarks">Remarks:</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="tripDuration">Trip Duration (hours):</label>
                            <input type="number" step="0.01" class="form-control" id="tripDuration" name="tripDuration" required>
                        </div>
                        <div class="form-group">
                            <label for="priority">Priority:</label>
                            <select class="form-control" id="priority" name="priority" required>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Create TNVS Sheet</button>
                    </form>

<?php include BASE_PATH . '/templates/footer.php'; ?>
