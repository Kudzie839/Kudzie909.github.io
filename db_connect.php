<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = ""; // Default WampServer password is empty
$database = "zimbabwe_bus_recovery";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?>