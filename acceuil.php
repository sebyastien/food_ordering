<?php
session_start();

include "connection.php";


include "header.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$link) {
    die("Erreur de connexion à la base de données.");
}

/**
 * Fonction pour récupérer les statistiques
 */
function getStats($link, string $startDate, string $endDate, string $extraWhere = "", array $params = []): array {
    $sql = "SELECT COUNT(*) AS nb_commandes, IFNULL(SUM(total_price), 0) AS total_recettes
            FROM orders
            WHERE order_date BETWEEN ? AND ?";
    if ($extraWhere) {
        $sql .= " AND $extraWhere";
    }

    $stmt = mysqli_prepare($link, $sql);
    if ($stmt === false) {
        die("Erreur de préparation de la requête.");
    }

    // On bind les paramètres
    $types = str_repeat("s", 2 + count($params));
    $stmt_params = array_merge([$startDate, $endDate], $params);

    mysqli_stmt_bind_param($stmt, $types, ...$stmt_params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($res);
}

// Récupérer la période sélectionnée et le critère de tri
$period_filter = isset($_GET['period']) ? $_GET['period'] : 'custom';
$date_start = $_GET['date_start'] ?? '';
$date_end = $_GET['date_end'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'total_ventes'; // NOUVEAU: Critère de tri par défaut

// S'assurer que le critère de tri est valide
if ($sortBy !== 'total_ventes' && $sortBy !== 'total_recettes') {
    $sortBy = 'total_ventes';
}


// Ajuster les dates en fonction de la période sélectionnée
$now = new DateTime();
if ($period_filter !== 'custom') {
    switch ($period_filter) {
        case 'today': // NOUVEAU: Cas pour "Aujourd'hui"
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
        case 'this_year':
            $date_start = $now->format('Y-01-01');
            $date_end = $now->format('Y-12-31');
            break;
    }
}


$where = "";
$params = [];

if ($date_start && $date_end) {
    $where = "order_date BETWEEN ? AND ?";
    $params = [$date_start . " 00:00:00", $date_end . " 23:59:59"];
} elseif ($date_start) {
    $where = "order_date >= ?";
    $params = [$date_start . " 00:00:00"];
} elseif ($date_end) {
    $where = "order_date <= ?";
    $params = [$date_end . " 23:59:59"];
}

// Dates par défaut (semaine, mois, année)
$now = new DateTime();
$startOfWeek = (clone $now)->modify('monday this week')->format('Y-m-d 00:00:00');
$endOfWeek = (clone $now)->modify('sunday this week')->format('Y-m-d 23:59:59');
$startOfMonth = (clone $now)->modify('first day of this month')->format('Y-m-d 00:00:00');
$endOfMonth = (clone $now)->modify('last day of this month')->format('Y-m-d 23:59:59');
$startOfYear = (clone $now)->modify('first day of January this year')->format('Y-m-d 00:00:00');
$endOfYear = (clone $now)->modify('last day of December this year')->format('Y-m-d 23:59:59');


// Stats globales
$statsWeek = getStats($link, $startOfWeek, $endOfWeek);
$statsMonth = getStats($link, $startOfMonth, $endOfMonth);
$statsYear = getStats($link, $startOfYear, $endOfYear);

// Stats filtrées (si filtre)
$statsFiltered = null;
if ($where) {
    if (count($params) === 2) {
        $statsFiltered = getStats($link, $params[0], $params[1]);
    } elseif (count($params) === 1) {
        if (strpos($where, ">= ") !== false) {
            $statsFiltered = getStats($link, $params[0], '9999-12-31 23:59:59');
        } else {
            $statsFiltered = getStats($link, '0000-01-01 00:00:00', $params[0]);
        }
    } else {
        $statsFiltered = ['nb_commandes' => 0, 'total_recettes' => 0];
    }
}

// Commandes par jour
$sql_count = "SELECT DATE(order_date) AS jour, COUNT(*) AS nb_commandes FROM orders";
if ($where) {
    $sql_count .= " WHERE $where";
}
$sql_count .= " GROUP BY jour ORDER BY jour DESC";

$stmt_count = mysqli_prepare($link, $sql_count);
if ($params) {
    $types = str_repeat("s", count($params));
    mysqli_stmt_bind_param($stmt_count, $types, ...$params);
}
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);

// Top 5 des plats vendus
$sql_plats = "SELECT oi.food_name AS plat,
                     SUM(oi.quantity) AS total_ventes,
                     SUM(oi.price * oi.quantity) AS total_recettes
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             WHERE 1=1";

if ($where) {
    $sql_plats .= " AND $where";
}

// MODIFIÉ: Utilisation de la variable de tri
$sql_plats .= " GROUP BY oi.food_name ORDER BY $sortBy DESC LIMIT 5";

$stmt_plats = mysqli_prepare($link, $sql_plats);
if ($params) {
    $types = str_repeat("s", count($params));
    mysqli_stmt_bind_param($stmt_plats, $types, ...$params);
}
mysqli_stmt_execute($stmt_plats);
$result_plats = mysqli_stmt_get_result($stmt_plats);
?>


<?php include "footer.php"; ?>