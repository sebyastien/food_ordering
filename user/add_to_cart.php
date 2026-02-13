<?php
session_start();

header('Content-Type: application/json');

// ================================
// VALIDATION DE SESSION PERMISSIVE
// ================================
// On vérifie juste que les variables de session existent
// Sans faire de validation stricte en BDD

// Vérifier que la session PHP existe
if (!isset($_SESSION['session_token']) || !isset($_SESSION['table_id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session non active. Veuillez scanner le QR code.']);
    exit;
}

// Récupérer les identifiants
$table_id = intval($_SESSION['table_id']);
$user_id = $_SESSION['user_id'];
$session_token = $_SESSION['session_token'];

// ================================
// VALIDATION LÉGÈRE (optionnelle)
// ================================
// On valide SEULEMENT si ça fait plus de 5 minutes depuis le dernier ajout
$should_validate = true;

if (isset($_SESSION['last_cart_action'])) {
    $time_since_last = time() - $_SESSION['last_cart_action'];
    if ($time_since_last < 300) { // Moins de 5 minutes
        $should_validate = false; // Pas besoin de revalider
    }
}

// Si on doit valider, on le fait SANS bloquer
if ($should_validate) {
    include "../admin/connection.php";
    require_once "../admin/TableSessionManager.php";
    
    try {
        $sessionManager = new TableSessionManager($link);
        $validation = $sessionManager->validateToken($session_token);
        
        // Si la session est invalide, on le signale MAIS on n'empêche PAS l'ajout
        if (!$validation['valid']) {
            // On met à jour les infos de session au cas où
            // mais on continue quand même
            error_log("Session validation warning: " . ($validation['error'] ?? 'unknown'));
        }
    } catch (Exception $e) {
        // En cas d'erreur, on continue quand même
        error_log("Session validation error: " . $e->getMessage());
    }
}

// Mettre à jour le timestamp de dernière action
$_SESSION['last_cart_action'] = time();

// ================================
// RÉCUPÉRER LES DONNÉES DU PRODUIT
// ================================
$id = null;
$qty = 1;
<<<<<<< Updated upstream
=======
$comment = ""; 
>>>>>>> Stashed changes

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
<<<<<<< Updated upstream
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
=======
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : "";
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
    $comment = isset($_GET['comment']) ? trim($_GET['comment']) : "";
>>>>>>> Stashed changes
}

// Vérifier que le produit est valide
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Produit non spécifié.']);
    exit;
}

// ================================
// RÉCUPÉRER LE PRODUIT
// ================================
if (!isset($link)) {
    include "../admin/connection.php";
}

$stmt = $link->prepare("SELECT * FROM food WHERE id = ? AND is_active = 1 LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Produit introuvable ou indisponible.']);
    $stmt->close();
    exit;
}

$product = $res->fetch_assoc();
$stmt->close();

// ================================
// GESTION DU PANIER
// ================================
// Initialisation de la structure de paniers
if (!isset($_SESSION['carts_by_table'])) {
    $_SESSION['carts_by_table'] = [];
}
if (!isset($_SESSION['carts_by_table'][$table_id])) {
    $_SESSION['carts_by_table'][$table_id] = [];
}
if (!isset($_SESSION['carts_by_table'][$table_id][$user_id])) {
    $_SESSION['carts_by_table'][$table_id][$user_id] = [];
}

// On travaille avec le panier de l'utilisateur actuel
$cart = &$_SESSION['carts_by_table'][$table_id][$user_id];

// Rechercher le produit dans le panier
$foundIndex = null;
<<<<<<< Updated upstream
foreach ($cart as $index => $item) {
    if ($item['tb_id'] == $id) {
        $foundIndex = $index;
        break;
=======

// IMPORTANT : On ne fusionne que si le commentaire est identique
if (empty($comment)) {
    // Pas de commentaire → chercher un article existant SANS commentaire
    foreach ($cart as $index => $item) {
        if ($item['tb_id'] == $id && empty($item['comment'])) { 
            $foundIndex = $index;
            break;
        }
>>>>>>> Stashed changes
    }
} else {
    // Avec commentaire → chercher un article avec LE MÊME commentaire
    foreach ($cart as $index => $item) {
        if ($item['tb_id'] == $id && isset($item['comment']) && $item['comment'] === $comment) {
            $foundIndex = $index;
            break;
        }
    }
}

// ================================
// AJOUTER OU METTRE À JOUR
// ================================
if ($foundIndex !== null) {
<<<<<<< Updated upstream
    // Produit déjà dans le panier de cet utilisateur => augmenter la quantité
    $cart[$foundIndex]['qty_total'] += $qty;
} else {
    // Produit absent du panier de cet utilisateur => ajout
=======
    // Produit déjà dans le panier avec le même commentaire → augmenter la quantité
    $cart[$foundIndex]['qty_total'] += $qty;
    $message = 'Quantité mise à jour dans le panier.';
} else {
    // Produit absent OU avec un nouveau commentaire → ajout
>>>>>>> Stashed changes
    $cart[] = [
        'img1' => $product['food_image'],
        'nm' => $product['food_name'],
        'price' => $product['food_discount_price'],
        'qty_total' => $qty,
        'tb_id' => $product['id'],
<<<<<<< Updated upstream
=======
        'comment' => $comment,
>>>>>>> Stashed changes
    ];
}

$cart_count = count($cart);
<<<<<<< Updated upstream
echo json_encode(['success' => true, 'message' => 'Produit ajouté au panier', 'cart_count' => $cart_count]);
=======
echo json_encode([
    'success' => true, 
    'message' => $message, 
    'cart_count' => $cart_count
]);
>>>>>>> Stashed changes
exit;
?>
