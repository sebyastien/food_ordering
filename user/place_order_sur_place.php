<?php
session_start();
include "../admin/connection.php"; // Chemin vers votre fichier de connexion à la base de données

// ----------------------------------------------------
// Vérification de la Connexion à la DB (Sécurité)
// ----------------------------------------------------
if (!isset($conn) || $conn === null) {
    error_log("FATAL ERROR: Database connection failed in place_order_sur_place.php.");
    echo "<script>alert('A critical server error occurred. Please call the waiter.'); window.location.href='index.php';</script>";
    exit();
}

// ----------------------------------------------------
// 1. Récupération des IDs et des données
// ----------------------------------------------------
$table_id = isset($_SESSION['table_id']) ? intval($_SESSION['table_id']) : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Vérification cruciale
if ($table_id === 0 || !$user_id) {
    header("Location: index.php");
    exit();
}

// Panier spécifique à la table et à l'utilisateur
$cart_for_this_user = $_SESSION['carts_by_table'][$table_id][$user_id] ?? [];

if (count($cart_for_this_user) === 0) {
    header("Location: view_carte.php"); // Rediriger si panier vide
    exit();
}

// Données du formulaire
$client_name = trim(htmlspecialchars($_POST['client_name'] ?? ''));
$comment_general = trim(htmlspecialchars($_POST['comment'] ?? ''));
$cart_total = floatval($_POST['total_price'] ?? 0); // Récupéré depuis le champ caché dans checkout.php

// ----------------------------------------------------
// 2. ENREGISTREMENT DANS LA BASE DE DONNÉES
// ----------------------------------------------------

try {
    // Statut : 'In Kitchen' car le paiement sera fait plus tard.
    $order_status = 'In Kitchen'; 
    $payment_method = 'On Site'; // Paiement sur place, non immédiat
    $payment_status = 'Unpaid';
    $order_type = 'On Site';

    // Insertion de la commande principale (table 'order_manager' ou équivalent)
    // NOTE: Assurez-vous que votre table order_manager a les colonnes nécessaires (table_id, order_type, general_comment)
    $stmt_order = $conn->prepare("INSERT INTO order_manager (table_id, cust_name, order_type, order_status, payment_method, payment_status, total_price, general_comment, created_by_user_id) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_order->execute([$table_id, $client_name, $order_type, $order_status, $payment_method, $payment_status, $cart_total, $comment_general, $user_id]);
    
    $order_id = $conn->lastInsertId();

    // Insertion des produits (table 'order_item')
    $stmt_item = $conn->prepare("INSERT INTO order_item (order_id, product_name, price, qty, comment) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($cart_for_this_user as $item) {
        $product_name = $item['name'] ?? 'Unknown Product';
        $price = $item['price'] ?? 0;
        $qty = $item['qty_total'] ?? $item['qty'] ?? 1; // Utiliser qty_total du panier sur place
        $item_comment = $item['comment'] ?? ''; 
        
        $stmt_item->execute([$order_id, $product_name, $price, $qty, $item_comment]);
    }

    // ----------------------------------------------------
    // 3. VIDER LE PANIER ET REDIRIGER
    // ----------------------------------------------------
    
    // Vider le panier spécifique de cet utilisateur pour cette table
    unset($_SESSION['carts_by_table'][$table_id][$user_id]);
    
    // Redirection vers une page de confirmation
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Order Confirmed</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding-top: 50px; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: auto; padding: 30px; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); background: #fff; }
            h2 { color: #a40301; }
            .success { color: #28a745; font-weight: bold; font-size: 1.2em; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Order Sent! (Table <?= $table_id ?>)</h2>
            <p class="success">Your order #<?= $order_id ?> has been sent to the kitchen.</p>
            <p>You can continue ordering or wait for the service.</p>
            <a href="index.php?table_id=<?= $table_id ?>" style="color: #007bff; text-decoration: none; font-weight: bold;">Continue Ordering</a>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    error_log("Order insertion failed (On Site): " . $e->getMessage());
    echo "<script>alert('An error occurred while sending your order. Please notify the waiter.'); window.location.href='view_carte.php';</script>";
}
?>