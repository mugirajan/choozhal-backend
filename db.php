<?php
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