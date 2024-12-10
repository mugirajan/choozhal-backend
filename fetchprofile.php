<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


// Database connection setup
$dbHost = '217.21.88.10';
$dbPort = '3306';
$dbUsername = 'u140987190_choozhal_dev';
$dbPassword = 'Choozhal_dev@7';
$dbName = 'u140987190_choozhal_app';

// Connect to the database
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Check if the data was decoded properly
if ($data && isset($data['email'])) {
    $email = $data['email'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT name, email, role FROM admintable WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = array('success' => true, 'data' => $row);
    } else {
        $response = array('success' => false, 'message' => 'Profile not found.');
    }

    echo json_encode($response);

    $stmt->close();
} else {
    echo json_encode(array('success' => false, 'message' => 'Email is required.'));
}

$conn->close();
?>
