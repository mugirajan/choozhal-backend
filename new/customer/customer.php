
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


function moveFile() {
    $targetDir = "../../uploads/profile-pic/";
  
    if (!file_exists($targetDir)) {
      mkdir($targetDir, 0777, true);
    }
  
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
      $originalName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
      $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  
      $uniqueName = $originalName . '_' . uniqid() . '.' . $extension;
      $targetFilePath = $targetDir . $uniqueName;
  
      if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
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
      return [
        'status' => 'error',
        'message' => "File upload error: " . $_FILES['file']['error']
      ];
    }
  }

function createCustomerDetails($data, $crntUsr, $file)
{
    global $pdo;

    $profile_pic = moveFile($file);

    if ($profile_pic['status'] !== 'success') {
        return [
            'error' => $profile_pic['message']
        ];
    }

    $data = json_decode($data, true);

    // Validate input data
    $requiredFields = ['first_name', 'last_name', 'email', 'mobile_no', 'dob', 'gender', 'address', 'area', 'city', 'district', 'state', 'pincode'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            return [
                'error' => "Missing required field: $field"
            ];
        }
    }

    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $email = $data['email'];
    $mobile_no = $data['mobile_no'];
    $dob = $data['dob'];
    $gender = $data['gender'];
    $profile_pic = $profile_pic['filePath'];
    $address = $data['address'];
    $area = $data['area'];
    $city = $data['city'];
    $district = $data['district'];
    $state = $data['state'];
    $pincode = $data['pincode'];
    $is_active = isset($data['isActive']) && $data['isActive'] ? 1 : 0;
    $created_by = $crntUsr;

    $stmt = $pdo->prepare("
        INSERT INTO customers (
            first_name, last_name, email, mobile_no, dob, gender, profile_pic,
            address, area, city, district, state, pincode, is_active, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

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

    if ($stmt->rowCount()) {
        return [
            'success' => true,
            'message' => "Customer created successfully"
        ];
    } else {
        return [
            'error' => "Failed to create customer"
        ];
    }
}


function updateCustomerDetails($data, $crntUsr, $file = null)
{
    global $pdo;

    // Extract data with defaults
    $data = json_decode($data, true);

    // Extract data with default values
    $id = $data['id'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $email = $data['email'];
    $mobile_no = $data['mobile_no'];
    $dob = $data['dob'];
    $gender = $data['gender'];

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
        first_name = ?, last_name = ?, email = ?, mobile_no = ?, dob = ?, gender = ?, 
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
}



function deleteCustomerdetails($data, $crntUsr)
{
    global $pdo;

    $data = json_decode($data, true);

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Customer ID is required"];
    }

    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([true, $crntUsr, $id]);

    if ($stmt->rowCount()) {
        return ["message" => "Customer deleted successfully"];
    } else {
        return ["error" => "Failed to delete customer or customer not found"];
    }
}

function getListOfAllCustomers(int $crntUsr): array
{
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM customers WHERE is_deleted = false");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        error_log('Error fetching customer data: ' . $e->getMessage());
        return [
            'error' => true,
            'message' => 'Error fetching customer data: ' . $e->getMessage(),
        ];
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