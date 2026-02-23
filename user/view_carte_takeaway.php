<?php
session_start();

<<<<<<< HEAD
// 🔒 Gérer l'ID de l'utilisateur. On ne gère pas l'ID de la table ici.
=======
// 🔑 Gérer l'ID de l'utilisateur. On ne gère pas l'ID de la table ici.
>>>>>>> 4470edb (maj)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

include "header.php";
include "../admin/connection.php";
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet">

<style>
<<<<<<< HEAD
/* Style pour les cartes de produits (Adapté de view_carte.php) */
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

.product-price-label { /* Ajouté pour le prix unitaire */
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
    overflow: hidden; 
    width: 110px; 
    flex-shrink: 0; 
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
    height: 38px;
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
    flex-grow: 1; 
    max-width: 40px; 
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
=======
/* Table par défaut */
.cart-table {
    width: 100%;
    border-collapse: collapse;
}

.cart-table th, .cart-table td {
    padding: 10px;
    text-align: left;
}
.cart-table .quantity-spinner {
    width: 70px; /* largeur adaptée pour voir même de grands nombres */
    min-width: 70px;
    text-align: center;
    padding: 5px;
    font-size: 14px;
    box-sizing: border-box;
}

@media screen and (max-width: 768px) {
    .table-outer {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .cart-table {
        min-width: 600px; /* largeur mini pour scroll */
        border-collapse: collapse;
    }

    .cart-table th, .cart-table td {
        white-space: nowrap; /* Empêche le retour à la ligne */
        padding: 10px;
        vertical-align: middle;
    }

    .cart-table th {
        background: #f5f5f5;
        font-weight: bold;
    }

    .cart-table td img {
        max-width: 60px;
        border-radius: 4px;
    }

    /* Colonne prix et total en ligne unique */
    .cart-table .sub-total,
    .cart-table .price {
        white-space: nowrap;
    }

    /* Cart Totals (zone à droite) en ligne unique */
    .totals-table .price {
        white-space: nowrap;
    }
    .cart-table .quantity-spinner {
        width: 60px;
        min-width: 60px;
        font-size: 14px;
    }
}

>>>>>>> 4470edb (maj)
</style>

<section class="page-title" style="background-image: url(assets/images/background/11.jpg)">
    <div class="auto-container">
        <h1>View Cart</h1>
    </div>
</section>

<<<<<<< HEAD
<section class="cart-section" style="padding: 40px 15px;">
    <div class="auto-container">
        <div class="cart-outer">
            <?php
            $cart_total = 0;
            // 🔒 On utilise la clé de session pour le panier à emporter
            $cart_for_this_user = isset($_SESSION['cart_' . $user_id]) ? $_SESSION['cart_' . $user_id] : [];

            if (count($cart_for_this_user) > 0) {
                $i = 0;
                foreach ($cart_for_this_user as $item) {
                    $qty = intval($item['qty']);
                    $price = floatval($item['price']);
                    $total = $qty * $price;
                    $cart_total += $total;
                    // 💡 Récupérer le commentaire
                    $comment = isset($item['comment']) ? htmlspecialchars($item['comment']) : '';
            ?>
                    <div class="product-card" data-itemid="<?= intval($item['id']) ?>">
                        <div class="product-header">
                            <img src="../admin/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="product-price-label">Price: <span class="unit-price"><?= number_format($price, 2) ?></span>€</div>
                            </div>
                        </div>

                        <?php if (!empty($comment)): ?>
                            <div class="comment-display">
                                <span class="comment-label">📝 Instructions :</span> <?= $comment ?>
                            </div>
                        <?php endif; ?>

                        <div class="product-controls">
                            <div class="quantity-control">
                                <span class="quantity-label">Quantity:</span>
                                <div class="custom-spinner">
                                    <button class="qty-btn qty-minus" type="button" data-action="minus" aria-label="Decrease quantity">-</button>
                                    <input class="quantity-input" id="qty<?= $i ?>" type="number" min="1" value="<?= $qty ?>" name="quantity" readonly>
                                    <button class="qty-btn qty-plus" type="button" data-action="plus" aria-label="Increase quantity">+</button>
                                </div>
                            </div>

                            <div class="product-total">
                                <span class="total-label">Total:</span>
                                <span class="total-price"><?= number_format($total, 2) ?> €</span>
                            </div>

                            <button class="remove-button" onclick="delete_product('<?= intval($item['id']) ?>')">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
            <?php
                    $i++;
                }
            } else {
                echo '<div class="empty-cart">
                        <i class="fa fa-shopping-cart" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                        <h3>Your cart is empty</h3>
                        <p><a href="index.php" style="color: #a40301; font-weight: 600;">Back to menu</a></p>
                      </div>';
            }
            ?>

            <?php if (count($cart_for_this_user) > 0): ?>
            <div class="row clearfix" style="margin-top: 30px;">
=======
<section class="cart-section">
    <div class="auto-container">
        <div class="cart-outer">
            <div class="table-outer">
                <table class="cart-table">
                    <thead class="cart-header">
                        <tr>
                            <th>Preview</th>
                            <th class="prod-column">Product</th>
                            <th class="price">Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cart_total = 0;
                        // 🔑 On utilise la nouvelle clé de session pour le panier à emporter
                        $cart_for_this_user = isset($_SESSION['cart_' . $user_id]) ? $_SESSION['cart_' . $user_id] : [];

                        if (count($cart_for_this_user) > 0) {
                            $i = 0;
                            foreach ($cart_for_this_user as $item) {
                                $qty = intval($item['qty']);
                                $price = floatval($item['price']);
                                $total = $qty * $price;
                                $cart_total += $total;
                        ?>
                                <tr data-itemid="<?= intval($item['id']) ?>">
                                    <td class="prod-column">
                                        <div class="column-box">
                                            <figure class="prod-thumb"><a href="#"><img src="../admin/<?= htmlspecialchars($item['image']) ?>" alt=""></a></figure>
                                        </div>
                                    </td>
                                    <td><h4 class="prod-title"><?= htmlspecialchars($item['name']) ?></h4></td>
                                    <td class="sub-total"><?= number_format($price, 2) ?></td>
                                    <td class="qty">
                                        <div class="item-quantity">
                                            <input class="quantity-spinner" id="qty<?= $i ?>" type="number" min="1" value="<?= $qty ?>" name="quantity">
                                        </div>
                                    </td>
                                    <td class="price"><?= number_format($total, 2) ?>€</td>
                                    <td>
                                        <a href="#" class="remove-btn" onclick="delete_product('<?= intval($item['id']) ?>')"><span class="fa fa-times"></span></a>
                                    </td>
                                </tr>
                        <?php
                                $i++;
                            }
                        } else {
                            echo "<tr><td colspan='6'>Your cart is empty.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-options clearfix">
            </div>

            <div class="row clearfix">
>>>>>>> 4470edb (maj)
                <div class="column col-lg-7 col-md-12 col-sm-12"></div>

                <div class="column pull-right col-lg-5 col-md-12 col-sm-12">
                    <form method="POST" action="takeaway_checkout.php">
                        <ul class="totals-table">
                            <li><h3>Cart Totals</h3></li>
                            <li class="clearfix total">
                                <span class="col">Total</span>
                                <span class="col price" id="cart_total"><?= number_format($cart_total, 2) ?>€</span>
                            </li>
                            
                            <li class="text-right" style="margin-top: 20px;">
                                <button type="submit" 
<<<<<<< HEAD
                                        style="background:#007bff; color:#fff; padding: 12px 30px; border:none; border-radius:8px; font-size:1.1rem; font-weight:700; cursor:pointer; transition: background-color 0.3s ease; width: 100%;">
=======
                                        style="background:#007bff; color:#fff; padding: 12px 30px; border:none; border-radius:8px; font-size:1.1rem; font-weight:700; cursor:pointer; transition: background-color 0.3s ease;">
>>>>>>> 4470edb (maj)
                                    Proceed to Checkout
                                </button>
                            </li>
                        </ul>
                    </form>
                </div>
            </div>
<<<<<<< HEAD
            <?php endif; ?>
=======
>>>>>>> 4470edb (maj)
        </div>
    </div>
</section>

<script>
<<<<<<< HEAD
// Fonction pour gérer le changement de quantité via les boutons +/- (Adapté de view_carte.php)
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

// Fonction pour la mise à jour des prix et l'appel AJAX (Adapté de view_carte.php)
function updateRowAndCart(input) {
    const card = input.closest(".product-card");
    const priceText = card.querySelector(".unit-price").textContent;
    
    const price = parseFloat(priceText.replace(',', '.'));
    let qty = parseFloat(input.value);
    const totalCell = card.querySelector(".total-price");

    // Vérification de la quantité minimale
    if (qty < 1 || isNaN(qty)) {
        input.value = 1; 
        qty = 1;
    }

    if (!isNaN(price) && !isNaN(qty)) {
        const total = (price * qty).toFixed(2);
        totalCell.textContent = total + " €";
        updateCartTotal();

        const itemId = card.getAttribute("data-itemid");
        // Mise à jour via AJAX
        update_product(itemId, input.value);
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
        cartTotalElement.textContent = total.toFixed(2) + "€";
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

    // L'input est en 'readonly', mais on garde le 'change' en cas de changement de design
    const quantityInputs = document.querySelectorAll(".quantity-input");
=======
document.addEventListener("DOMContentLoaded", function() {
    const quantityInputs = document.querySelectorAll(".quantity-spinner");

>>>>>>> 4470edb (maj)
    quantityInputs.forEach(input => {
        input.addEventListener("change", function() {
            updateRowAndCart(input);
        });
    });
<<<<<<< HEAD
});

function delete_product(id) {
    if (confirm("Are you sure you want to remove this item?")) {
        const xmlhttp1 = new XMLHttpRequest();
        xmlhttp1.onreadystatechange = function () {
            if (xmlhttp1.readyState == 4 && xmlhttp1.status == 200) {
                window.location = "view_carte_takeaway.php";
            }
        };
        // 🔒 Appel au fichier de suppression pour la commande à emporter
        xmlhttp1.open("GET", "update_from_cart_takeaway.php?id=" + id + "&qty=0", true);
        xmlhttp1.send();
    }
=======

    function updateRowAndCart(input) {
        const row = input.closest("tr");
        const price = parseFloat(row.querySelector(".sub-total").textContent.replace(',', '.'));
        const qty = parseFloat(input.value);
        const totalCell = row.querySelector(".price");

        if (!isNaN(price) && !isNaN(qty)) {
            const total = (price * qty).toFixed(2);
            totalCell.textContent = total + "€";
            updateCartTotal();

            const itemId = row.getAttribute("data-itemid");
            update_product(itemId, input.value);
        }
    }

    function updateCartTotal() {
        let total = 0;
        document.querySelectorAll("tbody tr").forEach(row => {
            const totalCell = row.querySelector(".price");
            if (totalCell) {
                const amount = parseFloat(totalCell.textContent.replace("€", "").replace(',', '.'));
                if (!isNaN(amount)) total += amount;
            }
        });
        document.getElementById("cart_total").textContent = total.toFixed(2) + "€";
    }
});

function delete_product(id) {
    const xmlhttp1 = new XMLHttpRequest();
    xmlhttp1.onreadystatechange = function () {
        if (xmlhttp1.readyState == 4 && xmlhttp1.status == 200) {
            window.location = "view_carte_takeaway.php";
        }
    };
    // 🔑 Appel au nouveau fichier de suppression pour la commande à emporter
    xmlhttp1.open("GET", "update_from_cart_takeaway.php?id=" + id + "&qty=0", true);
    xmlhttp1.send();
>>>>>>> 4470edb (maj)
}

function update_product(id, qty) {
    const xmlhttp1 = new XMLHttpRequest();
    xmlhttp1.onreadystatechange = function () {
        if (xmlhttp1.readyState == 4 && xmlhttp1.status == 200) {
            console.log(xmlhttp1.responseText);
        }
    };
<<<<<<< HEAD
    // 🔒 Appel au fichier de mise à jour pour la commande à emporter
=======
    // 🔑 Appel au nouveau fichier de mise à jour pour la commande à emporter
>>>>>>> 4470edb (maj)
    xmlhttp1.open("GET", "update_from_cart_takeaway.php?id=" + id + "&qty=" + qty, true);
    xmlhttp1.send();
}
</script>

<?php include "footer.php"; ?>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/parallax.min.js"></script>
<script src="assets/js/popper.min.js"></script>
<<<<<<< HEAD
=======
<script src="assets/js/jquery-ui.js"></script>
>>>>>>> 4470edb (maj)
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/jquery.fancybox.js"></script>
<script src="assets/js/owl.js"></script>
<script src="assets/js/wow.js"></script>
<<<<<<< HEAD
=======
<script src="assets/js/jquery.bootstrap-touchspin.js"></script>
>>>>>>> 4470edb (maj)
<script src="assets/js/appear.js"></script>
<script src="assets/js/script.js"></script>