<?php
<<<<<<< HEAD
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
=======
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 🔑 On récupère l'ID de la table depuis la session
$table_id = isset($_SESSION['table_id']) ? intval($_SESSION['table_id']) : 0;
// 🔑 On récupère l'ID de l'utilisateur depuis la session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
>>>>>>> 4470edb (maj)

$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
$qty_get = isset($_GET["qty"]) ? intval($_GET["qty"]) : 0;

<<<<<<< HEAD
// Vérifier que les paramètres sont valides
if ($table_id > 0 && $user_id && $id > 0 && $qty_get > 0 && isset($_SESSION['carts_by_table'][$table_id][$user_id])) {
    // Référence le panier spécifique à cet utilisateur
=======
// On vérifie si les variables de session et les paramètres GET sont valides
if ($table_id > 0 && $user_id && $id > 0 && $qty_get > 0 && isset($_SESSION['carts_by_table'][$table_id][$user_id])) {
    // 🔑 On référence le panier spécifique à cet utilisateur pour le modifier
>>>>>>> 4470edb (maj)
    $cart = &$_SESSION['carts_by_table'][$table_id][$user_id];
    $found = false;
    
    foreach ($cart as $key => $item) {
        if (isset($item['tb_id']) && $item['tb_id'] == $id) {
            // Produit trouvé, on met à jour sa quantité
            $cart[$key]['qty_total'] = $qty_get;
            $found = true;
<<<<<<< HEAD
            break;
=======
            break; // Quitter la boucle une fois l'article trouvé et mis à jour
>>>>>>> 4470edb (maj)
        }
    }

    if ($found) {
<<<<<<< HEAD
        echo "Quantité mise à jour";
    } else {
        echo "Produit non trouvé";
    }
} else {
    echo "Informations manquantes";
}
?>
=======
        echo "Quantité du produit mise à jour avec succès pour l'utilisateur $user_id à la table $table_id.";
    } else {
        echo "Erreur : Produit non trouvé dans le panier de l'utilisateur $user_id à la table $table_id.";
    }
} else {
    echo "Erreur : Les informations nécessaires sont manquantes (ID de table, ID d'utilisateur ou produit).";
}
?>
>>>>>>> 4470edb (maj)
