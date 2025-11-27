<?php
session_start();

include "connection.php";

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

$roles_autorises = ['admin', 'patron', 'gérant', 'serveur'];
include "auth_check.php";

include "header.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$link) {
    die("Erreur de connexion à la base de données.");
}

// Filtres
$table_filter = isset($_GET['table_id']) ? trim($_GET['table_id']) : '';
$order_type_filter = isset($_GET['order_type']) ? $_GET['order_type'] : '';

$where_clauses = ["status = 'Prête'"]; // On affiche SEULEMENT les commandes prêtes
$params = [];
$types = "";

if (!empty($table_filter)) {
    $where_clauses[] = "table_id LIKE ?";
    $params[] = "%" . $table_filter . "%";
    $types .= "s";
}

if (!empty($order_type_filter)) {
    $where_clauses[] = "order_type = ?";
    $params[] = $order_type_filter;
    $types .= "s";
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Requête principale - SEULEMENT les commandes prêtes à servir
$sql = "SELECT * FROM orders $where_sql ORDER BY ready_time ASC";
$stmt = mysqli_prepare($link, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fonction pour formater le type de commande
function formatOrderType($orderType) {
    switch(strtolower($orderType)) {
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
            return '<span class="badge badge-secondary">' . htmlspecialchars($orderType) . '</span>';
    }
}

// Compter les commandes prêtes aujourd'hui
$sql_count = "SELECT COUNT(*) as count FROM orders WHERE status = 'Prête' AND DATE(order_date) = CURDATE()";
$result_count = mysqli_query($link, $sql_count);
$ready_count = mysqli_fetch_assoc($result_count)['count'];

// Compter les commandes servies aujourd'hui
$sql_served = "SELECT COUNT(*) as count FROM orders WHERE status = 'Terminée' AND DATE(served_time) = CURDATE()";
$result_served = mysqli_query($link, $sql_served);
$served_count = mysqli_fetch_assoc($result_served)['count'];
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="breadcrumbs">
    <div class="col-sm-12">
        <div class="page-header float-left">
            <div class="page-title">
                <h1><i class="fa fa-concierge-bell"></i> Espace Serveur - Commandes à Servir</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h2 class="display-3"><?= $ready_count ?></h2>
                    <p class="mb-0 h5"><i class="fa fa-bell"></i> Commandes prêtes à servir</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center bg-secondary text-white">
                <div class="card-body">
                    <h2 class="display-3"><?= $served_count ?></h2>
                    <p class="mb-0 h5"><i class="fa fa-check-double"></i> Servies aujourd'hui</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <strong><i class="fa fa-filter"></i> Filtrer les commandes</strong>
                </div>
                <div class="card-body">
                    <form method="get" class="form-inline flex-wrap">
                        <div class="form-group mr-2 mb-2">
                            <label for="order_type" class="mr-2">Type :</label>
                            <select id="order_type" name="order_type" class="form-control">
                                <option value="">Tous</option>
                                <option value="table" <?= $order_type_filter === 'table' ? 'selected' : '' ?>>Sur place</option>
                                <option value="takeaway" <?= $order_type_filter === 'takeaway' ? 'selected' : '' ?>>À emporter</option>
                                <option value="delivery" <?= $order_type_filter === 'delivery' ? 'selected' : '' ?>>Livraison</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <label for="table_id" class="mr-2">N° Table :</label>
                            <input type="text" id="table_id" name="table_id" class="form-control" placeholder="Numéro de table" value="<?= htmlspecialchars($table_filter) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary mr-2 mb-2">
                            <i class="fa fa-filter"></i> Filtrer
                        </button>
                        <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="btn btn-secondary mb-2">
                            <i class="fa fa-redo"></i> Réinitialiser
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des commandes PRÊTES -->
    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <strong><i class="fa fa-bell"></i> Commandes prêtes à servir</strong>
                    <button class="btn btn-sm btn-light float-right" onclick="location.reload()">
                        <i class="fa fa-sync-alt"></i> Actualiser
                    </button>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="row">
                            <?php while ($order = mysqli_fetch_assoc($result)): 
                                // Récupérer les détails de la commande avec item_comment
                                $order_id = $order['id'];
                                $stmt_items = $link->prepare("SELECT food_name, quantity, item_comment FROM order_items WHERE order_id = ?");
                                $stmt_items->bind_param("i", $order_id);
                                $stmt_items->execute();
                                $items_result = $stmt_items->get_result();
                                
                                // Calculer le temps depuis que la commande est prête
                                if ($order['ready_time'] && $order['ready_time'] != '0000-00-00 00:00:00') {
                                    $ready_time = new DateTime($order['ready_time']);
                                    $now = new DateTime();
                                    $interval = $ready_time->diff($now);
                                    
                                    // Calcul total des minutes
                                    $total_minutes = ($interval->h * 60) + $interval->i;
                                    
                                    if ($total_minutes < 1) {
                                        $elapsed = 'À l\'instant';
                                    } elseif ($interval->h > 0) {
                                        $elapsed = $interval->h . 'h ' . $interval->i . 'min';
                                    } else {
                                        $elapsed = $interval->i . ' min';
                                    }
                                    
                                    // Couleur selon le temps d'attente (alerte si trop long)
                                    $card_color = '';
                                    if ($total_minutes > 10) {
                                        $card_color = 'border-danger';
                                    } elseif ($total_minutes > 5) {
                                        $card_color = 'border-warning';
                                    } else {
                                        $card_color = 'border-success';
                                    }
                                } else {
                                    $elapsed = 'À l\'instant';
                                    $card_color = 'border-success';
                                }
                            ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card order-card <?= $card_color ?> shadow" style="border-width: 4px;" data-order-id="<?= $order['id'] ?>">
                                    <div class="card-header bg-success text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><i class="fa fa-hashtag"></i> <?= htmlspecialchars($order['order_number']) ?></h5>
                                            </div>
                                            <div>
                                                <span class="badge badge-light">
                                                    <i class="fa fa-bell"></i> PRÊTE
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h5><i class="fa fa-user"></i> <?= htmlspecialchars($order['customer_name']) ?></h5>
                                        </div>
                                        <div class="mb-3">
                                            <?= formatOrderType($order['order_type'] ?? 'table') ?>
                                            <?php if (($order['order_type'] ?? 'table') === 'table' && $order['table_id']): ?>
                                                <span class="badge badge-primary badge-lg ml-2">
                                                    <i class="fa fa-chair"></i> TABLE <?= htmlspecialchars($order['table_id']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="alert alert-info mb-3">
                                            <i class="fa fa-clock"></i> <strong>Prête depuis : <?= $elapsed ?></strong>
                                        </div>
                                        <hr>
                                        <div class="order-items">
                                            <strong><i class="fa fa-list-ul"></i> Articles :</strong>
                                            <ul class="list-unstyled ml-2 mt-2">
                                                <?php while ($item = mysqli_fetch_assoc($items_result)): 
                                                    $comment = isset($item['item_comment']) && trim($item['item_comment']) !== '' 
                                                        ? htmlspecialchars($item['item_comment']) 
                                                        : '';
                                                ?>
                                                    <li class="mb-2">
                                                        <span class="badge badge-dark badge-pill"><?= $item['quantity'] ?>x</span>
                                                        <strong><?= htmlspecialchars($item['food_name']) ?></strong>
                                                        <?php if ($comment): ?>
                                                            <div class="item-comment">
                                                                 <?= $comment ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endwhile; ?>
                                            </ul>
                                        </div>
                                        <hr>
                                        <div class="text-right">
                                            <h4 class="text-success mb-0">
                                                <i class="fa fa-euro-sign"></i> <?= number_format($order['total_price'], 2) ?> €
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <button class="btn btn-primary btn-lg btn-block mark-served pulse-animation" data-id="<?= $order['id'] ?>" data-order-number="<?= htmlspecialchars($order['order_number']) ?>">
                                            <i class="fa fa-hand-holding"></i> Servir cette commande
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                $stmt_items->close();
                            endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-check-circle fa-5x mb-4 text-success"></i>
                            <h3>Aucune commande en attente</h3>
                            <p class="lead">Toutes les commandes prêtes ont été servies !</p>
                        </div>
                    <?php endif; ?>
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
    // Sauvegarder le nombre de commandes actuel
    let previousOrderCount = document.querySelectorAll('.order-card').length;
    
    // Rafraîchissement automatique toutes les 15 secondes
    setInterval(function() {
        checkNewOrders();
    }, 15000);

    // Fonction pour vérifier les nouvelles commandes
    function checkNewOrders() {
        fetch(window.location.href, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newOrderCount = doc.querySelectorAll('.order-card').length;
            
            // Si nouvelles commandes, recharger et notifier
            if (newOrderCount > previousOrderCount) {
                document.getElementById('notification-sound').play();
                showNotification('🔔 Nouvelle commande prête à servir !', 'success');
                setTimeout(() => location.reload(), 1000);
            } else if (newOrderCount !== previousOrderCount) {
                location.reload();
            }
            
            previousOrderCount = newOrderCount;
        });
    }

    // Gestionnaire pour marquer comme servie
    document.querySelectorAll('.mark-served').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.id;
            const orderNumber = this.dataset.orderNumber;
            const card = this.closest('.order-card');
            
            if (confirm(`✅ Confirmer que la commande ${orderNumber} a été servie au client ?`)) {
                this.disabled = true;
                this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> En cours...';
                
                fetch('mark_order_served.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: `order_id=${orderId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Réponse réseau non OK');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Animation de succès
                        card.style.transition = 'all 0.5s ease';
                        card.style.transform = 'scale(0.8)';
                        card.style.opacity = '0';
                        
                        showNotification(`✅ Commande ${orderNumber} servie avec succès !`, 'success');
                        
                        // Recharger après l'animation
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        alert('❌ Erreur : ' + (data.error || 'Erreur inconnue'));
                        this.disabled = false;
                        this.innerHTML = '<i class="fa fa-hand-holding"></i> Servir cette commande';
                    }
                })
                .catch(error => {
                    console.error('Erreur complète:', error);
                    alert('❌ Erreur réseau : ' + error.message);
                    this.disabled = false;
                    this.innerHTML = '<i class="fa fa-hand-holding"></i> Servir cette commande';
                });
            }
        });
    });
});

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

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
    }
}

.order-card {
    transition: all 0.3s ease;
    height: 100%;
}

.order-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.3) !important;
}

.pulse-animation {
    animation: pulse 2s infinite;
}

.order-items ul li {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.order-items ul li:last-child {
    border-bottom: none;
}

/* Style pour les commentaires d'article */
.item-comment {
    font-size: 0.85em;
    color: #a40301;
    font-style: italic;
    margin-top: 4px;
    padding-left: 10px;
    display: block;
}

.badge-lg {
    font-size: 1em;
    padding: 8px 12px;
}

.card-footer button {
    font-weight: bold;
    font-size: 1.1em;
}

.border-danger {
    border-color: #dc3545 !important;
    animation: pulse-danger 2s infinite;
}

@keyframes pulse-danger {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
}

.border-warning {
    border-color: #ffc107 !important;
}

.border-success {
    border-color: #28a745 !important;
}

@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 15px;
    }
    
    .display-3 {
        font-size: 2.5rem;
    }
}
</style>

<?php include "footer.php"; ?>