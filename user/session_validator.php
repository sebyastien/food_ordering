<?php
/**
 * Validation de session STRICTE
 * Vérifie TOUJOURS que la table est ouverte
 * Mais ne revalide pas trop souvent pour éviter les blocages
 */

// =====================================================
// CONNEXION À LA BASE DE DONNÉES
// =====================================================

if (!isset($link) || $link === null) {
    $connection_paths = [
        __DIR__ . "/../admin/connection.php",
        "../admin/connection.php",
        dirname(__FILE__) . "/../admin/connection.php"
    ];
    
    $connection_loaded = false;
    foreach ($connection_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $connection_loaded = true;
            break;
        }
    }
    
    if (!$connection_loaded) {
        die("ERREUR FATALE : Impossible de charger connection.php");
    }
}

// =====================================================
// GESTIONNAIRE DE SESSIONS
// =====================================================

if (!class_exists('TableSessionManager')) {
    $manager_paths = [
        __DIR__ . "/../admin/TableSessionManager.php",
        "../admin/TableSessionManager.php",
        dirname(__FILE__) . "/../admin/TableSessionManager.php"
    ];
    
    $manager_loaded = false;
    foreach ($manager_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $manager_loaded = true;
            break;
        }
    }
    
    if (!$manager_loaded) {
        die("ERREUR FATALE : Impossible de charger TableSessionManager.php");
    }
}

// =====================================================
// FONCTION DE VALIDATION STRICTE
// =====================================================

function validateTableSession() {
    global $link;
    
    // ==========================================
    // ÉTAPE 1 : VÉRIFICATION DES VARIABLES PHP
    // ==========================================
    if (!isset($_SESSION['session_token']) || !isset($_SESSION['table_id']) || !isset($_SESSION['user_id'])) {
        displaySessionError(
            "Aucune session active", 
            "Vous devez scanner le QR code de votre table pour accéder au menu."
        );
        exit;
    }
    
    $session_token = $_SESSION['session_token'];
    $table_id = $_SESSION['table_id'];
    
    // ==========================================
    // ÉTAPE 2 : DÉCIDER SI ON DOIT VALIDER
    // ==========================================
    $should_validate = false;
    $force_validation = false;
    
    // Toujours valider la PREMIÈRE fois
    if (!isset($_SESSION['session_last_validated'])) {
        $should_validate = true;
        $force_validation = true; // Validation stricte obligatoire
    } else {
        // Valider toutes les 2 minutes (mais sans bloquer)
        $time_since_validation = time() - $_SESSION['session_last_validated'];
        if ($time_since_validation > 120) { // 2 minutes
            $should_validate = true;
            $force_validation = false; // Validation souple
        }
    }
    
    // ==========================================
    // ÉTAPE 3 : VALIDATION EN BDD
    // ==========================================
    if ($should_validate) {
        try {
            if (!isset($link) || $link === null) {
                // Pas de connexion - on log l'erreur
                error_log("Session validation failed: no database connection");
                
                // Si c'est la première validation, on BLOQUE
                if ($force_validation) {
                    displaySessionError(
                        "Erreur de connexion", 
                        "Impossible de vérifier votre session. Veuillez réessayer."
                    );
                    exit;
                }
            } else {
                $sessionManager = new TableSessionManager($link);
                
                // VALIDATION STRICTE
                $validation = $sessionManager->validateToken($session_token);
                
                if ($validation['valid']) {
                    // ✅ Session valide
                    $_SESSION['table_id'] = $validation['table_id'];
                    $_SESSION['table_name'] = $validation['table_name'];
                    
                    if (isset($validation['minutes_remaining'])) {
                        $_SESSION['minutes_remaining'] = $validation['minutes_remaining'];
                    }
                    
                    // Marquer la session comme validée
                    $_SESSION['session_last_validated'] = time();
                    
                } else {
                    // ❌ Session invalide
                    
                    // Si c'est une validation FORCÉE (première fois), on BLOQUE
                    if ($force_validation) {
                        displaySessionError(
                            "Table fermée", 
                            "Cette table n'est plus ouverte. Veuillez demander au serveur de l'ouvrir et rescanner le QR code."
                        );
                        exit;
                    }
                    
                    // Sinon, on vérifie si ça fait plus de 5 minutes
                    if (isset($_SESSION['session_last_validated'])) {
                        $time_since_last_valid = time() - $_SESSION['session_last_validated'];
                        
                        // Si ça fait plus de 5 minutes qu'on n'a pas réussi à valider, on BLOQUE
                        if ($time_since_last_valid > 300) { // 5 minutes
                            displaySessionError(
                                "Session expirée", 
                                "Votre session a expiré ou la table a été fermée. Veuillez rescanner le QR code."
                            );
                            exit;
                        }
                    }
                    
                    // Sinon, on continue mais on log l'erreur
                    error_log("Session validation failed but allowing continuation: " . ($validation['error'] ?? 'unknown'));
                }
            }
        } catch (Exception $e) {
            error_log("Session validation exception: " . $e->getMessage());
            
            // Si c'est la première validation, on BLOQUE
            if ($force_validation) {
                displaySessionError(
                    "Erreur de validation", 
                    "Une erreur s'est produite lors de la vérification de votre session. Veuillez rescanner le QR code."
                );
                exit;
            }
        }
    }
    
    // ==========================================
    // ÉTAPE 4 : S'ASSURER QUE LES INFOS EXISTENT
    // ==========================================
    if (!isset($_SESSION['table_name'])) {
        $_SESSION['table_name'] = 'Table ' . $_SESSION['table_id'];
    }
    
    return true;
}

// =====================================================
// AFFICHAGE DE L'ERREUR DE SESSION
// =====================================================

function displaySessionError($title, $message) {
    // Détruire la session invalide
    session_destroy();
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accès Refusé</title>
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
            
            .error-container {
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 500px;
                width: 100%;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
            }
            
            .error-icon {
                font-size: 5rem;
                color: #a40301;
                margin-bottom: 20px;
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
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
            
            .error-message {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 30px;
                text-align: left;
            }
            
            .error-message strong {
                color: #856404;
                display: block;
                margin-bottom: 5px;
            }
            
            .error-message span {
                color: #856404;
                font-size: 0.95rem;
            }
            
            .info-box {
                background: #e7f3ff;
                border-left: 4px solid #2196F3;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 30px;
                text-align: left;
            }
            
            .info-box i {
                color: #2196F3;
                margin-right: 10px;
            }
            
            .info-box p {
                color: #0c5460;
                font-size: 0.95rem;
                margin: 0;
            }
            
            .btn {
                display: inline-block;
                padding: 15px 30px;
                background: #a40301;
                color: white;
                text-decoration: none;
                border-radius: 10px;
                font-weight: 600;
                font-size: 1.1rem;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(164,3,1,0.3);
            }
            
            .btn:hover {
                background: #7a0301;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(164,3,1,0.4);
            }
            
            .btn i {
                margin-right: 8px;
            }
            
            @media (max-width: 600px) {
                .error-container {
                    padding: 30px 20px;
                }
                
                h1 {
                    font-size: 1.5rem;
                }
                
                p {
                    font-size: 1rem;
                }
                
                .error-icon {
                    font-size: 4rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-lock"></i>
            </div>
            
            <h1><?= htmlspecialchars($title) ?></h1>
            
            <div class="error-message">
                <strong>Raison :</strong>
                <span><?= htmlspecialchars($message) ?></span>
            </div>
            
            <div class="info-box">
                <i class="fas fa-qrcode"></i>
                <p><strong>Pour accéder au menu :</strong><br>
                1. Demandez au serveur d'ouvrir votre table<br>
                2. Scannez le QR code présent sur votre table<br>
                3. Vous pourrez alors commander</p>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// =====================================================
// EXÉCUTER LA VALIDATION AUTOMATIQUEMENT
// =====================================================

validateTableSession();
?>