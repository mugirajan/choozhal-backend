<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (isset($_GET['admin_id'])) {
    $adminId = $_GET['admin_id'];

    // Query the admintable to check if the admin_id exists and to get the admin's role
    $query = "SELECT * FROM admintable WHERE id = '$adminId'";
    $result = $conn->query($query);

    // Check if the admin_id exists
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $adminRole = $row['role'];

        // Apply filters based on the admin's role
        $filterQuery = '';
        if ($adminRole == 'SuperAdmin') {
            $filterQuery = '';
        } elseif ($adminRole == 'RegionAdmin') {
            $filterQuery = "WHERE u.sales_person_id= '$adminId'";
        } elseif ($adminRole == 'AreaAdmin') {
            $filterQuery = "WHERE u.sales_person_id = '$adminId'";
        } elseif ($adminRole == 'SalesPerson') {
            $filterQuery = "WHERE u.sales_person_id = '$adminId'";
        }

        // Query the tickets table with the applied filters
        $query = "SELECT t.*, u.uname, u.email,u.sales_person,u.sales_person_id,u.pro_name 
                   FROM tickets t 
                   INNER JOIN users u ON t.user_id = u.id $filterQuery";
        $result = $conn->query($query);

        // Check if any tickets exist with the applied filters
        if ($result && $result->num_rows > 0) {
            $tickets = array();
            while ($row = $result->fetch_assoc()) {
                $tickets[] = $row;
            }
            echo json_encode($tickets);
        } else {
            echo json_encode(["error" => "No tickets found."]);
        }
    } else {
        echo json_encode(["error" => "Invalid admin ID."]);
    }
} else {
    echo json_encode(["error" => "Admin ID is required."]);
}

$conn->close();
?>