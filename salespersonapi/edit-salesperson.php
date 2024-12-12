<?php
require_once '../db.php';

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204); // No content for preflight response
    exit();
}

// Set headers for other requests
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Get the incoming request data
$data = json_decode(file_get_contents("php://input"), true);

// Check if ID is provided (mandatory for updates)
if (isset($data['id']) && is_numeric($data['id'])) {
    $id = intval($data['id']);

    // List of all possible columns in the table
    $columns = ['name', 'phone', 'email', 'password', 'branch', 'role', 'region', 'area', 'state', 'district', 'city', 'address', 'created_at', 'modified_date', 'deleted_date', 'is_deleted'];

    // Prepare the update query dynamically
    $updateFields = [];
    $updateValues = [];

    foreach ($columns as $column) {
        if (isset($data[$column])) {
            $updateFields[] = "$column = ?";
            $updateValues[] = $data[$column];
        }
    }

    // Add the ID to the end of the values for the WHERE clause
    $updateValues[] = $id;

    if (!empty($updateFields)) {
        $query = "UPDATE admintable SET " . implode(', ', $updateFields) . " WHERE id = ?";

        if ($stmt = $conn->prepare($query)) {
            // Dynamically bind parameters based on the values
            $types = str_repeat('s', count($updateValues) - 1) . 'i'; // All are strings except the ID (integer)
            $stmt->bind_param($types, ...$updateValues);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(["message" => "Admin record updated successfully!"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to update record. Error: " . $stmt->error]);
            }

            $stmt->close();
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to prepare the query. Error: " . $conn->error]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "No valid fields provided for update."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid request. ID is required."]);
}

$conn->close();
?>
