<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Get the incoming request data (assumes it's a POST request)
$data = json_decode(file_get_contents("php://input"));

// Check if the necessary data is provided
if (isset($data->id) && isset($data->cat_name)) {
    $id = $data->id;
    $cat_name = $data->cat_name;

    // Prepare the SQL query to update the category
    $query = "UPDATE category SET cat_name = ? WHERE id = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameters
        $stmt->bind_param("si", $cat_name, $id);  // "si" indicates string and integer types

        // Execute the statement and check if the update was successful
        if ($stmt->execute()) {
            // Return a success message if the category was updated
            echo json_encode(["message" => "Category updated successfully!"]);
        } else {
            // Return an error message if the update failed
            echo json_encode(["message" => "Failed to update category. Please try again later."]);
        }
        // Close the statement
        $stmt->close();
    } else {
        // Return an error message if the statement could not be prepared
        echo json_encode(["message" => "Failed to prepare the query. Error: " . $conn->error]);
    }
} else {
    // Return an error message if the necessary data is missing
    echo json_encode(["message" => "Invalid request. Missing data."]);
}

// Close the database connection
$conn->close();
?>
