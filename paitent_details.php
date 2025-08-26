<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$patient_id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM patients WHERE id = $patient_id";
$patient = $conn->query($sql)->fetch_assoc();

$sql = "SELECT SUM(total_amount) as total_amount, SUM(paid_amount) as paid_amount FROM payments WHERE patient_id = $patient_id";
$payment_summary = $conn->query($sql)->fetch_assoc();
$balance = $payment_summary['total_amount'] - $payment_summary['paid_amount'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8";
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Details - Clinic Management</title>
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
                Patient Details
            </h1>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center space-x-4 mb-6">
                    <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/2fe60ad3-be05-40d8-ad39-5da97fb4a4be.png" alt="Patient profile placeholder" class="w-20 h-20 rounded-full">
                    <div>
                        <h2 class="text-2xl font-semibold"><?php echo htmlspecialchars($patient['name']); ?></h2>
                        <p class="text-gray-600">Patient ID: <?php echo $patient['id']; ?></p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Phone Number</h4>
                        <p class="text-gray-900"><?php echo htmlspecialchars($patient['phone']); ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Address</h4>
                        <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($patient['address'])); ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Balance</h4>
                        <p class="text-2xl font-semibold <?php echo ($balance > 0) ? 'text-red-600' : 'text-green-600'; ?>">
                            $<?php echo number_format($balance, 2); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 md:col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Medical Information</h3>
                <div class="prose max-w-none">
                    <p><?php echo nl2br(htmlspecialchars($patient['description'])); ?></p>
                </div>
            </div>
        </div>

        <div class="flex space-x-4 mb-6">
            <a href="edit_payment.php?id=<?php echo $patient['id']; ?>" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Record Payment
            </a>
            <a href="#" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Edit Patient Info
            </a>
        </div>
    </div>
</body>
</html>

