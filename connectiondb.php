<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "inventory_system";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set charset
$conn->set_charset("utf8mb4");
?>