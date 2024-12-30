<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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
        case 'createUser':
            echo json_encode(createUserDetails($getData, $crntUsr));
            break;
        case 'updateUser':
            echo json_encode(updateUserDetails($getData));
            break;
        case 'deleteUser':
            echo json_encode(deleteUserDetails($getData, $crntUsr));
            break;
        case 'getListOfAllUsers':
            echo json_encode(getListOfAllUsers($crntUsr));
            break;
        case 'getAUser':
            echo json_encode(getAUser($getData));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}

function getListOfAllUsers($adminId)
{
    global $conn;

    try {
        if (empty($adminId)) {
            throw new Exception('Admin ID is required.');
        }

        // Fetch admin details
        $stmt = $conn->prepare("SELECT * FROM usr_details WHERE id = ?");
        $stmt->bind_param("i", $adminId);
        $stmt->execute();

        $adminResult = $stmt->get_result();
        if ($adminResult->num_rows === 0) {
            throw new Exception('Invalid admin ID.');
        }

        $adminRow = $adminResult->fetch_assoc();
        $adminRole = $adminRow['usr_role'];
        $adminArea = $adminRow['area'];

        // Determine filter query based on admin role
        $filterQuery = '';
        if ($adminRole === 'RegionAdmin') {
            $branchAdminsQuery = "SELECT id FROM usr_details WHERE area = ? AND usr_role = 'BranchAdmin'";
            $stmt = $conn->prepare($branchAdminsQuery);
            $stmt->bind_param("s", $adminArea);
            $stmt->execute();

            $branchAdminsResult = $stmt->get_result();
            $branchAdminIds = array_column($branchAdminsResult->fetch_all(MYSQLI_ASSOC), 'id');

            $salesPersonsQuery = "SELECT id FROM usr_details WHERE branch IN (SELECT branch FROM usr_details WHERE area = ? AND usr_role = 'BranchAdmin') AND usr_role = 'SalesPerson'";
            $stmt = $conn->prepare($salesPersonsQuery);
            $stmt->bind_param("s", $adminArea);
            $stmt->execute();

            $salesPersonsResult = $stmt->get_result();
            $salesPersonIds = array_column($salesPersonsResult->fetch_all(MYSQLI_ASSOC), 'id');

            $allIds = array_merge($branchAdminIds, $salesPersonIds);
            $allIdsString = implode(',', array_map('intval', $allIds));

            if (!empty($allIdsString)) {
                $filterQuery = "WHERE sales_person_id IN ($allIdsString)";
            } else {
                error_log("No valid BranchAdmin or SalesPerson IDs found for RegionAdmin area: $adminArea");
                $filterQuery = "WHERE 1=0";
            }
        } elseif ($adminRole === 'BranchAdmin') {
            $adminBranch = $adminRow['branch'];
            $filterQuery = "WHERE sales_person_id IN (SELECT id FROM usr_details WHERE branch = '$adminBranch')";
        } elseif ($adminRole === 'SalesPerson') {
            $filterQuery = "WHERE sales_person_id = '$adminId'";
        }

        // Fetch users details
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
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'data' => $users,
            'totalCount' => count($users),
        ];
    } catch (Exception $e) {
        // Handle errors gracefully
        return [
            'error' => true,
            'message' => 'Error fetching user data: ' . $e->getMessage(),
        ];
    }
}
function createUserDetails($data, $crntUsr)
{
    global $conn;

    // Extract data with default values
    $usr_fname = $data['usr_fname'] ?? '';
    $usr_lname = $data['usr_lname'] ?? '';
    $usr_email = $data['usr_email'] ?? '';
    $usr_pass = $data['usr_pass'] ?? '';
    $usr_role = $data['usr_role'] ?? '';
    $usr_dob = $data['usr_dob'] ?? '';
    $address = $data['address'] ?? '';
    $usr_phone = $data['usr_phone'] ?? '';
    $date_of_joining = $data['date_of_joining'] ?? '';
    $branch = $data['branch'] ?? '';
    $region = $data['region'] ?? '';
    $area = $data['area'] ?? '';
    $is_active = isset($data['is_active']) && $data['is_active'] ? 1 : 0;
    $created_by = $crntUsr ?? '';

    // SQL query with placeholders
    $stmt = $conn->prepare("
        INSERT INTO usr_details (
            usr_fname, usr_lname, usr_email, usr_pass,usr_role, usr_dob, address, usr_phone,
            date_of_joining, branch, region, area, is_active, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Execute query with bound values
    $stmt->execute([
        $usr_fname, $usr_lname, $usr_email, $usr_pass, $usr_role, $usr_dob, $address, $usr_phone,
        $date_of_joining, $branch, $region, $area, $is_active, $created_by
    ]);

    // Check if the insertion was successful
    if ($stmt->affected_rows) {
        return ["message" => "User created successfully"];
    } else {
        return ["error" => "Failed to create user"];
    }
}

function updateUserDetails($data)
{
    global $conn;

    $id = $data['id'] ?? null;
    $usr_fname = $data['usr_fname'] ?? '';
    $usr_lname = $data['usr_lname'] ?? '';
    $usr_email = $data['usr_email'] ?? '';
    $usr_pass = $data['usr_pass'] ?? '';
    $usr_role = $data['usr_role'] ?? '';
    $usr_dob = $data['usr_dob'] ?? '';
    $address = $data['address'] ?? '';
    $usr_phone = $data['usr_phone'] ?? '';
    $date_of_joining = $data['date_of_joining'] ?? '';
    $branch = $data['branch'] ?? '';
    $region = $data['region'] ?? '';
    $area = $data['area'] ?? '';
    $is_active = isset($data['is_active']) && $data['is_active'] ? 1 : 0;

    if (!$id) {
        return ["error" => "User ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("
        UPDATE usr_details SET
            usr_fname = ?, usr_lname = ?, usr_email = ?, usr_pass = ?, usr_role = ?, usr_dob = ?,
            address = ?, usr_phone = ?, date_of_joining = ?, branch = ?, region = ?, area = ?, is_active = ?
        WHERE id = ?
    ");

    // Execute query with bound values
    $stmt->execute([
        $usr_fname, $usr_lname, $usr_email, $usr_pass, $usr_role, $usr_dob,
        $address, $usr_phone, $date_of_joining, $branch, $region, $area, $is_active, $id
    ]);

    // Check if the update was successful
    if ($stmt->affected_rows) {
        return ["message" => "User updated successfully"];
    } else {
        return ["error" => "Failed to update user or no changes made"];
    }
}

function deleteUserDetails($data, $crntUsr)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "User ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("UPDATE usr_details SET is_deleted = ?, updated_by = ? WHERE id = ?");

    // Execute query with bound values
    $stmt->execute([true, $crntUsr, $id]);

    // Check if the deletion was successful
    if ($stmt->affected_rows) {
        return ["message" => "User deleted successfully"];
    } else {
        return ["error" => "Failed to delete user or user not found"];
    }
}

function getAUser($data)
{
    global $conn;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "User ID is required"];
    }

    // SQL query with placeholders
    $stmt = $conn->prepare("SELECT * FROM usr_details WHERE id = ?");

    // Execute query with bound values
    $stmt->execute([$id]);

    // Fetch the user data
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        return $user;
    } else {
        return ["error" => "User not found"];
    }
}
?>