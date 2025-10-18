<?php
session_start();
include "../admin/connection.php";

header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit;
}

$food_id = intval($_POST['id']);
$user_id = $_SESSION['user_id'];
$qty = intval($_POST['qty'] ?? 1);

$res = mysqli_query($link, "SELECT * FROM food WHERE id = $food_id AND is_active = 1");
if (!$row = mysqli_fetch_assoc($res)) {
    echo json_encode(['success' => false, 'message' => 'Produit non trouvé.']);
    exit;
}

$food_name = htmlspecialchars($row['food_name']);
$food_price = htmlspecialchars($row['food_discount_price']);
$food_image = htmlspecialchars($row['food_image']);

$cart_key = 'cart_' . $user_id;

if (!isset($_SESSION[$cart_key])) {
    $_SESSION[$cart_key] = [];
}

$found = false;
foreach ($_SESSION[$cart_key] as $key => &$item) {
    if ($item['id'] == $food_id) {
        $item['qty'] += $qty;
        $found = true;
        break;
    }
}
unset($item);

if (!$found) {
    $_SESSION[$cart_key][] = [
        'id' => $food_id,
        'name' => $food_name,
        'price' => $food_price,
        'image' => $food_image,
        'qty' => $qty,
    ];
}

echo json_encode(['success' => true, 'message' => 'Produit ajouté au panier pour la commande à emporter.']);
?>