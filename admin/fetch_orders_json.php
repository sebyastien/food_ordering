<?php
// fetch_orders_json.php
session_start();
include "connection.php";

// Vérifier si la connexion à la base de données a réussi
if (!$link) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur de connexion à la base de données."]);
    exit;
}

// Requête pour récupérer les commandes en cours avec le statut 'En attente'
$query = "SELECT id, order_date, customer_name, order_type, table_id, total_price, payment_method, order_number, status FROM orders WHERE status = 'En attente' ORDER BY order_date ASC";

$result = mysqli_query($link, $query);

$orders = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Logique de conversion ici
        if ($row['order_type'] === 'takeaway') {
            $row['order_type'] = 'À emporter';
            $row['table_id'] = '-'; // Remplace le numéro de table par un tiret
        }
        $orders[] = $row;
    }
} else {
    http_response_code(500);
    echo json_encode(["error" => "Erreur de requête SQL: " . mysqli_error($link)]);
    exit;
}

// Renvoyer les données au format JSON
header('Content-Type: application/json');
echo json_encode($orders);

mysqli_close($link);
?>