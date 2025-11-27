<?php
// qr_generator_page.php

session_start();

// --- LOGIQUE DE GÉNÉRATION CÔTÉ SERVEUR ---
// Ce bloc s'exécute lorsque l'IMG tag fait un appel secondaire au serveur.
if (isset($_GET['action']) && $_GET['action'] === 'generate' && isset($_GET['data'])) {
    
    // Démarrer la mise en tampon de sortie
    ob_start(); 
    
    $data = urldecode($_GET['data']);
    
    try {
        // Inclure la bibliothèque phpqrcode (plus simple, sans Composer)
        // Téléchargez phpqrcode depuis : https://sourceforge.net/projects/phpqrcode/
        // Placez le dossier 'phpqrcode' dans le même répertoire que ce fichier
        
        if (!file_exists('phpqrcode/qrlib.php')) {
            throw new Exception("Bibliothèque phpqrcode non trouvée. Téléchargez-la et placez-la dans le dossier 'phpqrcode'.");
        }
        
        include 'phpqrcode/qrlib.php';
        
        // Nettoyer le buffer
        if (ob_get_level() > 0) {
            ob_end_clean(); 
        }
        
        // Générer le QR Code directement en sortie
        header('Content-Type: image/png');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // QRcode::png($data, false, QR_ECLEVEL_H, 10, 2);
        // Paramètres : données, fichier (false = sortie directe), niveau correction erreur, taille, marge
        QRcode::png($data, false, 'H', 10, 2);
        
    } catch (Exception $e) {
        // En cas d'erreur, nettoyage et envoi d'une image d'erreur
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        error_log("QR Code Generation Error: " . $e->getMessage());
        header('Content-Type: image/png');
        // Image de 1x1 pixel transparent
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
    }
    exit;
}
// ---------------------------------------------------


include "header.php";

$base_url = 'http://localhost/food_ordering/user'; // À REMPLACER PAR VOTRE URL

?>

<style>
    :root {
        --primary-color: #a41a13;
        --dark-color: #333;
        --light-color: #f7f7f7;
        --text-color: #555;
        --border-radius: 8px;
        --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--light-color);
        margin: 0;
        padding: 0;
    }
    
    .container {
        max-width: 900px;
        margin: 60px auto;
        padding: 40px;
        background-color: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        text-align: center;
    }
    
    .header-content {
        margin-bottom: 40px;
    }

    h2 {
        font-size: 2.5em;
        color: var(--dark-color);
        margin-bottom: 10px;
    }
    
    p.subtitle {
        color: var(--text-color);
        font-size: 1.1em;
    }
    
    .option-cards {
        display: flex;
        justify-content: center;
        gap: 30px;
        flex-wrap: wrap;
        margin-top: 40px;
    }
    
    .card {
        background-color: #fff;
        border: 1px solid #e0e0e0;
        border-radius: var(--border-radius);
        padding: 30px 25px;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        width: 350px;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    
    h3 {
        color: var(--primary-color);
        font-size: 1.8em;
        margin-bottom: 15px;
    }
    
    .card p {
        color: var(--text-color);
        line-height: 1.6;
    }
    
    .form-group {
        margin-top: 20px;
    }
    
    input[type="text"] {
        padding: 12px;
        font-size: 1em;
        border-radius: var(--border-radius);
        border: 1px solid #ddd;
        width: calc(100% - 24px);
        margin-bottom: 15px;
    }

    button {
        padding: 12px 30px;
        font-size: 1.1em;
        border-radius: var(--border-radius);
        border: none;
        background-color: var(--primary-color);
        color: white;
        cursor: pointer;
        transition: background-color 0.3s ease;
        text-transform: uppercase;
        font-weight: bold;
    }

    button:hover {
        background-color: #8c160f;
    }
    
    .qr-display-area {
        margin-top: 50px;
        padding-top: 30px;
        border-top: 1px solid #eee;
    }
    
    .qr-image {
        margin: 20px auto;
        border: 2px solid #ddd;
        padding: 15px;
        background-color: white;
        border-radius: var(--border-radius);
        display: inline-block;
    }
    
    .qr-url {
        font-size: 0.9em;
        color: #888;
        word-wrap: break-word;
    }
    
    .error-message {
        color: #dc3545;
        padding: 15px;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: var(--border-radius);
        margin-top: 20px;
    }
    
    .loading {
        color: #17a2b8;
        font-style: italic;
        margin-top: 20px;
    }
    
    .download-btn {
        display: inline-block;
        margin-top: 15px;
        padding: 10px 25px;
        background-color: #28a745;
        color: white;
        text-decoration: none;
        border-radius: var(--border-radius);
        font-weight: bold;
    }
    
    .download-btn:hover {
        background-color: #218838;
    }
</style>

<div class="container">
    <div class="header-content">
        <h2>Générer les QR codes</h2>
        <p class="subtitle">Créez et imprimez facilement des codes QR pour les tables et les commandes à emporter.</p>
    </div>

    <div class="option-cards">
        <div class="card">
            <h3>QR Code de table</h3>
            <p>Entrez le numéro de la table pour générer un code QR unique qui renverra au menu.</p>
            <div class="form-group">
                <input type="text" id="table_id_input" placeholder="Numéro de table (ex: 5)">
                <button onclick="generateQrCode('table')">Générer le code</button>
            </div>
        </div>
        
        <div class="card">
            <h3>QR Code à emporter</h3>
            <p>Générez un code QR générique pour les commandes à emporter, à placer sur votre comptoir.</p>
            <div class="form-group">
                <button onclick="generateQrCode('takeaway')">Générer le code</button>
            </div>
        </div>
    </div>
    
    <div id="qr-display-area" class="qr-display-area"></div>
</div>

<script>
    function generateQrCode(type) {
        const qrDisplayArea = document.getElementById('qr-display-area');
        let tableId = '';
        let url = '';
        
        const baseUrl = '<?php echo $base_url; ?>';

        if (type === 'table') {
            tableId = document.getElementById('table_id_input').value;
            if (tableId === '') {
                alert('Veuillez entrer un numéro de table.');
                return;
            }
            url = baseUrl + '/index.php?table_id=' + encodeURIComponent(tableId);
        } else if (type === 'takeaway') {
            url = baseUrl + '/takeaway.php';
        }
        
        // Afficher un message de chargement
        qrDisplayArea.innerHTML = '<p class="loading">⏳ Génération du QR Code en cours...</p>';
        
        // L'URL de l'image
        const qrImageUrl = 'qr_generator_page.php?action=generate&data=' + encodeURIComponent(url) + '&_t=' + new Date().getTime();
        
        // Créer une nouvelle image pour tester le chargement
        const testImg = new Image();
        
        testImg.onload = function() {
            // Si l'image se charge correctement, l'afficher
            const label = type === 'table' ? 'Table ' + tableId : 'À emporter';
            qrDisplayArea.innerHTML = `
                <h3>✅ QR Code généré pour : ${label}</h3>
                <div class="qr-image">
                    <img src="${qrImageUrl}" alt="QR Code" id="qr-code-img" />
                </div>
                <p class="qr-url">URL : <strong>${url}</strong></p>
                <a href="${qrImageUrl}" download="qrcode_${type}_${tableId || 'takeaway'}.png" class="download-btn">
                    📥 Télécharger le QR Code
                </a>
                <p style="margin-top: 15px;"><small>Ou faites un clic droit sur l'image pour la sauvegarder/imprimer.</small></p>
            `;
        };
        
        testImg.onerror = function() {
            // Si l'image ne se charge pas, afficher une erreur détaillée
            qrDisplayArea.innerHTML = `
                <div class="error-message">
                    <h4>❌ Erreur de génération du QR Code</h4>
                    <p><strong>La bibliothèque phpqrcode n'est pas installée.</strong></p>
                    <ol style="text-align: left; display: inline-block; margin-top: 15px;">
                        <li>Téléchargez phpqrcode : <a href="https://sourceforge.net/projects/phpqrcode/files/" target="_blank">SourceForge</a></li>
                        <li>Extrayez le fichier ZIP</li>
                        <li>Placez le dossier 'phpqrcode' dans : <code>C:\\wamp64\\www\\food_ordering\\admin\\</code></li>
                        <li>Vérifiez que le fichier existe : <code>C:\\wamp64\\www\\food_ordering\\admin\\phpqrcode\\qrlib.php</code></li>
                        <li>Rechargez cette page et réessayez</li>
                    </ol>
                    <p style="margin-top: 15px;"><small>URL de test : <code>${qrImageUrl}</code></small></p>
                </div>
            `;
            console.error('Erreur de chargement du QR Code:', qrImageUrl);
        };
        
        // Déclencher le chargement de l'image de test
        testImg.src = qrImageUrl;
    }
</script>

<?php
include "footer.php";
?>