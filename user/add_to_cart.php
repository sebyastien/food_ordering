<?php
session_start();

header('Content-Type: application/json');

$id = null;
$qty = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
}

// 🔑 On récupère les identifiants de la table et de l'utilisateur
$table_id = isset($_SESSION['table_id']) ? intval($_SESSION['table_id']) : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Vérifier que les identifiants sont présents
if (!$id || $table_id === 0 || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Informations de commande manquantes.']);
    exit;
}

include "../admin/connection.php";

$stmt = $link->prepare("SELECT * FROM food WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Produit introuvable']);
    exit;
}

$product = $res->fetch_assoc();
$stmt->close();

// 🔑 Initialisation de la structure de paniers par table et par utilisateur
if (!isset($_SESSION['carts_by_table'])) {
    $_SESSION['carts_by_table'] = [];
}
if (!isset($_SESSION['carts_by_table'][$table_id])) {
    $_SESSION['carts_by_table'][$table_id] = [];
}
if (!isset($_SESSION['carts_by_table'][$table_id][$user_id])) {
    $_SESSION['carts_by_table'][$table_id][$user_id] = [];
}

// 🔑 On travaille avec le panier de l'utilisateur actuel
$cart = &$_SESSION['carts_by_table'][$table_id][$user_id];

// Rechercher le produit dans le panier de CET utilisateur
$foundIndex = null;
foreach ($cart as $index => $item) {
    if ($item['tb_id'] == $id) {
        $foundIndex = $index;
        break;
    }
}

if ($foundIndex !== null) {
    // Produit déjà dans le panier de cet utilisateur => augmenter la quantité
    $cart[$foundIndex]['qty_total'] += $qty;
} else {
    // Produit absent du panier de cet utilisateur => ajout
    $cart[] = [
        'img1' => $product['food_image'],
        'nm' => $product['food_name'],
        'price' => $product['food_discount_price'],
        'qty_total' => $qty,
        'tb_id' => $product['id'],
    ];
}

$cart_count = count($cart);
echo json_encode(['success' => true, 'message' => 'Produit ajouté au panier', 'cart_count' => $cart_count]);
exit;
?>