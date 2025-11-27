<?php
session_start();
include "../admin/connection.php";

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
}

// 🔒 Récupération de l'ID de l'utilisateur
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Vérifier que les identifiants sont présents
if (!$id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Informations de commande manquantes.']);
    exit;
}

$stmt = $link->prepare("SELECT * FROM food WHERE id = ? AND is_active = 1 LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Produit introuvable']);
    exit;
}

$product = $res->fetch_assoc();
$stmt->close();

// 🔒 Initialisation du panier pour l'utilisateur
$cart_key = 'cart_' . $user_id;

if (!isset($_SESSION[$cart_key])) {
    $_SESSION[$cart_key] = [];
}

// Rechercher le produit dans le panier de CET utilisateur
$foundIndex = null;

// ● IMPORTANT : On ne fusionne plus les articles existants s'ils ont un commentaire différent.
// L'idée est de traiter un plat avec commentaire comme un article distinct.
// Si le commentaire est vide, on peut fusionner si l'article existe déjà SANS commentaire.
if (empty($comment)) {
    foreach ($_SESSION[$cart_key] as $index => $item) {
        // Vérifie si l'ID correspond ET qu'il n'y a pas de commentaire
        if ($item['id'] == $id && empty($item['comment'])) {
            $foundIndex = $index;
            break;
        }
    }
}

if ($foundIndex !== null) {
    // Produit déjà dans le panier SANS commentaire => augmenter la quantité
    $_SESSION[$cart_key][$foundIndex]['qty'] += $qty;
    $message = 'Quantité du produit mise à jour dans le panier.';
} else {
    // Produit absent du panier OU produit avec un nouveau commentaire => ajout
    $_SESSION[$cart_key][] = [
        'id' => $product['id'],
        'name' => $product['food_name'],
        'price' => $product['food_original_price'],
        'image' => $product['food_image'],
        'qty' => $qty,
        'comment' => $comment, // 💡 Ajout du commentaire dans l'article du panier
    ];
    $message = 'Produit ajouté au panier.';
}

$cart_count = count($_SESSION[$cart_key]);
echo json_encode(['success' => true, 'message' => $message, 'cart_count' => $cart_count]);
exit;
?>