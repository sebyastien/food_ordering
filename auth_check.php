<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Durée max d'inactivité en secondes (30 minutes)
$timeout_duration = 30 * 60;

if (!isset($_SESSION['admin_username'])) {
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}

$_SESSION['last_activity'] = time();

// Cette ligne doit être supprimée pour que le script utilise la variable définie dans la page appelante
// $roles_autorises = ['admin', 'patron', 'gerant'];

if (!in_array($_SESSION['admin_role'], $roles_autorises)) {
    header("Location: unauthorized.php");
    exit();
}
?>