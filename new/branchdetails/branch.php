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
        case 'createBranch':
            echo json_encode(createBranch($getData, $crntUsr));
            break;
        case 'updateBranch':
            echo json_encode(updateBranch($getData));
            break;
        case 'deleteBranch':
            echo json_encode(deleteBranch($getData, $crntUsr));
            break;
        case 'getListOfAllBranches':
            echo json_encode(getListOfAllBranches($crntUsr));
            break;
        case 'getABranch':
            echo json_encode(getABranch($getData));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}

function createBranch($data, $crntUsr) {
    global $conn;

    $b_name = $data['b_name'] ?? '';
    $b_phone = $data['b_phone'] ?? '';
    $b_email = $data['b_email'] ?? '';
    $b_addrs = $data['b_addrs'] ?? '';
    $pin = $data['pin'] ?? '';
    $region_id = $data['region_id'] ?? '';
    $b_owner_usr_id = $data['b_owner_usr_id'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO branch_details (
            b_name, b_phone, b_email, b_addrs, pin, region_id, b_owner_usr_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssssss", 
        $b_name, $b_phone, $b_email, $b_addrs, $pin, $region_id, $b_owner_usr_id
    );

    $stmt->execute();

    if ($stmt->affected_rows) {
        return ["message" => "Branch created successfully"];
    } else {
        return ["error" => "Failed to create branch"];
    }
}

function updateBranch($data)
{
    global $conn;

    $id = $data['id'] ?? null;
    $b_name = $data['b_name'] ?? '';
    $b_phone = $data['b_phone'] ?? '';
    $b_email = $data['b_email'] ?? '';
    $b_addrs = $data['b_addrs'] ?? '';
    $pin = $data['pin'] ?? '';
    $region_id = $data['region_id'] ?? '';
    $b_owner_usr_id = $data['b_owner_usr_id'] ?? '';

    if (!$id) {
        return ["error" => "Branch ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("
        UPDATE branch_details SET
            b_name = ?, b_phone = ?, b_email = ?, b_addrs = ?, pin = ?, region_id = ?, b_owner_usr_id = ?
        WHERE id = ?
    ");

    // Bind values to the prepared statement
    $stmt->bind_param("sssssssi", 
        $b_name, $b_phone, $b_email, $b_addrs, $pin, $region_id, $b_owner_usr_id, $id
    );

    // Execute the query
    $stmt->execute();

    // Check if the update was successful
    if ($stmt->affected_rows) {
        return ["message" => "Branch updated successfully"];
    } else {
        return ["error" => "Failed to update branch or no changes made"];
    }
}

function deleteBranch($data, $crntUsr)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Branch ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("UPDATE branch_details SET is_deleted = ? WHERE id = ?");

    // Bind values to the prepared statement
    $isDeleted = true;
    $stmt->bind_param("ii", $isDeleted, $id);

    // Execute the query
    $stmt->execute();

    // Check if the deletion was successful
    if ($stmt->affected_rows) {
        return ["message" => "Branch deleted successfully"];
    } else {
        return ["error" => "Failed to delete branch or branch not found"];
    }
}

function getListOfAllBranches($crntUsr)
{
    global $conn;

    try {
        // Fetch branches
        $stmt = $conn->prepare("SELECT * FROM branch_details WHERE is_deleted = ?");
        $stmt->bind_param("i", $isDeleted);
        $isDeleted = 0;
        $stmt->execute();

        $result = $stmt->get_result();
        $branches = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'data' => $branches,
            'totalCount' => count($branches),
        ];
    } catch (Exception $e) {
        // Handle errors gracefully
        return [
            'error' => true,
            'message' => 'Error fetching branches: ' . $e->getMessage(),
        ];
    }
}

function getABranch($data)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Branch ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("SELECT * FROM branch_details WHERE id = ? AND is_deleted = ?");
    $stmt->bind_param("ii", $id, $isDeleted);
    $isDeleted = 0;
    $stmt->execute();

    // Fetch the branch data
    $result = $stmt->get_result();
    $branch = $result->fetch_assoc();

    if ($branch) {
        return $branch;
    } else {
        return ["error" => "Branch not found"];
    }
}
?>