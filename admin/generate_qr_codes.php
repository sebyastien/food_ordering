<?php
/**
 * Générateur automatique de QR Codes pour les tables
 * Génère des QR codes pour toutes les tables du restaurant
 */

// Configuration
$base_url = "https://votre-domaine.com/client/qr_entry.php"; // À MODIFIER
$output_dir = "qr_codes";
$size = 300; // Taille en pixels

// Créer le dossier de sortie
if (!is_dir($output_dir)) {
    mkdir($output_dir, 0755, true);
}

// Connexion à la base de données
include "../admin/connection.php";

// Récupérer toutes les tables
$query = "SELECT * FROM restaurant_tables WHERE is_active = 1 ORDER BY table_number ASC";
$result = mysqli_query($link, $query);

if (!$result) {
    die("Erreur lors de la récupération des tables : " . mysqli_error($link));
}

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Générateur de QR Codes</title>
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
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #a40301;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        
        .info-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .info-box strong {
            color: #856404;
        }
        
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .qr-card {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .qr-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.5rem;
        }
        
        .table-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .qr-image {
            max-width: 100%;
            height: auto;
            margin: 15px 0;
            border: 3px solid #a40301;
            border-radius: 10px;
        }
        
        .url-display {
            background: #e9ecef;
            padding: 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            word-break: break-all;
            margin: 10px 0;
            color: #495057;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .global-actions {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #dee2e6;
        }
        
        @media print {
            body {
                background: white;
            }
            
            .container {
                box-shadow: none;
            }
            
            .info-box, .global-actions, .actions {
                display: none;
            }
            
            .qr-card {
                page-break-inside: avoid;
                border: 2px solid #000;
            }
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔲 Générateur de QR Codes</h1>
        <p class='subtitle'>QR Codes sécurisés pour toutes vos tables</p>
        
        <div class='info-box'>
            <strong>⚠️ Important :</strong> Vérifiez que l'URL de base est correcte avant d'imprimer les QR codes.
            <br>URL actuelle : <code>$base_url</code>
            <br>Modifiez la variable <code>\$base_url</code> dans ce fichier si nécessaire.
        </div>
        
        <div class='qr-grid'>";

$tables = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tables[] = $row;
}

foreach ($tables as $table) {
    $table_id = $table['id'];
    $table_number = $table['table_number'];
    $table_name = $table['table_name'];
    $capacity = $table['capacity'];
    $qr_identifier = $table['qr_code_identifier'];
    
    // Construire l'URL complète
    $full_url = $base_url . "?qr=" . urlencode($qr_identifier);
    
    // Générer le QR code via API Google Chart (gratuit)
    $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($full_url);
    
    // Nom du fichier de sortie
    $filename = "table_" . $table_number . "_qr.png";
    $filepath = $output_dir . "/" . $filename;
    
    // Télécharger et sauvegarder le QR code
    $qr_image_data = file_get_contents($qr_api_url);
    file_put_contents($filepath, $qr_image_data);
    
    echo "<div class='qr-card'>
            <h3>{$table_name}</h3>
            <div class='table-info'>
                Capacité : {$capacity} personnes<br>
                Identifiant : {$qr_identifier}
            </div>
            <img src='{$filepath}' alt='QR Code {$table_name}' class='qr-image'>
            <div class='url-display'>{$full_url}</div>
            <div class='actions'>
                <a href='{$filepath}' download class='btn btn-primary'>Télécharger</a>
                <a href='{$full_url}' target='_blank' class='btn btn-success'>Tester</a>
            </div>
          </div>";
}

echo "</div>
        
        <div class='global-actions'>
            <button onclick='window.print()' class='btn btn-primary' style='font-size: 1.1rem; padding: 15px 40px;'>
                🖨️ Imprimer tous les QR Codes
            </button>
            <p style='margin-top: 15px; color: #666;'>
                " . count($tables) . " QR codes générés avec succès
            </p>
        </div>
    </div>
</body>
</html>";

mysqli_close($link);
?>
