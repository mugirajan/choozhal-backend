<?php
require_once '../db.php';
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  $cust_id = $data['cust_id'];
  $prod_id = $data['prod_id'];
  $prod_uniq_no = $data['prod_uniq_no'];
  $bill_no = $data['bill_no'];
  $warnt_period = $data['warnt_period'];
  $prof_doc = $data['prof_doc'];
  $sale_note = $data['sale_note'];
  $admin_id = $data['admin_id'];

  $query = "INSERT INTO sales_records (cust_id, prod_id, prod_uniq_no, bill_no, warnt_period, prof_doc, sale_note, created_by, salesperson_id) VALUES ('$cust_id', '$prod_id', '$prod_uniq_no', '$bill_no', '$warnt_period', '$prof_doc', '$sale_note', '$admin_id', '$admin_id')";

  if (mysqli_query($conn, $query)) {
    echo json_encode(['message' => 'Sales record added successfully']);
  } else {
    echo json_encode(['error' => 'Failed to add sales record']);
  }
} else {
  echo json_encode(['error' => 'Invalid request method']);
}
?>