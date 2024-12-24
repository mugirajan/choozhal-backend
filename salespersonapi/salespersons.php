<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (isset($_GET['admin_id'])) {
    $adminId = $_GET['admin_id'];

    // Query to get the admin details
    $adminQuery = "SELECT * FROM admintable WHERE id = '$adminId'";
    $adminResult = $conn->query($adminQuery);

    if ($adminResult && $adminResult->num_rows > 0) {
        $adminRow = $adminResult->fetch_assoc();
        $adminRole = $adminRow['role'];
        $adminRegion = $adminRow['region'];

        $filterQuery = '';

        if ($adminRole == 'SuperAdmin') {
            $filterQuery = '';
        } elseif ($adminRole == 'HeadOffice') {
            $filterQuery = '';
        } elseif ($adminRole == 'GeneralManager') {
            $filterQuery = '';
        } elseif ($adminRole == 'RegionAdmin') {
            $branchAdminsQuery = "SELECT id FROM admintable WHERE region = '$adminRegion' AND role = 'BranchAdmin'";
            error_log("BranchAdmins query: $branchAdminsQuery");
            $branchAdminsResult = $conn->query($branchAdminsQuery);

            $branchAdminIds = [];
            if ($branchAdminsResult && $branchAdminsResult->num_rows > 0) {
                while ($branchAdmin = $branchAdminsResult->fetch_assoc()) {
                    $branchAdminIds[] = $branchAdmin['id'];
                }
            }
            error_log("BranchAdmin IDs: " . json_encode($branchAdminIds));

            $salesPersonsQuery = "SELECT id FROM admintable WHERE branch IN (
                                    SELECT branch FROM admintable WHERE id IN (" . implode(',', $branchAdminIds) . ") AND role = 'BranchAdmin'
                                 ) AND role = 'SalesPerson'";
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
                $filterQuery = "WHERE id IN ($allIdsString)";
            } else {
                error_log("No valid BranchAdmin or SalesPerson IDs found for RegionAdmin role: $adminRegion");
                $filterQuery = "WHERE 1=0"; 
            }
        } elseif ($adminRole == 'BranchAdmin') {
            $adminBranch = $adminRow['branch'];
            $filterQuery = "WHERE branch = '$adminBranch'";
        } elseif ($adminRole == 'SalesPerson') {
            $filterQuery = "WHERE id = '$adminId'";
        }

        $query = "SELECT * FROM admintable $filterQuery";
        error_log("Final admintable query: $query");

        $result = $conn->query($query);

        $admintable = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $admintable[] = $row;
            }
            echo json_encode($admintable);
        } else {
            $response = array(
                'success' => false,
                'message' => 'No admins found.'
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
