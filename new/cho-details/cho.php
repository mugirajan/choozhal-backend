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
        case 'createCHO':
            echo json_encode(createCHO($getData, $crntUsr));
            break;
        case 'updateCHO':
            echo json_encode(updateCHO($getData));
            break;
        case 'deleteCHO':
            echo json_encode(deleteCHO($getData, $crntUsr));
            break;
        case 'getListOfAllCHOs':
            echo json_encode(getListOfAllCHOs($crntUsr));
            break;
        case 'getACHO':
            echo json_encode(getACHO($getData));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}
function createCHO($data, $crntUsr)
{
    global $conn;

    // Extract data with default values
    $c_name = $data['c_name'] ?? '';
    $c_phone = $data['c_phone'] ?? '';
    $c_email = $data['c_email'] ?? '';
    $c_addrs = $data['c_addrs'] ?? '';
    $pin = $data['pin'] ?? '';
    $cho_owner_usr_id = $data['cho_owner_usr_id'] ?? '';

    // SQL query with placeholders
    $stmt = $conn->prepare("
        INSERT INTO cho_details (
            id, c_name, c_phone, c_email, c_addrs, pin, cho_owner_usr_id, is_deleted
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Bind values to the prepared statement
    $id = uniqid(); // Generate a unique ID
    $isDeleted = 0;
    $stmt->bind_param("sssssssi", 
        $id, $c_name, $c_phone, $c_email, $c_addrs, $pin, $cho_owner_usr_id, $isDeleted
    );

    // Execute query
    $stmt->execute();

    // Check if the insertion was successful
    if ($stmt->affected_rows) {
        return ["message" => "CHO created successfully"];
    } else {
        return ["error" => "Failed to create CHO"];
    }
}

function updateCHO($data)
{
    global $conn;

    $id = $data['id'] ?? 0;
    $c_name = $data['c_name'] ?? '';
    $c_phone = $data['c_phone'] ?? '';
    $c_email = $data['c_email'] ?? '';
    $c_addrs = $data['c_addrs'] ?? '';
    $pin = $data['pin'] ?? '';
    $cho_owner_usr_id = $data['cho_owner_usr_id'] ?? '';

    $stmt = $conn->prepare("
        UPDATE cho_details 
        SET c_name = ?, c_phone = ?, c_email = ?, c_addrs = ?, pin = ?, cho_owner_usr_id = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ssssssi", 
        $c_name, $c_phone, $c_email, $c_addrs, $pin, $cho_owner_usr_id, $id
    );

    $stmt->execute();

    if ($stmt->affected_rows) {
        return ["message" => "CHO updated successfully"];
    } else {
        return ["error" => "Failed to update CHO"];
    }
}
function deleteCHO($data, $crntUsr)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "CHO ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("UPDATE cho_details SET is_deleted = ? WHERE id = ?");

    // Bind values to the prepared statement
    $isDeleted = true;
    $stmt->bind_param("ii", $isDeleted, $id);

    // Execute query
    $stmt->execute();

    // Check if the deletion was successful
    if ($stmt->affected_rows) {
        return ["message" => "CHO deleted successfully"];
    } else {
        return ["error" => "Failed to delete CHO or CHO not found"];
    }
}
    
    function getListOfAllCHOs($crntUsr)
    {
        global $conn;
    
        try {
            // Fetch CHOs
            $stmt = $conn->prepare("SELECT * FROM cho_details WHERE is_deleted = ?");
            $stmt->bind_param("i", $isDeleted);
            $isDeleted = 0;
            $stmt->execute();
    
            $result = $stmt->get_result();
            $chos = $result->fetch_all(MYSQLI_ASSOC);
    
            return [
                'data' => $chos,
                'totalCount' => count($chos),
            ];
        } catch (Exception $e) {
            // Handle errors gracefully
            return [
                'error' => true,
                'message' => 'Error fetching CHOs: ' . $e->getMessage(),
            ];
        }
    }
    
    function getACHO($data)
    {
        global $conn;
    
        $id = $data['id'] ?? null;
    
        if (!$id) {
            return ["error" => "CHO ID is required"];
        }
    
        // SQL query with placeholders
        $stmt = $conn->prepare("SELECT * FROM cho_details WHERE id = ? AND is_deleted = ?");
        $stmt->bind_param("ii", $id, $isDeleted);
        $isDeleted = 0;
        $stmt->execute();
    
        // Fetch the CHO data
        $result = $stmt->get_result();
        $cho = $result->fetch_assoc();
    
        if ($cho) {
            return $cho;
        } else {
            return ["error" => "CHO not found"];
        }
    }
    ?>