
<?php

require_once "../../db.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    if (!isset($_POST['target'], $_POST['data'], $_POST['crntUsr'])) {
        echo json_encode([
            "success" => false,
            "error" => "Invalid request. Missing 'target', 'data', or 'crntUsr' in payload."
        ]);
        exit;
    }

    $method = $_POST['target'];
    $getData = $_POST['data'];
    $crntUsr = $_POST['crntUsr'];
    $file = '';

    if ($method == 'createCustomer' || $method == 'updateCustomer') {

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Profile picture is not available or upload failed."
            ]);
            exit;
        }
    }


    switch ($method) {
        case 'createCustomer':
            echo json_encode(createCustomerdetails($getData, $crntUsr, $file));
            break;
        case 'updateCustomer':
            echo json_encode(updateCustomerdetails($getData, $crntUsr, $file));
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


function moveFile($file)
{

    $targetDir = "../../uploads/profile-pic/";

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if ($file['error'] === UPLOAD_ERR_OK) {
        // Extract the file name and extension
        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Generate a unique file name
        $uniqueName = $originalName . '_' . uniqid() . '.' . $extension;

        // Full path to save the file
        $targetFilePath = $targetDir . $uniqueName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            return [
                'status' => 'success',
                'filePath' => '/uploads/profile-pic/' . $uniqueName
            ];
        } else {
            return [
                'status' => 'error',
                'message' => "Failed to move the uploaded file."
            ];
        }
    } else {
        return "File upload error: " . $file['error'];
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


function createCustomerDetails($data, $crntUsr, $file)
{


    global $pdo;

    $profile_pic = moveFile($file);


    $data = json_decode($data, true);

    if ($profile_pic['status'] == 'success') {

        // Extract data with default values
        $first_name = $data['fName'];
        $last_name = $data['lName'];
        $email = $data['email'];
        $mobile_no = $data['mobile_no'];
        $dob = $data['dob'];
        $gender = $data['gender'];

        $profile_pic =  $profile_pic['filePath'];
        $address = $data['address'];
        $area = $data['area'];
        $city = $data['city'];
        $district = $data['district'];
        $state = $data['state'];
        $pincode = $data['pincode'];
        $is_active = isset($data['isActive']) && $data['isActive'] ? 1 : 0; // Convert boolean to tinyint
        $created_by = $crntUsr;


        // SQL query with placeholders
        $stmt = $pdo->prepare("
        INSERT INTO customers (
            first_name, last_name, email, mobile_no, dob, gender, profile_pic,
            address, area, city, district, state, pincode, is_active, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

        // Execute query with bound values
        $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $mobile_no,
            $dob,
            $gender,
            $profile_pic,
            $address,
            $area,
            $city,
            $district,
            $state,
            $pincode,
            $is_active,
            $created_by
        ]);


        // Check if the insertion was successful
        if ($stmt->rowCount()) {
            return ["success" => true, "message" => "Customer created successfully"];
        } else {
            return ["error" => "Failed to create customer"];
        }
    } else {
        return ["error" => "Failed to upload image"];
    }
}


function updateCustomerDetails($data, $crntUsr, $file)
{
    global $pdo;

    // Extract data with defaults
    $profile_pic = moveFile($file);


    $data = json_decode($data, true);

    if ($profile_pic['status'] == 'success') {

        // Extract data with default values
        $id = $data['id'];
        $first_name = $data['fName'];
        $last_name = $data['lName'];
        $email = $data['email'];
        $mobile_no = $data['mobile_no'];
        $dob = $data['dob'];
        $gender = $data['gender'];

        $profile_pic =  $profile_pic['filePath'];
        $address = $data['address'];
        $area = $data['area'];
        $city = $data['city'];
        $district = $data['district'];
        $state = $data['state'];
        $pincode = $data['pincode'];
        $is_active = isset($data['isActive']) && $data['isActive'] ? 1 : 0; // Convert boolean to tinyint
        $updated_by = $crntUsr;

        // Check if ID is provided
        if (!$id) {
            return ["error" => "Customer ID is required"];
        }

        // SQL query to update the customer
        $stmt = $pdo->prepare("
        UPDATE customers 
        SET 
            first_name = ?, last_name = ?, email = ?, mobile_no = ?, dob = ?, gender = ?, profile_pic = ?, 
            address = ?, area = ?, city = ?, district = ?, state = ?, pincode = ?, is_active = ?, updated_by = ?
        WHERE id = ?
    ");

        // Execute the query with bound values
        $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $mobile_no,
            $dob,
            $gender,
            $profile_pic,
            $address,
            $area,
            $city,
            $district,
            $state,
            $pincode,
            $is_active,
            $updated_by,
            $id
        ]);

        // Check if the update was successful
        if ($stmt->rowCount()) {
            return ["success" => true, "message" => "Customer updated successfully"];
        } else {
            return ["error" => "Failed to update customer or no changes made"];
        }
    } else {
        return ["error" => "Failed to upload image"];
    }
}



function deleteCustomerdetails($data, $crntUsr)
{
    global $pdo;

    $data = json_decode($data, true);

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