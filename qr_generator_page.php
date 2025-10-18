<?php
// qr_generator_page.php

session_start();
include "header.php";

$base_url = 'http://localhost/food_ordering_system/user'; // À REMPLACER PAR VOTRE URL

?>

<style>
    :root {
        --primary-color: #a41a13; /* Rouge principal */
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
    
    <div id="qr-display-area" class="qr-display-area">
        </div>
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
        
        // Utilisation de l'API Google Charts pour la génération (simple, mais déprécié)
        const qrUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' + encodeURIComponent(url);
        
        qrDisplayArea.innerHTML = `
            <h3>QR Code généré :</h3>
            <div class="qr-image">
                <img src="${qrUrl}" alt="QR Code" />
            </div>
            <p class="qr-url">URL : <strong>${url}</strong></p>
            <p style="margin-top: 15px;"><small>Faites un clic droit sur l'image pour la sauvegarder ou l'imprimer.</small></p>
        `;
    }
</script>

<?php
include "footer.php";
?>