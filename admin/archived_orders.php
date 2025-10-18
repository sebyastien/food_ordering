<?php
session_start();

include "connection.php";

$roles_autorises = ['admin', 'patron', 'gerant'];
include "auth_check.php";

include "header.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$link) {
    die("Erreur de connexion à la base de données.");
}

// On sélectionne SEULEMENT les commandes terminées
$query = "SELECT * FROM orders WHERE status = 'Terminée' ORDER BY order_date DESC";
$result = $link->query($query);
if (!$result) {
    die("Erreur lors de la récupération des commandes : " . $link->error);
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>Archives des commandes</strong>
            <a href="kitchen_orders.php" class="btn btn-primary btn-sm float-end">Retour aux commandes en cours</a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Table</th>
                        <th>Total (€)</th>
                        <th>Paiement</th>
                        <th>N°commande</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= $order['order_date'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td>
                            <?php
                                // Conversion du type de commande
                                if ($order['order_type'] === 'takeaway') {
                                    echo 'À emporter';
                                } else {
                                    echo htmlspecialchars($order['order_type']);
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                // Affichage d'un tiret pour les commandes à emporter
                                if ($order['order_type'] === 'takeaway') {
                                    echo '-';
                                } else {
                                    echo htmlspecialchars($order['table_id']);
                                }
                            ?>
                        </td>
                        <td><?= number_format($order['total_price'], 2) ?></td>
                        <td><?= htmlspecialchars($order['payment_method']) ?></td>
                        <td><?= $order['order_number'] ?></td>
                        <td>
                            <?= htmlspecialchars($order['status']) ?>
                        </td>
                        <td>
                            <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-info btn-sm">Détails</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows === 0): ?>
                        <tr><td colspan="10" class="text-center">Aucune commande archivée.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>