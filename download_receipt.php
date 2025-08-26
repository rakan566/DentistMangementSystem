<?php
// download_receipt.php?id=PAYMENT_ID&patient_id=PATIENT_ID
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

if ($payment_id > 0 && $patient_id > 0) {
    $sql = "SELECT p.name, py.total_amount, py.paid_amount, py.payment_date, p.remaining_amount FROM patients p JOIN payments py ON p.id = py.patient_id WHERE py.id = ? AND p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $payment_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    // Get sum of all paid amounts for this patient
    $sum_sql = "SELECT SUM(paid_amount) as total_paid FROM payments WHERE patient_id = ?";
    $sum_stmt = $conn->prepare($sum_sql);
    $sum_stmt->bind_param("i", $patient_id);
    $sum_stmt->execute();
    $sum_result = $sum_stmt->get_result();
    $sum_row = $sum_result->fetch_assoc();
    $sum_stmt->close();
    $total_paid = $sum_row ? $sum_row['total_paid'] : 0;
    if ($row) {
        header('Content-Type: text/plain');
        $safe_name = preg_replace('/[^A-Za-z0-9_-]/', '_', $row['name']);
        header('Content-Disposition: attachment; filename="receipt_'.$safe_name.'.txt"');
        $receipt = "--- Payment Receipt ---\n";
        $receipt .= "Patient Name: " . $row['name'] . "\n";
        $receipt .= "Total Amount: $" . number_format($row['total_amount'], 2) . "\n";
        $receipt .= "Total Paid: $" . number_format($total_paid, 2) . "\n";
        $receipt .= "This Payment: $" . number_format($row['paid_amount'], 2) . "\n";
        $receipt .= "Date Paid: " . $row['payment_date'] . "\n";
        $receipt .= "Remaining Amount: $" . number_format($row['remaining_amount'], 2) . "\n";
        $receipt .= "----------------------\n";
        echo $receipt;
        exit();
    }
}
header('Location: index.php');
exit();
?>
