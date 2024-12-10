<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$dbHost = '217.21.88.10';
$dbPort = '3306';
$dbUsername = 'u140987190_choozhal_dev';
$dbPassword = 'Choozhal_dev@7';
$dbName = 'u140987190_choozhal_app';

// Connect to the database
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

if (!$conn) {
  die('Connection failed: ' . mysqli_connect_error());
}

?>