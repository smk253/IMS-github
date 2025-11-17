<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "intern_system_db";
$port="3307";
// Create connection
$conn = new mysqli($host, $user, $password, $database,$port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
