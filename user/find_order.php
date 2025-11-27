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
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // ✅ Redirection si commande trouvée
                header("Location: track_order.php?order_number=" . urlencode($order_number));
                exit(); // 🚨 INDISPENSABLE pour stopper ici
            } else {
                $error = "Numéro de commande incorrect.";
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
        <h4 class="card-title text-center mb-4">Suivi de commande</h4>

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
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Suivre ma commande</button>
            </div>
        </form>
    </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>