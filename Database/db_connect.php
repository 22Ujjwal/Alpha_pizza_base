<?php
$conn = new mysqli("localhost", "root", "YOUR_MYSQL_PASSWORD", "alpha_pizza_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
