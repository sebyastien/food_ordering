<?php
session_start();

// Sécurité admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // header("Location: login.php");
    // exit;
}

include "../admin/connection.php";
require_once "TableSessionManager.php";

$sessionManager = new TableSessionManager($link);

// Filtres
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$table_filter = $_GET['table_id'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Construction de la requête
$query = "
    SELECT 
        ts.*,
        rt.table_number,
        rt.table_name,
        rt.capacity,
        TIMESTAMPDIFF(MINUTE, ts.opened_at, COALESCE(ts.closed_at, NOW())) as duration_minutes,
        (SELECT COUNT(*) FROM orders o WHERE o.session_token = ts.session_token) as orders_count,
        (SELECT SUM(o.total_price) FROM orders o WHERE o.session_token = ts.session_token) as total_revenue
    FROM table_sessions ts
    INNER JOIN restaurant_tables rt ON ts.table_id = rt.id
    WHERE DATE(ts.opened_at) BETWEEN ? AND ?
";

$params = [$date_from, $date_to];
$types = "ss";

if ($table_filter !== '') {
    $query .= " AND ts.table_id = ?";
    $params[] = intval($table_filter);
    $types .= "i";
}

if ($status_filter !== '') {
    $query .= " AND ts.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY ts.opened_at DESC LIMIT 500";

$stmt = $link->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$sessions = [];
while ($row = $result->fetch_assoc()) {
    $sessions[] = $row;
}
$stmt->close();

// Statistiques globales
$stats_query = "
    SELECT 
        COUNT(*) as total_sessions,
        COUNT(CASE WHEN status = 'OPEN' THEN 1 END) as open_sessions,
        COUNT(CASE WHEN status = 'CLOSED' THEN 1 END) as closed_sessions,
        AVG(TIMESTAMPDIFF(MINUTE, opened_at, closed_at)) as avg_duration,
        SUM((SELECT COUNT(*) FROM orders o WHERE o.session_token = ts.session_token)) as total_orders,
        SUM((SELECT SUM(o.total_price) FROM orders o WHERE o.session_token = ts.session_token)) as total_revenue
    FROM table_sessions ts
    WHERE DATE(opened_at) BETWEEN ? AND ?
";

$stmt_stats = $link->prepare($stats_query);
$stmt_stats->bind_param("ss", $date_from, $date_to);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

// Liste des tables pour le filtre
$tables_result = mysqli_query($link, "SELECT * FROM restaurant_tables ORDER BY table_number");
$tables = [];
while ($row = mysqli_fetch_assoc($tables_result)) {
    $tables[] = $row;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Sessions</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
        }
        
        header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #a40301;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .filter-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #a40301;
            color: white;
        }
        
        .btn-primary:hover {
            background: #7a0301;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #a40301;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-open {
            background: #d4edda;
            color: #155724;
        }
        
        .status-closed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .details-btn {
            padding: 6px 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
        }
        
        .details-btn:hover {
            background: #0056b3;
        }
        
        .export-section {
            text-align: right;
            margin-bottom: 15px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-history"></i> Historique des Sessions</h1>
            <p style="color: #666; margin-top: 5px;">Consultez l'historique complet de toutes les sessions de tables</p>
        </header>
        
        <div class="filters">
            <form method="GET">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Date de début</label>
                        <input type="date" name="date_from" value="<?= $date_from ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Date de fin</label>
                        <input type="date" name="date_to" value="<?= $date_to ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Table</label>
                        <select name="table_id">
                            <option value="">Toutes les tables</option>
                            <?php foreach ($tables as $table): ?>
                                <option value="<?= $table['id'] ?>" <?= $table_filter == $table['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($table['table_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Statut</label>
                        <select name="status">
                            <option value="">Tous les statuts</option>
                            <option value="OPEN" <?= $status_filter === 'OPEN' ? 'selected' : '' ?>>Ouvertes</option>
                            <option value="CLOSED" <?= $status_filter === 'CLOSED' ? 'selected' : '' ?>>Fermées</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrer
                </button>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Réinitialiser
                </a>
            </form>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['total_sessions']) ?></div>
                <div class="stat-label">Sessions totales</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['total_orders']) ?></div>
                <div class="stat-label">Commandes</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['total_revenue'], 2) ?> €</div>
                <div class="stat-label">Chiffre d'affaires</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['avg_duration']) ?> min</div>
                <div class="stat-label">Durée moyenne</div>
            </div>
        </div>
        
        <div class="export-section">
            <button onclick="exportToCSV()" class="btn btn-primary">
                <i class="fas fa-download"></i> Exporter CSV
            </button>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>
        
        <div class="table-container">
            <?php if (count($sessions) > 0): ?>
                <table id="sessionsTable">
                    <thead>
                        <tr>
                            <th>Table</th>
                            <th>Ouvert le</th>
                            <th>Fermé le</th>
                            <th>Durée</th>
                            <th>Serveur</th>
                            <th>Commandes</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($session['table_name']) ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($session['opened_at'])) ?></td>
                                <td><?= $session['closed_at'] ? date('d/m/Y H:i', strtotime($session['closed_at'])) : '-' ?></td>
                                <td><?= $session['duration_minutes'] ?> min</td>
                                <td><?= htmlspecialchars($session['opened_by']) ?></td>
                                <td><?= $session['orders_count'] ?></td>
                                <td><?= number_format($session['total_revenue'], 2) ?> €</td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($session['status']) ?>">
                                        <?= $session['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="details-btn" onclick="viewDetails('<?= $session['session_token'] ?>')">
                                        <i class="fas fa-eye"></i> Détails
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Aucune session trouvée</h3>
                    <p>Modifiez vos filtres pour afficher plus de résultats</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function viewDetails(sessionToken) {
            window.location.href = 'session_details.php?token=' + sessionToken;
        }
        
        function exportToCSV() {
            const table = document.getElementById('sessionsTable');
            let csv = [];
            
            // En-têtes
            const headers = Array.from(table.querySelectorAll('thead th'))
                .slice(0, -1) // Exclure "Actions"
                .map(th => th.textContent);
            csv.push(headers.join(';'));
            
            // Données
            table.querySelectorAll('tbody tr').forEach(row => {
                const data = Array.from(row.querySelectorAll('td'))
                    .slice(0, -1) // Exclure "Actions"
                    .map(td => td.textContent.trim().replace(/;/g, ','));
                csv.push(data.join(';'));
            });
            
            // Téléchargement
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'historique_sessions_' + new Date().toISOString().split('T')[0] + '.csv';
            link.click();
        }
    </script>
</body>
</html>
