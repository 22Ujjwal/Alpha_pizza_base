<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

try {
    $conn = get_db_connection();

    $result = $conn->query(
        'SELECT Category, COUNT(*) AS ItemCount
         FROM ITEM
         GROUP BY Category
         ORDER BY Category ASC'
    );

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    json_success(['categories' => $categories], 'Categories fetched successfully');
} catch (Throwable $e) {
    json_error('Failed to fetch categories: ' . $e->getMessage(), 500);
}
