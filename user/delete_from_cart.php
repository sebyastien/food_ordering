<?php
session_start();

// ================================
// VÉRIFICATION SIMPLE DE SESSION
// ================================
// On vérifie SEULEMENT que les variables PHP existent
// PAS de validation en BDD

if (!isset($_SESSION['table_id']) || !isset($_SESSION['user_id'])) {
    // Pas de session PHP - rediriger
    header("Location: index.php");
    exit;
}

// ================================
// SUPPRESSION DU PRODUIT
// ================================
$table_id = intval($_SESSION['table_id']);
$user_id = $_SESSION['user_id'];
$tb_id = isset($_GET['tb_id']) ? intval($_GET['tb_id']) : 0;

if ($table_id > 0 && $tb_id > 0 && !empty($user_id) && isset($_SESSION['carts_by_table'][$table_id][$user_id])) {
    // Référence le panier spécifique à cette table ET cet utilisateur
    $cart = &$_SESSION['carts_by_table'][$table_id][$user_id];

    // Parcourt le panier pour trouver l'article à supprimer
    foreach ($cart as $index => $item) {
        if (isset($item['tb_id']) && $item['tb_id'] == $tb_id) {
            unset($cart[$index]); // Supprime l'article du panier
            $cart = array_values($cart); // Réindexe proprement le tableau
            break;
        }
    }
}
?>
