<?php
session_start();

include "connection.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$link) {
    die("Erreur de connexion à la base de données.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes cuisine</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Style pour les détails de la commande */
        .order-details-row {
            background-color: #f8f9fa; /* Couleur de fond pour distinguer */
        }
        .order-details-table {
            width: 100%;
            margin-bottom: 0;
        }
        .loading-details {
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>Commandes en cours (cuisine)</strong>
            <a href="archives.php" class="btn btn-secondary btn-sm float-end">Archives</a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        
                        <th>Date</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Table</th>
                        <th>Paiement</th>
                        <th>N°commande</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <tr><td colspan="8" class="text-center">Chargement des commandes...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('orders-table-body');
    let displayedOrderIds = new Set();
    let isFetching = false;

    // Nouvelle fonction pour gérer le bouton d'action
    function handleActionButton() {
        document.querySelectorAll('.action-complete-btn').forEach(function(elem) {
            elem.addEventListener('click', function(e) {
                e.preventDefault();
                const orderId = this.dataset.id;
                const newStatus = "Terminée"; // Le bouton "Terminer" met le statut à "Terminée"

                fetch('update_order_status_kitchen.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${orderId}&status=${encodeURIComponent(newStatus)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log(`Le statut de la commande #${orderId} a été mis à jour.`);
                        // Retire la commande terminée de la liste si le statut est mis à jour
                        const orderRow = document.querySelector(`tr[data-order-id="${orderId}"]`);
                        if (orderRow) {
                            orderRow.nextElementSibling.remove(); // Supprime la ligne des détails
                            orderRow.remove(); // Supprime la ligne de la commande
                            displayedOrderIds.delete(orderId);
                        }
                    } else {
                        alert("Erreur lors de la mise à jour du statut");
                    }
                })
                .catch(() => alert("Erreur réseau"));
            });
        });
    }

    function fetchOrderDetails(orderId) {
        fetch(`fetch_order_details.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                const detailsContainer = document.getElementById(`details-${orderId}`);
                if (detailsContainer) {
                    if (data.success && data.details.length > 0) {
                        let detailsHtml = `
                            <table class="table table-sm order-details-table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>Prix unitaire (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        data.details.forEach(detail => {
                            detailsHtml += `
                                <tr>
                                    <td>${detail.food_name}</td>
                                    <td>${detail.quantity}</td>
                                    <td>${detail.price}</td>
                                </tr>
                            `;
                        });
                        detailsHtml += `</tbody></table>`;
                        detailsContainer.innerHTML = detailsHtml;
                    } else {
                        detailsContainer.innerHTML = '<p class="text-muted">Aucun détail trouvé.</p>';
                    }
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails:', error);
                const detailsContainer = document.getElementById(`details-${orderId}`);
                if (detailsContainer) {
                    detailsContainer.innerHTML = '<p class="text-danger">Erreur de chargement des détails.</p>';
                }
            });
    }

    function fetchNewOrders() {
        if (isFetching) return;
        isFetching = true;
        
        fetch('fetch_orders_json.php')
            .then(response => response.json())
            .then(orders => {
                const newOrders = orders.filter(order => !displayedOrderIds.has(String(order.id)));
                
                if (tableBody.innerHTML.includes("Chargement des commandes...")) {
                    tableBody.innerHTML = "";
                }
                
                if (newOrders.length > 0) {
                    let html = '';
                    newOrders.forEach(order => {
                        html += `
                            <tr data-order-id="${order.id}">
                               
                                <td>${order.order_date}</td>
                                <td>${order.customer_name}</td>
                                <td>${order.order_type}</td>
                                <td>${order.table_id}</td>
                              
                                <td>${order.payment_method}</td>
                                <td>${order.order_number}</td>
                                <td>${order.status} <td>
                                    <button class="btn btn-success btn-sm action-complete-btn"
                                            data-id="${order.id}">
                                        Terminer
                                    </button>
                                </td>
                            </tr>
                            <tr class="order-details-row">
                                <td colspan="8">
                                    <div id="details-${order.id}" class="loading-details">Chargement des détails...</div>
                                </td>
                            </tr>
                        `;
                        displayedOrderIds.add(String(order.id));
                    });
                    
                    tableBody.insertAdjacentHTML('afterbegin', html);
                    handleActionButton(); // Attache les événements aux nouveaux boutons
                    newOrders.forEach(order => fetchOrderDetails(order.id));
                }

                if (tableBody.children.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Aucune commande en cours.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des commandes :', error);
                if (!tableBody.querySelector('.text-danger')) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Erreur de chargement des commandes.</td></tr>';
                }
            })
            .finally(() => {
                isFetching = false;
            });
    }
    
    // Chargement initial pour remplir la table
    fetchNewOrders();
    // Définir l'intervalle pour vérifier les nouvelles commandes toutes les 1 seconde
    setInterval(fetchNewOrders, 1000);
});
</script>

<?php include "footer.php"; ?>
</body>
</html>