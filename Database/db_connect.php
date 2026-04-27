<?php
$conn = new mysqli("localhost", "root", "Duckymomo2002!", "alpha_pizza_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>