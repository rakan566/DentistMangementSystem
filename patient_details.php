<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$patient = null;
$payments = [];

if ($patient_id > 0) {
    $sql = "SELECT * FROM patients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    $stmt->close();

    $sql = "SELECT * FROM payments WHERE patient_id = ? ORDER BY payment_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $payments = $stmt->get_result();
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        <?php if ($patient): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4"><?php echo htmlspecialchars($patient['name']); ?></h2>
            <a href="add_operation.php?patient_id=<?php echo $patient['id']; ?>" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add Operation</a>
            <p class="mb-2"><strong>Address:</strong> <?php echo htmlspecialchars($patient['address']); ?></p>
            <p class="mb-2"><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
            <p class="mb-2"><strong>Description:</strong> <?php echo htmlspecialchars($patient['description']); ?></p>
            <p class="mb-2"><strong>Total Amount:</strong> $<?php echo number_format($patient['total_amount'], 2); ?></p>
            <p class="mb-2"><strong>Remaining:</strong> $<?php echo number_format($patient['remaining_amount'], 2); ?></p>
            <?php if (!empty($patient['xray_photo'])): ?>
                <p class="mb-2"><strong>X-ray Photo:</strong> <a href="uploads/<?php echo urlencode($patient['xray_photo']); ?>" target="_blank" class="text-blue-600 underline">View X-ray</a></p>
                <img src="uploads/<?php echo urlencode($patient['xray_photo']); ?>" alt="X-ray Photo" class="my-4 max-w-xs border rounded shadow">
            <?php endif; ?>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment History</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        if ($payments && $payments->num_rows > 0): 
                            $running_total = $patient['total_amount'];
                            while($payment = $payments->fetch_assoc()): 
                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(date('Y-m-d', strtotime($payment['payment_date']))); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo number_format($running_total, 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">$<?php echo number_format($payment['paid_amount'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">$<?php echo number_format($running_total - $payment['paid_amount'], 2); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($payment['notes']); ?></td>
                                <td class="px-6 py-4 text-sm text-blue-600">
<a href="download_receipt.php?id=<?php echo $payment['id']; ?>&patient_id=<?php echo $patient['id']; ?>" class="underline" target="_self" download>Download</a>
                                </td>
                            </tr>
                        <?php 
                            $running_total -= $payment['paid_amount'];
                            endwhile; 
                        else: 
                        ?>
                            <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No payment records found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Operations History -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Operations History</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        // Fetch operations for this patient
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        $op_sql = "SELECT * FROM operations WHERE patient_id = ? ORDER BY operation_date DESC";
                        $op_stmt = $conn->prepare($op_sql);
                        $op_stmt->bind_param("i", $patient_id);
                        $op_stmt->execute();
                        $ops = $op_stmt->get_result();
                        if ($ops && $ops->num_rows > 0):
                            while($op = $ops->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(date('Y-m-d', strtotime($op['operation_date']))); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800"><?php echo htmlspecialchars($op['operation_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($op['notes']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">$<?php echo number_format($op['cost'], 2); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No operations found</td></tr>
                        <?php endif; $op_stmt->close(); $conn->close(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center text-gray-500">
                Patient not found.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
