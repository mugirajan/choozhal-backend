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
if ($data && isset($data['email']) && isset($data['password'])) {
    $email = $data['email'];
    $password = $data['password'];

    // Query database for user credentials
    $query = "SELECT * FROM admintable WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($query);
    $query = "SELECT * FROM admintable WHERE email = '$email' AND password = '$password'";
$result = $conn->query($query);


    // Check if user exists
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = array(
          'success' => true,
          'role' => $row['role'],
          'name' => $row['name']
        );
        echo json_encode($response);
      } else {
        $response = array(
          'success' => false,
          'message' => 'Invalid email or password.'
        );
        echo json_encode($response);
      }
} else {
    echo json_encode(array('success' => false, 'message' => 'Email and password are required'));
}

$conn->close();
?>
