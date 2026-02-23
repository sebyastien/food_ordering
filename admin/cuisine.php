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
<<<<<<< HEAD
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Style pour les détails de la commande */
        .order-details-row {
            background-color: #f8f9fa;
=======
    <style>
        /* Style pour les détails de la commande */
        .order-details-row {
            background-color: #f8f9fa; /* Couleur de fond pour distinguer */
>>>>>>> 4470edb (maj)
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
<<<<<<< HEAD
        
        /* Style pour les commentaires */
        .item-comment {
            font-size: 0.85em;
            color: #a40301;
            font-style: italic;
            margin-top: 3px;
            display: block;
        }
        
        tbody tr {
            transition: background-color 0.3s ease;
        }
        
        tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        
        .badge {
            font-size: 0.85em;
        }
        
        .action-complete-btn {
            transition: all 0.3s ease;
        }
        
        .action-complete-btn:hover {
            transform: scale(1.05);
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
=======
>>>>>>> 4470edb (maj)
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="card">
<<<<<<< HEAD
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong><i class="fa fa-fire"></i> Commandes en cours (cuisine)</strong>
                <span class="badge bg-warning text-dark ms-2" id="pending-count">0</span>
            </div>
            <div>
                <button class="btn btn-info btn-sm" onclick="location.reload()">
                    <i class="fa fa-sync-alt"></i> Actualiser
                </button>
                <a href="archives.php" class="btn btn-secondary btn-sm">
                    <i class="fa fa-archive"></i> Archives
                </a>
            </div>
=======
        <div class="card-header d-flex justify-content-between">
            <strong>Commandes en cours (cuisine)</strong>
            <a href="archives.php" class="btn btn-secondary btn-sm float-end">Archives</a>
>>>>>>> 4470edb (maj)
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
<<<<<<< HEAD
=======
                        
>>>>>>> 4470edb (maj)
                        <th>Date</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Table</th>
<<<<<<< HEAD
                        <th>N°commande</th>
                        <th>Statut</th>
                        <th>Temps écoulé</th>
=======
                        <th>Paiement</th>
                        <th>N°commande</th>
                        <th>Statut</th>
>>>>>>> 4470edb (maj)
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
<<<<<<< HEAD
                    <tr><td colspan="8" class="text-center">
                        <i class="fa fa-spinner fa-spin"></i> Chargement des commandes...
                    </td></tr>
=======
                    <tr><td colspan="8" class="text-center">Chargement des commandes...</td></tr>
>>>>>>> 4470edb (maj)
                </tbody>
            </table>
        </div>
    </div>
</div>

<<<<<<< HEAD
<audio id="notification-sound" preload="auto">
    <source src="https://notificationsounds.com/storage/sounds/file-sounds-1150-pristine.mp3" type="audio/mpeg">
</audio>

=======
>>>>>>> 4470edb (maj)
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('orders-table-body');
<<<<<<< HEAD
    const pendingCountBadge = document.getElementById('pending-count');
    let displayedOrderIds = new Set();
    let isFetching = false;
    let previousOrderCount = 0;

    // Fonction pour formater le type de commande
    function formatOrderType(orderType) {
        switch(orderType ? orderType.toLowerCase() : 'table') {
            case 'table':
            case 'sur place':
                return '<span class="badge bg-success"><i class="fa fa-utensils"></i> Sur place</span>';
            case 'takeaway':
            case 'à emporter':
                return '<span class="badge bg-warning"><i class="fa fa-shopping-bag"></i> À emporter</span>';
            case 'delivery':
            case 'livraison':
                return '<span class="badge bg-info"><i class="fa fa-motorcycle"></i> Livraison</span>';
            default:
                return '<span class="badge bg-secondary">' + orderType + '</span>';
        }
    }

    // Fonction pour calculer le temps écoulé
    function getElapsedTime(orderDate) {
        const now = new Date();
        const orderTime = new Date(orderDate);
        const diffMs = now - orderTime;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        
        let badgeClass = 'bg-info';
        let icon = 'fa-clock';
        
        if (diffMins > 30) {
            badgeClass = 'bg-danger';
            icon = 'fa-exclamation-triangle';
        } else if (diffMins > 15) {
            badgeClass = 'bg-warning text-dark';
            icon = 'fa-clock';
        }
        
        if (diffHours > 0) {
            return `<span class="badge ${badgeClass}"><i class="fa ${icon}"></i> ${diffHours}h ${diffMins % 60}min</span>`;
        } else {
            return `<span class="badge ${badgeClass}"><i class="fa ${icon}"></i> ${diffMins} min</span>`;
        }
    }

    // Fonction pour gérer le bouton d'action - Change le statut à "Prête"
=======
    let displayedOrderIds = new Set();
    let isFetching = false;

    // Nouvelle fonction pour gérer le bouton d'action
>>>>>>> 4470edb (maj)
    function handleActionButton() {
        document.querySelectorAll('.action-complete-btn').forEach(function(elem) {
            elem.addEventListener('click', function(e) {
                e.preventDefault();
                const orderId = this.dataset.id;
<<<<<<< HEAD
                const orderNumber = this.dataset.orderNumber;
                const newStatus = "Prête"; // On met le statut à "Prête" au lieu de "Terminée"

                if (confirm(`Marquer la commande ${orderNumber} comme PRÊTE ?`)) {
                    this.disabled = true;
                    this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Traitement...';

                    fetch('update_order_status_kitchen.php', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: `id=${orderId}&status=Prête`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Animation de succès
                            const orderRow = document.querySelector(`tr[data-order-id="${orderId}"]`);
                            if (orderRow) {
                                orderRow.style.backgroundColor = '#d4edda';
                                orderRow.style.transition = 'all 0.5s ease';
                            }
                            
                            showNotification(`✅ Commande ${orderNumber} marquée comme PRÊTE !`, 'success');
                            
                            // Retire la commande de la liste après animation
                            setTimeout(() => {
                                if (orderRow) {
                                    const detailsRow = orderRow.nextElementSibling;
                                    if (detailsRow) detailsRow.remove();
                                    orderRow.remove();
                                    displayedOrderIds.delete(String(orderId));
                                }
                                updatePendingCount();
                            }, 1000);
                        } else {
                            alert("Erreur lors de la mise à jour du statut");
                            this.disabled = false;
                            this.innerHTML = '<i class="fa fa-check"></i> Marquer prête';
                        }
                    })
                    .catch(() => {
                        alert("Erreur réseau");
                        this.disabled = false;
                        this.innerHTML = '<i class="fa fa-check"></i> Marquer prête';
                    });
                }
=======
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
>>>>>>> 4470edb (maj)
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
<<<<<<< HEAD
                                        <th>Instructions spéciales</th>
                                        <th>Quantité</th>
=======
                                        <th>Quantité</th>
                                        <th>Prix unitaire (€)</th>
>>>>>>> 4470edb (maj)
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        data.details.forEach(detail => {
<<<<<<< HEAD
                            const comment = detail.item_comment && detail.item_comment.trim() !== '' 
                                ? `<span class="item-comment">${detail.item_comment}</span>` 
                                : '<span class="text-muted">Aucune</span>';
                            
                            detailsHtml += `
                                <tr>
                                    <td>${detail.food_name}</td>
                                    <td>${comment}</td>
                                    <td>${detail.quantity}</td>
=======
                            detailsHtml += `
                                <tr>
                                    <td>${detail.food_name}</td>
                                    <td>${detail.quantity}</td>
                                    <td>${detail.price}</td>
>>>>>>> 4470edb (maj)
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

<<<<<<< HEAD
    function updatePendingCount() {
        const visibleOrders = tableBody.querySelectorAll('tr[data-order-id]').length;
        pendingCountBadge.textContent = visibleOrders;
    }

=======
>>>>>>> 4470edb (maj)
    function fetchNewOrders() {
        if (isFetching) return;
        isFetching = true;
        
        fetch('fetch_orders_json.php')
            .then(response => response.json())
            .then(orders => {
                const newOrders = orders.filter(order => !displayedOrderIds.has(String(order.id)));
<<<<<<< HEAD
                const currentOrderCount = orders.length;
                
                // Notifier si nouvelle commande
                if (currentOrderCount > previousOrderCount) {
                    document.getElementById('notification-sound').play();
                    showNotification('🔔 Nouvelle commande reçue !', 'info');
                }
                previousOrderCount = currentOrderCount;
=======
>>>>>>> 4470edb (maj)
                
                if (tableBody.innerHTML.includes("Chargement des commandes...")) {
                    tableBody.innerHTML = "";
                }
                
                if (newOrders.length > 0) {
                    let html = '';
                    newOrders.forEach(order => {
<<<<<<< HEAD
                        const tableDisplay = order.table_id 
                            ? `<span class="badge bg-primary"><i class="fa fa-chair"></i> ${order.table_id}</span>` 
                            : '<span class="text-muted">-</span>';
                        
                        html += `
                            <tr data-order-id="${order.id}">
                                <td><small>${order.order_date}</small></td>
                                <td><strong>${order.customer_name}</strong></td>
                                <td>${formatOrderType(order.order_type)}</td>
                                <td>${tableDisplay}</td>
                                <td><code>${order.order_number}</code></td>
                                <td><span class="badge bg-warning text-dark">${order.status}</span></td>
                                <td>${getElapsedTime(order.order_date)}</td>
                                <td>
                                    <button class="btn btn-success btn-sm action-complete-btn"
                                            data-id="${order.id}"
                                            data-order-number="${order.order_number}">
                                        <i class="fa fa-check"></i> Marquer prête
=======
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
>>>>>>> 4470edb (maj)
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
<<<<<<< HEAD
                    handleActionButton();
                    newOrders.forEach(order => fetchOrderDetails(order.id));
                }

                updatePendingCount();

                if (tableBody.children.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4"><i class="fa fa-check-circle fa-3x mb-3 text-success"></i><br><strong>Aucune commande en attente</strong><br>Toutes les commandes ont été traitées !</td></tr>';
=======
                    handleActionButton(); // Attache les événements aux nouveaux boutons
                    newOrders.forEach(order => fetchOrderDetails(order.id));
                }

                if (tableBody.children.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Aucune commande en cours.</td></tr>';
>>>>>>> 4470edb (maj)
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des commandes :', error);
                if (!tableBody.querySelector('.text-danger')) {
<<<<<<< HEAD
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Erreur de chargement des commandes.</td></tr>';
=======
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Erreur de chargement des commandes.</td></tr>';
>>>>>>> 4470edb (maj)
                }
            })
            .finally(() => {
                isFetching = false;
            });
    }
<<<<<<< HEAD

    // Fonction pour afficher une notification toast
    function showNotification(message, type = 'info') {
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            info: '#17a2b8',
            warning: '#ffc107'
        };
        
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${colors[type]};
            color: white;
            padding: 20px 25px;
            border-radius: 8px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
            z-index: 9999;
            font-size: 1.1em;
            font-weight: bold;
            animation: slideIn 0.3s ease;
        `;
        notification.innerHTML = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
    
    // Chargement initial pour remplir la table
    fetchNewOrders();
    // Rafraîchissement automatique toutes les 5 secondes
    setInterval(fetchNewOrders, 5000);
=======
    
    // Chargement initial pour remplir la table
    fetchNewOrders();
    // Définir l'intervalle pour vérifier les nouvelles commandes toutes les 1 seconde
    setInterval(fetchNewOrders, 1000);
>>>>>>> 4470edb (maj)
});
</script>

<?php include "footer.php"; ?>
</body>
</html>