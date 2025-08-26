<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "clinic_management";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $description = $_POST['description'];
    $total_amount = $_POST['total_amount'];
    $xray_photo = null;

    // Handle file upload
    if (isset($_FILES['xray_photo']) && $_FILES['xray_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['xray_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileTmpPath = $_FILES['xray_photo']['tmp_name'];
            $fileName = basename($_FILES['xray_photo']['name']);
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
            $destPath = $uploadDir . $fileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $xray_photo = $fileName;
            }
        }
        // else: ignore other errors, allow patient to be saved without image
    }

    // Check for duplicate patient name
    $dup_sql = "SELECT id FROM patients WHERE name = ?";
    $dup_stmt = $conn->prepare($dup_sql);
    $dup_stmt->bind_param("s", $name);
    $dup_stmt->execute();
    $dup_stmt->store_result();
    if ($dup_stmt->num_rows > 0) {
        $error_message = "A patient with this name already exists.";
    } else {
        $sql = "INSERT INTO patients (name, address, phone, description, total_amount, remaining_amount, xray_photo) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdds", $name, $address, $phone, $description, $total_amount, $total_amount, $xray_photo);
        
        if ($stmt->execute()) {
            $patient_id = $stmt->insert_id;
            $stmt->close();
            
            if ($total_amount > 0) {
                // Insert payment with current date and time
                date_default_timezone_set('Asia/Beirut'); // Set to Lebanon timezone
                $payment_date = date('Y-m-d H:i:s');
                $sql = "INSERT INTO payments (patient_id, total_amount, paid_amount, payment_date) VALUES (?, ?, 0, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ids", $patient_id, $total_amount, $payment_date);
                $stmt->execute();
            }
            $stmt->close();
            
            header("Location: index.php?success=1");
            exit();
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    $dup_stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Patient - Clinic Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <header class="mb-8">
            <a href="index.php" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Back to patients
            </a>
            <h1 class="text-3xl font-bold text-blue-800">
                <img src="dentist%20logo.png" alt="Professional Dentist Logo" class="inline-block mr-2 w-10 h-10 rounded-full align-middle">
                Add New Patient
            </h1>
        </header>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-6" role="alert">
                    <strong class="font-bold">Error:</strong> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="add_patient.php" class="p-6 space-y-6" id="addPatientForm" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" id="name" name="name" required autocomplete="off" list="none" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required autocomplete="off" list="none" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label for="total_amount" class="block text-sm font-medium text-gray-700">Total Amount Due ($)</label>
                            <input type="number" step="0.01" id="total_amount" name="total_amount" autocomplete="off" list="none" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo isset($_POST['total_amount']) ? htmlspecialchars($_POST['total_amount']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea id="address" name="address" rows="3" autocomplete="off" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Medical Description</label>
                            <textarea id="description" name="description" rows="3" autocomplete="off" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        <div>
                            <label for="xray_photo" class="block text-sm font-medium text-gray-700">X-ray Photo</label>
                        <input type="file" id="xray_photo" name="xray_photo" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="reset" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="clearFormFields(event)">
                        Clear Form
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    function clearFormFields(e) {
        e.preventDefault();
        const form = document.getElementById('addPatientForm');
        form.reset();
        // Clear all text, number, and textarea fields
        form.querySelectorAll('input[type="text"], input[type="tel"], input[type="number"], textarea').forEach(field => {
            field.value = '';
        });
    }
    </script>
</body>
</html>

