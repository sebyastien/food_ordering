<?php
session_start();

// Sécurité : vérifier que l'utilisateur est un administrateur/serveur
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Pour le développement, on peut commenter cette vérification
    // header("Location: login.php");
    // exit;
}

include "connection.php";
require_once "TableSessionManager.php";

$sessionManager = new TableSessionManager($link);

// --- GESTION DES MESSAGES FLASH (Pour affichage après redirection) ---
$message = '';
$message_type = '';

if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'] ?? 'success';
    // On nettoie la session pour ne pas réafficher le message indéfiniment
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}

// Nettoyer les sessions expirées AUTOMATIQUEMENT
$cleaned = $sessionManager->cleanExpiredSessions();
if ($cleaned > 0) {
    $message = "$cleaned table(s) fermée(s) automatiquement (temps expiré)";
    $message_type = 'success';
}

// --- TRAITEMENT DES ACTIONS (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $success = false;
    $post_message = '';
    
    if ($action === 'open_table') {
        $table_id = intval($_POST['table_id']);
        $opened_by = trim($_POST['opened_by'] ?? 'Serveur');
        $duration = intval($_POST['duration'] ?? 90);
        
        $result = $sessionManager->openTableSession($table_id, $opened_by, $duration);
        
        if ($result['success']) {
            $success = true;
            $post_message = "Table ouverte avec succès pour " . $duration . " minutes !";
        } else {
            $post_message = "Erreur : " . $result['error'];
        }
    }
    
    if ($action === 'close_table') {
        $table_id = intval($_POST['table_id']);
        if ($sessionManager->closeTableSession($table_id)) {
            $success = true;
            $post_message = "Table fermée manuellement avec succès";
        } else {
            $post_message = "Erreur lors de la fermeture de la table";
        }
    }
    
    if ($action === 'extend_session') {
        $token = $_POST['token'] ?? '';
        $minutes = intval($_POST['minutes'] ?? 30);
        if ($sessionManager->extendSession($token, $minutes)) {
            $success = true;
            $post_message = "Session prolongée de {$minutes} minutes";
        } else {
            $post_message = "Erreur lors de la prolongation";
        }
    }

    // REDIRECTION (Empêche le renvoi du formulaire au rafraîchissement)
    if ($success) {
        $_SESSION['flash_message'] = $post_message;
        $_SESSION['flash_type'] = 'success';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else if (!empty($post_message)) {
        // En cas d'erreur, on peut aussi rediriger ou afficher direct
        $_SESSION['flash_message'] = $post_message;
        $_SESSION['flash_type'] = 'error';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Inclure le header standard APRÈS le traitement des formulaires pour éviter "headers already sent"
include "header.php";

// Récupérer toutes les tables
$tables_query = "SELECT * FROM restaurant_tables WHERE is_active = 1 ORDER BY table_number ASC";
$tables_result = mysqli_query($link, $tables_query);
$tables = [];
while ($row = mysqli_fetch_assoc($tables_result)) {
    $tables[] = $row;
}

// Récupérer les sessions actives
$active_sessions = $sessionManager->getActiveSessions();
$active_table_ids = array_column($active_sessions, 'table_id');
?>

<!-- Styles spécifiques pour la page de gestion des tables -->
<style>
    .breadcrumbs {
        background: transparent;
        padding: 0 0 20px 0;
    }
    
    .content {
        padding: 20px;
    }
    
    * {
        box-sizing: border-box;
    }
    
    .manage-tables-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        background: #f5f5f5;
        padding: 20px;
        max-width: 100%;
    }
    
    .page-header {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .page-header h1 {
        color: #333;
        font-size: 2rem;
        margin-bottom: 8px;
    }
    
    .page-header h1 i {
        color: #a40301;
        margin-right: 12px;
    }
    
    .subtitle {
        color: #666;
        font-size: 1rem;
    }
    
    .message {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .message.success {
        background: #d4edda;
        border-left: 4px solid #28a745;
        color: #155724;
    }
    
    .message.error {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
        color: #721c24;
    }
    
    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        color: #a40301;
    }
    
    .stat-label {
        color: #666;
        margin-top: 5px;
        font-size: 0.9rem;
    }
    
    .sections {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
    }
    
    .section {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title i {
        color: #a40301;
    }
    
    .table-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .table-card {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        position: relative;
    }
    
    .table-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .table-card.available {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        border-color: #4caf50;
    }
    
    .table-card.occupied {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        border-color: #f44336;
        cursor: not-allowed;
    }
    
    .table-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .table-card.available .table-number {
        color: #2e7d32;
    }
    
    .table-card.occupied .table-number {
        color: #c62828;
    }
    
    .table-status {
        font-size: 0.9rem;
        font-weight: 500;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 8px;
    }
    
    .table-card.available .table-status {
        background: #4caf50;
        color: white;
    }
    
    .table-card.occupied .table-status {
        background: #f44336;
        color: white;
    }
    
    .table-capacity {
        margin-top: 8px;
        color: #666;
        font-size: 0.9rem;
    }
    
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-success {
        background: #28a745;
        color: white;
    }
    
    .btn-success:hover {
        background: #218838;
    }
    
    .btn-danger {
        background: #dc3545;
        color: white;
    }
    
    .btn-danger:hover {
        background: #c82333;
    }
    
    .btn-warning {
        background: #ffc107;
        color: #212529;
    }
    
    .btn-warning:hover {
        background: #e0a800;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.85rem;
    }
    
    .session-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .session-item {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 15px;
        background: #fafafa;
    }
    
    .session-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .session-table {
        font-size: 1.2rem;
        font-weight: bold;
        color: #a40301;
    }
    
    .session-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 0.9rem;
        color: #555;
    }
    
    .session-info i {
        width: 20px;
        color: #666;
    }
    
    .time-remaining {
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 5px;
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .time-remaining.time-warning {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .time-remaining.time-danger {
        background: #ffebee;
        color: #c62828;
        animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .session-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.3;
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    
    .modal.active {
        display: flex;
    }
    
    .modal-content {
        background: white;
        border-radius: 15px;
        padding: 0;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        animation: modalSlideIn 0.3s;
    }
    
    @keyframes modalSlideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .modal-header {
        background: linear-gradient(135deg, #a40301 0%, #d32f2f 100%);
        color: white;
        padding: 20px 25px;
        border-radius: 15px 15px 0 0;
    }
    
    .modal-header h3 {
        margin: 0;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .modal-content form {
        padding: 25px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #a40301;
    }
    
    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 25px;
    }
    
    @media (max-width: 768px) {
        .sections {
            grid-template-columns: 1fr;
        }
        
        .table-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }
</style>

<!-- CONTENU DE LA PAGE -->
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Gestion des Tables</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="manage-tables-container">
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <i class="fas <?= $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?= count($tables) ?></div>
                <div class="stat-label">Tables totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count($active_sessions) ?></div>
                <div class="stat-label">Tables occupées</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count($tables) - count($active_sessions) ?></div>
                <div class="stat-label">Tables disponibles</div>
            </div>
        </div>
        
        <div class="sections">
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-chair"></i> Toutes les tables
                </div>
                <div class="table-grid">
                    <?php foreach ($tables as $table): ?>
                        <?php 
                        $is_occupied = in_array($table['id'], $active_table_ids);
                        $class = $is_occupied ? 'occupied' : 'available';
                        ?>
                        <div class="table-card <?= $class ?>" 
                             <?= !$is_occupied ? "onclick=\"openTableModal({$table['id']}, 'Table {$table['table_number']}')\"" : "" ?>>
                            <div class="table-number">
                                <?= $table['table_number'] ?>
                            </div>
                            <div class="table-capacity">
                                <?= $table['capacity'] ?> places
                            </div>
                            <div class="table-status">
                                <?= $is_occupied ? 'Occupée' : 'Disponible' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-clock"></i> Sessions actives
                </div>
                <?php if (count($active_sessions) > 0): ?>
                    <div class="session-list">
                        <?php foreach ($active_sessions as $session): ?>
                            <div class="session-item">
                                <div class="session-header">
                                    <div class="session-table">
                                        Table <?= $session['table_number'] ?>
                                    </div>
                                </div>
                                <div class="session-info">
                                    <div><?= htmlspecialchars($session['opened_by']) ?></div>
                                    <div><?= $session['total_orders'] ?> commande(s)</div>
                                    <div class="time-remaining" 
                                         data-expires="<?= $session['expires_at'] ?>">
                                        <span class="time-text">Calcul en cours...</span>
                                    </div>
                                </div>
                                <div class="session-actions">
                                    <button class="btn btn-warning btn-sm" 
                                            onclick="extendSession('<?= $session['session_token'] ?>', 15)">
                                        +15 min
                                    </button>
                                    <button class="btn btn-warning btn-sm" 
                                            onclick="extendSession('<?= $session['session_token'] ?>', 30)">
                                        +30 min
                                    </button>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="confirmCloseTable(<?= $session['table_id'] ?>)">
                                        Fermer
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-coffee"></i>
                        <p>Aucune table occupée pour le moment</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="openTableModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-door-open"></i> Ouvrir une table</h3>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="open_table">
            <input type="hidden" name="table_id" id="modal_table_id">
            
            <div class="form-group">
                <label>Table sélectionnée</label>
                <input type="text" id="modal_table_name" readonly style="background: #f8f9fa;">
            </div>
            
            <div class="form-group">
                <label for="opened_by">Nom du serveur/employé</label>
                <input type="text" name="opened_by" id="opened_by" value="Serveur" required>
            </div>
            
            <div class="form-group">
                <label for="duration">Durée de la session</label>
                <select name="duration" id="duration">
                    <option value="15">15 minutes</option>
                    <option value="30">30 minutes</option>
                    <option value="60">1 heure (60 minutes)</option>
                    <option value="90" selected>1h30 (90 minutes - recommandé)</option>
                    <option value="120">2 heures (120 minutes)</option>
                    <option value="180">3 heures (180 minutes)</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn btn-success">
                    Ouvrir la table
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openTableModal(tableId, tableName) {
        document.getElementById('modal_table_id').value = tableId;
        document.getElementById('modal_table_name').value = tableName;
        document.getElementById('openTableModal').classList.add('active');
    }
    
    function closeModal() {
        document.getElementById('openTableModal').classList.remove('active');
    }
    
    function confirmCloseTable(tableId) {
        if (confirm('Êtes-vous sûr de vouloir fermer cette table ? Les clients ne pourront plus commander.')) {
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
    
    function extendSession(token, minutes) {
        if (confirm(`Prolonger la session de ${minutes} minutes ?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="extend_session">
                <input type="hidden" name="token" value="${token}">
                <input type="hidden" name="minutes" value="${minutes}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // DÉCOMPTE EN TEMPS RÉEL
    function updateTimers() {
        const now = new Date();
        
        document.querySelectorAll('.time-remaining').forEach(el => {
            let expiresStr = el.getAttribute('data-expires').replace(' ', 'T');
            const expiresAt = new Date(expiresStr);
            
            const diff = expiresAt - now;
            
            if (diff <= 0) {
                el.className = 'time-remaining time-danger';
                el.querySelector('.time-text').textContent = 'EXPIRÉ - Fermeture auto...';
                
                if (diff < -5000) {
                     location.reload(); 
                }
                return;
            }
            
            const heuresLeft = Math.floor(diff / 1000 / 60 / 60);
            const minutesLeft = Math.floor((diff / 1000 / 60) % 60);
            const secondsLeft = Math.floor((diff / 1000) % 60);
            
            const totalMinutes = heuresLeft * 60 + minutesLeft;
            if (totalMinutes < 15) {
                el.className = 'time-remaining time-danger';
            } else if (totalMinutes < 30) {
                el.className = 'time-remaining time-warning';
            } else {
                el.className = 'time-remaining';
            }
            
            let timeText = '';
            if (heuresLeft > 0) {
                timeText = `${heuresLeft}h ${minutesLeft}min ${secondsLeft}s restantes`;
            } else if (minutesLeft > 0) {
                timeText = `${minutesLeft} minute${minutesLeft > 1 ? 's' : ''} ${secondsLeft}s restantes`;
            } else {
                timeText = `${secondsLeft} seconde${secondsLeft > 1 ? 's' : ''} restante${secondsLeft > 1 ? 's' : ''}`;
            }
            
            el.querySelector('.time-text').textContent = timeText;
        });
    }
    
    setInterval(updateTimers, 1000);
    updateTimers();
    
    setInterval(() => {
        location.reload();
    }, 60000);
</script>

<?php include "footer.php"; ?>