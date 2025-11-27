<?php
session_start();

// Vérifier que l'utilisateur a les droits
$roles_autorises = ['admin', 'patron', 'gérant'];
include "auth_check.php";

include "connection.php";

// Définir l'encodage UTF-8
mysqli_set_charset($link, "utf8mb4");

// Vérifier la connexion
if (!$link) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    header("Location: all_user.php");
    exit;
}

// Vérifier que l'ID est fourni
if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    $_SESSION['error_message'] = "ID utilisateur manquant.";
    header("Location: all_user.php");
    exit;
}

$user_id = intval($_POST['user_id']);

// Vérifier que l'utilisateur existe et récupérer son nom
$check_query = "SELECT id, username FROM admin_login WHERE id = ?";
$check_stmt = mysqli_prepare($link, $check_query);
mysqli_stmt_bind_param($check_stmt, "i", $user_id);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error_message'] = "❌ Utilisateur introuvable.";
    mysqli_stmt_close($check_stmt);
    header("Location: all_user.php");
    exit;
}

$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($check_stmt);

// Empêcher la suppression de son propre compte (optionnel)
if (isset($_SESSION['username']) && $_SESSION['username'] === $user['username']) {
    $_SESSION['error_message'] = "❌ Vous ne pouvez pas supprimer votre propre compte !";
    header("Location: all_user.php");
    exit;
}

// Supprimer l'utilisateur
$delete_query = "DELETE FROM admin_login WHERE id = ?";
$stmt = mysqli_prepare($link, $delete_query);

if (!$stmt) {
    $_SESSION['error_message'] = "❌ Erreur de préparation de la requête : " . mysqli_error($link);
    header("Location: all_user.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $user_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success_message'] = "✅ L'utilisateur '" . htmlspecialchars($user['username']) . "' a été supprimé avec succès !";
    } else {
        $_SESSION['error_message'] = "❌ Aucune suppression effectuée.";
    }
} else {
    $_SESSION['error_message'] = "❌ Erreur lors de la suppression : " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($link);

// Rediriger vers la page de liste
header("Location: all_user.php");
exit;
?>