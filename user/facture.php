<?php
// user/facture.php

session_start();
// Inclusion de l'autoloader de Composer, en remontant d'un dossier
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Inclusion du fichier de connexion à la base de données, en remontant d'un dossier
include '../admin/connection.php';

// Récupérer le numéro de commande de l'URL
$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : null;

if (!$order_number) {
    exit('Erreur : Numéro de commande manquant.');
}

// Récupérer les informations de la commande
$stmt = $link->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->bind_param("s", $order_number);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    exit('Erreur : Commande non trouvée.');
}

// Récupérer les articles de la commande
$stmt_items = $link->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt_items->bind_param("i", $order['id']);
$stmt_items->execute();
$order_items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

// Début de la construction du HTML pour la facture
$html = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            .invoice-box {
                max-width: 800px;
                margin: auto;
                padding: 30px;
                border: 1px solid #eee;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
                font-size: 16px;
                line-height: 24px;
                color: #555;
            }
            .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
            .invoice-box table td { padding: 5px; vertical-align: top; }
            .invoice-box table tr td:nth-child(2) { text-align: right; }
            .invoice-box table tr.top table td { padding-bottom: 20px; }
            .invoice-box table tr.information table td { padding-bottom: 40px; }
            .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
            .invoice-box table tr.details td { padding-bottom: 20px; }
            .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
            .invoice-box table tr.item.last td { border-bottom: none; }
            .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; }
        </style>
    </head>
    <body>
    <div class="invoice-box">
        <table>
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <h1>Facture</h1>
                            </td>
                            <td>
                                Numéro de facture : ' . htmlspecialchars($order['order_number']) . '<br>
                                Date : ' . date('d/m/Y', strtotime($order['created_at'])) . '
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                Restaurant [Nom du restaurant]<br>
                                Adresse du restaurant<br>
                                Ville, Code Postal
                            </td>
                            <td>
                                Nom du client : ' . htmlspecialchars($order['customer_name']) . '<br>
                                Mode de paiement : ' . htmlspecialchars($order['payment_method']) . '
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td>Article</td>
                <td>Prix</td>
            </tr>';

foreach ($order_items as $item) {
    $html .= '<tr class="item">
                <td>' . htmlspecialchars($item['food_name']) . ' (x' . intval($item['quantity']) . ')</td>
                <td>' . number_format(floatval($item['price']), 2) . ' €</td>
              </tr>';
}

$html .= '<tr class="total">
                <td></td>
                <td>Total : ' . number_format(floatval($order['total_price']), 2) . ' €</td>
            </tr>
        </table>
    </div>
    </body>
    </html>';

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$file_name = 'Facture_' . $order_number . '.pdf';
$dompdf->stream($file_name, ["Attachment" => true]);
exit;