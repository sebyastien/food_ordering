<?php
session_start();

header('Content-Type: application/json');

$id = null;
$qty = 1;
// 💡 Nouvelle variable pour le commentaire
$comment = ""; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
    // 💡 Récupération du commentaire (sanitize/validation minimale)
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : "";
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
    // 💡 Récupération du commentaire
    $comment = isset($_GET['comment']) ? trim($_GET['comment']) : "";
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

// ❗ IMPORTANT : On ne fusionne plus les articles existants s'ils ont un commentaire différent.
// L'idée est de traiter un plat avec commentaire comme un article distinct.
// Si le commentaire est vide, on peut fusionner si l'article existe déjà SANS commentaire.
if (empty($comment)) {
    foreach ($cart as $index => $item) {
        // Vérifie si l'ID correspond ET qu'il n'y a pas de commentaire
        if ($item['tb_id'] == $id && empty($item['comment'])) { 
            $foundIndex = $index;
            break;
        }
    }
}

if ($foundIndex !== null) {
    // Produit déjà dans le panier SANS commentaire => augmenter la quantité
    $cart[$foundIndex]['qty_total'] += $qty;
    $message = 'Quantité du produit mise à jour dans le panier.';
} else {
    // Produit absent du panier OU produit avec un nouveau commentaire => ajout
    $cart[] = [
        'img1' => $product['food_image'],
        'nm' => $product['food_name'],
        'price' => $product['food_original_price'],
        'qty_total' => $qty,
        'tb_id' => $product['id'],
        'comment' => $comment, // 💡 Ajout du commentaire dans l'article du panier
    ];
    $message = 'Produit ajouté au panier.';
}

$cart_count = count($cart);
echo json_encode(['success' => true, 'message' => $message, 'cart_count' => $cart_count]);
exit;
?>