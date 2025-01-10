<?php

require_once "../connect/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);

error_log('Received payload: ' . json_encode($payload));

if (!isset($payload['target'], $payload['data'], $payload['crntUsr'])) {
    error_log('Missing required properties in payload');
    echo json_encode([
        "success" => false,
        "error" => "Invalid request. Missing 'target', 'data', or 'crntUsr' in payload."
    ]);
    exit;
}

    $method = $payload['target'];
    $getData = $payload['data'];
    $crntUsr = $payload['crntUsr'];

    switch ($method) {
        case 'createTicket':
            echo json_encode(createTicket($getData, $crntUsr));
            break;
        case 'updateTicket':
            echo json_encode(updateTicket($getData, $crntUsr));
            break;
        case 'deleteTicket':
            echo json_encode(deleteTicket($getData, $crntUsr));
            break;
        case 'getListOfAllTickets':
            echo json_encode(getListOfAllTickets($crntUsr));
            break;
        case 'getTicketChatMessages':
            echo json_encode(getTicketChatMessages($getData));
            break;
        case 'getATicket':
            echo json_encode(getATicket($getData));
            break;
        case 'createTicketChatMessage':
            echo json_encode(createTicketChatMessage($getData, $crntUsr));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}

function createTicket($data, $crntUsr)
{
    global $pdo;

    $data = json_decode($data, true);

    $requiredFields = ['sales_id', 'cust_id', 'salesperson_id', 'type', 't_desc', 'status'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            return [
                'error' => "Missing required field: $field"
            ];
        }
    }

    $sales_id = $data['sales_id'];
    $cust_id = $data['cust_id'];
    $salesperson_id = $data['salesperson_id'];
    $type = $data['type'];
    $t_desc = $data['t_desc'];
    $status = $data['status'];
    $created_by = $crntUsr;

    $stmt = $pdo->prepare("
        INSERT INTO ticket_details (
            sales_id, cust_id, salesperson_id, type, t_desc, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $sales_id,
        $cust_id,
        $salesperson_id,
        $type,
        $t_desc,
        $status,
        $created_by
    ]);

    if ($stmt->rowCount()) {
        return [
            'success' => true,
            'message' => "Ticket created successfully"
        ];
    } else {
        return [
            'error' => "Failed to create ticket"
        ];
    }
}

function updateTicket($data, $crntUsr)
{
    global $pdo;

    $id = $data['id'];

    if (!$id) {
        return ["error" => "Ticket ID is required"];
    }

    $fields = [];
    $values = [];

    foreach ($data as $key => $value) {
        if ($key != 'id') {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
    }

    $values[] = $crntUsr;
    $values[] = $id;

    $fields[] = "updated_by = ?";
    $fields[] = "updated_at = NOW()";

    $stmt = $pdo->prepare("
        UPDATE ticket_details 
        SET " . implode(', ', $fields) . "
        WHERE id = ?
    ");

    $stmt->execute($values);

    if ($stmt->rowCount()) {
        return ["success" => true, "message" => "Ticket updated successfully"];
    } else {
        return ["error" => "Failed to update ticket or no changes made"];
    }
}


function deleteTicket($data, $crntUsr)
{
    global $pdo;

    $data = json_decode($data, true);

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Ticket ID is required"];
    }

    $stmt = $pdo->prepare("UPDATE ticket_details SET is_deleted = true, updated_by = ? WHERE id = ?");
    $stmt->execute([$crntUsr, $id]);

    if ($stmt->rowCount()) {
        return ["message" => "Ticket deleted successfully"];
    } else {
        return ["error" => "Failed to delete ticket or ticket not found"];
    }
}

function getListOfAllTickets($crntUsr)
{
    global $pdo;

    try {
        $adminId = $crntUsr;

        $adminQuery = "SELECT * FROM usr_details WHERE id = '$adminId'";
        $adminResult = $pdo->query($adminQuery);
        
        $adminRow = $adminResult->fetch(PDO::FETCH_ASSOC);

        if ($adminRow) {
            $adminRole = $adminRow['usr_role'];
            $adminRegion = $adminRow['region'];
            $adminBranch = $adminRow['branch'];

            $filterQuery = '';

            if ($adminRole == 'SuperAdmin') {
                $filterQuery = '';
            } elseif ($adminRole == 'HeadOffice') {
                $filterQuery = '';
            } elseif ($adminRole == 'GeneralManager') {
                $filterQuery = '';
            } elseif ($adminRole == 'RegionAdmin') {
                $filterQuery = "";
            } elseif ($adminRole == 'BranchAdmin') {
                $filterQuery = "";
            } elseif ($adminRole == 'SalesPerson') {
                $filterQuery = "WHERE td.salesperson_id = '$adminId'";
            }

            $query = "SELECT 
            td.*,
            c.first_name,
            c.last_name,
            c.email,
            c.mobile_no,
            c.profile_pic,
            c.address,
            c.area,
            c.city,
            c.district,
            c.state,
            c.pincode,

            sr.prod_uniq_no,
            sr.bill_no,
            sr.bill_date,
            sr.warnt_period,
            
            p.p_name,
            s.usr_fname
          FROM 
            ticket_details td
          LEFT JOIN 
            customers c
          ON 
            td.cust_id = c.id
          LEFT JOIN 
            sales_records sr
          ON 
            td.sales_id = sr.id
          LEFT JOIN 
            products p
          ON 
            sr.prod_id = p.id
          LEFT JOIN 
            usr_details s
          ON 
            td.salesperson_id = s.id
          $filterQuery";

            $result = $pdo->query($query);

            $tickets = [];
            if ($result && $result->rowCount() > 0) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $tickets[] = $row;
                }
                return [
                    'data' => $tickets,
                    'totalCount' => count($tickets),
                ];
            } else {
                return [
                    'error' => "No tickets found.",
                ];
            }
        } else {
            return [
                'error' => "Invalid admin ID.",
            ];
        }
    } catch (PDOException $e) {
        return [
            'error' => true,
            'message' => 'Error fetching sales records: ' . $e->getMessage(),
        ];
    }
}

function getATicket($data)
{
    global $pdo;

    $id = $data['id'] ?? null;
    if (!$id) {
        return ["error" => "Ticket ID is required"];
    }

    $stmt = $pdo->prepare("SELECT * FROM ticket_details WHERE id = ?");
    $stmt->execute([$id]);

    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($ticket) {
        return $ticket;
    } else {
        return ["error" => "Ticket not found"];
    }
}

function getTicketChatMessages($data)
{
    global $pdo;

    $ticketId = $data['ticketId'] ?? null;

    if (!$ticketId) {
        return ["error" => "Ticket ID is required"];
    }

    try {
        $query = "SELECT tc.*, ud.usr_fname AS user_name, c.first_name AS customer_name
        FROM ticket_chats tc
        LEFT JOIN usr_details ud ON tc.usr_id = ud.id
        LEFT JOIN ticket_details td ON tc.ticket_id = td.id
        LEFT JOIN customers c ON td.cust_id = c.id
        WHERE tc.ticket_id = :ticketId
        ORDER BY tc.id ASC";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':ticketId', $ticketId);
        $stmt->execute();
        
        
        $chatMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($chatMessages)) {
            return [
                'message' => 'No data found for the specified ticket ID.',
                'data' => [],
                'totalCount' => 0
            ];
        } else {
            return [
                'data' => $chatMessages,
                'totalCount' => count($chatMessages),
            ];
        }
    } catch (PDOException $e) {
        return [
            'error' => true,
            'message' => 'Error fetching ticket chat messages: ' . $e->getMessage(),
        ];
    }
}

function createTicketChatMessage($data, $crntUsr)
{
    global $conn;

    $ticketId = $data['ticketId'] ?? '';
    $message = $data['message'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO ticket_chats (
            ticket_id, message, is_cust, usr_id, created_by, is_active
        ) VALUES (?, ?, 0, ?, ?, 1)
    ");

    $stmt->bind_param("isii", 
        $ticketId, $message, $crntUsr, $crntUsr
    );

    $stmt->execute();

    if ($stmt->affected_rows) {
        return ["message" => "Chat message created successfully"];
    } else {
        return ["error" => "Failed to create chat message"];
    }
}

?>