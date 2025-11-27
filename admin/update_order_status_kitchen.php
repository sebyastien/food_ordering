<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

include "connection.php";

// Définir l'encodage UTF-8
mysqli_set_charset($link, "utf8mb4");

// Vérifier la connexion
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Vérifier que les données sont reçues
if (!isset($_POST['id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants'], JSON_UNESCAPED_UNICODE);
    exit;
}

$orderId = intval($_POST['id']);
$newStatus = trim($_POST['status']);

// Log pour debug (à retirer en production)
error_log("Order ID: " . $orderId);
error_log("New Status: " . $newStatus);

// Valider le statut - avec différentes encodages possibles
$allowedStatuses = ['En attente', 'En cours', 'Prête', 'PrÃªte', 'Terminée', 'TerminÃ©e', 'Annulée', 'AnnulÃ©e'];
if (!in_array($newStatus, $allowedStatuses)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Statut invalide: ' . $newStatus,
        'allowed' => $allowedStatuses
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Si le statut devient "Prête", on met aussi à jour ready_time
if ($newStatus === 'Prête' || $newStatus === 'PrÃªte') {
    $query = "UPDATE orders SET status = 'Prête', ready_time = NOW() WHERE id = ?";
} else {
    $query = "UPDATE orders SET status = ? WHERE id = ?";
}

$stmt = mysqli_prepare($link, $query);

if (!$stmt) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur de préparation de la requête: ' . mysqli_error($link)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($newStatus === 'Prête' || $newStatus === 'PrÃªte') {
    // Pour Prête, on n'a pas besoin de bind le status car il est fixé dans la requête
    mysqli_stmt_bind_param($stmt, "i", $orderId);
} else {
    mysqli_stmt_bind_param($stmt, "si", $newStatus, $orderId);
}

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Statut mis à jour avec succès',
            'order_id' => $orderId,
            'new_status' => $newStatus
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Vérifier si la commande existe
        $check_query = "SELECT id, status FROM orders WHERE id = ?";
        $check_stmt = mysqli_prepare($link, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $orderId);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $order = mysqli_fetch_assoc($check_result);
            echo json_encode([
                'success' => false, 
                'message' => 'Statut déjà à jour ou identique',
                'current_status' => $order['status']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Aucune commande trouvée avec cet ID: ' . $orderId
            ], JSON_UNESCAPED_UNICODE);
        }
        mysqli_stmt_close($check_stmt);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur lors de la mise à jour: ' . mysqli_stmt_error($stmt)
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_stmt_close($stmt);
mysqli_close($link);
?>