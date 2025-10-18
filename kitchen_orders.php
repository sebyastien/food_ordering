<?php
session_start();

include "connection.php";

$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon la page
include "auth_check.php";

include "header.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$link) {
    die("Erreur de connexion à la base de données.");
}
?>


<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>Commandes en cours (cuisine)</strong>
            <a href="archived_orders.php" class="btn btn-secondary btn-sm float-end">Archives</a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Date</th>
                        <th>Client </th>
                        <th>Type </th>
                        <th>Table </th>
                        <th>Total (€)</th>
                        <th>Paiement</th>
                        <th>N° commande</th>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('orders-table-body');

    // Function to handle status toggling
    function handleStatusToggle() {
        document.querySelectorAll('.status-toggle').forEach(function(elem) {
            elem.addEventListener('click', function(e) {
                e.preventDefault();
                const orderId = this.dataset.id;
                const currentStatus = this.dataset.status;
                let newStatus = (currentStatus === "En attente") ? "Terminée" : "En attente";

                fetch('update_order_status_kitchen.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${orderId}&status=${encodeURIComponent(newStatus)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Le prochain rafraîchissement (géré par setInterval) mettra à jour le tableau
                        console.log(`Le statut de la commande #${orderId} a été mis à jour. Le tableau sera bientôt rafraîchi.`);
                    } else {
                        alert("Erreur lors de la mise à jour du statut");
                    }
                })
                .catch(() => alert("Erreur réseau"));
            });
        });
    }

    // Function to fetch and render orders
    function fetchOrders() {
        fetch('fetch_orders_json.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(orders => {
                let html = '';
                if (orders.length > 0) {
                    orders.forEach(order => {
                        html += `
                            <tr>
                                <td>${order.id}</td>
                                <td>${order.order_date}</td>
                                <td>${order.customer_name}</td>
                                <td>${order.order_type}</td>
                                <td>${order.table_id}</td>
                                <td>${order.total_price}</td>
                                <td>${order.payment_method}</td>
                                <td>${order.order_number}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary status-toggle" 
                                            data-id="${order.id}" 
                                            data-status="${order.status}">
                                        ${order.status}
                                    </button>
                                </td>
                                <td>
                                    <a href="order_details.php?id=${order.id}" class="btn btn-info btn-sm">Détails</a>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="8" class="text-center">Aucune commande en cours.</td></tr>';
                }
                tableBody.innerHTML = html;
                handleStatusToggle(); // Réattache les événements aux nouveaux boutons
            })
            .catch(error => {
                console.error('Erreur lors du chargement des commandes :', error);
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Erreur de chargement des commandes.</td></tr>';
            });
    }

    // Chargement initial puis rafraîchissement automatique
    fetchOrders();
    setInterval(fetchOrders, 1000); // Rafraîchit toutes les 5 secondes
});
</script>

<?php include "footer.php"; ?>
</body>
</html>