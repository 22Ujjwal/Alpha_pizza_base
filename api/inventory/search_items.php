<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

try {
    $conn = get_db_connection();

    $search = '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $search = trim((string) ($_GET['search'] ?? ''));
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = read_json_input();
        if (empty($payload) && !empty($_POST)) {
            $payload = $_POST;
        }
        $search = trim((string) ($payload['search'] ?? ''));
    } else {
        json_error('Method not allowed', 405);
    }

    $sql = 'SELECT ItemID, ItemName, Category, Price, isAvailable, ItemStockQty FROM ITEM';
    $params = [];
    $types = '';

    if ($search !== '') {
        $sql .= ' WHERE ItemName LIKE ? OR Category LIKE ?';
        $wildcard = '%' . $search . '%';
        $params[] = $wildcard;
        $params[] = $wildcard;
        $types .= 'ss';

        if (ctype_digit($search)) {
            $sql .= ' OR ItemID = ?';
            $params[] = (int) $search;
            $types .= 'i';
        }
    }

    $sql .= ' ORDER BY ItemID ASC';

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    json_success(['items' => $items], 'Items fetched successfully');
} catch (Throwable $e) {
    json_error('Failed to fetch items: ' . $e->getMessage(), 500);
}
