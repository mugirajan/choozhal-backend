<?php
require_once '../db.php'; 

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['id'])) {
    $id = $data['id'];

    $sql = "DELETE FROM usr_details WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Admin deleted successfully']);
        } else {
            echo json_encode(['error' => 'Admin Deleted error']);
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare the statement']);
    }
} else {
    echo json_encode(['error' => 'Invalid or missing category ID']);
}

$conn->close();
?>
