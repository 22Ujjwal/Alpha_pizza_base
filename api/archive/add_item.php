<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

require_method('POST');

try {
    $payload = read_json_input();
    if (empty($payload) && !empty($_POST)) {
        $payload = $_POST;
    }

    $itemName = get_param($payload, 'itemName');
    $itemCategory = get_param($payload, 'itemCategory');
    $itemPriceRaw = get_param($payload, 'itemPrice');

    if ($itemName === null || $itemCategory === null || $itemPriceRaw === null) {
        json_error('itemName, itemCategory, and itemPrice are required');
    }

    $itemPrice = to_non_negative_float($itemPriceRaw, 'itemPrice');
    $itemAvailable = to_bool_flag($payload['itemAvailable'] ?? 1, 1);
    $itemStockQty = to_non_negative_int($payload['itemStockQty'] ?? 0, 'itemStockQty');

    $conn = get_db_connection();

    $nextIdResult = $conn->query('SELECT COALESCE(MAX(ItemID), 0) + 1 AS next_id FROM ITEM');
    $nextIdRow = $nextIdResult->fetch_assoc();
    $nextItemId = (int) $nextIdRow['next_id'];

    $stmt = $conn->prepare(
        'INSERT INTO ITEM (ItemID, ItemStockQty, isAvailable, Price, ItemName, Category) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('iiidss', $nextItemId, $itemStockQty, $itemAvailable, $itemPrice, $itemName, $itemCategory);
    $stmt->execute();

    json_success(
        [
            'item' => [
                'ItemID' => $nextItemId,
                'ItemName' => $itemName,
                'Category' => $itemCategory,
                'Price' => $itemPrice,
                'isAvailable' => $itemAvailable,
                'ItemStockQty' => $itemStockQty,
            ],
        ],
        'Item added successfully',
        201
    );
} catch (Throwable $e) {
    json_error('Failed to add item: ' . $e->getMessage(), 500);
}
