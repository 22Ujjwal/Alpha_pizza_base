<?php
require_once 'config.php';
require_once 'functions.php';

$conn = get_db_connection();
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = 'SELECT ing.IngredientName 
        FROM INGREDIENT ing 
        JOIN CONTAIN c ON ing.IngredientID = c.IngredientID 
        WHERE c.ItemID = ?';

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $itemId);
$stmt->execute();
$res = $stmt->get_result();

$ingredients = [];
while ($row = $res->fetch_assoc()) {
    $ingredients[] = $row['IngredientName'];
}

// Send it back as a comma-separated string, or a fallback message
if (count($ingredients) > 0) {
    echo implode(', ', $ingredients);
} else {
    echo 'No ingredients listed';
}
?>