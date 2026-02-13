<?php
session_start();

include "../admin/connection.php";
require_once "TableSessionManager.php";
require_once "NotificationManager.php";

$sessionManager = new TableSessionManager($link);
$notificationManager = new NotificationManager($link);

// Nettoyer les sessions expirées
$sessionManager->cleanExpiredSessions();

// Traitement des actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'open_table') {
        $table_id = intval($_POST['table_id']);
        $opened_by = trim($_POST['opened_by'] ?? 'Serveur');
        
        $result = $sessionManager->openTableSession($table_id, $opened_by, 90);
        
        if ($result['success']) {
            $message = "Table ouverte !";
            $message_type = 'success';
        } else {
            $message = "Erreur : " . $result['error'];
            $message_type = 'error';
        }
    }
    
    if ($action === 'close_table') {
        $table_id = intval($_POST['table_id']);
        if ($sessionManager->closeTableSession($table_id)) {
            $message = "Table fermée";
            $message_type = 'success';
        }
    }
    
    if ($action === 'extend_session') {
        $token = $_POST['token'] ?? '';
        if ($sessionManager->extendSession($token, 30)) {
            $message = "Session prolongée de 30 minutes";
            $message_type = 'success';
        }
    }
}

// Récupérer les tables et sessions
$tables_result = mysqli_query($link, "SELECT * FROM restaurant_tables WHERE is_active = 1 ORDER BY table_number");
$tables = [];
while ($row = mysqli_fetch_assoc($tables_result)) {
    $tables[] = $row;
}

$active_sessions = $sessionManager->getActiveSessions();
$active_table_ids = array_column($active_sessions, 'table_id');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#a40301">
    <title>Restaurant Manager</title>
    <link rel="manifest" href="manifest.json">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding-bottom: 70px;
            overscroll-behavior: none;
        }
        
        .mobile-header {
            background: linear-gradient(135deg, #a40301 0%, #7a0301 100%);
            color: white;
            padding: 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .mobile-header h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .mobile-header .subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .stats-bar {
            background: white;
            padding: 15px;
            display: flex;
            justify-content: space-around;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #a40301;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #666;
            margin-top: 3px;
        }
        
        .section {
            padding: 15px;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        
        .table-btn {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 20px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .table-btn:active {
            transform: scale(0.95);
        }
        
        .table-btn.open {
            background: #d4edda;
            border-color: #28a745;
        }
        
        .table-btn.closed:active {
            background: #fff8f8;
        }
        
        .table-number {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .table-status {
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
        }
        
        .status-open {
            background: #28a745;
            color: white;
        }
        
        .status-closed {
            background: #6c757d;
            color: white;
        }
        
        .session-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }
        
        .session-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .session-table {
            font-weight: bold;
            font-size: 1.1rem;
            color: #333;
        }
        
        .session-time {
            font-size: 0.9rem;
            color: #666;
        }
        
        .time-remaining {
            color: #28a745;
            font-weight: bold;
        }
        
        .time-warning {
            color: #ffc107;
        }
        
        .time-danger {
            color: #dc3545;
        }
        
        .session-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }
        
        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn:active {
            transform: scale(0.95);
        }
        
        .btn-extend {
            background: #28a745;
            color: white;
        }
        
        .btn-close {
            background: #dc3545;
            color: white;
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 100;
        }
        
        .nav-item {
            text-align: center;
            color: #666;
            text-decoration: none;
            padding: 5px 15px;
            flex: 1;
            transition: color 0.2s ease;
        }
        
        .nav-item.active {
            color: #a40301;
        }
        
        .nav-item i {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 3px;
        }
        
        .nav-item span {
            font-size: 0.7rem;
        }
        
        .toast {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            z-index: 1000;
            animation: slideDown 0.3s ease;
        }
        
        .toast.success {
            background: #28a745;
        }
        
        .toast.error {
            background: #dc3545;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .install-prompt {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            margin: 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .install-prompt i {
            font-size: 2rem;
        }
        
        .install-prompt .content {
            flex: 1;
        }
        
        .install-prompt h3 {
            font-size: 1rem;
            margin-bottom: 3px;
        }
        
        .install-prompt p {
            font-size: 0.8rem;
            opacity: 0.9;
        }
        
        .install-btn {
            background: white;
            color: #667eea;
            padding: 8px 15px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <h1><i class="fas fa-table"></i> Restaurant Manager</h1>
        <div class="subtitle">Gestion des tables en temps réel</div>
    </div>
    
    <?php if ($message): ?>
    <div class="toast <?= $message_type ?>" id="toast">
        <?= htmlspecialchars($message) ?>
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('toast').style.display = 'none';
        }, 3000);
    </script>
    <?php endif; ?>
    
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-value"><?= count($active_sessions) ?></div>
            <div class="stat-label">Ouvertes</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?= count($tables) - count($active_sessions) ?></div>
            <div class="stat-label">Disponibles</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?= array_sum(array_column($active_sessions, 'total_orders')) ?></div>
            <div class="stat-label">Commandes</div>
        </div>
    </div>
    
    <div id="installPrompt" class="install-prompt" style="display: none;">
        <i class="fas fa-mobile-alt"></i>
        <div class="content">
            <h3>Installer l'application</h3>
            <p>Ajoutez à votre écran d'accueil pour un accès rapide</p>
        </div>
        <button class="install-btn" onclick="installApp()">Installer</button>
    </div>
    
    <div class="section">
        <div class="section-title">
            <i class="fas fa-door-open"></i>
            Toutes les tables
        </div>
        <div class="table-grid">
            <?php foreach ($tables as $table): 
                $is_open = in_array($table['id'], $active_table_ids);
            ?>
            <div class="table-btn <?= $is_open ? 'open' : 'closed' ?>" 
                 onclick="<?= $is_open ? 'closeTable(' . $table['id'] . ')' : 'openTable(' . $table['id'] . ', \'' . htmlspecialchars($table['table_name']) . '\')' ?>">
                <div class="table-number">T<?= $table['table_number'] ?></div>
                <div class="table-status <?= $is_open ? 'status-open' : 'status-closed' ?>">
                    <?= $is_open ? 'OUVERTE' : 'FERMÉE' ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">
            <i class="fas fa-clock"></i>
            Sessions actives (<?= count($active_sessions) ?>)
        </div>
        <?php if (count($active_sessions) > 0): ?>
            <?php foreach ($active_sessions as $session): 
                $minutes_left = $session['minutes_remaining'];
                $time_class = $minutes_left > 30 ? 'time-remaining' : ($minutes_left > 15 ? 'time-warning' : 'time-danger');
            ?>
            <div class="session-card">
                <div class="session-header">
                    <div class="session-table"><?= htmlspecialchars($session['table_name']) ?></div>
                    <div class="session-time <?= $time_class ?>">
                        <i class="fas fa-clock"></i> <?= $minutes_left ?> min
                    </div>
                </div>
                <div style="font-size: 0.85rem; color: #666; margin-bottom: 8px;">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($session['opened_by']) ?> | 
                    <i class="fas fa-shopping-cart"></i> <?= $session['total_orders'] ?> commande(s)
                </div>
                <div class="session-actions">
                    <button class="btn btn-extend" onclick="extendSession('<?= $session['session_token'] ?>')">
                        <i class="fas fa-plus"></i> +30 min
                    </button>
                    <button class="btn btn-close" onclick="closeTable(<?= $session['table_id'] ?>)">
                        <i class="fas fa-times"></i> Fermer
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune session active</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="bottom-nav">
        <a href="#" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Accueil</span>
        </a>
        <a href="session_history.php" class="nav-item">
            <i class="fas fa-history"></i>
            <span>Historique</span>
        </a>
        <a href="export_reports.php" class="nav-item">
            <i class="fas fa-file-export"></i>
            <span>Rapports</span>
        </a>
    </div>
    
    <script>
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('installPrompt').style.display = 'flex';
        });
        
        function installApp() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    deferredPrompt = null;
                    document.getElementById('installPrompt').style.display = 'none';
                });
            }
        }
        
        function openTable(tableId, tableName) {
            const serverName = prompt('Nom du serveur:', 'Serveur');
            if (serverName) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="open_table">
                    <input type="hidden" name="table_id" value="${tableId}">
                    <input type="hidden" name="opened_by" value="${serverName}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function closeTable(tableId) {
            if (confirm('Fermer cette table ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="close_table">
                    <input type="hidden" name="table_id" value="${tableId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function extendSession(token) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="extend_session">
                <input type="hidden" name="token" value="${token}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        // Rafraîchir toutes les 30 secondes
        setTimeout(() => location.reload(), 30000);
        
        // Service Worker pour PWA
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>
