<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// header("Content-Type: application/json");

$dbHost = '217.21.88.10';
$dbPort = '3306';
$dbUsername = 'u140987190_choozhal_dev';
$dbPassword = 'Choozhal_dev@7';
$dbName = 'u140987190_choozhal_app';

$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($data && isset($data['email']) && isset($data['password'])) {
    $email = $data['email'];
    $password = $data['password'];

    $query = "SELECT * FROM usr_details WHERE usr_email = '$email' AND usr_pass = '$password'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = array(
            'success' => true,
            'admin_id' => $row['id'],
            'first_name' => $row['usr_fname'],
            'role' => $row['usr_role'],
            'branch' => $row['branch'],
            'area' => $row['area'],
            'address' => $row['address'],
            'phone' => $row['usr_phone']
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
