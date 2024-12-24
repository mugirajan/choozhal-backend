
<?php

require_once "../db.php";

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    if (!$data['target'] || !$data['data']) {
        echo json_encode([
            "success" => false,
            "error" => "Invalid request. Missing 'target' or 'data' in payload."
        ]);
        exit;
    }

    $method = $data['target'];
    $getData = $data['data'];

    switch ($method) {
        case 'createCustomer':
            echo json_encode(createCustomerdetails($getData));
            break;
        case 'updateCustomer':
            echo json_encode(updateCustomerdetails($getData));
            break;
        case 'deleteCustomer':
            echo json_encode(deleteCustomerdetails($getData));
            break;
        case 'getListOfAllCustomers':
            echo json_encode(getListOfAllCustomers());
            break;
        case 'getACustomer':
            echo json_encode(getACustomer($getData));
            break;
        default:
            return print("Invalid path...");
            break;
    }
}

function getListOfAllCustomers()
{
    global $pdo;

    // Fetch all rows
    $stmt = $pdo->query("SELECT * FROM customers");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get row count
    $rowCount = $stmt->rowCount();

    return [
        'data' => $customers,
        'totalCount' => $rowCount,
    ];
}



function createCustomerdetails($data)
{
    global $pdo;

    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $phone]);

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


function deleteCustomerdetails($data)
{
    global $pdo;

    $id = $data['id'] ?? null;

    if (!$id) {
        return ["error" => "Customer ID is required"];
    }

    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$id]);

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