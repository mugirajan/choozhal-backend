<?php
require_once '../connect/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  $p_name = $data['p_name'];
  $p_modal_no = $data['p_modal_no'];
  $p_category = $data['p_category'];
  $p_desc = $data['p_desc'];
  $p_manual = $data['p_manual'];
  $p_img = $data['p_img'];
  $p_height = $data['p_height'];
  $p_weight = $data['p_weight'];


$query = "INSERT INTO products (p_name, p_modal_no, p_category, p_desc, p_manual, p_img, p_height, p_weight) 
           VALUES ('$p_name', '$p_modal_no', '$p_category', '$p_desc', '$p_manual', '$p_img', '$p_height', '$p_weight')";

  if (mysqli_query($conn, $query)) {
    echo json_encode(['message' => 'products added successfully']);
  } else {
    echo json_encode(['error' => 'products added failed']);
  }
} else {
  echo json_encode(['error' => 'Invalid request method']);
}
?>