<?php

require_once '../connect/db.php';

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
        case 'createRegion':
            echo json_encode(createRegion($getData, $crntUsr));
            break;
        case 'updateRegion':
            echo json_encode(updateRegion($getData));
            break;
        case 'deleteRegion':
            echo json_encode(deleteRegion($getData, $crntUsr));
            break;
        case 'getListOfAllRegions':
            echo json_encode(getListOfAllRegions($crntUsr));
            break;
        case 'getARegion':
            echo json_encode(getARegion($getData));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}

function createRegion($data, $crntUsr)
{
    global $conn;

    $r_name = $data['r_name'] ?? '';
    $r_phone = $data['r_phone'] ?? '';
    $r_email = $data['r_email'] ?? '';
    $r_addrs = $data['r_addrs'] ?? '';
    $pin = (int) $data['pin'] ?? 0;
    $cho_id = (int) $data['cho_id'] ?? 0;
    $r_owner_usr_id = (int) $data['r_owner_usr_id'] ?? 0;

    $stmt = $conn->prepare("
        INSERT INTO region_details (
            r_name, r_phone, r_email, r_addrs, pin, cho_id, r_owner_usr_id, is_deleted, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $isDeleted = 0;
    $is_active = 1;
    $stmt->bind_param("ssssssiss", 
        $r_name, $r_phone, $r_email, $r_addrs, $pin, $cho_id, $r_owner_usr_id, $isDeleted, $is_active
    );

    $stmt->execute();

    if ($stmt->affected_rows) {
        return ["message" => "Region created successfully"];
    } else {
        return ["error" => "Failed to create Region"];
    }
}

function updateRegion($data)
{
    global $conn;

    $id = $data['id'] ?? null;
    $r_name = $data['r_name'] ?? '';
    $r_phone = $data['r_phone'] ?? '';
    $r_email = $data['r_email'] ?? '';
    $r_addrs = $data['r_addrs'] ?? '';
    $pin = (int) $data['pin'] ?? 0;
    $cho_id = (int) $data['cho_id'] ?? 0;
    $r_owner_usr_id = (int) $data['r_owner_usr_id'] ?? 0;

    if (!$id) {
        return ["error" => "Region ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("
        UPDATE region_details SET
            r_name = ?, r_phone = ?, r_email = ?, r_addrs = ?, pin = ?, cho_id = ?, r_owner_usr_id = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ssssssii", 
    $r_name, $r_phone, $r_email, $r_addrs, $pin, $cho_id, $r_owner_usr_id, $id
);

    // Execute the query
    $stmt->execute();

    // Check if the update was successful
    if ($stmt->affected_rows) {
        return ["message" => "Region updated successfully"];
    } else {
        return ["error" => "Failed to update Region"];
    }
}

function deleteRegion($data, $crntUsr)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Region ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("UPDATE region_details SET is_deleted = ? WHERE id = ?");

    // Bind values to the prepared statement
    $isDeleted = true;
    $stmt->bind_param("ii", $isDeleted, $id);

    // Execute query
    $stmt->execute();

    // Check if the deletion was successful
    if ($stmt->affected_rows) {
        return ["message" => "Region deleted successfully"];
    } else {
        return ["error" => "Failed to delete Region or Region not found"];
    }
}
    
function getListOfAllRegions($crntUsr)
{
    global $conn;

    try {
        // Fetch Regions
        $isDeleted = 0;
        $stmt = $conn->prepare("SELECT * FROM region_details WHERE is_deleted = ?");
        $stmt->bind_param("i", $isDeleted);

        if (!$stmt->execute()) {
            throw new Exception("Failed to execute query: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Failed to get result: " . $stmt->error);
        }

        $regions = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'data' => $regions,
            'totalCount' => count($regions),
        ];
    } catch (Exception $e) {
        // Handle errors gracefully
        return [
            'error' => true,
            'message' => 'Error fetching Regions: ' . $e->getMessage(),
        ];
    }
}
    
    function getARegion($data)
    {
        global $conn;
    
        $id = $data['id'] ?? null;
    
        if (!$id) {
            return ["error" => "Region ID is required"];
        }
    
        // SQL query with placeholders
        $stmt = $conn->prepare("SELECT * FROM region_details WHERE id = ? AND is_deleted = ?");
        $stmt->bind_param("ii", $id, $isDeleted);
        $isDeleted = 0;
        $stmt->execute();
    
        // Fetch the Region data
        $result = $stmt->get_result();
        $region = $result->fetch_assoc();
    
        if ($region) {
            return $region;
        } else {
            return ["error" => "Region not found"];
        }
    }
?>