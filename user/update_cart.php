<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $_POST['item_name'] ?? '';
    $item_price = $_POST['item_price'] ?? '';
    $item_qty = intval($_POST['item_qty'] ?? 1);
    $item_img = $_POST['item_img'] ?? '';
    $table_id = $_POST['table_id'] ?? 0;

    if (!$item_name || !$item_price) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
        exit;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Chercher si l'article est déjà dans le panier
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['nm'] === $item_name) {
            $item['qty_total'] += $item_qty;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = [
            'nm' => $item_name,
            'price' => $item_price,
            'qty_total' => $item_qty,
            'img1' => $item_img,
            'tb_id' => $table_id,
        ];
    }

    // Calcul du total d'articles
    $total_items = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_items += $item['qty_total'];
    }

    echo json_encode(['success' => true, 'total_items' => $total_items]);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Mauvaise requête']);
