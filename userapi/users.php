<?php
require_once '../db.php';

if (isset($_GET['admin_id'])) {
    $adminId = $_GET['admin_id'];

    $query = "SELECT * FROM usr_details WHERE id = '$adminId'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $adminRole = $row['usr_role'];
        $adminArea = $row['area']; 

        $filterQuery = '';

        if ($adminRole == 'SuperAdmin') {
            $filterQuery = '';
        } elseif ($adminRole == 'HeadOffice') {
            $filterQuery = '';
        } elseif ($adminRole == 'GeneralManager') {
            $filterQuery = '';
        } elseif ($adminRole == 'RegionAdmin') {
            $branchAdminsQuery = "SELECT id FROM usr_details WHERE area = '$adminArea' AND role = 'BranchAdmin'";
            error_log("BranchAdmins query: $branchAdminsQuery");
            $branchAdminsResult = $conn->query($branchAdminsQuery);

            $branchAdminIds = [];
            if ($branchAdminsResult && $branchAdminsResult->num_rows > 0) {
                while ($branchAdmin = $branchAdminsResult->fetch_assoc()) {
                    $branchAdminIds[] = $branchAdmin['id'];
                }
            }
            error_log("BranchAdmin IDs: " . json_encode($branchAdminIds));

            $salesPersonsQuery = "SELECT id FROM usr_details WHERE branch IN (SELECT branch FROM usr_details WHERE area = '$adminArea' AND role = 'BranchAdmin') AND role = 'SalesPerson'";
            error_log("SalesPersons query: $salesPersonsQuery");
            $salesPersonsResult = $conn->query($salesPersonsQuery);

            $salesPersonIds = [];
            if ($salesPersonsResult && $salesPersonsResult->num_rows > 0) {
                while ($salesPerson = $salesPersonsResult->fetch_assoc()) {
                    $salesPersonIds[] = $salesPerson['id'];
                }
            }
            error_log("SalesPerson IDs: " . json_encode($salesPersonIds));

            $allIds = array_merge($branchAdminIds, $salesPersonIds);
            $allIdsString = implode(',', array_map('intval', $allIds));

            if (!empty($allIdsString)) {
                $filterQuery = "WHERE sales_person_id IN ($allIdsString)";
            } else {
                error_log("No valid BranchAdmin or SalesPerson IDs found for RegionAdmin area: $adminArea");
                $filterQuery = "WHERE 1=0"; 
            }
        } elseif ($adminRole == 'BranchAdmin') {
            $adminBranch = $row['branch']; 
            $filterQuery = "WHERE sales_person_id IN (SELECT id FROM usr_details WHERE branch = '$adminBranch')";
        } elseif ($adminRole == 'SalesPerson') {
            $filterQuery = "WHERE sales_person_id = '$adminId'";
        }

        // Updated query with product table join
        $query = "
        SELECT 
            customers.*,
            sales_records.salesperson_id,
            usr_details.usr_fname AS salesperson_name
        FROM 
            customers 
        $filterQuery
        LEFT JOIN 
            sales_records 
        ON 
            customers.id = sales_records.cust_id
        LEFT JOIN 
            usr_details 
        ON 
            sales_records.salesperson_id = usr_details.id
    ";

        error_log("Final users query: $query");
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $users = array();
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $response = array(
                'success' => true,
                'data' => $users
            );
            echo json_encode($response);
        } else {
            $response = array(
                'success' => false,
                'message' => 'No users found.'
            );
            echo json_encode($response);
        }
    } else {
        $response = array(
            'success' => false,
            'message' => 'Invalid admin ID.'
        );
        echo json_encode($response);
    }
} else {
    $response = array(
        'success' => false,
        'message' => 'Admin ID is required.'
    );
    echo json_encode($response);
}

$conn->close();
?>
