<?php
require_once "db.php";


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
