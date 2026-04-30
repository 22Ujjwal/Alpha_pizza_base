<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

try {
    $conn = get_db_connection();

    $search = '';
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $search = trim((string) ($_GET['search'] ?? ''));
    } else {
        $payload = read_json_input();
        if (empty($payload) && !empty($_POST)) {
            $payload = $_POST;
        }
        $search = trim((string) ($payload['search'] ?? ''));
    }

    $sql = 'SELECT IngredientID, IngredientName, QtyInStock, Unit, ReorderLevel,
                   CASE WHEN QtyInStock <= ReorderLevel THEN "Low Stock" ELSE "In Stock" END AS StockStatus
            FROM INGREDIENT';

    $params = [];
    $types = '';
    if ($search !== '') {
        $sql .= ' WHERE IngredientName LIKE ?';
        $wildcard = '%' . $search . '%';
        $params[] = $wildcard;
        $types .= 's';

        if (ctype_digit($search)) {
            $sql .= ' OR IngredientID = ?';
            $params[] = (int) $search;
            $types .= 'i';
        }
    }

    $sql .= ' ORDER BY IngredientID ASC';

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $ingredients = [];
    while ($row = $result->fetch_assoc()) {
        $ingredients[] = $row;
    }

    json_success(['ingredients' => $ingredients], 'Ingredients fetched successfully');
} catch (Throwable $e) {
    json_error('Failed to fetch ingredients: ' . $e->getMessage(), 500);
}
