<?php
session_start();

// 🔑 Gérer l'ID de l'utilisateur. On ne gère pas l'ID de la table ici.
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

include "header.php";
include "../admin/connection.php";
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet">

<style>
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

</style>

<section class="page-title" style="background-image: url(assets/images/background/11.jpg)">
    <div class="auto-container">
        <h1>View Cart</h1>
    </div>
</section>

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
                                        style="background:#007bff; color:#fff; padding: 12px 30px; border:none; border-radius:8px; font-size:1.1rem; font-weight:700; cursor:pointer; transition: background-color 0.3s ease;">
                                    Proceed to Checkout
                                </button>
                            </li>
                        </ul>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const quantityInputs = document.querySelectorAll(".quantity-spinner");

    quantityInputs.forEach(input => {
        input.addEventListener("change", function() {
            updateRowAndCart(input);
        });
    });

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
}

function update_product(id, qty) {
    const xmlhttp1 = new XMLHttpRequest();
    xmlhttp1.onreadystatechange = function () {
        if (xmlhttp1.readyState == 4 && xmlhttp1.status == 200) {
            console.log(xmlhttp1.responseText);
        }
    };
    // 🔑 Appel au nouveau fichier de mise à jour pour la commande à emporter
    xmlhttp1.open("GET", "update_from_cart_takeaway.php?id=" + id + "&qty=" + qty, true);
    xmlhttp1.send();
}
</script>

<?php include "footer.php"; ?>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/parallax.min.js"></script>
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/jquery-ui.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/jquery.fancybox.js"></script>
<script src="assets/js/owl.js"></script>
<script src="assets/js/wow.js"></script>
<script src="assets/js/jquery.bootstrap-touchspin.js"></script>
<script src="assets/js/appear.js"></script>
<script src="assets/js/script.js"></script>