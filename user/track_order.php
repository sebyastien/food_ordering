<?php
session_start();

// Configuration et validation
$order_type = $_SESSION['order_type'] ?? 'table';
$table_id = $_SESSION['table_id'] ?? 0;
$menu_link = ($order_type === 'takeaway') ? 'takeaway.php' : 'index.php?table_id=' . $table_id;

// Validation du numéro de commande
if (!isset($_GET['order_number']) || empty($_GET['order_number'])) {
    die("Erreur : Numéro de commande invalide.");
}

$order_number = htmlspecialchars($_GET['order_number'], ENT_QUOTES, 'UTF-8');

// Inclusion des fichiers nécessaires
<<<<<<< HEAD
=======
include "header.php";
>>>>>>> 4470edb (maj)
include "../admin/connection.php";

// Configuration de MySQLi pour le rapport d'erreurs
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Vérification de la connexion à la base de données
if (!$link) {
    die("Erreur : Impossible de se connecter à la base de données.");
}
<<<<<<< HEAD

// Définir le titre de la page pour le header
$page_title = "Suivi de ma commande";

// Inclure le header APRÈS avoir défini les variables nécessaires
include "header.php";
?>

<!-- Styles spécifiques à cette page -->
<style>
    .tracking-card {
        max-width: 800px;
        margin: 2rem auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
    }
    
    .progress-tracker {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 2rem 0;
        position: relative;
        padding: 0 1rem;
        width: calc(100% - 2rem); 
    }
    
    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        flex: 1;
        position: relative;
        z-index: 2;
    }
    
    .step-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #e9ecef, #dee2e6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #6c757d;
        margin-bottom: 0.75rem;
        transition: all 0.4s ease;
        border: 3px solid #e9ecef;
    }
    
    .progress-step.active .step-icon {
        background: linear-gradient(135deg, #a40301, #8b0200);
        color: white;
        border-color: #a40301;
        transform: scale(1.1);
    }
    
    .step-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
    }
    
    .progress-step.active .step-label {
        color: #a40301;
    }
    
    .progress-line {
        position: absolute;
        height: 4px;
        background-color: #e9ecef;
        top: 30px;
        left: 0;
        right: 0;
        z-index: 1;
        border-radius: 2px;
    }
    
    .progress-line-active {
        position: absolute;
        height: 4px;
        background: linear-gradient(90deg, #a40301, #8b0200);
        top: 30px;
        left: 0;
        width: 0;
        transition: width 0.6s ease-in-out;
        border-radius: 2px;
        z-index: 1;
    }
    
    .status-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 10px;
        padding: 1.5rem;
        margin: 1.5rem 0;
        border-left: 5px solid #a40301;
    }
    
    .order-number {
        font-family: 'Courier New', monospace;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: bold;
    }
    
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #a40301;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .btn-return {
        background: linear-gradient(135deg, #a40301, #8b0200);
        border: none;
        border-radius: 8px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        color: white;
    }
    
    .btn-return:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(164, 3, 1, 0.3);
        color: white;
    }
    
    @media (max-width: 768px) {
        .progress-tracker {
            padding: 0 0.5rem;
        }
        
        .step-icon {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }
        
        .progress-line,
        .progress-line-active {
            top: 25px;
        }
    }
</style>

<!-- Contenu principal -->
<div class="container py-5">
    <div class="tracking-card card border-0">
        <div class="card-header text-white text-center py-3" style="background: linear-gradient(135deg, #a40301, #8b0200);">
=======
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi de ma commande</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .tracking-card {
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }
        
        .progress-tracker {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 2rem 0;
    position: relative;
    padding: 0 1rem;
    /* Add this to create a relative container for the bars */
    width: calc(100% - 2rem); 
}
        
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex: 1;
            position: relative;
            z-index: 2;
        }
        
        .step-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #e9ecef, #dee2e6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #6c757d;
            margin-bottom: 0.75rem;
            transition: all 0.4s ease;
            border: 3px solid #e9ecef;
        }
        
        .progress-step.active .step-icon {
            background: linear-gradient(135deg, #a40301, #8b0200);
            color: white;
            border-color: #a40301;
            transform: scale(1.1);
        }
        
        .step-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .progress-step.active .step-label {
            color: #a40301;
        }
        
        .progress-line {
    position: absolute;
    height: 4px;
    background-color: #e9ecef;
    top: 30px;
    /* Change left and right to be relative to the container */
    left: 0;
    right: 0;
    z-index: 1;
    border-radius: 2px;
}
        
        .progress-line-active {
    position: absolute;
    height: 4px;
    background: linear-gradient(90deg, #a40301, #8b0200);
    top: 30px;
    /* Change left and right to be relative to the container */
    left: 0;
    width: 0; /* Keep this as it's set by JS */
    transition: width 0.6s ease-in-out;
    border-radius: 2px;
    z-index: 1;
}
        
        .status-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-left: 5px solid #a40301;
        }
        
        .order-number {
            font-family: 'Courier New', monospace;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #a40301;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-return {
            background: linear-gradient(135deg, #a40301, #8b0200);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-return:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(164, 3, 1, 0.3);
        }
        
        @media (max-width: 768px) {
            .progress-tracker {
                padding: 0 0.5rem;
            }
            
            .step-icon {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .progress-line,
    .progress-line-active {
        top: 25px;
        /* Now that left and right are 0, this media query is no longer necessary for alignment */
    }
}
    </style>
</head>
<body>

<div class="container py-5">
    <div class="tracking-card card border-0">
        <div class="card-header bg-primary text-white text-center py-3" style="background-color:#a40301!important;">
>>>>>>> 4470edb (maj)
            <h4 class="mb-0">
                <i class="fas fa-clipboard-list me-2"></i>
                Suivi de votre commande
            </h4>
<<<<<<< HEAD
            <p class="mb-0 mt-2">Commande n° <span class="order-number"><?= $order_number ?></span></p>
=======
            <p class="mb-0 mt-2" style="color: white;">Commande n° <span class="order-number"><?= $order_number ?></span></p>
>>>>>>> 4470edb (maj)
        </div>
        
        <div class="card-body p-4">
            <div class="progress-tracker">
                <div class="progress-line"></div>
                <div id="progress-line-fill" class="progress-line-active"></div>
                
                <div id="step-preparation" class="progress-step">
                    <div class="step-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="step-label">En préparation</div>
                </div>
                
                <div id="step-completed" class="progress-step">
                    <div class="step-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="step-label">Terminée</div>
                </div>
            </div>
            
            <div class="status-card text-center">
                <h5 class="mb-3">
<<<<<<< HEAD
                    <i class="fas fa-info-circle me-2" style="color:#a40301;"></i>
=======
                    <i class="fas fa-info-circle text-primary me-2" style="color:#a40301!important;"></i>
>>>>>>> 4470edb (maj)
                    Statut actuel
                </h5>
                <p class="mb-2">
                    <strong id="current-status">
                        <span class="loading-spinner"></span>
                        Chargement...
                    </strong>
                </p>
                <p class="text-muted mb-0" id="status-message">
                    Récupération des informations de votre commande...
                </p>
            </div>
            
            <div class="text-center mt-4">
<<<<<<< HEAD
                <a href="<?= htmlspecialchars($menu_link) ?>" class="btn btn-return">
=======
                <a href="<?= htmlspecialchars($menu_link) ?>" class="btn btn-return btn-success">
>>>>>>> 4470edb (maj)
                    <i class="fas fa-arrow-left me-2"></i>
                    Retourner au menu
                </a>
            </div>
        </div>
    </div>
</div>

<<<<<<< HEAD
<!-- Script de suivi de commande -->
<script>
class OrderTracker {
    constructor() {
        this.orderNumber = document.querySelector('.order-number')?.textContent;
=======
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
class OrderTracker {
    constructor() {
        this.orderNumber = document.getElementById('order-id')?.textContent || 
                           document.querySelector('.order-number')?.textContent;
>>>>>>> 4470edb (maj)
        this.currentStatusElement = document.getElementById('current-status');
        this.statusMessageElement = document.getElementById('status-message');
        this.progressLineFill = document.getElementById('progress-line-fill');
        this.refreshInterval = null;
        
        this.init();
    }
    
    init() {
        if (!this.orderNumber) {
            this.showError("Numéro de commande introuvable");
            return;
        }
        
        this.fetchOrderStatus();
        this.startAutoRefresh();
    }
    
    updateProgressDisplay(status) {
        const statusMessages = {
            'En attente': {
                display: 'En préparation',
                message: 'Votre commande est en cours de préparation en cuisine.',
                progress: 30,
                activeSteps: ['step-preparation']
            },
            'En préparation': {
                display: 'En préparation',
                message: 'Nos chefs préparent votre commande avec soin.',
                progress: 50,
                activeSteps: ['step-preparation']
            },
            'Terminée': {
                display: 'Terminée',
                message: 'Votre commande est prête ! Bon appétit !',
                progress: 100,
                activeSteps: ['step-preparation', 'step-completed']
            }
        };
        
        const config = statusMessages[status] || {
            display: 'Statut inconnu',
            message: 'Impossible de déterminer le statut de votre commande.',
            progress: 0,
            activeSteps: []
        };
        
        // Réinitialiser tous les états
        this.resetAllSteps();
        
        // Appliquer le nouveau statut
        this.currentStatusElement.textContent = config.display;
        this.statusMessageElement.textContent = config.message;
        this.progressLineFill.style.width = `${config.progress}%`;
        
        // Activer les étapes appropriées
        config.activeSteps.forEach(stepId => {
            const stepElement = document.getElementById(stepId);
            if (stepElement) {
                stepElement.classList.add('active');
            }
        });
        
        // Arrêter le rafraîchissement si la commande est terminée
        if (status === 'Terminée') {
            this.stopAutoRefresh();
        }
    }
    
    resetAllSteps() {
        document.querySelectorAll('.progress-step').forEach(step => {
            step.classList.remove('active');
        });
        this.progressLineFill.style.width = '0%';
    }
    
    async fetchOrderStatus() {
        try {
            const response = await fetch(`fetch_order_status.php?order_number=${encodeURIComponent(this.orderNumber)}`);
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.status) {
                this.updateProgressDisplay(data.status);
            } else {
                this.showError("Statut de commande introuvable");
            }
            
        } catch (error) {
            console.error('Erreur lors du chargement du statut:', error);
            this.showError("Erreur de connexion");
        }
    }
    
    showError(message) {
        this.currentStatusElement.innerHTML = `<i class="fas fa-exclamation-triangle text-warning me-2"></i>${message}`;
        this.statusMessageElement.textContent = "Veuillez réessayer dans quelques instants.";
    }
    
    startAutoRefresh() {
        this.refreshInterval = setInterval(() => {
            this.fetchOrderStatus();
        }, 5000); // Rafraîchissement toutes les 5 secondes
    }
    
    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }
}

// Initialisation lors du chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
<<<<<<< HEAD
    window.orderTracker = new OrderTracker();
});

// Nettoyage lors de la fermeture de la page
window.addEventListener('beforeunload', function() {
    if (window.orderTracker && window.orderTracker.refreshInterval) {
        clearInterval(window.orderTracker.refreshInterval);
    }
=======
    new OrderTracker();
    
    // Nettoyage lors de la fermeture de la page
    window.addEventListener('beforeunload', function() {
        if (window.orderTracker && window.orderTracker.refreshInterval) {
            clearInterval(window.orderTracker.refreshInterval);
        }
    });
>>>>>>> 4470edb (maj)
});

// Gestion de la visibilité de la page (pause/reprise du rafraîchissement)
document.addEventListener('visibilitychange', function() {
    if (window.orderTracker) {
        if (document.hidden) {
            window.orderTracker.stopAutoRefresh();
        } else {
            window.orderTracker.startAutoRefresh();
            window.orderTracker.fetchOrderStatus(); // Rafraîchissement immédiat
        }
    }
});
</script>

<<<<<<< HEAD
<?php include "footer.php"; ?>
=======
<?php include "footer.php"; ?>
</body>
</html>
>>>>>>> 4470edb (maj)
