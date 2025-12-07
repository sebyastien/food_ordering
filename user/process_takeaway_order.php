<?php
session_start();
include "../admin/connection.php"; // Chemin vers votre fichier de connexion à la base de données

// ----------------------------------------------------
// FIX ERREUR : Vérification de la connexion à la DB
// ----------------------------------------------------
if (!isset($conn) || $conn === null) {
    // Si la connexion a échoué dans connection.php, on arrête le script ici.
    error_log("FATAL ERROR: Database connection variable \$conn is not set. Check your database credentials in ../admin/connection.php.");
    echo "<script>alert('A critical error occurred: Database connection failed. Please check your credentials and server status.'); window.location.href='view_carte_takeaway.php';</script>";
    exit();
}
// ----------------------------------------------------

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$cart_for_this_user = isset($_SESSION['cart_' . $user_id]) ? $_SESSION['cart_' . $user_id] : [];

if (!isset($_POST['final_checkout']) || count($cart_for_this_user) == 0) {
    header("Location: view_carte_takeaway.php"); // Rediriger si accès direct ou panier vide
    exit();
}

// ----------------------------------------------------
// 1. Récupération des données du formulaire
// ----------------------------------------------------
$cust_name = trim(htmlspecialchars($_POST['cust_name']));
$phone_number = trim(htmlspecialchars($_POST['phone_number']));
$pickup_time = trim(htmlspecialchars($_POST['pickup_time']));
$comment_general = trim(htmlspecialchars($_POST['comment'] ?? ''));
$payment_method = 'Online'; // Forcé pour le takeaway
$payment_status = 'Pending'; // Statut initial avant le paiement en ligne
$order_type = 'Takeaway';

// ----------------------------------------------------
// 2. Calcul du Total
// ----------------------------------------------------
$cart_total = 0;
foreach ($cart_for_this_user as $item) {
    $qty = intval($item['qty']);
    $price = floatval($item['price']);
    $cart_total += $qty * $price;
}

// ----------------------------------------------------
// 3. ENREGISTREMENT DANS LA BASE DE DONNÉES
// ----------------------------------------------------

try {
    // Insertion de la commande principale (table 'order' ou équivalent)
    $stmt_order = $conn->prepare("INSERT INTO order_manager (cust_name, order_type, order_status, payment_method, payment_status, total_price, customer_phone, pickup_time, general_comment) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_order->execute([$cust_name, $order_type, 'Waiting for Payment', $payment_method, $payment_status, $cart_total, $phone_number, $pickup_time, $comment_general]);
    
    // Récupérer l'ID de la commande insérée
    $order_id = $conn->lastInsertId();

    // Insertion des produits (table 'order_item' ou équivalent)
    $stmt_item = $conn->prepare("INSERT INTO order_item (order_id, product_name, price, qty, comment) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($cart_for_this_user as $item) {
        $product_name = $item['name'];
        $price = $item['price'];
        $qty = $item['qty'];
        $item_comment = $item['comment'] ?? ''; // Commentaire spécifique au produit
        
        $stmt_item->execute([$order_id, $product_name, $price, $qty, $item_comment]);
    }

    // ----------------------------------------------------
    // 4. INITIATION DU PAIEMENT EN LIGNE (Zone à coder)
    // ----------------------------------------------------
    
    // Vider le panier de session APRÈS avoir stocké les items dans la DB
    unset($_SESSION['cart_' . $user_id]); 
    
    // --- SIMULATION DE REDIRECTION ---
    // C'est ici que vous intégrerez la redirection vers Stripe/PayPal avec l'order_id
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Payment Initiation</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding-top: 50px; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: auto; padding: 30px; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); background: #fff; }
            h2 { color: #a40301; }
            .total { font-size: 1.5em; font-weight: bold; margin: 20px 0; color: #28a745;}
            .simulated { color: #007bff; background: #e9f5ff; padding: 15px; border-radius: 6px; border: 1px dashed #007bff; margin-top: 20px;}
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Order #<?= $order_id ?> Placed (Takeaway)</h2>
            <p>Your order details have been saved successfully. Status: **Waiting for Payment**.</p>
            <p>Total amount due:</p>
            <div class="total"><?= number_format($cart_total, 2) ?> €</div>
            
            <div class="simulated">
                🚨 **Next Step (Online Payment):** You should be automatically redirected to the secure payment platform (Stripe, PayPal, etc.).
            </div>
            
            <p style="margin-top: 20px;">If the redirection fails, please check your online payment integration code.</p>
            <a href="index.php" style="color: #a40301; text-decoration: none; font-weight: bold;">Return to Home Page</a>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    // Gérer l'erreur de base de données
    error_log("Order insertion failed: " . $e->getMessage());
    echo "<script>alert('An error occurred while processing your order (DB write error). Please try again or contact support.'); window.location.href='view_carte_takeaway.php';</script>";
}
?>