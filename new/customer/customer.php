
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
        case 'createCustomer':
            echo json_encode(createCustomerdetails($getData, $crntUsr));
            break;
        case 'updateCustomer':
            echo json_encode(updateCustomerdetails($getData, $crntUsr));
            break;
        case 'deleteCustomer':
            echo json_encode(deleteCustomerdetails($getData, $crntUsr));
            break;
        case 'getListOfAllCustomers':
            echo json_encode(getListOfAllCustomers($crntUsr));
            break;
        case 'getACustomer':
            echo json_encode(getACustomer($getData));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}



function getListOfAllCustomers($crntUsr)
{
    global $pdo;
    $adminId = $crntUsr;
    try {
        // Fetch all rows
        $stmt = $pdo->query("SELECT * FROM customers Where is_deleted = false");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Transform the data if needed
        $customerList = array_map(function ($values) {
            return [
                'id' => $values['id'],
                'first_name' => $values['first_name'],
                'last_name' => $values['last_name'],
                'email' => $values['email'],
                'mobile_no' => $values['mobile_no'],
                'dob' => $values['dob'],
                'gender' => $values['gender'],
                'profilePic' => $values['profile_pic'],
                'address' => $values['address'],
                'area' => $values['area'],
                'city' => $values['city'],
                'district' => $values['district'],
                'state' => $values['state'],
                'pincode' => $values['pincode'],
                'isActive' => (bool)$values['is_active'],
                'createdDate' => $values['created_at']
            ];
        }, $customers);

        return [
            'data' => $customerList,
            'totalCount' => count($customerList),
        ];
    } catch (PDOException $e) {
        // Handle errors gracefully
        return [
            'error' => true,
            'message' => 'Error fetching customer data: ' . $e->getMessage(),
        ];
    }
}


function createCustomerDetails($data, $crntUsr)
{
    global $pdo;

    // Extract data with default values
    $first_name = $data['first_name'] ?? '';
    $last_name = $data['last_name'] ?? '';
    $email = $data['email'] ?? '';
    $mobile_no = $data['mobile_no'] ?? '';
    $dob = $data['dob'] ?? '';
    $gender = $data['gender'] ?? '';
    $profile_pic = $data['profilePic'] ?? '';
    $address = $data['address'] ?? '';
    $area = $data['area'] ?? '';
    $city = $data['city'] ?? '';
    $district = $data['district'] ?? '';
    $state = $data['state'] ?? '';
    $pincode = $data['pincode'] ?? '';
    $is_active = isset($data['isActive']) && $data['isActive'] ? 1 : 0; // Convert boolean to tinyint
    $created_by = $crntUsr ?? '';

    // SQL query with placeholders
    $stmt = $pdo->prepare("
        INSERT INTO customers (
            first_name, last_name, email, mobile_no, dob, gender, profile_pic,
            address, area, city, district, state, pincode, is_active, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Execute query with bound values
    $stmt->execute([
        $first_name, $last_name, $email, $mobile_no, $dob, $gender, $profile_pic,
        $address, $area, $city, $district, $state, $pincode, $is_active, $created_by
    ]);

    // Check if the insertion was successful
    if ($stmt->rowCount()) {
        return ["message" => "Customer created successfully"];
    } else {
        return ["error" => "Failed to create customer"];
    }
}


function updateCustomerdetails($data)
{
    global $pdo;

    $id = $data['id'] ?? null;
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';

    if (!$id) {
        return ["error" => "Customer ID is required"];
    }

    $stmt = $pdo->prepare("UPDATE customers SET name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->execute([$name, $email, $phone, $id]);

    if ($stmt->rowCount()) {
        return ["message" => "Customer updated successfully"];
    } else {
        return ["error" => "Failed to update customer or no changes made"];
    }
}


function deleteCustomerdetails($data, $crntUsr)
{
    global $pdo;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Customer ID is required"];
    }

    $stmt = $pdo->prepare("UPDATE customers SET is_deleted = ?, updated_by = ? WHERE id = ?");
    $stmt->execute([true, $crntUsr, $id]);

    if ($stmt->rowCount()) {
        return ["message" => "Customer deleted successfully"];
    } else {
        return ["error" => "Failed to delete customer or customer not found"];
    }
}

function getACustomer($data)
{
    global $pdo;

    $id = $data['id'] ?? null;
    if (!$id) {
        return ["error" => "Customer ID is required"];
    }

    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$id]);

    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($customer) {
        return $customer;
    } else {
        return ["error" => "Customer not found"];
    }
}


?>