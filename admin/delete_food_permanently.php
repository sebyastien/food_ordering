<?php
include "connection.php";

$roles_autorises = ['admin', 'patron', 'gérant'];
include "auth_check.php";

// Récupérer l'ID du plat à supprimer
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Récupérer l'image avant suppression pour la supprimer du serveur
    $res = mysqli_query($link, "SELECT food_image FROM food WHERE id = $id");
    
    if ($row = mysqli_fetch_array($res)) {
        $image_path = $row['food_image'];
        
        // Supprimer définitivement le plat de la base de données
        $delete_query = "DELETE FROM food WHERE id = $id";
        
        if (mysqli_query($link, $delete_query)) {
            // Supprimer l'image du serveur si elle existe
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            
            // Rediriger avec message de succès
            header("Location: display_food.php?status=0&msg=deleted");
        } else {
            // Erreur lors de la suppression
            header("Location: display_food.php?status=0&error=delete_failed");
        }
    } else {
        // Plat non trouvé
        header("Location: display_food.php?status=0&error=not_found");
    }
} else {
    // ID invalide
    header("Location: display_food.php?status=0&error=invalid_id");
}

exit();
?>