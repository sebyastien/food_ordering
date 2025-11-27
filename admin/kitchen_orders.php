<?php
session_start();

include "connection.php";

$roles_autorises = ['admin', 'patron', 'gérant', 'serveur'];  // adapter selon la page
include "auth_check.php";

include "header.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$link) {
    die("Erreur de connexion à la base de données.");
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="breadcrumbs">
    <div class="col-sm-12">
        <div class="page-header float-left">
            <div class="page-title">
                <h1><i class="fa fa-fire"></i> Cuisine - Commandes en cours</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong><i class="fa fa-list"></i> Commandes en attente</strong>
                        <span class="badge badge-warning ml-2" id="pending-count">0</span>
                    </div>
                    <div>
                        <button class="btn btn-info btn-sm" onclick="location.reload()">
                            <i class="fa fa-sync-alt"></i> Actualiser
                        </button>
                        <a href="archived_orders.php" class="btn btn-secondary btn-sm">
                            <i class="fa fa-archive"></i> Archives
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>N°</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Table</th>
                                <th>Total (€)</th>
                                <th>Paiement</th>
                                <th>N° commande</th>
                                <th>Temps écoulé</th>
                                <th>Actions</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody id="orders-table-body">
                            <tr><td colspan="10" class="text-center">
                                <i class="fa fa-spinner fa-spin"></i> Chargement des commandes...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audio pour notification -->
<audio id="notification-sound" preload="auto">
    <source src="https://notificationsounds.com/storage/sounds/file-sounds-1150-pristine.mp3" type="audio/mpeg">
</audio>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('orders-table-body');
    const pendingCountBadge = document.getElementById('pending-count');
    let previousOrderCount = 0;

    // Fonction pour formater le type de commande
    function formatOrderType(orderType) {
        switch(orderType ? orderType.toLowerCase() : 'table') {
            case 'table':
            case 'sur place':
                return '<span class="badge badge-success"><i class="fa fa-utensils"></i> Sur place</span>';
            case 'takeaway':
            case 'à emporter':
                return '<span class="badge badge-warning"><i class="fa fa-shopping-bag"></i> À emporter</span>';
            case 'delivery':
            case 'livraison':
                return '<span class="badge badge-info"><i class="fa fa-motorcycle"></i> Livraison</span>';
            default:
                return '<span class="badge badge-secondary">' + orderType + '</span>';
        }
    }

    // Fonction pour calculer le temps écoulé
    function getElapsedTime(orderDate) {
        const now = new Date();
        const orderTime = new Date(orderDate);
        const diffMs = now - orderTime;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        
        let badgeClass = 'badge-info';
        let icon = 'fa-clock';
        
        if (diffMins > 30) {
            badgeClass = 'badge-danger';
            icon = 'fa-exclamation-triangle';
        } else if (diffMins > 15) {
            badgeClass = 'badge-warning';
            icon = 'fa-clock';
        }
        
        if (diffHours > 0) {
            return `<span class="badge ${badgeClass}"><i class="fa ${icon}"></i> ${diffHours}h ${diffMins % 60}min</span>`;
        } else {
            return `<span class="badge ${badgeClass}"><i class="fa ${icon}"></i> ${diffMins} min</span>`;
        }
    }

    // Function to handle status toggling to "Prête"
    function handleStatusToggle() {
        document.querySelectorAll('.mark-ready').forEach(function(elem) {
            elem.addEventListener('click', function(e) {
                e.preventDefault();
                const orderId = this.dataset.id;
                const orderNumber = this.dataset.orderNumber;

                if (confirm(`Marquer la commande ${orderNumber} comme PRÊTE ?`)) {
                    this.disabled = true;
                    this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Traitement...';

                    fetch('update_order_status_kitchen.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${orderId}&status=Prête`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Animation de succès
                            const row = this.closest('tr');
                            row.style.backgroundColor = '#d4edda';
                            row.style.transition = 'all 0.5s ease';
                            
                            showNotification(`✅ Commande ${orderNumber} marquée comme PRÊTE !`, 'success');
                            
                            // Rafraîchir après animation
                            setTimeout(() => {
                                fetchOrders();
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
                const currentOrderCount = orders.length;
                
                // Notifier si nouvelle commande
                if (currentOrderCount > previousOrderCount) {
                    document.getElementById('notification-sound').play();
                    showNotification('🔔 Nouvelle commande reçue !', 'info');
                }
                previousOrderCount = currentOrderCount;
                
                // Mettre à jour le badge de comptage
                pendingCountBadge.textContent = currentOrderCount;
                
                if (orders.length > 0) {
                    orders.forEach(order => {
                        const tableDisplay = order.table_id 
                            ? `<span class="badge badge-primary"><i class="fa fa-chair"></i> ${order.table_id}</span>` 
                            : '<span class="text-muted">-</span>';
                        
                        html += `
                            <tr>
                                <td><strong>${order.id}</strong></td>
                                <td><small>${order.order_date}</small></td>
                                <td><strong>${order.customer_name}</strong></td>
                                <td>${formatOrderType(order.order_type)}</td>
                                <td>${tableDisplay}</td>
                                <td><strong>${order.total_price} €</strong></td>
                                <td><small>${order.payment_method}</small></td>
                                <td><code>${order.order_number}</code></td>
                                <td>${getElapsedTime(order.order_date)}</td>
                                <td>
                                    <button class="btn btn-success btn-sm mark-ready" 
                                            data-id="${order.id}" 
                                            data-order-number="${order.order_number}">
                                        <i class="fa fa-check"></i> Marquer prête
                                    </button>
                                    <td>
                                    <a href="order_details.php?id=${order.id}" class="btn btn-info btn-sm mt-1">
                                        <i class="fa fa-eye"></i> Détails
                                    </a>
                                    </td>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="11" class="text-center text-muted py-4"><i class="fa fa-check-circle fa-3x mb-3 text-success"></i><br><strong>Aucune commande en attente</strong><br>Toutes les commandes ont été traitées !</td></tr>';
                }
                tableBody.innerHTML = html;
                handleStatusToggle(); // Réattache les événements aux nouveaux boutons
            })
            .catch(error => {
                console.error('Erreur lors du chargement des commandes :', error);
                tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Erreur de chargement des commandes.</td></tr>';
            });
    }

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

    // Chargement initial puis rafraîchissement automatique
    fetchOrders();
    setInterval(fetchOrders, 5000); // Rafraîchit toutes les 5 secondes
});
</script>

<style>
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

tbody tr {
    transition: background-color 0.3s ease;
}

tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.badge {
    font-size: 0.85em;
}

.mark-ready {
    transition: all 0.3s ease;
}

.mark-ready:hover {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .table {
        font-size: 0.85em;
    }
}
</style>

<?php include "footer.php"; ?>
</body>
</html>