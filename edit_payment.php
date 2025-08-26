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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_id = $_POST['payment_id'];
    $paid_amount = $_POST['paid_amount'];
    $payment_date = $_POST['payment_date'];
    $notes = $_POST['notes'];
    
    if ($payment_id > 0) {
        $sql = "UPDATE payments SET paid_amount = paid_amount + ?, payment_date = ?, notes = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dssi", $paid_amount, $payment_date, $notes, $payment_id);
        $stmt->execute();
        $stmt->close();
        // Update remaining_amount in patients table
        $sql = "UPDATE patients SET remaining_amount = remaining_amount - ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $paid_amount, $patient_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $sql = "SELECT total_amount FROM payments WHERE patient_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = $result->fetch_assoc();
        $stmt->close();
        
        $sql = "INSERT INTO payments (patient_id, total_amount, paid_amount, payment_date, notes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iddss", $patient_id, $payments['total_amount'], $paid_amount, $payment_date, $notes);
        $stmt->execute();
        $stmt->close();
        // Update remaining_amount in patients table
        $sql = "UPDATE patients SET remaining_amount = remaining_amount - ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $paid_amount, $patient_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: patient_details.php?id=$patient_id");
    exit();
}

$sql = "SELECT p.* FROM patients p WHERE p.id = $patient_id";
$patient = $conn->query($sql)->fetch_assoc();

$sql = "SELECT SUM(total_amount) as total_amount, SUM(paid_amount) as paid_amount FROM payments WHERE patient_id = $patient_id";
$payment_summary = $conn->query($sql)->fetch_assoc();
$balance = $payment_summary['total_amount'] - $payment_summary['paid_amount'];

$sql = "SELECT * FROM payments WHERE patient_id = $patient_id ORDER BY payment_date DESC";
$payments = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Payments - Clinic Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <header class="mb-8">
            <a href="index.php" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Back to patients
            </a>
            <h1 class="text-3xl font-bold text-blue-800">
                <img src="dentist%20logo.png" alt="Professional Dentist Logo" class="inline-block mr-2 w-10 h-10 rounded-full align-middle">
                Edit Payment
            </h1>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Overview</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Total Charged</p>
                        <p class="text-2xl font-semibold">$<?php echo number_format($patient['total_amount'], 2); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Paid</p>
                        <p class="text-2xl font-semibold text-green-600">$<?php echo number_format($patient['total_amount'] - $patient['remaining_amount'], 2); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Balance</p>
                        <p class="text-2xl font-semibold <?php echo ($patient['remaining_amount'] > 0) ? 'text-red-600' : 'text-green-600'; ?>">
                            $<?php echo number_format($patient['remaining_amount'], 2); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 md:col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Record New Payment</h3>
                <form method="POST" action="edit_payment.php?id=<?php echo $patient_id; ?>">
                    <input type="hidden" name="payment_id" value="0">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="paid_amount" class="block text-sm font-medium text-gray-700">Amount ($)</label>
                            <input type="number" step="0.01" id="paid_amount" name="paid_amount" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" id="payment_date" name="payment_date" required value="<?php echo date('Y-m-d'); ?>"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <input type="text" id="notes" name="notes" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        if ($payments->num_rows > 0) {
                            while($payment = $payments->fetch_assoc()) {
                                echo '<tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">'.htmlspecialchars($payment['payment_date']).'</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">$'.number_format($payment['paid_amount'], 2).'</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">'.htmlspecialchars($payment['notes']).'</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No payment records found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

