<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

require_method('POST');

try {
    $payload = read_json_input();
    if (empty($payload) && !empty($_POST)) {
        $payload = $_POST;
    }

    $itemIdRaw = get_param($payload, 'itemID');
    if ($itemIdRaw === null || !ctype_digit($itemIdRaw)) {
        json_error('itemID is required and must be a valid integer');
    }
    $itemId = (int) $itemIdRaw;

    $hasQty = array_key_exists('invNewQty', $payload) || array_key_exists('itemStockQty', $payload);
    $hasPrice = array_key_exists('invNewPrice', $payload) || array_key_exists('itemPrice', $payload);
    $hasAvailability = array_key_exists('invAvailable', $payload) || array_key_exists('itemAvailable', $payload);

    if (!$hasQty && !$hasPrice && !$hasAvailability) {
        json_error('At least one field must be provided: invNewQty/itemStockQty, invNewPrice/itemPrice, invAvailable/itemAvailable');
    }

    $qty = null;
    $price = null;
    $available = null;

    if ($hasQty) {
        $qtyKey = array_key_exists('invNewQty', $payload) ? 'invNewQty' : 'itemStockQty';
        $qty = to_non_negative_int($payload[$qtyKey], $qtyKey);
    }

    if ($hasPrice) {
        $priceKey = array_key_exists('invNewPrice', $payload) ? 'invNewPrice' : 'itemPrice';
        if ($payload[$priceKey] !== '' && $payload[$priceKey] !== null) {
            $price = to_non_negative_float($payload[$priceKey], $priceKey);
        }
    }

    if ($hasAvailability) {
        $availabilityKey = array_key_exists('invAvailable', $payload) ? 'invAvailable' : 'itemAvailable';
        $available = to_bool_flag($payload[$availabilityKey], 1);
    }

    $conn = get_db_connection();

    $existsStmt = $conn->prepare('SELECT ItemID FROM ITEM WHERE ItemID = ?');
    $existsStmt->bind_param('i', $itemId);
    $existsStmt->execute();
    $existsResult = $existsStmt->get_result();
    if ($existsResult->num_rows === 0) {
        json_error('Item not found', 404);
    }

    $stmt = $conn->prepare(
        'UPDATE ITEM
         SET ItemStockQty = COALESCE(?, ItemStockQty),
             Price = COALESCE(?, Price),
             isAvailable = COALESCE(?, isAvailable)
         WHERE ItemID = ?'
    );
    $stmt->bind_param('idii', $qty, $price, $available, $itemId);
    $stmt->execute();

    json_success([
        'itemID' => $itemId,
        'updated' => [
            'ItemStockQty' => $qty,
            'Price' => $price,
            'isAvailable' => $available,
        ],
    ], 'Inventory updated successfully');
} catch (Throwable $e) {
    json_error('Failed to update inventory: ' . $e->getMessage(), 500);
}
