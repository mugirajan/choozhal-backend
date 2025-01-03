<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204); 
    exit();
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && is_numeric($data['id'])) {
    $id = intval($data['id']); 

    $columns = [
         'cust_id', 'prod_id', 'prod_uniq_no', 'bill_no',   
        'warnt_period','prof_doc', 'sale_note'
    ];

    $updateFields = [];
    $updateValues = [];

    foreach ($columns as $column) {
        if (isset($data[$column])) {
            $updateFields[] = "$column = ?";
            $updateValues[] = $data[$column];
        }
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(["message" => "No valid fields provided for update."]);
        exit();
    }

    $updateValues[] = $id;

    // Prepare the SQL query
    $query = "UPDATE sales_records SET " . implode(', ', $updateFields) . " WHERE id = ?";

    // Prepare the statement and bind the parameters
    if ($stmt = $conn->prepare($query)) {
        // Build the types string for bind_param (use 's' for strings, 'i' for integers)
        $types = str_repeat('s', count($updateValues) - 1) . 'i'; // All values are strings except for ID (integer)

        // Debugging: Log the query and values
        error_log("Query: $query");
        error_log("Values: " . implode(', ', $updateValues));

        // Bind parameters dynamically based on the number of fields
        $stmt->bind_param($types, ...$updateValues);

        // Execute the query
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "sale records record updated successfully!"]);
        } else {
            // Log the error if the query fails
            error_log("Error executing query: " . $stmt->error);
            http_response_code(500);
            echo json_encode(["message" => "Failed to update record. Error: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        // Log the error if the statement preparation fails
        error_log("Error preparing query: " . $conn->error);
        http_response_code(500);
        echo json_encode(["message" => "Failed to prepare the query. Error: " . $conn->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid request. ID is required."]);
}

$conn->close();
?>
