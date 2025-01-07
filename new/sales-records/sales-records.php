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
        case 'createSalesRecord':
            echo json_encode(createSalesRecord($getData, $crntUsr));
            break;
        case 'updateSalesRecord':
            echo json_encode(updateSalesRecord($getData));
            break;
        case 'deleteSalesRecord':
            echo json_encode(deleteSalesRecord($getData, $crntUsr));
            break;
        case 'getListOfAllSalesRecords':
            echo json_encode(getListOfAllSalesRecords($crntUsr));
            break;
        case 'getASalesRecord':
            echo json_encode(getASalesRecord($getData));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}


function createSalesRecord($data, $crntUsr){
    global $conn;

    $cust_id = $data['cust_id'] ?? '';
    $prod_id = $data['prod_id'] ?? '';
    $prod_uniq_no = $data['prod_uniq_no'] ?? '';
    $bill_no = $data['bill_no'] ?? '';
    $warnt_period = $data['warnt_period'] ?? '';
    $sale_note = $data['sale_note'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO sales_records (
            cust_id, prod_id, prod_uniq_no, bill_no, bill_date, warnt_period, salesperson_id, sale_note, created_by, updated_by, is_deleted
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $isDeleted = 0;
    $billDate = date('Y-m-d');
    $profDoc = 0; 
    $salesperson_id = $crntUsr;

    $stmt->bind_param("sssssssssss", 
    $cust_id, $prod_id, $prod_uniq_no, $bill_no, $billDate, $warnt_period, $salesperson_id, $sale_note, $crntUsr, $crntUsr, $isDeleted
);

    $stmt->execute();

    if ($stmt->affected_rows) {
        return ["message" => "Sales Record created successfully"];
    } else {
        return ["error" => "Failed to create Sales Record"];
    }
}

function updateSalesRecord($data)
{
    global $conn;

    // Required fields
    $id = $data['id'] ?? null;
    if (!$id) {
        return ["error" => "Sales Record ID is required"];
    }

    // Optional fields
    $cust_id = $data['cust_id'] ?? '';
    $prod_id = $data['prod_id'] ?? '';
    $prod_uniq_no = $data['prod_uniq_no'] ?? '';
    $bill_no = $data['bill_no'] ?? '';
    $warnt_period = $data['warnt_period'] ?? '';
    $salesperson_id = $data['salesperson_id'] ?? '';
    $sale_note = $data['sale_note'] ?? '';
    $updated_by = $data['updated_by'] ?? '';

    // Prepare SQL query
    $bill_date = $data['bill_date'] ?? date('Y-m-d');
    $updatedAt = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("
        UPDATE sales_records SET
            cust_id = ?, prod_id = ?, prod_uniq_no = ?, bill_no = ?, bill_date = ?, warnt_period = ?, salesperson_id = ?, sale_note = ?, updated_by = ?, updated_at = ?
        WHERE id = ?
    ");

    // Bind parameters
    $stmt->bind_param("ssssssssssi", 
        $cust_id, $prod_id, $prod_uniq_no, $bill_no, $bill_date, $warnt_period, $salesperson_id, $sale_note, $updated_by, $updatedAt, $id
    );

    // Execute query
    $stmt->execute();

    // Check result
    if ($stmt->affected_rows) {
        return ["message" => "Sales Record updated successfully"];
    } else {
        return ["error" => "Failed to update Sales Record or no changes made"];
    }
}

function deleteSalesRecord($data, $crntUsr)
{
global $conn;
$id = $data['id'] ?? null;

if (!$id) {
    return ["error" => "Sales Record ID is required"];
}

$stmt = $conn->prepare("UPDATE sales_records SET is_deleted = ?, updated_by = ?, updated_at = ? WHERE id = ?");

$isDeleted = 1;
$updatedAt = date('Y-m-d H:i:s');
$stmt->bind_param("issi", $isDeleted, $crntUsr, $updatedAt, $id);

$stmt->execute();

if ($stmt->affected_rows) {
    return ["message" => "Sales Record deleted successfully"];
} else {
    return ["error" => "Failed to delete Sales Record or Sales Record not found"];
}
}
function getListOfAllSalesRecords($crntUsr)
{
global $conn;
try {
    $stmt = $conn->prepare("SELECT * FROM sales_records WHERE is_deleted = ?");
    $stmt->bind_param("i", $isDeleted);
    $isDeleted = 0;
    $stmt->execute();

    $result = $stmt->get_result();
    $salesRecords = $result->fetch_all(MYSQLI_ASSOC);

    return [
        'data' => $salesRecords,
        'totalCount' => count($salesRecords),
    ];
} catch (Exception $e) {
    return [
        'error' => true,
        'message' => 'Error fetching Sales Records: ' . $e->getMessage(),
    ];
}
}
function getASalesRecord($data)
{
global $conn;
$id = $data['id'] ?? null;

if (!$id) {
    return ["error" => "Sales Record ID is required"];
}

$stmt = $conn->prepare("SELECT * FROM sales_records WHERE id = ? AND is_deleted = ?");
$stmt->bind_param("ii", $id, $isDeleted);
$isDeleted = 0;
$stmt->execute();

$result = $stmt->get_result();
$salesRecord = $result->fetch_assoc();

if ($salesRecord) {
    return $salesRecord;
} else {
    return ["error" => "Sales Record not found"];
}
}
?>