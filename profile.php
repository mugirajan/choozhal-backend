<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database connection setup
$dbHost = '217.21.88.10';
$dbPort = '3306';
$dbUsername = 'u140987190_choozhal_dev';
$dbPassword = 'Choozhal_dev@7';
$dbName = 'u140987190_choozhal_app';

$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($data && isset($data['admin_id'])) {
    $adminId = $data['admin_id'];

    // Query database for admin details
    $query = "SELECT * FROM admintable WHERE id = '$adminId'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = array(
            'success' => true,
            'data' => array(
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'role' => $row['role'],
                'branch' => $row['branch'],
                'area' => $row['area'],
                'region' => $row['region']
            )
        );
        echo json_encode($response);
    } else {
        echo json_encode(array('success' => false, 'message' => 'Admin not found.'));
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'Admin ID is required.'));
}

$conn->close();
?>
