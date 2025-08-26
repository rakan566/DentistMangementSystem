<?php
define('localhost');
define('root');
define('');
define('clinic_management');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!-- Dentist Logo for Consistency -->
<img src="dentist%20logo.png" alt="Professional Dentist Logo" style="display:none;">

