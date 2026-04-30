<?php

// Single-file inventory API (Option 1 approach)
// Supports actions: search, add, update, delete, categories, search_ingredients, add_ingredient

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Accept action via query param or JSON/body
$method = $_SERVER['REQUEST_METHOD'];
$input = [];
if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
    $input = read_json_input();
    if (empty($input) && !empty($_POST)) {
        $input = $_POST;
    }
} else {
    $input = $_GET;
}

$action = get_param($input, 'action', get_param($_GET, 'action', ''));
$action = strtolower((string) $action);
if ($action === '') {
    json_error('No action specified', 400);
}

try {
    $conn = get_db_connection();

    switch ($action) {
        case 'search':
            $search = get_param($input, 'search', '');
            $sql = 'SELECT ItemID, ItemName, Category, Price, isAvailable, ItemStockQty FROM ITEM';
            $params = [];
            $types = '';
            if ($search !== '') {
                $sql .= ' WHERE ItemName LIKE ? OR Category LIKE ?';
                $wild = '%' . $search . '%';
                $params[] = $wild; $params[] = $wild; $types .= 'ss';

                if (ctype_digit($search)) {
                    $sql .= ' OR ItemID = ?';
                    $params[] = (int)$search; $types .= 'i';
                }
            }
            $sql .= ' ORDER BY ItemID ASC';
            $stmt = $conn->prepare($sql);
            if (!empty($params)) { $stmt->bind_param($types, ...$params); }
            $stmt->execute();
            $res = $stmt->get_result();
            $items = [];
            while ($r = $res->fetch_assoc()) { $items[] = $r; }
            json_success(['items' => $items], 'Items fetched');
            break;

        case 'add':
            if ($method !== 'POST') json_error('Use POST for add', 405);

            $itemName = get_param($input, 'itemName');
            $itemCategory = get_param($input, 'itemCategory');
            $itemPriceRaw = get_param($input, 'itemPrice');

            if ($itemName === null || $itemCategory === null || $itemPriceRaw === null) {
                json_error('itemName, itemCategory, and itemPrice are required', 400);
            }

            $itemPrice = to_non_negative_float($itemPriceRaw, 'itemPrice');
            $itemAvailable = to_bool_flag($input['itemAvailable'] ?? 1, 1);
            $itemStockQty = to_non_negative_int($input['itemStockQty'] ?? 0, 'itemStockQty');

            $nextIdR = $conn->query('SELECT COALESCE(MAX(ItemID),0)+1 AS next_id FROM ITEM');
            $nextId = (int)$nextIdR->fetch_assoc()['next_id'];

            $stmt = $conn->prepare('INSERT INTO ITEM (ItemID, ItemStockQty, isAvailable, Price, ItemName, Category) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('iiidss', $nextId, $itemStockQty, $itemAvailable, $itemPrice, $itemName, $itemCategory);
            $stmt->execute();

            json_success(['item' => ['ItemID'=>$nextId,'ItemName'=>$itemName,'Category'=>$itemCategory,'Price'=>$itemPrice,'isAvailable'=>$itemAvailable,'ItemStockQty'=>$itemStockQty]], 'Item added', 201);
            break;

        case 'update':
            if ($method !== 'POST' && $method !== 'PUT') json_error('Use POST/PUT for update', 405);

            $itemIdRaw = get_param($input, 'itemID');
            if ($itemIdRaw === null || !ctype_digit($itemIdRaw)) json_error('itemID required', 400);
            $itemId = (int)$itemIdRaw;

            $hasQty = array_key_exists('invNewQty', $input) || array_key_exists('itemStockQty', $input);
            $hasPrice = array_key_exists('invNewPrice', $input) || array_key_exists('itemPrice', $input);
            $hasAvailability = array_key_exists('invAvailable', $input) || array_key_exists('itemAvailable', $input);

            if (!$hasQty && !$hasPrice && !$hasAvailability) json_error('No fields to update', 400);

            $qty = null; $price = null; $available = null;
            if ($hasQty) {
                $qtyKey = array_key_exists('invNewQty',$input) ? 'invNewQty' : 'itemStockQty';
                $qty = to_non_negative_int($input[$qtyKey], $qtyKey);
            }
            if ($hasPrice) {
                $priceKey = array_key_exists('invNewPrice',$input) ? 'invNewPrice' : 'itemPrice';
                if ($input[$priceKey] !== '' && $input[$priceKey] !== null) $price = to_non_negative_float($input[$priceKey], $priceKey);
            }
            if ($hasAvailability) {
                $availabilityKey = array_key_exists('invAvailable',$input) ? 'invAvailable' : 'itemAvailable';
                $available = to_bool_flag($input[$availabilityKey], 1);
            }

            $exists = $conn->prepare('SELECT ItemID FROM ITEM WHERE ItemID = ?');
            $exists->bind_param('i',$itemId); $exists->execute();
            if ($exists->get_result()->num_rows === 0) json_error('Item not found',404);

            $stmt = $conn->prepare('UPDATE ITEM SET ItemStockQty = COALESCE(?, ItemStockQty), Price = COALESCE(?, Price), isAvailable = COALESCE(?, isAvailable) WHERE ItemID = ?');
            $stmt->bind_param('idii', $qty, $price, $available, $itemId);
            $stmt->execute();

            json_success(['itemID'=>$itemId,'updated'=>['ItemStockQty'=>$qty,'Price'=>$price,'isAvailable'=>$available]], 'Item updated');
            break;

        case 'delete':
            if ($method !== 'POST' && $method !== 'DELETE') json_error('Use POST/DELETE for delete',405);

            $idRaw = get_param($input, 'itemID');
            if ($idRaw === null || !ctype_digit($idRaw)) json_error('itemID required',400);
            $id = (int)$idRaw;

            // check references in ORDERDETAIL_ITEM and CONTAIN
            $check1 = $conn->prepare('SELECT ItemID FROM ORDERDETAIL_ITEM WHERE ItemID = ? LIMIT 1');
            $check1->bind_param('i',$id); $check1->execute();
            if ($check1->get_result()->num_rows > 0) json_error('Cannot delete item: referenced by order details',409);

            $check2 = $conn->prepare('SELECT ItemID FROM CONTAIN WHERE ItemID = ? LIMIT 1');
            $check2->bind_param('i',$id); $check2->execute();
            if ($check2->get_result()->num_rows > 0) json_error('Cannot delete item: referenced by contain/recipe',409);

            $del = $conn->prepare('DELETE FROM ITEM WHERE ItemID = ?');
            $del->bind_param('i',$id); $del->execute();

            json_success(['deleted'=> $id], 'Item deleted');
            break;

        case 'categories':
            $res = $conn->query('SELECT Category, COUNT(*) AS ItemCount FROM ITEM GROUP BY Category ORDER BY Category ASC');
            $cats = [];
            while ($r = $res->fetch_assoc()) $cats[] = $r;
            json_success(['categories'=>$cats],'Categories');
            break;

        case 'search_ingredients':
            $search = get_param($input,'search','');
            $sql = 'SELECT IngredientID, IngredientName, QtyInStock, Unit, ReorderLevel, CASE WHEN QtyInStock <= ReorderLevel THEN "Low Stock" ELSE "In Stock" END AS StockStatus FROM INGREDIENT';
            $params=[]; $types='';
            if ($search !== '') { $sql .= ' WHERE IngredientName LIKE ?'; $params[]= '%'.$search.'%'; $types.='s'; if (ctype_digit($search)) { $sql .= ' OR IngredientID = ?'; $params[]=(int)$search; $types.='i'; } }
            $sql .= ' ORDER BY IngredientID ASC';
            $stmt = $conn->prepare($sql);
            if (!empty($params)) $stmt->bind_param($types,...$params);
            $stmt->execute(); $res = $stmt->get_result(); $ings = []; while ($r=$res->fetch_assoc()) $ings[]=$r;
            json_success(['ingredients'=>$ings],'Ingredients');
            break;

        case 'add_ingredient':
            if ($method!=='POST') json_error('Use POST',405);
            $name = get_param($input,'ingredientName'); $unit = get_param($input,'unit');
            if ($name===null||$unit===null) json_error('ingredientName and unit required',400);
            $qty = to_non_negative_float($input['qtyInStock'] ?? 0,'qtyInStock');
            $reorder = to_non_negative_float($input['reorderLevel'] ?? 0,'reorderLevel');
            $nextR = $conn->query('SELECT COALESCE(MAX(IngredientID),0)+1 AS next_id FROM INGREDIENT'); $next = (int)$nextR->fetch_assoc()['next_id'];
            $stmt = $conn->prepare('INSERT INTO INGREDIENT (IngredientID, ReorderLevel, IngredientName, QtyInStock, Unit) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('idsds',$next,$reorder,$name,$qty,$unit);
            $stmt->execute();
            json_success(['IngredientID'=>$next],'Ingredient added',201);
            break;

        default:
            json_error('Unknown action: '.$action,400);
    }
} catch (Throwable $e) {
    json_error('Server error: '.$e->getMessage(),500);
}
