<?php

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
        case 'createSalesRecord':
            echo json_encode(createSalesRecord($getData, $crntUsr));
            break;
        case 'updateSalesRecord':
            echo json_encode(updateSalesRecord($getData, $crntUsr));
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


function getListOfAllSalesRecords($crntUsr)
{
    global $pdo;
    try {
        $adminId = $crntUsr;
        $query = "SELECT * FROM usr_details WHERE id = '$adminId'";
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $adminRole = $result['usr_role'];
            $adminArea = $result['area']; 

            $filterQuery = '';

            if ($adminRole == 'SuperAdmin') {
                $filterQuery = '';
            } elseif ($adminRole == 'HeadOffice') {
                $filterQuery = '';
            } elseif ($adminRole == 'GeneralManager') {
                $filterQuery = '';
            } elseif ($adminRole == 'RegionAdmin') {
                $branchAdminsQuery = "SELECT id FROM usr_details WHERE area = '$adminArea' AND role = 'BranchAdmin'";
                $branchAdminsStmt = $pdo->query($branchAdminsQuery);
                $branchAdminIds = array_column($branchAdminsStmt->fetchAll(PDO::FETCH_ASSOC), 'id');

                $salesPersonsQuery = "SELECT id FROM usr_details WHERE branch IN (SELECT branch FROM usr_details WHERE area = '$adminArea' AND role = 'BranchAdmin') AND role = 'SalesPerson'";
                $salesPersonsStmt = $pdo->query($salesPersonsQuery);
                $salesPersonIds = array_column($salesPersonsStmt->fetchAll(PDO::FETCH_ASSOC), 'id');

                $allIds = array_merge($branchAdminIds, $salesPersonIds);
                $allIdsString = implode(',', array_map('intval', $allIds));

                if (!empty($allIdsString)) {
                    $filterQuery = "WHERE sales_person_id IN ($allIdsString)";
                } else {
                    error_log("No valid BranchAdmin or SalesPerson IDs found for RegionAdmin area: $adminArea");
                    $filterQuery = "WHERE 1=0"; 
                }
            } elseif ($adminRole == 'BranchAdmin') {
                $adminBranch = $result['branch']; 
                $filterQuery = "WHERE sales_person_id IN (SELECT id FROM usr_details WHERE branch = '$adminBranch')";
            } elseif ($adminRole == 'SalesPerson') {
                $filterQuery = "WHERE sales_person_id = '$adminId'";
            }

            $query = "SELECT 
                sales_records.*, 
                customers.first_name AS customer_name, 
                customers.mobile_no AS customer_mobile,
                products.p_name AS product_name 
              FROM 
                sales_records 
              LEFT JOIN 
                customers 
              ON 
                sales_records.cust_id = customers.id 
              LEFT JOIN 
                products 
              ON 
                sales_records.prod_id = products.id 
              $filterQuery";

            $stmt = $pdo->query($query);
            $salesRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $salesRecords,
                'totalCount' => count($salesRecords),
            ];
        } else {
            return [
                'error' => true,
                'message' => 'Invalid admin ID.'
            ];
        }
    } catch (PDOException $e) {
        return [
            'error' => true,
            'message' => 'Error fetching sales records: ' . $e->getMessage(),
        ];
    }
}


function createSalesRecord($data, $crntUsr)
{
    global $pdo;

    $cust_id = $data['cust_id'] ?? '';
    $prod_id = $data['prod_id'] ?? '';
    $prod_uniq_no = $data['prod_uniq_no'] ?? '';
    $bill_no = $data['bill_no'] ?? '';
    $bill_date = $data['bill_date'] ?? '';
    $warnt_period = $data['warnt_period'] ?? '';
    $salesperson_id = $data['salesperson_id'] ?? '';
    $has_tickets = $data['has_tickets'] ?? '';
    $prof_doc = $data['prof_doc'] ?? '';
    $sale_note = $data['sale_note'] ?? '';
    $is_active = isset($data['is_active']) && $data['is_active'] ? 1 : 0;
    $created_by = $crntUsr ?? '';

    $stmt = $pdo->prepare("
        INSERT INTO sales_records (
            cust_id, prod_id, prod_uniq_no, bill_no, bill_date, warnt_period, 
            salesperson_id, has_tickets, prof_doc, sale_note, is_active, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $cust_id, $prod_id, $prod_uniq_no, $bill_no, $bill_date, $warnt_period, 
        $salesperson_id, $has_tickets, $prof_doc, $sale_note, $is_active, $created_by
    ]);

    if ($stmt->rowCount()) {
        return ["message" => "Sales record created successfully"];
    }      else {
        return ["error" => "Failed to create sales record"];
}
}



function updateSalesRecord($data, $crntUsr)
{
    global $pdo;

    $id = $data['id'] ?? null;
    $cust_id = $data['cust_id'] ?? '';
    $prod_id = $data['prod_id'] ?? '';
    $prod_uniq_no = $data['prod_uniq_no'] ?? '';
    $bill_no = $data['bill_no'] ?? '';
    $bill_date = $data['bill_date'] ?? '';
    $warnt_period = $data['warnt_period'] ?? '';
    $salesperson_id = $data['salesperson_id'] ?? '';
    $has_tickets = $data['has_tickets'] ?? '';
    $prof_doc = $data['prof_doc'] ?? '';
    $sale_note = $data['sale_note'] ?? '';
    $is_active = isset($data['is_active']) && $data['is_active'] ? 1 : 0;
    $updated_by = $crntUsr ?? '';

    if (!$id) {
        return ["error" => "Sales record ID is required"];
    }

    $stmt = $pdo->prepare("
        UPDATE sales_records 
        SET 
            cust_id = ?, 
            prod_id = ?, 
            prod_uniq_no = ?, 
            bill_no = ?, 
            bill_date = ?, 
            warnt_period = ?, 
            salesperson_id = ?, 
            has_tickets = ?, 
            prof_doc = ?, 
            sale_note = ?, 
            is_active = ?, 
            updated_by = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $cust_id, 
        $prod_id, 
        $prod_uniq_no, 
        $bill_no, 
        $bill_date, 
        $warnt_period, 
        $salesperson_id, 
        $has_tickets, 
        $prof_doc, 
        $sale_note, 
        $is_active, 
        $updated_by,
        $id
    ]);

    if ($stmt->rowCount()) {
        return ["message" => "Sales record updated successfully"];
    } else {
        return ["error" => "Failed to update sales record or no changes made"];
    }
}


function deleteSalesRecord($data, $crntUsr)
{
    global $pdo;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Sales record ID is required"];
    }

    $stmt = $pdo->prepare("DELETE FROM sales_records WHERE id = ?");

    try {
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        return ["error" => $e->getMessage()];
    }

    if ($stmt->rowCount() > 0) {
        return ["message" => "Sales record deleted successfully"];
    } else {
        $errorInfo = $stmt->errorInfo();
        return ["error" => "Failed to delete sales record: " . $errorInfo[2]];
    }
}

function getASalesRecord($data)
{
    global $pdo;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Sales record ID is required"];
    }

    $stmt = $pdo->prepare("SELECT * FROM sales_records WHERE id = ?");
    $stmt->execute([$id]);

    $salesRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($salesRecord) {
        return $salesRecord;
    } else {
        return ["error" => "Sales record not found"];
    }
}

?>