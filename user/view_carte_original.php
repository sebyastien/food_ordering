<?php
session_start();

// Gérer l'ID de la table
if (isset($_GET['table_id'])) {
    $_SESSION['table_id'] = intval($_GET['table_id']);
}
// 🛠️ CORRECTION PHP : Utilisation de l'opérateur de coalescence nulle (??) pour éviter l'erreur "Undefined array key"
$table_id = intval($_SESSION['table_id'] ?? 0);

// 🔒 Gérer l'ID de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = uniqid('user_', true);
}
$user_id = $_SESSION['user_id'];

include "header.php";
include "../admin/connection.php";
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet">

<style>
/* Style pour les cartes de produits */
.product-card {
    border: 1px solid #e0e0e0;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    background: #fafafa;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.product-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e0e0;
}

.product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
    flex-shrink: 0;
}

.product-info {
    flex: 1;
}

.product-name {
    font-weight: 700;
    color: #a40301;
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.product-price {
    font-size: 0.95rem;
    color: #555;
    font-weight: 600;
}

.product-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    justify-content: space-between;
}

.quantity-label {
    font-size: 0.9rem;
    color: #555;
    font-weight: 600;
    flex-shrink: 0;
}

/* NOUVEAU: Conteneur Flexbox pour le spinner personnalisé */
.custom-spinner {
    display: flex;
    align-items: center;
    border: 1px solid #ccc;
    border-radius: 6px;
    overflow: hidden; /* Empêche les débordements */
    width: 110px; /* Largeur fixe pour la cohérence */
    flex-shrink: 0; /* Empêche de rétrécir sur mobile */
}

/* NOUVEAU: Style des boutons + / - */
.qty-btn {
    background: #f8f8f8;
    color: #333;
    border: none;
    padding: 8px 10px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: bold;
    line-height: 1;
    transition: background-color 0.2s;
    height: 38px; /* Hauteur cohérente */
}

.qty-btn:hover {
    background: #e0e0e0;
}

/* NOUVEAU: Style du champ de quantité */
.quantity-input {
    text-align: center;
    border: none;
    width: 100%; 
    padding: 8px 0;
    font-size: 14px;
    height: 38px;
    line-height: 1.2;
    /* Pour s'assurer qu'il prend l'espace restant */
    flex-grow: 1; 
    max-width: 40px; /* Limite la largeur du champ de saisie lui-même */
}

/* Désactiver les flèches internes du champ de type number */
.quantity-input::-webkit-inner-spin-button, 
.quantity-input::-webkit-outer-spin-button { 
    -webkit-appearance: none;
    margin: 0;
}
.quantity-input {
    -moz-appearance: textfield;
}

.product-total {
    display: flex;
    align-items: center;
    gap: 10px;
}

.total-label {
    font-size: 0.9rem;
    color: #555;
}

.total-price {
    font-weight: 700;
    font-size: 1.1rem;
    color: #333;
}

.remove-button {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
}

.remove-button:hover {
    background: #c82333;
}

.comment-display {
    font-size: 0.85rem;
    color: #007bff;
    padding: 8px;
    background: #f0f8ff;
    border-left: 3px solid #007bff;
    border-radius: 4px;
    font-style: italic;
}

.comment-label {
    font-weight: 600;
    color: #0056b3;
}

.empty-cart {
    text-align: center;
    padding: 40px 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

/* Totaux */
.totals-table {
    list-style: none;
    padding: 0;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}

.totals-table li {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
}

.totals-table li:last-child {
    border-bottom: none;
}

.totals-table h3 {
    margin: 0;
    color: #a40301;
    font-size: 1.3rem;
}

.totals-table .total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 700;
    font-size: 1.2rem;
    background: #fff8f8;
}

.totals-table .total .price {
    color: #a40301;
    font-size: 1.3rem;
}

/* Styles pour mobile (768px et moins) */
@media screen and (max-width: 768px) {
    .product-header {
        flex-direction: row;
    }
    
    .product-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .quantity-control {
        justify-content: space-between;
        width: 100%;
        margin-bottom: 10px;
    }

    .custom-spinner {
        width: 110px;
        flex-shrink: 0;
    }
    
    .product-total {
        justify-content: space-between;
        width: 100%;
    }
    
    .remove-button {
        width: 100%;
    }
}
</style>

<section class="page-title" style="background-image: url(assets/images/background/11.jpg)">
    <div class="auto-container">
        <h1>Votre Panier</h1>
    </div>
</section>

<section class="cart-section" style="padding: 40px 15px;">
    <div class="auto-container">
        <div class="cart-outer">
            <?php
            $cart_total = 0;
            $cart_for_this_user = isset($_SESSION['carts_by_table'][$table_id][$user_id]) ? $_SESSION['carts_by_table'][$table_id][$user_id] : [];

            if (count($cart_for_this_user) > 0) {
                $i = 0;
                foreach ($cart_for_this_user as $item) {
                    $qty = intval($item['qty_total']);
                    $price = floatval($item['price']);
                    $total = $qty * $price;
                    $cart_total += $total;
                    $comment = isset($item['comment']) ? $item['comment'] : '';
            ?>
                    <div class="product-card" data-tbid="<?= intval($item['tb_id']) ?>">
                        <div class="product-header">
                            <img src="../admin/<?= htmlspecialchars($item['img1']) ?>" alt="<?= htmlspecialchars($item['nm']) ?>" class="product-image">
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($item['nm']) ?></div>
                                <div class="product-price">Prix unitaire : <?= number_format($price, 2) ?> €</div>
                            </div>
                        </div>

                        <?php if (!empty($comment)): ?>
                            <div class="comment-display">
                                <span class="comment-label">📝 Instructions :</span> <?= htmlspecialchars($comment) ?>
                            </div>
                        <?php endif; ?>

                        <div class="product-controls">
                            <div class="quantity-control">
                                <span class="quantity-label">Quantité :</span>
                                <div class="custom-spinner">
                                    <button class="qty-btn qty-minus" type="button" data-action="minus" aria-label="Diminuer la quantité">-</button>
                                    <input class="quantity-input" id="qty<?= $i ?>" type="number" min="1" value="<?= $qty ?>" name="quantity" readonly>
                                    <button class="qty-btn qty-plus" type="button" data-action="plus" aria-label="Augmenter la quantité">+</button>
                                </div>
                            </div>

                            <div class="product-total">
                                <span class="total-label">Total :</span>
                                <span class="total-price"><?= number_format($total, 2) ?> €</span>
                            </div>

                            <button class="remove-button" onclick="delete_product('<?= intval($item['tb_id']) ?>')">
                                <i class="fa fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
            <?php
                    $i++;
                }
            } else {
                echo '<div class="empty-cart">
                        <i class="fa fa-shopping-cart" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                        <h3>Votre panier est vide</h3>
                        <p><a href="index.php" style="color: #a40301; font-weight: 600;">Retour au menu</a></p>
                      </div>';
            }
            ?>

            <?php if (count($cart_for_this_user) > 0): ?>
            <div class="row clearfix" style="margin-top: 30px;">
                <div class="column col-lg-7 col-md-12 col-sm-12"></div>

                <div class="column pull-right col-lg-5 col-md-12 col-sm-12">
                    <form method="POST" action="checkout.php">
                        <ul class="totals-table">
                            <li><h3>Total du Panier</h3></li>
                            <li class="clearfix total">
                                <span class="col">Total</span>
                                <span class="col price" id="cart_total"><?= number_format($cart_total, 2) ?> €</span>
                            </li>
                            
                            <li class="text-right" style="margin-top: 20px;">
                                <button type="submit" 
                                        style="background:#007bff; color:#fff; padding: 12px 30px; border:none; border-radius:8px; font-size:1.1rem; font-weight:700; cursor:pointer; transition: background-color 0.3s ease; width: 100%;">
                                    Procéder au paiement
                                </button>
                            </li>
                        </ul>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Fonction pour gérer le changement de quantité via les boutons +/-
function handleQtyChange(button) {
    const card = button.closest(".product-card");
    const input = card.querySelector(".quantity-input");
    let currentQty = parseInt(input.value);
    const action = button.getAttribute('data-action');
    
    if (action === 'minus') {
        currentQty = Math.max(1, currentQty - 1); // La quantité minimale est 1
    } else if (action === 'plus') {
        currentQty += 1;
    }
    
    input.value = currentQty;
    // Mise à jour de l'affichage du prix et envoi de la requête AJAX
    updateRowAndCart(input);
}

// Fonction existante pour la mise à jour des prix et l'appel AJAX
function updateRowAndCart(input) {
    const card = input.closest(".product-card");
    const priceText = card.querySelector(".product-price").textContent;
    // Extraction du prix unitaire
    const priceMatch = priceText.match(/[\d,]+\.?\d*/);
    const price = priceMatch ? parseFloat(priceMatch[0].replace(',', '.')) : 0;
    
    let qty = parseFloat(input.value);
    const totalCell = card.querySelector(".total-price");

    // Vérification de la quantité minimale
    if (qty < 1) {
        input.value = 1; 
        qty = 1;
    }

    if (!isNaN(price) && !isNaN(qty)) {
        const total = (price * qty).toFixed(2);
        totalCell.textContent = total + " €";
        updateCartTotal();

        const tb_id = card.getAttribute("data-tbid");
        // Mise à jour via AJAX
        update_product(tb_id, input.value);
    }
}

function updateCartTotal() {
    let total = 0;
    document.querySelectorAll(".product-card").forEach(card => {
        const totalCell = card.querySelector(".total-price");
        if (totalCell) {
            const amount = parseFloat(totalCell.textContent.replace("€", "").replace(',', '.').trim());
            if (!isNaN(amount)) total += amount;
        }
    });
    const cartTotalElement = document.getElementById("cart_total");
    if (cartTotalElement) {
        cartTotalElement.textContent = total.toFixed(2) + " €";
    }
}

document.addEventListener("DOMContentLoaded", function() {
    
    // Ajout des écouteurs d'événements pour les boutons +/-
    const qtyButtons = document.querySelectorAll(".qty-btn");
    qtyButtons.forEach(button => {
        button.addEventListener("click", function() {
            handleQtyChange(button);
        });
    });

    // Écouteur pour la saisie manuelle (si jamais l'input n'est plus readonly)
    const quantityInputs = document.querySelectorAll(".quantity-input");
    quantityInputs.forEach(input => {
        input.addEventListener("change", function() {
            updateRowAndCart(input);
        });
    });
});

function delete_product(tb_id) {
    if (confirm("Êtes-vous sûr de vouloir supprimer cet article ?")) {
        const xmlhttp1 = new XMLHttpRequest();
        xmlhttp1.onreadystatechange = function () {
            if (xmlhttp1.readyState == 4 && xmlhttp1.status == 200) {
                // Rechargement après suppression
                window.location = "view_carte.php"; 
            }
        };
        xmlhttp1.open("GET", "delete_from_cart.php?tb_id=" + tb_id, true);
        xmlhttp1.send();
    }
}

function update_product(tb_id, qty) {
    const xmlhttp1 = new XMLHttpRequest();
    xmlhttp1.onreadystatechange = function () {
        if (xmlhttp1.readyState == 4 && xmlhttp1.status == 200) {
            console.log(xmlhttp1.responseText);
        }
    };
    xmlhttp1.open("GET", "update_from_cart.php?id=" + tb_id + "&qty=" + qty, true);
    xmlhttp1.send();
}
</script>

<?php include "footer.php"; ?>

<script src="assets/js/appear.js"></script>
<script src="assets/js/script.js"></script>