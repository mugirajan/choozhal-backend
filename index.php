<?php
require_once "connect/db.php";



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
            'email' => $row['usr_email'],
            'branch' => $row['branch'],
            'area' => $row['area'],
            'address' => $row['address'],
            'region' => $row['region'],
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
