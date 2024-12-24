<?php

require_once '../db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);


if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
    exit;
}

$ticket_id = isset($data['ticket_id']) ? $data['ticket_id'] : null;
$assignedTo = isset($data['assigned_to']) ? $data['assigned_to'] : null;
$updated_by = isset($data['updated_by']) ? $data['updated_by'] : null;

if (!$ticket_id || !$assignedTo || !$updated_by) {
    echo json_encode(["status" => "error", "message" => "Missing ticket_id, assignedTo or updated_by"]);
    exit;
}

if ($ticket_id && $assignedTo && $updated_by) {
    $query = "UPDATE tickets SET salesperson_id = ?, updated_by = ? WHERE ticket_id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssi", $assignedTo, $updated_by, $ticket_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Ticket assigned successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to assign ticket"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Database query failed"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing ticket_id, assignedTo or updated_by"]);
}

$conn->close();

?>