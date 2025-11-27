<?php
session_start();

include "connection.php";

$roles_autorises = ['admin', 'patron', 'gérant'];
include "auth_check.php";

include "header.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$link) {
    die("Erreur de connexion à la base de données.");
}

// --- Fonction pour récupérer stats (nombre de commandes + total recettes) selon une période et filtre éventuel ---
function getStats($link, $startDate, $endDate, $extraWhere = "", $params = []) {
    $sql = "SELECT COUNT(*) AS nb_commandes, IFNULL(SUM(total_price), 0) AS total_recettes 
            FROM orders 
            WHERE order_date BETWEEN ? AND ? ";
    if ($extraWhere) {
        $sql .= " AND $extraWhere ";
    }
    $stmt = mysqli_prepare($link, $sql);
    $types = str_repeat("s", 2);
    $stmt_params = [$startDate, $endDate];

    if ($params) {
        $types .= str_repeat("s", count($params));
        $stmt_params = array_merge($stmt_params, $params);
    }
    
    mysqli_stmt_bind_param($stmt, $types, ...$stmt_params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($res);
}

// --- Gestion du filtre ---
$period_filter = isset($_GET['period']) ? $_GET['period'] : 'custom';
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';

// Ajuster les dates en fonction de la période sélectionnée
if ($period_filter !== 'custom') {
    $now = new DateTime();
    switch ($period_filter) {
        case 'today':
            $date_start = $now->format('Y-m-d');
            $date_end = $now->format('Y-m-d');
            break;
        case 'yesterday':
            $yesterday = (clone $now)->modify('-1 day');
            $date_start = $yesterday->format('Y-m-d');
            $date_end = $yesterday->format('Y-m-d');
            break;
        case 'this_week':
            $startOfWeek = (clone $now)->modify('monday this week');
            $endOfWeek = (clone $now)->modify('sunday this week');
            $date_start = $startOfWeek->format('Y-m-d');
            $date_end = $endOfWeek->format('Y-m-d');
            break;
        case 'last_week':
            $lastWeek = (clone $now)->modify('monday last week');
            $endLastWeek = (clone $now)->modify('sunday last week');
            $date_start = $lastWeek->format('Y-m-d');
            $date_end = $endLastWeek->format('Y-m-d');
            break;
        case 'this_month':
            $date_start = $now->format('Y-m-01');
            $date_end = $now->format('Y-m-t');
            break;
        case 'last_month':
            $lastMonth = (clone $now)->modify('first day of last month');
            $date_start = $lastMonth->format('Y-m-01');
            $date_end = $lastMonth->format('Y-m-t');
            break;
    }
}

$customer_name_filter = isset($_GET['customer_name']) ? trim($_GET['customer_name']) : '';
$payment_method_filter = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$order_number_filter = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';
$order_type_filter = isset($_GET['order_type']) ? $_GET['order_type'] : '';
$table_id_filter = isset($_GET['table_id']) ? trim($_GET['table_id']) : '';

$where_clauses = [];
$params = [];

if ($date_start) {
    $where_clauses[] = "order_date >= ?";
    $params[] = $date_start . " 00:00:00";
}
if ($date_end) {
    $where_clauses[] = "order_date <= ?";
    $params[] = $date_end . " 23:59:59";
}

if (!empty($customer_name_filter)) {
    $where_clauses[] = "customer_name LIKE ?";
    $params[] = "%" . $customer_name_filter . "%";
}
if (!empty($payment_method_filter)) {
    $where_clauses[] = "payment_method = ?";
    $params[] = $payment_method_filter;
}
if (!empty($status_filter)) {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
}
if (!empty($order_number_filter)) {
    $where_clauses[] = "order_number LIKE ?";
    $params[] = "%" . $order_number_filter . "%";
}
if (!empty($order_type_filter)) {
    $where_clauses[] = "order_type = ?";
    $params[] = $order_type_filter;
}
if (!empty($table_id_filter)) {
    $where_clauses[] = "table_id LIKE ?";
    $params[] = "%" . $table_id_filter . "%";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// --- Pagination ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- Calcul du nombre total de commandes pour pagination ---
$sql_count_total = "SELECT COUNT(*) as total FROM orders $where_sql";
$stmt_count_total = mysqli_prepare($link, $sql_count_total);

if (!empty($params)) {
    $types = str_repeat("s", count($params));
    mysqli_stmt_bind_param($stmt_count_total, $types, ...$params);
}
mysqli_stmt_execute($stmt_count_total);
$res_count_total = mysqli_stmt_get_result($stmt_count_total);
$total_orders = mysqli_fetch_assoc($res_count_total)['total'];
$total_pages = ceil($total_orders / $limit);

// --- Calcul des périodes courantes (pour stats sans filtre) ---
$now = new DateTime();
$startOfWeek = (clone $now)->modify('monday this week')->format('Y-m-d 00:00:00');
$endOfWeek = (clone $now)->modify('sunday this week')->format('Y-m-d 23:59:59');
$startOfMonth = (clone $now)->modify('first day of this month')->format('Y-m-d 00:00:00');
$endOfMonth = (clone $now)->modify('last day of this month')->format('Y-m-d 23:59:59');
$startOfYear = (clone $now)->modify('first day of January this year')->format('Y-m-d 00:00:00');
$endOfYear = (clone $now)->modify('last day of December this year')->format('Y-m-d 23:59:59');

// --- Récupération stats selon filtre ---
$statsFiltered = ['nb_commandes' => 0, 'total_recettes' => 0];
if (count($params) > 0) {
    $start_date_stats = $date_start ? $params[0] : '0000-01-01 00:00:00';
    $end_date_stats = $date_end ? (isset($params[1]) && $date_end ? $params[1] : end($params)) : '9999-12-31 23:59:59';
    
    $extra_params_stats = $params;
    if ($date_start) array_shift($extra_params_stats);
    if ($date_end) array_shift($extra_params_stats);
    
    $extra_where_stats_clauses = [];
    if (!empty($customer_name_filter)) $extra_where_stats_clauses[] = "customer_name LIKE ?";
    if (!empty($payment_method_filter)) $extra_where_stats_clauses[] = "payment_method = ?";
    if (!empty($status_filter)) $extra_where_stats_clauses[] = "status = ?";
    if (!empty($order_number_filter)) $extra_where_stats_clauses[] = "order_number LIKE ?";
    if (!empty($order_type_filter)) $extra_where_stats_clauses[] = "order_type = ?";
    if (!empty($table_id_filter)) $extra_where_stats_clauses[] = "table_id LIKE ?";
    $extra_where_stats = implode(" AND ", $extra_where_stats_clauses);
    
    $statsFiltered = getStats($link, $start_date_stats, $end_date_stats, $extra_where_stats, $extra_params_stats);
}

// Stats semaine/mois/année (sans filtre)
$statsWeek = getStats($link, $startOfWeek, $endOfWeek);
$statsMonth = getStats($link, $startOfMonth, $endOfMonth);
$statsYear = getStats($link, $startOfYear, $endOfYear);

// --- Requête principale avec filtre + pagination ---
$sql = "SELECT * FROM orders $where_sql ORDER BY order_date DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($link, $sql);

$types = str_repeat("s", count($params)) . "ii";
$stmt_params = array_merge($params, [$limit, $offset]);
mysqli_stmt_bind_param($stmt, $types, ...$stmt_params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Requête pour résumé nombre commandes par jour dans la période filtrée
$sql_count = "SELECT DATE(order_date) as jour, COUNT(*) as nb_commandes FROM orders $where_sql GROUP BY jour ORDER BY jour DESC";
$stmt_count = mysqli_prepare($link, $sql_count);
if (!empty($params)) {
    $types = str_repeat("s", count($params));
    mysqli_stmt_bind_param($stmt_count, $types, ...$params);
}
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);

// Fonction pour formater l'affichage du type de commande
function formatOrderType($orderType, $tableNumber = null) {
    switch(strtolower($orderType)) {
        case 'table':
        case 'sur place':
            return '<span class="badge badge-success">Sur place</span>';
        case 'takeaway':
        case 'à emporter':
            return '<span class="badge badge-warning">À emporter</span>';
        case 'delivery':
        case 'livraison':
            return '<span class="badge badge-info">Livraison</span>';
        default:
            return '<span class="badge badge-secondary">' . htmlspecialchars($orderType) . '</span>';
    }
}

function formatTableNumber($orderType, $tableNumber) {
    if (strtolower($orderType) === 'table' || strtolower($orderType) === 'sur place') {
        return $tableNumber ? '<span class="badge badge-primary">Table ' . htmlspecialchars($tableNumber) . '</span>' : '<span class="text-muted">-</span>';
    }
    return '<span class="text-muted">-</span>';
}
?>

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Gestion des Commandes</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <strong>Filtrer les commandes</strong>
                </div>
                <div class="card-body">
                    <form method="get" class="form-inline flex-wrap">
                        <!-- Première ligne de filtres -->
                        <div class="form-group mr-2 mb-2">
                            <label for="period" class="mr-2">Période :</label>
                            <select id="period" name="period" class="form-control">
                                <option value="custom" <?= $period_filter === 'custom' ? 'selected' : '' ?>>Période personnalisée</option>
                                <option value="today" <?= $period_filter === 'today' ? 'selected' : '' ?>>Aujourd'hui</option>
                                <option value="yesterday" <?= $period_filter === 'yesterday' ? 'selected' : '' ?>>Hier</option>
                                <option value="this_week" <?= $period_filter === 'this_week' ? 'selected' : '' ?>>Cette semaine</option>
                                <option value="last_week" <?= $period_filter === 'last_week' ? 'selected' : '' ?>>Semaine passée</option>
                                <option value="this_month" <?= $period_filter === 'this_month' ? 'selected' : '' ?>>Mois en cours</option>
                                <option value="last_month" <?= $period_filter === 'last_month' ? 'selected' : '' ?>>Mois dernier</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <label for="date_start" class="mr-2">Date début :</label>
                            <input type="date" id="date_start" name="date_start" class="form-control" value="<?= htmlspecialchars($date_start) ?>">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <label for="date_end" class="mr-2">Date fin :</label>
                            <input type="date" id="date_end" name="date_end" class="form-control" value="<?= htmlspecialchars($date_end) ?>">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <label for="customer_name" class="mr-2">Nom client :</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Nom du client" value="<?= htmlspecialchars($customer_name_filter) ?>">
                        </div>

                        <!-- Deuxième ligne de filtres -->
                        <div class="w-100"></div>
                        <div class="form-group mr-2 mb-2">
                            <label for="payment_method" class="mr-2">Paiement :</label>
                            <select id="payment_method" name="payment_method" class="form-control">
                                <option value="">Tous</option>
                                <option value="Espèces" <?= $payment_method_filter === 'Espèces' ? 'selected' : '' ?>>Espèces</option>
                                <option value="Carte bancaire" <?= $payment_method_filter === 'Carte bancaire' ? 'selected' : '' ?>>Carte bancaire</option>
                                <option value="PayPal" <?= $payment_method_filter === 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <label for="status" class="mr-2">Statut :</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">Tous</option>
                                <option value="En attente" <?= $status_filter === 'En attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="Prête" <?= $status_filter === 'Prête' ? 'selected' : '' ?>>Prête</option>
                                <option value="Terminée" <?= $status_filter === 'Terminée' ? 'selected' : '' ?>>Terminée</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <label for="order_number" class="mr-2">N° Commande :</label>
                            <input type="text" id="order_number" name="order_number" class="form-control" placeholder="Numéro de commande" value="<?= htmlspecialchars($order_number_filter) ?>">
                        </div>

                        <!-- NOUVEAUX filtres -->
                        <div class="form-group mr-2 mb-2">
                            <label for="order_type" class="mr-2">Type commande :</label>
                            <select id="order_type" name="order_type" class="form-control">
                                <option value="">Tous</option>
                                <option value="table" <?= $order_type_filter === 'table' ? 'selected' : '' ?>>Sur place</option>
                                <option value="takeaway" <?= $order_type_filter === 'takeaway' ? 'selected' : '' ?>>À emporter</option>
                                <option value="delivery" <?= $order_type_filter === 'delivery' ? 'selected' : '' ?>>Livraison</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <label for="table_id" class="mr-2">N° Table :</label>
                            <input type="text" id="table_id" name="table_id" class="form-control" placeholder="Numéro de table" value="<?= htmlspecialchars($table_id_filter) ?>">
                        </div>

                        <div class="w-100"></div>
                        <button type="submit" class="btn btn-primary mt-2">
                            <i class="fa fa-filter"></i> Filtrer
                        </button>
                        <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="btn btn-secondary mt-2 ml-2">
                            <i class="fa fa-redo"></i> Réinitialiser
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <strong>Liste des commandes</strong>
                    <small class="text-muted">(<?= $total_orders ?> commande<?= $total_orders > 1 ? 's' : '' ?> trouvée<?= $total_orders > 1 ? 's' : '' ?>)</small>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Table</th>
                                <th>Total (€)</th>
                                <th>Paiement</th>
                                <th>N° Commande</th>
                                <th>Statut</th>
                                <th>Facture</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $order['id'] ?></td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td><?= formatOrderType($order['order_type'] ?? 'table', $order['table_id'] ?? null) ?></td>
                                    <td><?= formatTableNumber($order['order_type'] ?? 'table', $order['table_id'] ?? null) ?></td>
                                    <td><strong><?= number_format($order['total_price'], 2) ?></strong></td>
                                    <td>
                                        <small><?= htmlspecialchars($order['payment_method']) ?></small>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($order['order_number']) ?></code>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = 'badge-secondary';
                                        $statusIcon = 'fa-question-circle';
                                        
                                        if ($order['status'] === 'Terminée') {
                                            $statusClass = 'badge-success';
                                            $statusIcon = 'fa-check-circle';
                                        } elseif ($order['status'] === 'Prête') {
                                            $statusClass = 'badge-info';
                                            $statusIcon = 'fa-check';
                                        } elseif ($order['status'] === 'En attente') {
                                            $statusClass = 'badge-warning';
                                            $statusIcon = 'fa-clock';
                                        }
                                        ?>
                                        <span class="badge badge-lg <?= $statusClass ?>">
                                            <i class="fa <?= $statusIcon ?>"></i>
                                            <?= htmlspecialchars($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="../user/facture.php?order_number=<?= urlencode($order['order_number']) ?>" 
                                           class="btn btn-primary btn-sm" 
                                           title="Télécharger la facture">
                                            <i class="fa fa-file-pdf"></i> Facture
                                        </a>
                                    </td>
                                    <td>
                                        <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i> Détails
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">
                                        <i class="fa fa-inbox fa-3x mb-3"></i><br>
                                        Aucune commande trouvée avec ces critères
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php
                    // Construire l'URL de base avec les filtres
                    $base_url_params = [];
                    if ($period_filter !== 'custom') $base_url_params['period'] = $period_filter;
                    if ($date_start) $base_url_params['date_start'] = $date_start;
                    if ($date_end) $base_url_params['date_end'] = $date_end;
                    if ($customer_name_filter) $base_url_params['customer_name'] = $customer_name_filter;
                    if ($payment_method_filter) $base_url_params['payment_method'] = $payment_method_filter;
                    if ($status_filter) $base_url_params['status'] = $status_filter;
                    if ($order_number_filter) $base_url_params['order_number'] = $order_number_filter;
                    if ($order_type_filter) $base_url_params['order_type'] = $order_type_filter;
                    if ($table_id_filter) $base_url_params['table_id'] = $table_id_filter;

                    $base_url = strtok($_SERVER["REQUEST_URI"], '?');
                    $query_string = http_build_query($base_url_params);

                    function pageUrl($pageNum, $base_url, $query_string) {
                        $params = [];
                        if ($query_string) $params[] = $query_string;
                        $params[] = "page=$pageNum";
                        return $base_url . "?" . implode("&", $params);
                    }

                    // Lien "Précédent"
                    if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= pageUrl($page - 1, $base_url, $query_string) ?>">
                                <i class="fa fa-chevron-left"></i> Précédent
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="fa fa-chevron-left"></i> Précédent</span>
                        </li>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 5);
                    $endPage = min($total_pages, $page + 5);
                    for ($p = $startPage; $p <= $endPage; $p++): ?>
                        <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= pageUrl($p, $base_url, $query_string) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= pageUrl($page + 1, $base_url, $query_string) ?>">
                                Suivant <i class="fa fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Suivant <i class="fa fa-chevron-right"></i></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <div class="card mt-4">
                <div class="card-header">
                    <strong><i class="fa fa-chart-bar"></i> Statistiques</strong>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fa fa-calendar-week"></i> Semaine
                                    </h6>
                                    <p class="card-text">
                                        <strong><?= $statsWeek['nb_commandes'] ?></strong> commandes<br>
                                        <span class="text-success"><strong><?= number_format($statsWeek['total_recettes'], 2) ?> €</strong></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-info">
                                        <i class="fa fa-calendar-alt"></i> Mois
                                    </h6>
                                    <p class="card-text">
                                        <strong><?= $statsMonth['nb_commandes'] ?></strong> commandes<br>
                                        <span class="text-success"><strong><?= number_format($statsMonth['total_recettes'], 2) ?> €</strong></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-warning">
                                        <i class="fa fa-calendar"></i> Année
                                    </h6>
                                    <p class="card-text">
                                        <strong><?= $statsYear['nb_commandes'] ?></strong> commandes<br>
                                        <span class="text-success"><strong><?= number_format($statsYear['total_recettes'], 2) ?> €</strong></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php if ($statsFiltered['nb_commandes'] > 0): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-black">
                                            <i class="fa fa-filter"></i> Filtré
                                        </h6>
                                        <p class="card-text">
                                            <strong><?= $statsFiltered['nb_commandes'] ?></strong> commandes<br>
                                            <span class="text-success"><strong><?= number_format($statsFiltered['total_recettes'], 2) ?> €</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <strong><i class="fa fa-list"></i> Commandes par jour</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Jour</th>
                                    <th><i class="fa fa-shopping-cart"></i> Nombre de commandes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result_count) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result_count)): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($row['jour'])) ?></td>
                                        <td>
                                            <span class="badge badge-info"><?= $row['nb_commandes'] ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">
                                            <i class="fa fa-info-circle"></i> Aucune donnée disponible
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logique pour les champs de date
    const periodSelect = document.getElementById('period');
    const startDateInput = document.getElementById('date_start');
    const endDateInput = document.getElementById('date_end');

    function toggleDateInputs() {
        if (periodSelect.value === 'custom') {
            startDateInput.disabled = false;
            endDateInput.disabled = false;
            startDateInput.parentElement.classList.remove('text-muted');
            endDateInput.parentElement.classList.remove('text-muted');
        } else {
            startDateInput.disabled = true;
            endDateInput.disabled = true;
            startDateInput.parentElement.classList.add('text-muted');
            endDateInput.parentElement.classList.add('text-muted');
        }
    }

    // Appeler la fonction au chargement
    toggleDateInputs();
    
    // Écouteur pour le changement de période
    periodSelect.addEventListener('change', function() {
        toggleDateInputs();
    });
    
    // Animation des badges au survol
    document.querySelectorAll('.badge').forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        badge.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});

// Raccourcis clavier
document.addEventListener('keydown', function(e) {
    // Ctrl+F pour focus sur le filtre nom client
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('customer_name').focus();
    }
    
    // Ctrl+R pour réinitialiser les filtres
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        window.location.href = window.location.pathname;
    }
});
</script>

<style>
.badge {
    transition: transform 0.2s ease;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.4rem 0.8rem;
    transition: transform 0.2s ease;
}

.table th {
    border-top: none;
}

.card {
    border: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.form-inline .form-group {
    align-items: center;
}

@media (max-width: 768px) {
    .form-inline .form-group {
        margin-bottom: 10px;
    }
    
    .table-responsive {
        font-size: 0.9em;
    }
    
    .badge-lg {
        min-width: auto;
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
}

/* Animation pour les lignes du tableau */
tbody tr {
    transition: background-color 0.2s ease;
}

tbody tr:hover {
    background-color: rgba(0,123,255,0.1) !important;
}

/* Styles pour les badges de type de commande */
.badge-success { 
    background-color: #28a745 !important; 
}

.badge-warning { 
    background-color: #ffc107 !important; 
    color: #212529 !important; 
}

.badge-info { 
    background-color: #17a2b8 !important; 
    color: white !important;
}

.badge-primary { 
    background-color: #007bff !important; 
}

.badge-secondary { 
    background-color: #6c757d !important; 
}
</style>

<?php include "footer.php"; ?>