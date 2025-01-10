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
        case 'createManagement':
            echo json_encode(createManagement($getData, $crntUsr));
            break;
        case 'updateManagement':
            echo json_encode(updateManagement($getData));
            break;
        case 'deleteManagement':
            echo json_encode(deleteManagement($getData, $crntUsr));
            break;
        case 'getListOfAllManagements':
            echo json_encode(getListOfAllManagements($crntUsr));
            break;
        case 'getAManagement':
            echo json_encode(getAManagement($getData));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}

function createManagement($data, $crntUsr)
{
    global $conn;

    $m_name = $data['m_name'] ?? '';
    $m_phone = $data['m_phone'] ?? '';
    $m_email = $data['m_email'] ?? '';
    $m_addrs = $data['m_addrs'] ?? '';
    $pin = $data['pin'] ?? '';
    $m_owner_usr_id = $data['m_owner_usr_id'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO management_details (
            id, m_name, m_phone, m_email, m_addrs, pin, m_owner_usr_id, is_deleted, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $id = uniqid();
    $isDeleted = 0;
    $is_active = 1;
    $stmt->bind_param("sssssssis", 
        $id, $m_name, $m_phone, $m_email, $m_addrs, $pin, $m_owner_usr_id, $isDeleted, $is_active
    );

    $stmt->execute();

    if ($stmt->affected_rows) {
        return ["message" => "Management created successfully"];
    } else {
        return ["error" => "Failed to create Management"];
    }
}

function updateManagement($data)
{
    global $conn;

    $id = $data['id'] ?? null;
    $m_name = $data['m_name'] ?? '';
    $m_phone = $data['m_phone'] ?? '';
    $m_email = $data['m_email'] ?? '';
    $m_addrs = $data['m_addrs'] ?? '';
    $pin = $data['pin'] ?? '';
    $m_owner_usr_id = $data['m_owner_usr_id'] ?? '';

    if (!$id) {
        return ["error" => "Management ID is required"];
    }

    $stmt = $conn->prepare("
        UPDATE management_details SET
            m_name = ?, m_phone = ?, m_email = ?, m_addrs = ?, pin = ?, m_owner_usr_id = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ssssssi", 
        $m_name, $m_phone, $m_email, $m_addrs, $pin, $m_owner_usr_id, $id
    );

    $stmt->execute();

    if ($stmt->affected_rows) {
        return ["message" => "Management updated successfully"];
    } else {
        return ["error" => "Failed to update Management or no changes made"];
    }
}

function deleteManagement($data, $crntUsr)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Management ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("UPDATE management_details SET is_deleted = ? WHERE id = ?");

    // Bind values to the prepared statement
    $isDeleted = 1;
    $stmt->bind_param("ii", $isDeleted, $id);

    // Execute query
    $stmt->execute();

    // Check if the deletion was successful
    if ($stmt->affected_rows) {
        return ["message" => "Management deleted successfully"];
    } else {
        return ["error" => "Failed to delete Management or Management not found"];
    }
}

    function getListOfAllManagements($crntUsr)
    {
        global $conn;
    
        try {
            // Fetch Managements
            $stmt = $conn->prepare("SELECT * FROM management_details WHERE is_deleted = ?");
            $stmt->bind_param("i", $isDeleted);
            $isDeleted = 0;
            $stmt->execute();
    
            $result = $stmt->get_result();
            $managements = $result->fetch_all(MYSQLI_ASSOC);
    
            return [
                'data' => $managements,
                'totalCount' => count($managements),
            ];
        } catch (Exception $e) {
            // Handle errors gracefully
            return [
                'error' => true,
                'message' => 'Error fetching Managements: ' . $e->getMessage(),
            ];
        }
    }
    
    function getAManagement($data)
    {
        global $conn;
    
        $id = $data['id'] ?? null;
    
        if (!$id) {
            return ["error" => "Management ID is required"];
        }
    
        // SQL query with placeholders
        $stmt = $conn->prepare("SELECT * FROM management_details WHERE id = ? AND is_deleted = ?");
        $stmt->bind_param("ii", $id, $isDeleted);
        $isDeleted = 0;
        $stmt->execute();
    
        // Fetch the Management data
        $result = $stmt->get_result();
        $management = $result->fetch_assoc();
    
        if ($management) {
            return $management;
        } else {
            return ["error" => "Management not found"];
        }
    }
    ?>