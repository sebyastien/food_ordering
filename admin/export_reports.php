<?php
session_start();

include "../admin/connection.php";
require_once "ReportGenerator.php";

$reportGenerator = new ReportGenerator($link);

// Traitement de l'export
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
    $date_to = $_GET['date_to'] ?? date('Y-m-d');
    $table_id = !empty($_GET['table_id']) ? intval($_GET['table_id']) : null;
    
    if ($action === 'export_sessions_csv') {
        $csv = $reportGenerator->generateSessionsCSV($date_from, $date_to, $table_id);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="sessions_' . $date_from . '_' . $date_to . '.csv"');
        echo $csv;
        exit;
    }
    
    if ($action === 'export_orders_csv') {
        $csv = $reportGenerator->generateOrdersCSV($date_from, $date_to, $table_id);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="commandes_' . $date_from . '_' . $date_to . '.csv"');
        echo $csv;
        exit;
    }
    
    if ($action === 'export_html_pdf') {
        $html = $reportGenerator->generateHTMLReport($date_from, $date_to, $table_id);
        
        // Pour une vraie conversion PDF, utiliser une librairie comme TCPDF ou mPDF
        // Ici on retourne le HTML qui peut être imprimé en PDF via le navigateur
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }
}

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
    <title>Export de Rapports</title>
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
            max-width: 900px;
            margin: 0 auto;
        }
        
        header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        h1 {
            color: #a40301;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .export-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .export-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .export-btn {
            padding: 20px;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        
        .export-btn:hover {
            border-color: #a40301;
            background: #fff8f8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(164,3,1,0.2);
        }
        
        .export-btn i {
            font-size: 3rem;
            color: #a40301;
            margin-bottom: 15px;
        }
        
        .export-btn .title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 8px;
        }
        
        .export-btn .description {
            color: #666;
            font-size: 0.9rem;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }
        
        .info-box i {
            color: #2196F3;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-file-export"></i> Export de Rapports</h1>
            <p style="color: #666; margin-top: 10px;">Générez et téléchargez vos rapports d'activité</p>
        </header>
        
        <div class="export-card">
            <h2 style="margin-bottom: 20px;">Paramètres d'export</h2>
            
            <form id="exportForm">
                <div class="form-group">
                    <label>Date de début</label>
                    <input type="date" name="date_from" id="date_from" value="<?= date('Y-m-d', strtotime('-30 days')) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Date de fin</label>
                    <input type="date" name="date_to" id="date_to" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Filtrer par table (optionnel)</label>
                    <select name="table_id" id="table_id">
                        <option value="">Toutes les tables</option>
                        <?php foreach ($tables as $table): ?>
                            <option value="<?= $table['id'] ?>">
                                <?= htmlspecialchars($table['table_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Formats disponibles :</strong> Les fichiers CSV s'ouvrent directement dans Excel. 
                Le format HTML/PDF peut être imprimé ou sauvegardé en PDF via votre navigateur.
            </div>
        </div>
        
        <div class="export-card">
            <h2 style="margin-bottom: 20px;">Choisissez votre format</h2>
            
            <div class="export-options">
                <a href="#" class="export-btn" onclick="exportReport('export_sessions_csv')">
                    <i class="fas fa-file-csv"></i>
                    <div class="title">Sessions (CSV)</div>
                    <div class="description">Historique des sessions pour Excel</div>
                </a>
                
                <a href="#" class="export-btn" onclick="exportReport('export_orders_csv')">
                    <i class="fas fa-file-excel"></i>
                    <div class="title">Commandes (CSV)</div>
                    <div class="description">Détail des commandes pour Excel</div>
                </a>
                
                <a href="#" class="export-btn" onclick="exportReport('export_html_pdf')">
                    <i class="fas fa-file-pdf"></i>
                    <div class="title">Rapport complet</div>
                    <div class="description">Version imprimable (HTML/PDF)</div>
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function exportReport(action) {
            const form = document.getElementById('exportForm');
            const formData = new FormData(form);
            
            const params = new URLSearchParams(formData);
            params.append('action', action);
            
            window.location.href = '?' + params.toString();
        }
    </script>
</body>
</html>
