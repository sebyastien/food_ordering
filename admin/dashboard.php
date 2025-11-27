<?php
session_start();

include "connection.php";

$roles_autorises = ['admin', 'gérant'];
include "auth_check.php";

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

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Statistiques globales</h1>
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <form method="get" class="form-inline float-right" style="margin-top:20px;">
            <div class="form-group mr-2">
                <label for="period" class="mr-2">Période :</label>
                <select id="period" name="period" class="form-control">
                    <option value="custom" <?= $period_filter === 'custom' ? 'selected' : '' ?>>Période personnalisée</option>
                    <option value="today" <?= $period_filter === 'today' ? 'selected' : '' ?>>Aujourd'hui</option>
                    <option value="yesterday" <?= $period_filter === 'yesterday' ? 'selected' : '' ?>>Hier</option>
                    <option value="this_week" <?= $period_filter === 'this_week' ? 'selected' : '' ?>>Cette semaine</option>
                    <option value="last_week" <?= $period_filter === 'last_week' ? 'selected' : '' ?>>Semaine passée</option>
                    <option value="this_month" <?= $period_filter === 'this_month' ? 'selected' : '' ?>>Mois en cours</option>
                    <option value="last_month" <?= $period_filter === 'last_month' ? 'selected' : '' ?>>Mois dernier</option>
                    <option value="this_year" <?= $period_filter === 'this_year' ? 'selected' : '' ?>>Année en cours</option>
                </select>
            </div>
            <div class="form-group mr-2">
                <label for="date_start" class="mr-2">Date début :</label>
                <input type="date" id="date_start" name="date_start" class="form-control" value="<?= htmlspecialchars($date_start) ?>">
            </div>
            <div class="form-group mr-2">
                <label for="date_end" class="mr-2">Date fin :</label>
                <input type="date" id="date_end" name="date_end" class="form-control" value="<?= htmlspecialchars($date_end) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </form>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-lg-12">

            <div class="card mt-4">
                <div class="card-header"><strong>Statistiques</strong></div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h5>Semaine</h5>
                            Commandes : <?= $statsWeek['nb_commandes'] ?><br>
                            Recettes : <?= number_format($statsWeek['total_recettes'], 2, ',', ' ') ?> €
                        </div>
                        <div class="col-md-3">
                            <h5>Mois</h5>
                            Commandes : <?= $statsMonth['nb_commandes'] ?><br>
                            Recettes : <?= number_format($statsMonth['total_recettes'], 2, ',', ' ') ?> €
                        </div>
                        <div class="col-md-3">
                            <h5>Année</h5>
                            Commandes : <?= $statsYear['nb_commandes'] ?><br>
                            Recettes : <?= number_format($statsYear['total_recettes'], 2, ',', ' ') ?> €
                        </div>
                        <?php if ($statsFiltered !== null): ?>
                        <div class="col-md-3">
                            <h5>Filtré</h5>
                            Commandes : <?= $statsFiltered['nb_commandes'] ?><br>
                            Recettes : <?= number_format($statsFiltered['total_recettes'], 2, ',', ' ') ?> €
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header"><strong>Commandes par jour</strong></div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Jour</th>
                                <th>Nombre commandes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // On sauvegarde les données pour Chart.js aussi
                            $rows_for_chart = [];
                            mysqli_data_seek($result_count, 0);
                            while ($row = mysqli_fetch_assoc($result_count)):
                                $rows_for_chart[$row['jour']] = (int)$row['nb_commandes'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['jour']) ?></td>
                                <td><?= (int) $row['nb_commandes'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <canvas id="ordersChart" height="150" style="margin-top:30px;"></canvas>
                </div>
            </div>

            <div class="card mt-4" id="top-plats">
                <div class="card-header"><strong>Top 5 des plats vendus</strong></div>
                <div class="card-body">
                    <form method="get" class="form-inline mb-3" action="#top-plats">
                        <input type="hidden" name="period" value="<?= htmlspecialchars($period_filter) ?>">
                        <input type="hidden" name="date_start" value="<?= htmlspecialchars($date_start) ?>">
                        <input type="hidden" name="date_end" value="<?= htmlspecialchars($date_end) ?>">

                        <div class="form-group mr-2">
                            <label for="sort_by" class="mr-2">Trier par :</label>
                            <select id="sort_by" name="sort_by" class="form-control">
                                <option value="total_ventes" <?= $sortBy === 'total_ventes' ? 'selected' : '' ?>>Quantité vendue</option>
                                <option value="total_recettes" <?= $sortBy === 'total_recettes' ? 'selected' : '' ?>>Recettes générées</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary">Appliquer</button>
                    </form>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Plat</th>
                                <th>Quantité vendue</th>
                                <th>Recettes générées (€)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_plats)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['plat']) ?></td>
                                <td><?= (int)$row['total_ventes'] ?></td>
                                <td><?= number_format($row['total_recettes'], 2, ',', ' ') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const periodSelect = document.getElementById('period');
        const startDateInput = document.getElementById('date_start');
        const endDateInput = document.getElementById('date_end');

        function toggleDateInputs() {
            if (periodSelect.value === 'custom') {
                startDateInput.disabled = false;
                endDateInput.disabled = false;
            } else {
                startDateInput.disabled = true;
                endDateInput.disabled = true;
                // Vider les champs si une période prédéfinie est choisie pour ne pas interférer avec le filtre
                startDateInput.value = '';
                endDateInput.value = '';
            }
        }
        toggleDateInputs();
        periodSelect.addEventListener('change', toggleDateInputs);

        // Construction des labels et data pour Chart.js
        const chartLabels = [];
        const chartData = [];

        <?php
        if (!empty($rows_for_chart)) {
            $dates = array_keys($rows_for_chart);
            sort($dates);
            $minDate = new DateTime(reset($dates));
            $maxDate = new DateTime(end($dates));
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($minDate, $interval, $maxDate->modify('+1 day'));

            $fullDates = [];
            foreach ($period as $date) {
                $fullDates[] = $date->format('Y-m-d');
            }
            sort($fullDates);

            echo "const fullDates = " . json_encode($fullDates) . ";\n";
            echo "const rowsForChart = " . json_encode($rows_for_chart) . ";\n";
        } else {
            echo "const fullDates = [];\n";
            echo "const rowsForChart = {};\n";
        }
        ?>

        fullDates.forEach(date => {
            chartLabels.push(date);
            chartData.push(rowsForChart[date] ?? 0);
        });

        const ctx = document.getElementById('ordersChart').getContext('2d');
        const ordersChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Nombre de commandes',
                    data: chartData,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    });
</script>

<?php include "footer.php"; ?>