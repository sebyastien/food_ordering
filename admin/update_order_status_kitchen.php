<?php
include "connection.php";

$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon la page
include "auth_check.php";

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['id']);
    $new_status = $_POST['status'];

    $valid_statuses = ['En attente', 'Terminée'];
    if (!in_array($new_status, $valid_statuses)) {
        $response['message'] = "Statut invalide.";
        echo json_encode($response);
        exit;
    }

    $stmt = $link->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Statut de la commande #{$order_id} mis à jour avec succès.";
    } else {
        $response['message'] = "Erreur lors de la mise à jour du statut : " . $stmt->error;
    }

    $stmt->close();
    $link->close();
} else {
    $response['message'] = "Requête invalide.";
}

echo json_encode($response);
?>