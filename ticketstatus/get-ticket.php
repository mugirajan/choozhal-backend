<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (isset($_GET['admin_id'])) {
    $adminId = $_GET['admin_id'];

    // Query to get the admin details
    $adminQuery = "SELECT * FROM usr_details WHERE id = '$adminId'";
    $adminResult = $conn->query($adminQuery);

    if ($adminResult && $adminResult->num_rows > 0) {
        $adminRow = $adminResult->fetch_assoc();
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
            $branchAdminsQuery = "SELECT id FROM usr_details WHERE region = '$adminRegion' AND role = 'BranchAdmin'";
            $branchAdminsResult = $conn->query($branchAdminsQuery);

            $branchAdminIds = [];
            if ($branchAdminsResult && $branchAdminsResult->num_rows > 0) {
                while ($branchAdmin = $branchAdminsResult->fetch_assoc()) {
                    $branchAdminIds[] = $branchAdmin['id'];
                }
            }

            $salesPersonsQuery = "SELECT id FROM usr_details WHERE branch IN (
                                        SELECT branch FROM usr_details WHERE id IN (" . implode(',', $branchAdminIds) . ") AND role = 'BranchAdmin'
                                    ) AND role = 'SalesPerson'";
            $salesPersonsResult = $conn->query($salesPersonsQuery);

            $salesPersonIds = [];
            if ($salesPersonsResult && $salesPersonsResult->num_rows > 0) {
                while ($salesPerson = $salesPersonsResult->fetch_assoc()) {
                    $salesPersonIds[] = $salesPerson['id'];
                }
            }

            $allIds = array_merge($branchAdminIds, $salesPersonIds);
            $allIdsString = implode(',', array_map('intval', $allIds));

            if (!empty($allIdsString)) {
                $filterQuery = "WHERE u.sales_person_id IN ($allIdsString)";
            } else {
                $filterQuery = "WHERE 1=0"; 
            }
        } elseif ($adminRole == 'BranchAdmin') {
            $filterQuery = "WHERE u.sales_person_id IN (
                                SELECT id FROM usr_details WHERE branch = (
                                    SELECT branch FROM usr_details WHERE id = '$adminId'
                                )
                            )";
        } elseif ($adminRole == 'SalesPerson') {
            $filterQuery = "WHERE u.sales_person_id = '$adminId'";
        }

        $query = "
            SELECT 
                ticket_details.*,
                products.p_name AS product_name,
                customers.first_name,
                customers.mobile_no,
                usr_details.usr_fname AS salesperson_name
            FROM 
                ticket_details 
            INNER JOIN 
                sales_records 
            ON 
                ticket_details.sales_id = sales_records.id
            INNER JOIN 
                products 
            ON 
                sales_records.prod_id = products.id
            INNER JOIN 
                customers 
            ON 
                ticket_details.cust_id = customers.id
            INNER JOIN 
                usr_details 
            ON 
                sales_records.salesperson_id = usr_details.id
            $filterQuery";


        $result = $conn->query($query);

        $tickets = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tickets[] = $row;
            }
            echo json_encode($tickets);
        } else {
            echo json_encode(["error" => "No tickets found."]);
        }
    } else {
        echo json_encode(["error" => "Invalid admin ID."]);
    }
} else {
    echo json_encode(["error" => "Admin ID is required."]);
}

$conn->close();
?>
