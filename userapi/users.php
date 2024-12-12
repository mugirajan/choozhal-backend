<?php

// Include the database connection file
require_once '../db.php';

// Check if the admin_id is passed in the request
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
        } 
        elseif ($adminRole == 'HeadOfiice') {
            $filterQuery = '';
        }elseif ($adminRole == 'GeneralManager') {
            $filterQuery = '';
        }elseif ($adminRole == 'RegionAdmin') {
            $filterQuery = "WHERE sales_person_id = '$adminId'";
        } elseif ($adminRole == 'AreaAdmin') {
            $filterQuery = "WHERE sales_person_id = '$adminId'";
        } elseif ($adminRole == 'SalesPerson') {
            $filterQuery = "WHERE sales_person_id = '$adminId'";
        }

        // Query the users table with the applied filters
        $query = "SELECT * FROM users $filterQuery";
        $result = $conn->query($query);

        // Check if any users exist with the applied filters
        if ($result && $result->num_rows > 0) {
            $users = array();
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $response = array(
                'success' => true,
                'data' => $users
            );
            echo json_encode($response);
        } else {
            $response = array(
                'success' => false,
                'message' => 'No users found.'
            );
            echo json_encode($response);
        }
    } else {
        $response = array(
            'success' => false,
            'message' => 'Invalid admin ID.'
        );
        echo json_encode($response);
    }
} else {
    $response = array(
        'success' => false,
        'message' => 'Admin ID is required.'
    );
    echo json_encode($response);
}

$conn->close();
?>