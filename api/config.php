<?php

$host = '127.0.0.1';
$port = 3306;
$dbname = 'alpha_pizza_db';
$username = 'root';
$password = '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function get_db_connection(): mysqli
{
    global $host, $port, $dbname, $username, $password;

    $conn = new mysqli($host, $username, $password, $dbname, $port);
    $conn->set_charset('utf8mb4');

    return $conn;
}
