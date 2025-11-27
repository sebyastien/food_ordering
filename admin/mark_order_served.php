<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

include "connection.php";

// Définir l'encodage UTF-8
mysqli_set_charset($link, "utf8mb4");

// Vérifier la connexion
if (!$link) {
    echo json_encode(['success' => false, 'error' => 'Erreur de connexion à la base de données'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Vérifier que les données sont reçues
if (!isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de commande manquant'], JSON_UNESCAPED_UNICODE);
    exit;
}

$order_id = intval($_POST['order_id']);

// Vérifier que la commande existe et est bien "Prête"
$check_query = "SELECT id, status, order_number FROM orders WHERE id = ?";
$check_stmt = mysqli_prepare($link, $check_query);
mysqli_stmt_bind_param($check_stmt, "i", $order_id);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'error' => 'Commande introuvable'], JSON_UNESCAPED_UNICODE);
    mysqli_stmt_close($check_stmt);
    exit;
}

$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($check_stmt);

// Mettre à jour le statut à "Terminée" et enregistrer l'heure de service
$update_query = "UPDATE orders SET status = 'Terminée', served_time = NOW() WHERE id = ?";
$stmt = mysqli_prepare($link, $update_query);

if (!$stmt) {
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur de préparation de la requête: ' . mysqli_error($link)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $order_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Commande marquée comme servie',
            'order_id' => $order_id,
            'order_number' => $order['order_number']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Aucune modification effectuée (commande déjà servie ?)',
            'current_status' => $order['status']
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur lors de la mise à jour: ' . mysqli_stmt_error($stmt)
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_stmt_close($stmt);
mysqli_close($link);
?>