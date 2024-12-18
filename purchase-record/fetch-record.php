<?php
require_once '../db.php';

if (isset($_GET['admin_id'])) {
    $adminId = $_GET['admin_id'];

    $query = "SELECT * FROM admintable WHERE id = '$adminId'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $adminRole = $row['role'];
        $adminArea = $row['area']; 

        $filterQuery = '';

        if ($adminRole == 'SuperAdmin') {
            $filterQuery = '';
        } elseif ($adminRole == 'HeadOffice') {
            $filterQuery = '';
        } elseif ($adminRole == 'GeneralManager') {
            $filterQuery = '';
        } elseif ($adminRole == 'RegionAdmin') {
            $branchAdminsQuery = "SELECT id FROM admintable WHERE area = '$adminArea' AND role = 'BranchAdmin'";
            error_log("BranchAdmins query: $branchAdminsQuery");
            $branchAdminsResult = $conn->query($branchAdminsQuery);

            $branchAdminIds = [];
            if ($branchAdminsResult && $branchAdminsResult->num_rows > 0) {
                while ($branchAdmin = $branchAdminsResult->fetch_assoc()) {
                    $branchAdminIds[] = $branchAdmin['id'];
                }
            }
            error_log("BranchAdmin IDs: " . json_encode($branchAdminIds));

            $salesPersonsQuery = "SELECT id FROM admintable WHERE branch IN (SELECT branch FROM admintable WHERE area = '$adminArea' AND role = 'BranchAdmin') AND role = 'SalesPerson'";
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
            $filterQuery = "WHERE sales_person_id IN (SELECT id FROM admintable WHERE branch = '$adminBranch')";
        } elseif ($adminRole == 'SalesPerson') {
            $filterQuery = "WHERE sales_person_id = '$adminId'";
        }

        // Updated query with product table join
        $query = "SELECT 
            sale_records.*, 
            users.uname AS user_name, 
            product.name AS product_name 
          FROM 
            sale_records 
          LEFT JOIN 
            users 
          ON 
            sale_records.user_id = users.id 
          LEFT JOIN 
            product 
          ON 
            sale_records.pro_id = product.id 
          $filterQuery";


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
