<?php
session_start();

// ================================
// CONNEXION À LA BASE DE DONNÉES
// ================================
if (!isset($link)) {
    include "../admin/connection.php";
}

// ================================
// INCLURE LE GESTIONNAIRE DE SESSIONS
// ================================
require_once "../admin/TableSessionManager.php";

$sessionManager = new TableSessionManager($link);

// Nettoyer les sessions expirées
$sessionManager->cleanExpiredSessions();

// ================================
// RÉCUPÉRER L'IDENTIFIANT QR
// ================================
$qr_identifier = isset($_GET['qr']) ? trim($_GET['qr']) : '';

if (empty($qr_identifier)) {
    die("Erreur : Aucun QR code fourni");
}

// ================================
// VÉRIFIER QUE LA TABLE EXISTE
// ================================
$stmt = $link->prepare("SELECT * FROM restaurant_tables WHERE qr_identifier = ? AND is_active = 1");
$stmt->bind_param("s", $qr_identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die("Erreur : Table non trouvée ou désactivée");
}

$table = $result->fetch_assoc();
$table_id = $table['id'];
$table_name = $table['table_name'];
$stmt->close();

// ================================
// VÉRIFIER SI UNE SESSION ACTIVE EXISTE
// ================================
$active_session = $sessionManager->getActiveSessionForTable($table_id);

if ($active_session === null) {
    // Pas de session active → Afficher page d'attente
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="refresh" content="10">
        <title>Table non ouverte</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .waiting-container {
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 500px;
                width: 100%;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
            }
            
            .waiting-icon {
                font-size: 5rem;
                color: #ffc107;
                margin-bottom: 20px;
                animation: bounce 2s infinite;
            }
            
            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-20px); }
            }
            
            h1 {
                color: #333;
                font-size: 1.8rem;
                margin-bottom: 15px;
            }
            
            p {
                color: #666;
                font-size: 1.1rem;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            
            .table-info {
                background: #e7f3ff;
                border-left: 4px solid #2196F3;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 30px;
                text-align: left;
            }
            
            .table-info strong {
                color: #0c5460;
                font-size: 1.2rem;
            }
            
            .spinner {
                border: 4px solid #f3f3f3;
                border-top: 4px solid #a40301;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: spin 1s linear infinite;
                margin: 20px auto;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .auto-refresh {
                color: #999;
                font-size: 0.9rem;
                margin-top: 20px;
            }
            
            @media (max-width: 600px) {
                .waiting-container {
                    padding: 30px 20px;
                }
                
                h1 {
                    font-size: 1.5rem;
                }
                
                .waiting-icon {
                    font-size: 4rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="waiting-container">
            <div class="waiting-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            
            <h1>Table non ouverte</h1>
            
            <div class="table-info">
                <i class="fas fa-table"></i>
                <strong><?= htmlspecialchars($table_name) ?></strong>
            </div>
            
            <p>Cette table n'a pas encore été ouverte par le personnel.</p>
            
            <p><strong>Veuillez patienter ou appeler un serveur.</strong></p>
            
            <div class="spinner"></div>
            
            <div class="auto-refresh">
                <i class="fas fa-sync-alt"></i>
                Actualisation automatique dans quelques secondes...
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ================================
// SESSION ACTIVE TROUVÉE
// ================================

// Stocker les informations dans la session PHP
$_SESSION['session_token'] = $active_session['session_token'];
$_SESSION['table_id'] = $table_id;
$_SESSION['table_name'] = $table_name;

// Générer un user_id si nécessaire
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = uniqid('user_', true);
}

// Rediriger vers le menu
header("Location: index.php");
exit;
?>
