<?php
// Inclut le fichier de connexion à la base de données
include "connection.php";

$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon la page
include "auth_check.php";

// Affiche les erreurs MySQLi pour faciliter le débogage
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Initialise le tableau de réponse
$response = ['success' => false, 'message' => '', 'details' => []];

// Vérifie si un ID de commande valide a été fourni dans l'URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = $_GET['id'];
    
    // Requête SQL corrigée pour utiliser les noms de tables et de colonnes de votre base de données
    $stmt = $link->prepare("
        SELECT food_name, quantity, price
        FROM order_items
        WHERE order_id = ?
    ");
    $stmt->bind_param("i", $order_id); // Lie l'ID de commande à la requête
    $stmt->execute();
    $result = $stmt->get_result();

    // Vérifie si la requête a renvoyé des résultats
    if ($result) {
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row; // Ajoute chaque ligne de résultat au tableau de détails
        }
        $response['success'] = true; // Indique que la requête a réussi
        $response['details'] = $details; // Ajoute les détails au tableau de réponse
    } else {
        // En cas d'erreur lors de l'exécution de la requête
        $response['message'] = "Erreur lors de la récupération des détails de la commande.";
    }

    $stmt->close(); // Ferme la requête préparée
} else {
    // Si aucun ID valide n'est fourni, renvoie un message d'erreur
    $response['message'] = "ID de commande non valide.";
}

// Encode le tableau de réponse au format JSON et l'affiche
echo json_encode($response);
$link->close(); // Ferme la connexion à la base de données
?>