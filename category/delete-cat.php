<?php
require_once '../db.php'; // Ensure the database connection file is correct

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['id'])) {
    $id = $data['id'];

    $sql = "DELETE FROM category WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Category deleted successfully']);
        } else {
            echo json_encode(['error' => 'Failed to delete category']);
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
