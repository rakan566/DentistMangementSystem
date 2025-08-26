<?php
// add_operation.php: Form to add a new operation for a patient
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
$success = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $operation_name = $_POST['operation_name'];
    $operation_date = $_POST['operation_date'];
    $notes = $_POST['notes'];
    $cost = $_POST['cost'];
    $sql = "INSERT INTO operations (patient_id, operation_name, operation_date, notes, cost) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssd", $patient_id, $operation_name, $operation_date, $notes, $cost);
    if ($stmt->execute()) {
        $operation_id = $stmt->insert_id;
        $stmt->close();
        // Only update patient's remaining_amount and total_amount
        $update_sql = "UPDATE patients SET total_amount = total_amount + ?, remaining_amount = remaining_amount + ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ddi", $cost, $cost, $patient_id);
        $update_stmt->execute();
        $update_stmt->close();
        $success = true;
    } else {
        $error_message = "Error: " . $stmt->error;
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Operation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <a href="index.php" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            &#8592; Back to patients
        </a>
        <h1 class="text-2xl font-bold mb-6">Add Operation for Patient #<?php echo $patient_id; ?></h1>
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Operation added successfully.</div>
        <?php elseif ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST" class="bg-white p-6 rounded shadow-md space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Operation Name</label>
                <input type="text" name="operation_name" required class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" name="operation_date" required class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
                <input type="number" step="0.01" name="cost" required class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add Operation</button>
            </div>
        </form>
    </div>
</body>
</html>
