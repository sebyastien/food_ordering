<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
$qty_get = isset($_GET["qty"]) ? intval($_GET["qty"]) : 0;

if ($user_id && $id > 0) {
    $cart_key = 'cart_' . $user_id;
    
    if (isset($_SESSION[$cart_key])) {
        $found = false;
        
        foreach ($_SESSION[$cart_key] as $key => &$item) {
            if ($item['id'] == $id) {
                if ($qty_get > 0) {
                    $item['qty'] = $qty_get;
                    echo "Quantité du produit mise à jour avec succès.";
                } else {
                    unset($_SESSION[$cart_key][$key]);
                    echo "Produit retiré du panier.";
                }
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            echo "Erreur : Produit non trouvé dans le panier.";
        }
    } else {
        echo "Erreur : Panier non trouvé pour cet utilisateur.";
    }
} else {
    echo "Erreur : Les informations nécessaires sont manquantes ou la quantité est invalide.";
}
?>