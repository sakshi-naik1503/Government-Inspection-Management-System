<?php
$servername = "localhost";
$username = "root";
$password = "HRajput@1308";
$database = "tour_expenses";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
