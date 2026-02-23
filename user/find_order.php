<?php
// 🔧 Débogage (à désactiver en production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔗 Connexion à la base
session_start();
include "../admin/connection.php";

// 🔧 Active les erreurs MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$error = '';
$order_number = '';
<<<<<<< HEAD
// $customer_name a été supprimé

// ⏳ Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // On ne vérifie que la présence de 'order_number'
    if (isset($_POST['order_number'])) {
        $order_number = trim($_POST['order_number']);

        if (empty($order_number)) {
            $error = "Veuillez saisir votre numéro de commande.";
        } else {
            // Requête MODIFIÉE : Ne vérifie que le numéro de commande
            $stmt = $link->prepare("SELECT id FROM orders WHERE order_number = ?");
            $stmt->bind_param("s", $order_number); // Un seul paramètre
=======
$customer_name = '';

// ⏳ Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['order_number']) && isset($_POST['customer_name'])) {
        $order_number = trim($_POST['order_number']);
        $customer_name = trim($_POST['customer_name']);

        if (empty($order_number) || empty($customer_name)) {
            $error = "Veuillez remplir tous les champs.";
        } else {
            // Requête sécurisée
            $stmt = $link->prepare("SELECT id FROM orders WHERE order_number = ? AND LOWER(customer_name) = LOWER(?)");
            $stmt->bind_param("ss", $order_number, $customer_name);
>>>>>>> 4470edb (maj)
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // ✅ Redirection si commande trouvée
                header("Location: track_order.php?order_number=" . urlencode($order_number));
                exit(); // 🚨 INDISPENSABLE pour stopper ici
            } else {
<<<<<<< HEAD
                $error = "Numéro de commande incorrect.";
=======
                $error = "Numéro de commande ou nom du client incorrect.";
>>>>>>> 4470edb (maj)
            }

            $stmt->close();
        }
    } else {
        $error = "Une erreur est survenue. Veuillez réessayer.";
    }
}

$link->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Retrouver ma commande</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .card {
            max-width: 500px;
            margin: 50px auto;
        }
    </style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container mt-5">
    <div class="card p-4">
<<<<<<< HEAD
        <h4 class="card-title text-center mb-4">Suivi de commande</h4>
=======
        <h4 class="card-title text-center mb-4">Retrouver ma commande</h4>
>>>>>>> 4470edb (maj)

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="find_order.php" method="POST">
            <div class="mb-3">
                <label for="order_number" class="form-label">Numéro de commande</label>
                <input type="text" class="form-control" id="order_number" name="order_number"
                       value="<?= htmlspecialchars($order_number) ?>" required>
            </div>
<<<<<<< HEAD
=======
            <div class="mb-3">
                <label for="customer_name" class="form-label">Nom du client</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name"
                       value="<?= htmlspecialchars($customer_name) ?>" required>
            </div>
>>>>>>> 4470edb (maj)
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Suivre ma commande</button>
            </div>
        </form>
    </div>
</div>

<?php include "footer.php"; ?>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> 4470edb (maj)
