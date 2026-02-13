<?php
session_start();

// ================================
// ENCODAGE UTF-8
// ================================
header('Content-Type: text/html; charset=UTF-8');

// ================================
// SÉCURITÉ : Validation de session
// ================================
// SEULEMENT pour l'affichage du formulaire (GET)
// PAS pour la confirmation (POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    require_once "session_validator.php";
}

<<<<<<< Updated upstream
// On récupère la valeur en session. Si elle n'existe pas, on redirige.
$table_id = isset($_SESSION['table_id']) ? intval($_SESSION['table_id']) : 0;
// 🔑 Ajout de la récupération de l'ID de l'utilisateur
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
=======
// La session est validée - récupérer les informations
$table_id = $_SESSION['table_id'];
$table_name = $_SESSION['table_name'];
$user_id = $_SESSION['user_id'] ?? null;
$session_token = $_SESSION['session_token'] ?? null;
>>>>>>> Stashed changes

// Vérifier que l'utilisateur est identifié
if (!$user_id) {
    header("Location: index.php");
    exit("Erreur : Utilisateur non identifié.");
}

include "header.php";
include "../admin/connection.php";
require_once "../admin/TableSessionManager.php";

<<<<<<< Updated upstream
// 🔑 On accède au panier spécifique à l'utilisateur actuel.
=======
$sessionManager = new TableSessionManager($link);

// Accès au panier spécifique à l'utilisateur actuel
>>>>>>> Stashed changes
$cart_for_this_user = isset($_SESSION['carts_by_table'][$table_id][$user_id]) ? $_SESSION['carts_by_table'][$table_id][$user_id] : [];

// Vérifier si le panier de l'utilisateur est vide
if (count($cart_for_this_user) === 0) {
    echo "<section class='page-title' style='background-image: url(assets/images/background/11.jpg)'>
              <div class='auto-container'>
                  <h1>Checkout</h1>
              </div>
          </section>";
    echo "<section class='cart-section'>
              <div class='auto-container'>
                  <p>Votre panier est vide. <a href='index.php'>Retour au menu</a></p>
              </div>
          </section>";
    include "footer.php";
    exit;
}

$cart_total = 0;
<<<<<<< Updated upstream
// 🔑 Boucler sur le panier de l'utilisateur
=======
>>>>>>> Stashed changes
foreach ($cart_for_this_user as $item) {
    $qty = intval($item['qty_total']);
    $price = floatval($item['price']);
    $cart_total += $qty * $price;
}
$cart_total_formatted = number_format($cart_total, 2);

$error = "";
$client_name = "";
$payment_method = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $client_name = trim($_POST['client_name'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    if (empty($client_name) || empty($payment_method)) {
        $error = "Le nom du client et le mode de paiement sont obligatoires.";
    } else {
<<<<<<< Updated upstream
        // 🔑 Utilisation du panier de l'utilisateur pour la validation de la commande
=======
        // ================================
        // PAS DE VALIDATION ICI !
        // ================================
        // On fait confiance à la session qui a déjà été validée
        // lors de l'affichage du formulaire
        
        // Session valide - procéder à l'enregistrement de la commande
>>>>>>> Stashed changes
        $cart_items = $cart_for_this_user;
        $total_confirm = 0;
        foreach ($cart_items as $item) {
            $qty = intval($item['qty_total']);
            $price = floatval($item['price']);
            $total_confirm += $qty * $price;
        }

        // Génération d’un numéro de commande
        $order_number = strtoupper('CMD' . bin2hex(random_bytes(3)));

<<<<<<< Updated upstream
        // Insertion de la commande
        // 🔑 SÉCURITÉ : La connexion doit être ouverte ici, pas fermée avant.
        // 🔑 Ajout de la colonne user_id
        $stmt = $link->prepare("INSERT INTO orders (order_number, customer_name, payment_method, total_price, table_id, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdis", $order_number, $client_name, $payment_method, $total_confirm, $table_id, $user_id);
=======
        // Insertion de la commande avec session_token
        $stmt = $link->prepare("INSERT INTO orders (order_number, customer_name, payment_method, total_price, table_id, user_id, session_token) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdiss", $order_number, $client_name, $payment_method, $total_confirm, $table_id, $user_id, $session_token);
>>>>>>> Stashed changes
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Insertion des articles commandés
<<<<<<< Updated upstream
        // ⚠️ SÉCURITÉ : Assainissement du nom pour la base de données
        $stmt_item = $link->prepare("INSERT INTO order_items (order_id, food_name, quantity, price) VALUES (?, ?, ?, ?)");
=======
        $stmt_item = $link->prepare("INSERT INTO order_items (order_id, food_name, quantity, price, item_comment) VALUES (?, ?, ?, ?, ?)");
        
>>>>>>> Stashed changes
        foreach ($cart_items as $item) {
            $name = htmlspecialchars($item['nm']); // Assainissement
            $qty = intval($item['qty_total']);
            $price = floatval($item['price']);
<<<<<<< Updated upstream
            $stmt_item->bind_param("isid", $order_id, $name, $qty, $price);
=======
            $comment = isset($item['comment']) ? htmlspecialchars(trim($item['comment'])) : ''; 
            
            $stmt_item->bind_param("isids", $order_id, $name, $qty, $price, $comment);
>>>>>>> Stashed changes
            $stmt_item->execute();
        }
        $stmt_item->close();

<<<<<<< Updated upstream
        // 🔑 Vider le panier spécifique à l'utilisateur après la commande
=======
        // Incrémenter le compteur de commandes de la session
        if ($session_token) {
            try {
                $sessionManager->incrementOrderCount($session_token);
            } catch (Exception $e) {
                // Erreur d'incrémentation mais on continue quand même
                error_log("Failed to increment order count: " . $e->getMessage());
            }
        }

        // Vider le panier spécifique à l'utilisateur après la commande
>>>>>>> Stashed changes
        unset($_SESSION["carts_by_table"][$table_id][$user_id]);

        // Affichage confirmation
        echo "<section class='page-title' style='background-image: url(assets/images/background/11.jpg)'>
                  <div class='auto-container'>
                      <h1>Commande confirmée</h1>
                  </div>
              </section>";
        echo "<section class='cart-section' style='padding: 40px 15px;'>
                  <div class='auto-container' style='max-width:700px; margin:auto; background:#fff; padding:25px; box-shadow:0 0 15px rgba(0,0,0,0.1); border-radius:10px;'>
                      <h2 style='color:#a40301; margin-bottom:20px;'>Merci, votre commande est enregistrée</h2>
                      <p><strong>Numéro de commande :</strong> <span style='color:#333;'>$order_number</span></p>
                      <p><strong>Table :</strong> <span style='color:#333;'>" . htmlspecialchars($table_name) . "</span></p>
                      <p><strong>Au nom de :</strong> <span style='color:#333;'>" . htmlspecialchars($client_name) . "</span></p>
                      <p><strong>Mode de paiement :</strong> <span style='color:#333;'>" . htmlspecialchars($payment_method) . "</span></p>
                      <hr style='margin:25px 0;'>
                      <h3>Détail de la commande :</h3>";

<<<<<<< Updated upstream
        echo "<table style='width:100%; border-collapse: collapse; font-size:1rem;'>
                  <thead>
                      <tr style='background:#a40301; color:#fff;'>
                          <th style='padding:10px; text-align:left;'>Produit</th>
                          <th style='padding:10px; text-align:center;'>Quantité</th>
                          <th style='padding:10px; text-align:right;'>Prix Unitaire (€)</th>
                          <th style='padding:10px; text-align:right;'>Total Ligne (€)</th>
                      </tr>
                  </thead>
                  <tbody>";

=======
        // Début de l'affichage adapté au mobile avec STYLISATION
        echo "<div style='margin-bottom: 20px;'>";
>>>>>>> Stashed changes
        $total_confirm = 0;
        foreach ($cart_items as $item) {
            $name = htmlspecialchars($item['nm']);
            $qty = intval($item['qty_total']);
            $price = floatval($item['price']);
            $total_line = $qty * $price;
            $total_confirm += $total_line;

<<<<<<< Updated upstream
            echo "<tr>
                      <td style='padding:8px; border-bottom:1px solid #ddd;'>$name</td>
                      <td style='padding:8px; border-bottom:1px solid #ddd; text-align:center;'>$qty</td>
                      <td style='padding:8px; border-bottom:1px solid #ddd; text-align:right;'>" . number_format($price, 2) . "</td>
                      <td style='padding:8px; border-bottom:1px solid #ddd; text-align:right;'>" . number_format($total_line, 2) . "</td>
                  </tr>";
=======
            echo "<div style='border: 1px solid #e0e0e0; padding: 15px; margin-bottom: 12px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05); background: #fafafa;'>
                      <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;'>
                          <span style='font-weight: 700; color: #a40301; font-size: 1.1rem;'>" . $name . "</span>
                          <span style='font-weight: 700; color: #333; font-size: 1.1rem;'>" . number_format($total_line, 2) . " €</span>
                      </div>
                      <div style='display: flex; justify-content: space-between; font-size: 0.9rem; color: #555;'>
                          <span>Qté: <span style='font-weight: 600;'>$qty</span></span>
                          <span>Prix U.: " . number_format($price, 2) . " €</span>
                      </div>";
            if (!empty($comment)) {
                echo "<div style='font-size: 0.85rem; color: #007bff; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e0e0e0;'>
                          Instructions : <span style='color: #007bff; font-style: italic;'>" . $comment . "</span>
                      </div>";
            }
            echo "</div>";
>>>>>>> Stashed changes
        }

<<<<<<< Updated upstream
        echo "</tbody>
                  <tfoot>
                  <tr>
                      <td colspan='3' style='padding:10px; font-weight:700; text-align:right; border-top:2px solid #a40301;'>Montant Total :</td>
                      <td style='padding:10px; font-weight:700; text-align:right; border-top:2px solid #a40301; color:#a40301;'>" . number_format($total_confirm, 2) . " €</td>
                  </tr>
                  </tfoot>
              </table>";
=======
        // Affichage du total stylisé
        echo "<div style='text-align: right; padding: 15px; border-top: 3px solid #a40301; background: #fff8f8; border-radius: 0 0 10px 10px; margin-top: 10px;'>
                  <span style='font-weight: 700; font-size: 1.3rem; color: #333;'>Montant Total : </span>
                  <span style='font-weight: 700; font-size: 1.3rem; color: #a40301;'>" . number_format($total_confirm, 2) . " €</span>
              </div>";
>>>>>>> Stashed changes

        // Liens d'actions
        echo "<div style='text-align: center; margin-top: 35px;'>
<<<<<<< Updated upstream
                  <p>Gardez ce numéro pour suivre votre commande à tout moment : <strong>" . htmlspecialchars($order_number) . "</strong></p>
=======
                  <p>Gardez ce numéro pour suivre votre commande : <strong>" . htmlspecialchars($order_number) . "</strong></p>
                  <p id='download-status' style='color: #28a745; font-weight: 600; margin-bottom: 10px;'>Téléchargement de votre facture en cours...</p>
>>>>>>> Stashed changes
                  <a href='facture.php?order_number=$order_number' style='display:block; background:#007bff; color:#fff; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:700; margin-bottom:10px;'>
                      Télécharger la facture (PDF)
                  </a>
                  <a href='track_order.php?order_number=$order_number' style='display:block; background:#6c757d; color:#fff; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:700; margin-bottom:10px;'>
                      Suivre ma commande
                  </a>
                  <a href='index.php' style='display:block; background:#a40301; color:#fff; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:700;'>
                      Continuer à commander
                  </a>
              </div>
          </div>
          </section>";

<<<<<<< Updated upstream
=======
        // Script pour télécharger automatiquement la facture
        echo "<script>
                window.onload = function() {
                    var iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = 'facture.php?order_number=$order_number';
                    document.body.appendChild(iframe);
                    
                    setTimeout(function() {
                        var statusElement = document.getElementById('download-status');
                        if (statusElement) {
                            statusElement.textContent = 'Facture téléchargée avec succès !';
                        }
                    }, 2000);
                };
              </script>";

>>>>>>> Stashed changes
        include "footer.php";
        exit;
    }
}
?>

<section class="page-title" style="background-image: url(assets/images/background/11.jpg);">
    <div class="auto-container">
        <h1>Checkout</h1>
    </div>
</section>

<section class="checkout-section">
    <div class="auto-container" style="max-width: 700px; margin:auto; background:#fff; padding:25px; box-shadow:0 0 15px rgba(0,0,0,0.1); border-radius:10px;">
        <h2 style="color:#a40301; margin-bottom: 20px;">Détails de la commande</h2>
        
        <!-- Afficher la table -->
        <div style="background: #f0f8ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #667eea;">
            <strong><i class="fas fa-table"></i> Table :</strong> <?= htmlspecialchars($table_name) ?>
        </div>

        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #842029; padding: 12px 20px; margin-bottom: 20px; border-radius: 6px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="checkout.php">
            <div style="margin-bottom: 15px;">
                <label for="client_name" style="display:block; font-weight:600; margin-bottom:6px;">Nom du client :</label>
                <input type="text" id="client_name" name="client_name" value="<?= htmlspecialchars($client_name) ?>" required
                       style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:1rem;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="payment_method" style="display:block; font-weight:600; margin-bottom:6px;">Mode de paiement :</label>
                <select id="payment_method" name="payment_method" required
                        style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:1rem;">
                    <option value="" disabled <?= $payment_method == "" ? "selected" : "" ?>>-- Sélectionnez --</option>
                    <option value="Espèces" <?= $payment_method == "Espèces" ? "selected" : "" ?>>Espèces</option>
                    <option value="Carte bancaire" <?= $payment_method == "Carte bancaire" ? "selected" : "" ?>>Carte bancaire</option>
                    <option value="PayPal" <?= $payment_method == "PayPal" ? "selected" : "" ?>>PayPal</option>
                </select>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" 
                        style="background:#007bff; color:#fff; padding: 12px 30px; border:none; border-radius:8px; font-size:1.1rem; font-weight:700; cursor:pointer; transition: background-color 0.3s ease;">
                    Valider la commande
                </button>
            </div>
        </form>

        <hr style="margin: 40px 0;">

        <h3>Résumé de la commande :</h3>
        <ul style="list-style:none; padding-left:0; font-size:1rem;">
            <?php foreach ($cart_for_this_user as $item): ?>
                <li style="padding: 8px 0; border-bottom:1px solid #ddd;">
                    <?= htmlspecialchars($item['nm']) ?> — Quantité : <?= intval($item['qty_total']) ?> — Prix unitaire : <?= number_format(floatval($item['price']), 2) ?> €
<<<<<<< Updated upstream
=======
                    <?php if (!empty($comment)): ?>
                        <span style="display: block; font-size: 0.9rem; color: #a40301; margin-top: 3px;">
                             Instructions : <?= $comment ?>
                        </span>
                    <?php endif; ?>
>>>>>>> Stashed changes
                </li>
            <?php endforeach; ?>
        </ul>

        <p style="text-align:right; font-weight:700; font-size:1.2rem; margin-top: 15px;">
            Total : <span style="color:#a40301;"><?= $cart_total_formatted ?> €</span>
        </p>
    </div>
</section>

<?php include "footer.php"; ?>