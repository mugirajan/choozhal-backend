<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once "../../db.php";

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($data['target'], $data['data'], $data['crntUsr'])) {
        echo json_encode([
            "success" => false,
            "error" => "Invalid request. Missing 'target', 'data', or 'crntUsr' in payload."
        ]);
        exit;
    }

    $method = $data['target'];
    $getData = $data['data'];
    $crntUsr = $data['crntUsr'];

    switch ($method) {
        case 'createCategory':
            echo json_encode(createCategory($getData, $crntUsr));
            break;
        case 'updateCategory':
            echo json_encode(updateCategory($getData));
            break;
        case 'deleteCategory':
            echo json_encode(deleteCategory($getData, $crntUsr));
            break;
        case 'getListOfAllCategories':
            echo json_encode(getListOfAllCategories($crntUsr));
            break;
        case 'getACategory':
            echo json_encode(getACategory($getData));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}

function createCategory($data, $crntUsr)
{
    global $conn;

    // Extract data with default values
    $cat_name = $data['cat_name'] ?? '';

    // SQL query with placeholders
    $stmt = $conn->prepare("
        INSERT INTO category (
            cat_name, created_at
        ) VALUES (?, NOW())
    ");

    // Execute query with bound values
    $stmt->execute([
        $cat_name
    ]);

    // Check if the insertion was successful
    if ($stmt->affected_rows) {
        return ["message" => "Category created successfully"];
    } else {
        return ["error" => "Failed to create category"];
    }
}

function updateCategory($data)
{
    global $conn;

    $id = $data['id'] ?? null;
    $cat_name = $data['cat_name'] ?? '';

    if (!$id) {
        return ["error" => "Category ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("
        UPDATE category SET
            cat_name = ?
        WHERE id = ?
    ");

    // Execute query with bound values
    $stmt->execute([
        $cat_name, $id
    ]);

    // Check if the update was successful
    if ($stmt->affected_rows) {
        return ["message" => "Category updated successfully"];
    } else {
        return ["error" => "Failed to update category or no changes made"];
    }
}
function deleteCategory($data, $crntUsr)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Category ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("UPDATE category SET is_deleted = ?, deleted_date = NOW() WHERE id = ?");

    // Execute query with bound values
    $stmt->execute([true, $id]);

    // Check if the deletion was successful
    if ($stmt->affected_rows) {
        return ["message" => "Category deleted successfully"];
    } else {
        return ["error" => "Failed to delete category or category not found"];
    }
}

function getListOfAllCategories($crntUsr)
{
    global $conn;

    try {
        // Fetch categories
        $stmt = $conn->prepare("SELECT * FROM category WHERE is_deleted = ?");
        $stmt->bind_param("i", $isDeleted);
        $isDeleted = 0;
        $stmt->execute();

        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'data' => $categories,
            'totalCount' => count($categories),
        ];
    } catch (Exception $e) {
        // Handle errors gracefully
        return [
            'error' => true,
            'message' => 'Error fetching categories: ' . $e->getMessage(),
        ];
    }
}

function getACategory($data)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Category ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("SELECT * FROM category WHERE id = ? AND is_deleted = ?");
    $stmt->bind_param("ii", $id, $isDeleted);
    $isDeleted = 0;
    $stmt->execute();

    // Fetch the category data
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();

    if ($category) {
        return $category;
    } else {
        return ["error" => "Category not found"];
    }
}
?>