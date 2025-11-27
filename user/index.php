<?php
session_start();

// Définir le type de commande comme 'table'
$_SESSION['order_type'] = 'table';

// Gérer l'ID de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = uniqid('user_', true);
}

// Gérer l'ID de la table
if (isset($_GET['table_id'])) {
    $_SESSION['table_id'] = intval($_GET['table_id']);
}
$table_id = isset($_SESSION['table_id']) ? intval($_SESSION['table_id']) : 0;

include "header.php";
include "slider.php";
include "../admin/connection.php";
?>

<title>Home Page</title>

<style>
/* Styles responsive pour la grille de produits */
@media screen and (max-width: 768px) {
    /* Section produits */
    .products-section {
        padding: 30px 0 !important;
    }

    .products-section .auto-container {
        padding: 0 10px !important;
    }

    /* Titre */
    .sec-title h2 {
        font-size: 1.8em !important;
        margin-bottom: 20px;
    }

    /* Filtres */
    .filter-tabs {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
        padding: 0 10px;
        margin-bottom: 25px;
    }

    .filter-tabs li {
        font-size: 0.85em !important;
        padding: 8px 12px !important;
        margin: 0 !important;
        border-radius: 20px;
        background: #f5f5f5;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-tabs li.active,
    .filter-tabs li:hover {
        background: #a41a13;
        color: white;
    }

    /* Grille produits - 2 colonnes sur mobile */
    .filter-list {
        margin: 0 -5px !important;
    }

    .product-block {
        width: 50% !important;
        max-width: 50% !important;
        flex: 0 0 50%;
        padding: 0 5px !important;
        margin-bottom: 15px !important;
    }

    /* Carte produit */
    .product-block .inner-box {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    /* Image produit */
    .product-block .image-box {
        width: 100%;
        height: 140px;
        overflow: hidden;
        margin: 0;
    }

    .product-block .image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Contenu produit */
    .product-block .lower-content {
        padding: 10px !important;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-block h4 {
        font-size: 0.95em !important;
        margin-bottom: 5px !important;
        line-height: 1.3;
        height: 38px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .product-block h4 a {
        color: #333;
        text-decoration: none;
    }

    .product-block .text {
        font-size: 0.75em !important;
        color: #666;
        margin-bottom: 8px !important;
        height: 30px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .product-block .price {
        font-size: 1.1em !important;
        color: #a41a13;
        font-weight: bold;
        margin-bottom: 10px !important;
    }

    /* Boutons */
    .custom-button-container {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: auto;
    }

    .custom-btn {
        width: 100% !important;
        padding: 8px 10px !important;
        font-size: 0.8em !important;
        border-radius: 6px;
        text-align: center;
        text-decoration: none;
        display: block;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .custom-btn-description {
        background: #6c757d;
        color: white;
    }

    .custom-btn-description:hover {
        background: #5a6268;
    }

    .custom-btn-order {
        background: #a41a13;
        color: white;
    }

    .custom-btn-order:hover {
        background: #7a0f0e;
    }

    .custom-btn .txt {
        font-size: 1em;
    }

    /* Bouton voir le panier */
    .cart-button-container {
        margin: 30px 0;
        padding: 0 15px;
        text-align: center;
    }

    .cart-button-container a {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 15px 20px !important;
        font-size: 1.1em !important;
        border-radius: 8px;
        box-sizing: border-box;
    }

    .cart-button-container p {
        font-size: 0.9em !important;
        margin-top: 10px;
    }
}

/* Styles Desktop pour le bouton panier */
.cart-button-container {
    margin: 40px auto;
    padding: 0 15px;
    text-align: center;
    max-width: 500px;
}

.cart-button-container a {
    display: inline-block;
}

.cart-button-container p {
    margin-top: 10px;
    font-size: 16px;
    color: #555;
}

/* Styles pour très petits écrans (< 400px) */
@media screen and (max-width: 400px) {
    .product-block .image-box {
        height: 120px;
    }

    .product-block h4 {
        font-size: 0.85em !important;
    }

    .product-block .price {
        font-size: 1em !important;
    }

    .custom-btn {
        padding: 7px 8px !important;
        font-size: 0.75em !important;
    }

    .filter-tabs li {
        font-size: 0.75em !important;
        padding: 6px 10px !important;
    }
}

/* Pour tablettes (768px - 1024px) : 3 colonnes */
@media screen and (min-width: 769px) and (max-width: 1024px) {
    .product-block {
        width: 33.333% !important;
        max-width: 33.333% !important;
        flex: 0 0 33.333%;
    }
}
</style>

<section class="products-section">
    <div class="auto-container">

        <div class="sec-title centered">
            <h2>Our Products</h2>
        </div>

        <div class="mixitup-gallery">

            <div class="filters clearfix">
                <ul class="filter-tabs filter-btns clearfix">
                    <li class="active filter" data-role="button" data-filter="all">All</li>
                    <?php
                    $res = mysqli_query($link, "SELECT * FROM food_categories ORDER BY ordre ASC, id ASC");
                    while ($row = mysqli_fetch_assoc($res)) {
                        $category_name = htmlspecialchars($row["food_categories"]);
                        $category_class = str_replace(' ', '-', $category_name);
                        ?>
                        <li class="filter" data-role="button" data-filter=".<?= $category_class ?>">
                            <?= $category_name ?>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>

            <div class="filter-list row clearfix">

                <?php
                $res = mysqli_query($link, "SELECT * FROM food WHERE is_active = 1");
                while ($row = mysqli_fetch_assoc($res)) {
                    $category_name = htmlspecialchars($row["food_category"]);
                    $category = str_replace(' ', '-', $category_name);
                    $food_name = htmlspecialchars($row["food_name"]);
                    $food_desc = htmlspecialchars(substr($row["food_description"], 0, 30)) . "..";
                    $food_price = htmlspecialchars($row["food_original_price"]);
                    $food_image = htmlspecialchars($row["food_image"]);
                    $food_id = intval($row["id"]);
                    ?>
                    <div class="product-block all mix <?= $category ?> fest wraps fries col-lg-3 col-md-6 col-sm-12">
                        <div class="inner-box">
                            <figure class="image-box">
                                <img src="../admin/<?= $food_image ?>" alt="<?= $food_name ?>">
                            </figure>
                            <div class="lower-content">
                                <h4>
                                    <a href="food_description.php?id=<?= $food_id ?>&table_id=<?= $table_id ?>">
                                        <?= $food_name ?>
                                    </a>
                                </h4>
                                <div class="text"><?= $food_desc ?></div>
                                <div class="price"><?= $food_price ?>€</div>
                                <div class="custom-button-container">
                                    <a href="food_description.php?id=<?= $food_id ?>&table_id=<?= $table_id ?>"
                                       class="custom-btn custom-btn-description">
                                        <span class="txt">Voir détails</span>
                                    </a>

                                    <button class="custom-btn custom-btn-order add-to-cart-btn"
                                            data-id="<?= $food_id ?>">
                                        <span class="txt">Commander</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>

            </div>

        </div>

    </div>
</section>

<div class="cart-button-container">
    <a href="view_carte.php?table_id=<?= $table_id ?>"
       style="
           display: inline-block;
           background-color: #a41a13;
           color: white;
           padding: 12px 25px;
           font-size: 18px;
           border-radius: 8px;
           text-decoration: none;
           box-shadow: 0 4px 8px rgba(0,0,0,0.2);
           transition: background-color 0.3s ease;
         "
       onmouseover="this.style.backgroundColor='black';"
       onmouseout="this.style.backgroundColor='#a41a13';"
    >
        🛒 Voir le panier
    </a>
    <p style="margin-top: 10px; font-size: 16px; color: #555;">
        Consultez vos articles sélectionnés
    </p>
</div>

<script>
document.querySelectorAll(".add-to-cart-btn").forEach(btn => {
    btn.addEventListener("click", function() {
        const id = this.dataset.id;

        fetch("add_to_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + encodeURIComponent(id) + "&qty=1"
        })
        .then(res => res.json())
        .then(data => {
            let alertBox = document.createElement("div");
            alertBox.className = "alert-notif";
            alertBox.style.position = "fixed";
            alertBox.style.top = "50%";
            alertBox.style.left = "50%";
            alertBox.style.transform = "translate(-50%, -50%)";
            alertBox.style.padding = "15px 30px";
            alertBox.style.color = "white";
            alertBox.style.borderRadius = "8px";
            alertBox.style.zIndex = 9999;
            alertBox.style.background = data.success ? "#28a745" : "#dc3545";
            alertBox.style.opacity = "0";
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.maxWidth = "90%";
            alertBox.style.textAlign = "center";
            alertBox.style.fontSize = "0.95em";
            alertBox.textContent = data.message;
            document.body.appendChild(alertBox);

            setTimeout(() => alertBox.style.opacity = "1", 10);
            setTimeout(() => {
                alertBox.style.opacity = "0";
                setTimeout(() => alertBox.remove(), 500);
            }, 2000);
        })
        .catch(() => {
            alert("Une erreur est survenue, veuillez réessayer.");
        });
    });
});
</script>

<?php
include "delivery_section.php";
include "service_section.php";
include "footer.php";
?>