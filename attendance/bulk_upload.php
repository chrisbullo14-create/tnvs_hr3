<?php
require_once __DIR__ . '/../config/app.php';

require_once __DIR__ . '/../config/app.php';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] == UPLOAD_ERR_OK) {
        // Add file size validation (5MB max)
        if ($_FILES['csvFile']['size'] > 5 * 1024 * 1024) {
            $error = "File size exceeds 5MB limit";
        } else {
            $file = $_FILES['csvFile']['tmp_name'];
            $handle = fopen($file, "r");
            $conn->autocommit(FALSE);
            
            try {
                // Check if file is empty
                if (filesize($file) === 0) {
                    throw new Exception("Uploaded file is empty");
                }

                fgetcsv($handle); // Skip header
                $lineNumber = 1;
                
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $lineNumber++;
                    if (count($data) != 4) {
                        throw new Exception("Invalid column count on line $lineNumber");
                    }
                    
                    // Validate data formats
                    $employeeId = htmlspecialchars(trim($data[0]));
                    $recordDate = DateTime::createFromFormat('Y-m-d', trim($data[1]));
                    $timeIn = DateTime::createFromFormat('H:i', trim($data[2]));
                    $timeOut = DateTime::createFromFormat('H:i', trim($data[3]));

                    if (!$recordDate || !$timeIn || !$timeOut) {
                        throw new Exception("Invalid date/time format on line $lineNumber");
                    }

                    if ($timeIn >= $timeOut) {
                        throw new Exception("Invalid time in/out on line $lineNumber");
                    }

                    $stmt = $conn->prepare("INSERT INTO attendance_time_log 
                        (employee_id, record_date, time_in, time_out)
                        VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", 
                        $employeeId,
                        $recordDate->format('Y-m-d'),
                        $timeIn->format('H:i:s'),
                        $timeOut->format('H:i:s')
                    );
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Database error on line $lineNumber: " . $stmt->error);
                    }
                }
                
                $conn->commit();
                $success = "Bulk upload completed successfully!";
                // Clear uploaded file reference
                echo '<script>document.getElementById("csvFile").value = "";</script>';
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error: " . $e->getMessage();
            }
            
            fclose($handle);
        }
    } else {
        $error = "Please select a valid CSV file to upload";
    }
}
?>

<!-- Rest of HTML remains the same until the script section -->

<?php
$page_title = 'Bulk Upload';
$current_page = 'attendance_record';
$extra_css = '<style>
        /* Enhanced Custom Styles */
        .upload-container {
            background: linear-gradient(135deg, #f8f9fc 0%, #f1f5f9 100%);
            min-height: 100vh;
        }
        
        .upload-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .upload-card:hover {
            transform: translateY(-5px);
        }
        
        .upload-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border-radius: 1rem 1rem 0 0 !important;
        }
        
        .upload-dropzone {
            border: 2px dashed #4e73df;
            border-radius: 1rem;
            background: rgba(78, 115, 223, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-dropzone:hover {
            background: rgba(78, 115, 223, 0.1);
            border-color: #224abe;
        }
        
        .guideline-list .list-group-item {
            border: none;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .guideline-icon {
            width: 30px;
            height: 30px;
            background: #4e73df;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        .template-link {
            color: #4e73df;
            font-weight: 600;
            text-decoration: underline dotted;
        }
        
        .upload-illustration {
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.1));
        }
    </style>
';
$extra_js = '<script>document.getElementById("csvFile").value = "";</script>
<script>
    // Fixed file handling using DataTransfer
    document.getElementById(\'csvFile\').addEventListener(\'change\', function(e) {
        handleFileSelection(this.files);
    });

    // Improved drag & drop handling
    const dropzone = document.querySelector(\'.upload-dropzone\');
    
    dropzone.addEventListener(\'dragover\', (e) => {
        e.preventDefault();
        dropzone.style.borderColor = \'#224abe\';
    });

    dropzone.addEventListener(\'dragleave\', () => {
        dropzone.style.borderColor = \'#4e73df\';
    });

    dropzone.addEventListener(\'drop\', (e) => {
        e.preventDefault();
        const files = e.dataTransfer.files;
        if(files.length > 0) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            document.getElementById(\'csvFile\').files = dataTransfer.files;
            handleFileSelection(dataTransfer.files);
        }
    });

    function handleFileSelection(files) {
        const fileName = files[0] ? files[0].name : \'No file selected\';
        const dropzone = document.querySelector(\'.upload-dropzone\');
        
        if(files.length > 0) {
            dropzone.style.borderColor = \'#1cc88a\';
            dropzone.innerHTML = `
                <div class="mb-3">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="font-weight-bold text-success">File Ready</h5>
                    <p class="text-muted mb-0">${fileName}</p>
                </div>
            `;
        } else {
            dropzone.innerHTML = `
                <div class="mb-3">
                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                    <h5 class="font-weight-bold">Drag & Drop or Click to Upload</h5>
                    <p class="text-muted mb-0">CSV files only (max size 5MB)</p>
                </div>
            `;
            dropzone.style.borderColor = \'#4e73df\';
        }
    }
</script>
';

include BASE_PATH . '/templates/header.php';
include BASE_PATH . '/templates/sidebar.php';
include BASE_PATH . '/templates/topbar.php';
?>



<?php include BASE_PATH . '/templates/footer.php'; ?>
