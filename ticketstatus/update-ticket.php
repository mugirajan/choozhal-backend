<?php

require_once '../db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$ticket_id = isset($data['ticket_id']) ? $data['ticket_id'] : null;
$status = isset($data['status']) ? $data['status'] : null;
$updated_by = isset($data['updated_by']) ? $data['updated_by'] : null;

if (!$ticket_id || !$status || !$updated_by) {
    file_put_contents('debug_log.txt', "ticket_id: $ticket_id, status: $status, updated_by: $updated_by\n", FILE_APPEND);
    echo json_encode(["status" => "error", "message" => "Missing ticket_id, status or updated_by"]);
    exit;
}

if ($ticket_id && $status && $updated_by) {
    $query = "UPDATE tickets SET status = ?, updated_by = ? WHERE ticket_id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssi", $status, $updated_by, $ticket_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Ticket status updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update ticket status"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Database query failed"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing ticket_id, status or updated_by"]);
}

$conn->close();

?>