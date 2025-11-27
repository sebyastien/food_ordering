<?php
session_start();

include "connection.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$link) {
    die("Erreur de connexion à la base de données.");
}

// On sélectionne UNIQUEMENT les commandes terminées ou annulées pour les archives
$query = "SELECT * FROM orders WHERE status = 'Terminée' OR status = 'Annulée' ORDER BY order_date DESC";
$result = $link->query($query);
if (!$result) {
    die("Erreur lors de la récupération des commandes : " . $link->error);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Archives des commandes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Style pour les commentaires */
        .item-comment {
            font-size: 0.85em;
            color: #a40301;
            font-style: italic;
            display: block;
            margin-top: 3px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>Archives des commandes</strong>
            <a href="cuisine.php" class="btn btn-primary btn-sm float-end">Retour aux commandes en cours</a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Table</th>
                        <th>Total</th>
                        <th>Paiement</th>
                        <th>N°commande</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $order['order_date'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td>
                            <?php
                                if ($order['order_type'] === 'takeaway') {
                                    echo 'À emporter';
                                } else {
                                    echo htmlspecialchars($order['order_type']);
                                }
                            ?>
                        </td>
                        <td>
                            <?php
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
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td>
                            <button class="btn btn-info btn-sm details-btn" 
                                    data-id="<?= $order['id'] ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#orderDetailsModal">
                                Détails
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows === 0): ?>
                        <tr><td colspan="9" class="text-center">Aucune commande archivée.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Détails de la commande archivée n°<span id="orderIdModal"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Instructions spéciales</th>
                            <th>Quantité</th>
                            <th>Prix unitaire (€)</th>
                        </tr>
                    </thead>
                    <tbody id="orderDetailsTableBody">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderDetailsModalElement = document.getElementById('orderDetailsModal');
    const orderDetailsTableBody = document.getElementById('orderDetailsTableBody');
    const orderIdModal = document.getElementById('orderIdModal');

    // Écouter l'événement d'ouverture du modal
    orderDetailsModalElement.addEventListener('show.bs.modal', function (event) {
        // Récupérer le bouton qui a déclenché le modal
        const button = event.relatedTarget;
        const orderId = button.getAttribute('data-id');
        
        orderIdModal.textContent = orderId;
        orderDetailsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Chargement...</td></tr>';
        
        fetch(`fetch_order_details.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.details.length > 0) {
                    let detailsHtml = '';
                    data.details.forEach(detail => {
                        const comment = detail.item_comment && detail.item_comment.trim() !== '' 
                            ? `<span class="item-comment"><i class="fa fa-comment-dots"></i> ${detail.item_comment}</span>` 
                            : '<span class="text-muted">Aucune</span>';
                        
                        detailsHtml += `
                            <tr>
                                <td>${detail.food_name}</td>
                                <td>${comment}</td>
                                <td>${detail.quantity}</td>
                                <td>${detail.price}</td>
                            </tr>
                        `;
                    });
                    orderDetailsTableBody.innerHTML = detailsHtml;
                } else {
                    orderDetailsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Aucun détail trouvé.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails:', error);
                orderDetailsTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Erreur de chargement des détails.</td></tr>';
            });
    });

    // Nettoyer le contenu à la fermeture
    orderDetailsModalElement.addEventListener('hidden.bs.modal', function () {
        orderDetailsTableBody.innerHTML = '';
    });
});
</script>

<?php include "footer.php"; ?>
</body>
</html>