<?php
require_once "db.php";

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    echo json_encode(array('success' => false, 'message' => 'Database connection failed.'));
    exit;
}

// Get the raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data || !isset($data['admin_id'])) {
    echo json_encode(array('success' => false, 'message' => 'Admin ID is required.'));
    exit;
}

$adminId = $data['admin_id'];

// Validate admin ID
if (!is_numeric($adminId)) {
    echo json_encode(array('success' => false, 'message' => 'Invalid admin ID.'));
    exit;
}

// Query database for admin details
$stmt = $conn->prepare("SELECT * FROM usr_details WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();

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
    echo json_encode(array('success' => false, 'message' => 'Admin not found.'));
}

$conn->close();
?>