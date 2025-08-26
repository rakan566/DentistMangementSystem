<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

if (isset($_GET['id'])) {
    $patient_id = intval($_GET['id']);
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Delete payments first to maintain referential integrity
    $conn->query("DELETE FROM payments WHERE patient_id = $patient_id");
    // Delete the patient
    $conn->query("DELETE FROM patients WHERE id = $patient_id");
    $conn->close();
    header("Location: index.php?deleted=1");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Patient</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        /* Custom styles */
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-blue-800">
            <img src="dentist%20logo.png" alt="Professional Dentist Logo" class="inline-block mr-2 w-10 h-10 rounded-full align-middle">
            Delete Patient
        </h1>
        <div class="mt-4">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p class="font-bold">Success!</p>
                    <p>Patient record deleted successfully.</p>
                </div>
            <?php endif; ?>
            <p class="text-gray-700">Are you sure you want to delete this patient record? This action cannot be undone.</p>
        </div>
        <div class="mt-8">
            <a href="index.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-l">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <form action="" method="post" class="inline">
                <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? intval($_GET['id']) : ''; ?>">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-r">
                    <i class="fas fa-trash"></i> Delete Patient
                </button>
            </form>
        </div>
    </div>
</body>
</html>
