<?php
$host = 'localhost';
$username = 'root';
$password = '';  // Empty for XAMPP
$database = 'healthcare_application';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to support all characters
$conn->set_charset("utf8");

// Uncomment to test (remove // to see if it works)
// echo "Connected successfully";
?>