<?php
session_start();

// On s'assure que l'ID de la table et l'ID de l'utilisateur sont présents
$table_id = isset($_SESSION['table_id']) ? intval($_SESSION['table_id']) : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; // Récupérer l'ID de l'utilisateur
$tb_id = isset($_GET['tb_id']) ? intval($_GET['tb_id']) : 0;

if ($table_id > 0 && $tb_id > 0 && !empty($user_id) && isset($_SESSION['carts_by_table'][$table_id][$user_id])) {
    // 🔑 On référence le panier spécifique à cette table ET cet utilisateur pour le modifier
    $cart = &$_SESSION['carts_by_table'][$table_id][$user_id];

    // On parcourt le panier pour trouver l'article à supprimer
    foreach ($cart as $index => $item) {
        if (isset($item['tb_id']) && $item['tb_id'] == $tb_id) {
            unset($cart[$index]); // Supprime l'article du panier
            $cart = array_values($cart); // Réindexe proprement le tableau après suppression
            break;
        }
    }
}
?>