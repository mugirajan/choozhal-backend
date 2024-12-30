<?php

require_once '../../db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

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
        case 'createProduct':
            echo json_encode(createProduct($getData, $crntUsr));
            break;
        case 'updateProduct':
            echo json_encode(updateProduct($getData));
            break;
        case 'deleteProduct':
            echo json_encode(deleteProduct($getData, $crntUsr));
            break;
        case 'getListOfAllProducts':
            echo json_encode(getListOfAllProducts($crntUsr));
            break;
        case 'getAProduct':
            echo json_encode(getAProduct($getData));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}

function createProduct($data, $crntUsr)
{
    global $conn;

    // Extract data with default values
    $p_name = $data['p_name'] ?? '';
    $p_modal_no = $data['p_modal_no'] ?? '';
    $p_category = $data['p_category'] ?? '';
    $p_desc = $data['p_desc'] ?? '';
    $p_manual = $data['p_manual'] ?? '';
    $p_img = $data['p_img'] ?? '';
    $p_height = $data['p_height'] ?? '';
    $p_weight = $data['p_weight'] ?? '';

    // SQL query with placeholders
    $stmt = $conn->prepare("
        INSERT INTO products (
            p_name, p_modal_no, p_category, p_desc, p_manual, p_img, p_height, p_weight
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Execute query with bound values
    $stmt->execute([
        $p_name, $p_modal_no, $p_category, $p_desc, $p_manual, $p_img, $p_height, $p_weight
    ]);

    // Check if the insertion was successful
    if ($stmt->affected_rows) {
        return ["message" => "Product created successfully"];
    } else {
        return ["error" => "Failed to create product"];
    }
}

function updateProduct($data)
{
    global $conn;

    $id = $data['id'] ?? null;
    $p_name = $data['p_name'] ?? '';
    $p_modal_no = $data['p_modal_no'] ?? '';
    $p_category = $data['p_category'] ?? '';
    $p_desc = $data['p_desc'] ?? '';
    $p_manual = $data['p_manual'] ?? '';
    $p_img = $data['p_img'] ?? '';
    $p_height = $data['p_height'] ?? '';
    $p_weight = $data['p_weight'] ?? '';

    if (!$id) {
        return ["error" => "Product ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("
        UPDATE products SET
            p_name = ?, p_modal_no = ?, p_category = ?, p_desc = ?, p_manual = ?, p_img = ?, p_height = ?, p_weight = ?
        WHERE id = ?
    ");

    // Execute query with bound values
    $stmt->execute([
        $p_name, $p_modal_no, $p_category, $p_desc, $p_manual, $p_img, $p_height, $p_weight, $id
    ]);

    // Check if the update was successful
    if ($stmt->affected_rows) {
        return ["message" => "Product updated successfully"];
    } else {
        return ["error" => "Failed to update product or no changes made"];
    }
}
function deleteProduct($data, $crntUsr)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Product ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("UPDATE products SET is_deleted = ? WHERE id = ?");

    // Execute query with bound values
    $stmt->execute([true, $id]);

    // Check if the deletion was successful
    if ($stmt->affected_rows) {
        return ["message" => "Product deleted successfully"];
    } else {
        return ["error" => "Failed to delete product or product not found"];
    }
}

function getListOfAllProducts($crntUsr)
{
    global $conn;

    try {
        // Fetch products
        $stmt = $conn->prepare("SELECT * FROM products WHERE is_deleted = ?");
        $stmt->bind_param("i", $isDeleted);
        $isDeleted = 0;
        $stmt->execute();

        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'data' => $products,
            'totalCount' => count($products),
        ];
    } catch (Exception $e) {
        // Handle errors gracefully
        return [
            'error' => true,
            'message' => 'Error fetching products: ' . $e->getMessage(),
        ];
    }
}

function getAProduct($data)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Product ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_deleted = ?");
    $stmt->bind_param("ii", $id, $isDeleted);
    $isDeleted = 0;
    $stmt->execute();

    // Fetch the product data
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        return $product;
    } else {
        return ["error" => "Product not found"];
    }
}
?>