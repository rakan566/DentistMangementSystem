<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .patient-card:hover {
            transform: translateY(-5px);
            transition: transform 0.2s ease;
        }
        .modal {
            transition: opacity 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-blue-800">
                <a href="#" id="logoFocus" tabindex="0">
                    <img src="dentist%20logo.png" alt="Professional Dentist Logo" class="inline-block mr-2 rounded-full w-12 h-12 align-middle shadow-md border border-blue-200 bg-white">
                </a>
                Dr.Naji Abou Rafeh Clinic Patient Management
            </h1>
            <nav>
                <a href="add_patient.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add New Patient
                </a>
            </nav>
        </header>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Patient Records</h2>
                <div class="mt-2 flex items-center">
                    <div class="relative flex-grow">
                        <input type="text" id="searchInput" onkeyup="filterPatients()" placeholder="Search patients..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <!-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th> -->
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $sql = "SELECT p.*, SUM(py.total_amount) as total_amount, SUM(py.paid_amount) as paid_amount 
                                FROM patients p 
                                LEFT JOIN payments py ON p.id = py.patient_id 
                                GROUP BY p.id";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $balance = $row['remaining_amount'];
                                echo '<tr class="hover:bg-gray-50" data-patient-name="'.htmlspecialchars($row["name"]).'">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-blue-100 rounded-full">
                                                <i class="fas fa-user-md text-blue-600 text-xl"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">'.$row["name"].'</div>
                                                <div class="text-sm text-gray-500">'.$row["address"].'</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">'.$row["phone"].'</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">'.substr($row["description"], 0, 50).'...</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm '.($balance > 0 ? 'text-red-600' : 'text-green-600').'">$'.number_format($balance, 2).'</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="edit_payment.php?id='.$row["id"].'" class="text-blue-600 hover:text-blue-900 mr-3">Payment</a>
                                        <a href="patient_details.php?id='.$row["id"].'" class="text-indigo-600 hover:text-indigo-900 mr-3">Details</a>
                                        <a href="delete_patient.php?id='.$row["id"].'" class="text-red-600 hover:text-red-900" onclick="return confirm(\'Are you sure you want to delete this patient?\');">Delete</a>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No patients found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function filterPatients() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr[data-patient-name]');
        rows.forEach(row => {
            const name = row.getAttribute('data-patient-name').toLowerCase();
            if (name.includes(input) || input === "") {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    window.onload = function() {
        var logo = document.getElementById('logoFocus');
        if (logo) {
            logo.focus();
        }
    };
    </script>

    <?php $conn->close(); ?>
</body>
</html>

