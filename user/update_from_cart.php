<?php
session_start();

// ================================
// VÉRIFICATION SIMPLE DE SESSION
// ================================
// On vérifie SEULEMENT que les variables PHP existent
// PAS de validation en BDD

if (!isset($_SESSION['table_id']) || !isset($_SESSION['user_id'])) {
    echo "Erreur : Session non active";
    exit;
}

// ================================
// MISE À JOUR DE LA QUANTITÉ
// ================================
$table_id = intval($_SESSION['table_id']);
$user_id = $_SESSION['user_id'];

$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
$qty_get = isset($_GET["qty"]) ? intval($_GET["qty"]) : 0;

// Vérifier que les paramètres sont valides
if ($table_id > 0 && $user_id && $id > 0 && $qty_get > 0 && isset($_SESSION['carts_by_table'][$table_id][$user_id])) {
    // Référence le panier spécifique à cet utilisateur
    $cart = &$_SESSION['carts_by_table'][$table_id][$user_id];
    $found = false;
    
    foreach ($cart as $key => $item) {
        if (isset($item['tb_id']) && $item['tb_id'] == $id) {
            // Produit trouvé, on met à jour sa quantité
            $cart[$key]['qty_total'] = $qty_get;
            $found = true;
            break;
        }
    }

    if ($found) {
        echo "Quantité mise à jour";
    } else {
        echo "Produit non trouvé";
    }
} else {
    echo "Informations manquantes";
}
?>
