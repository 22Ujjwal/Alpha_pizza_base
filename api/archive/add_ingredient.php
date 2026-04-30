<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

require_method('POST');

try {
    $payload = read_json_input();
    if (empty($payload) && !empty($_POST)) {
        $payload = $_POST;
    }

    $ingredientName = get_param($payload, 'ingredientName');
    $unit = get_param($payload, 'unit');

    if ($ingredientName === null || $unit === null) {
        json_error('ingredientName and unit are required');
    }

    $qtyInStock = to_non_negative_float($payload['qtyInStock'] ?? 0, 'qtyInStock');
    $reorderLevel = to_non_negative_float($payload['reorderLevel'] ?? 0, 'reorderLevel');

    $conn = get_db_connection();

    $nextIdResult = $conn->query('SELECT COALESCE(MAX(IngredientID), 0) + 1 AS next_id FROM INGREDIENT');
    $nextIdRow = $nextIdResult->fetch_assoc();
    $nextIngredientId = (int) $nextIdRow['next_id'];

    $stmt = $conn->prepare(
        'INSERT INTO INGREDIENT (IngredientID, ReorderLevel, IngredientName, QtyInStock, Unit)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('idsds', $nextIngredientId, $reorderLevel, $ingredientName, $qtyInStock, $unit);
    $stmt->execute();

    json_success([
        'ingredient' => [
            'IngredientID' => $nextIngredientId,
            'IngredientName' => $ingredientName,
            'QtyInStock' => $qtyInStock,
            'Unit' => $unit,
            'ReorderLevel' => $reorderLevel,
        ],
    ], 'Ingredient added successfully', 201);
} catch (Throwable $e) {
    json_error('Failed to add ingredient: ' . $e->getMessage(), 500);
}
