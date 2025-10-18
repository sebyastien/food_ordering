<?php
session_start();

// Définir le type de commande comme 'table'
$_SESSION['order_type'] = 'table';

// 🔑 Gérer l'ID de l'utilisateur. On génère un ID unique s'il n'existe pas déjà en session.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = uniqid('user_', true);
}

// Gérer l'ID de la table
if (isset($_GET['table_id'])) {
    $_SESSION['table_id'] = intval($_GET['table_id']);
}
$table_id = isset($_SESSION['table_id']) ? intval($_SESSION['table_id']) : 0;

// Le reste du code de la page...
// ...

include "header.php";
include "slider.php";
include "../admin/connection.php";
?>

<title>Home Page</title>

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
                    $res = mysqli_query($link, "SELECT * FROM food_categories");
                    while ($row = mysqli_fetch_assoc($res)) {
                        // sécuriser le nom de catégorie dans le filtre CSS
                        $category_class = htmlspecialchars($row["food_categories"]);
                        ?>
                        <li class="filter" data-role="button" data-filter=".<?= $category_class ?>">
                            <?= $category_class ?>
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
                    $category = htmlspecialchars($row["food_category"]);
                    $food_name = htmlspecialchars($row["food_name"]);
                    $food_desc = htmlspecialchars(substr($row["food_description"], 0, 30)) . "..";
                    $food_price = htmlspecialchars($row["food_discount_price"]);
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
                                <div class="price"><?= $food_price ?></div>
                                <!-- Remplacement des styles inline par des classes CSS -->
                                <div class="custom-button-container">
                                    <a href="food_description.php?id=<?= $food_id ?>&table_id=<?= $table_id ?>"
                                       class="custom-btn custom-btn-description">
                                        <span class="txt">Food Description</span>
                                    </a>

                                    <button class="custom-btn custom-btn-order add-to-cart-btn"
                                            data-id="<?= $food_id ?>">
                                        <span class="txt">Order Now</span>
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

<div style="margin-top: 30px; text-align: center;">
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
        Voir le panier
    </a>
    <p style="margin-top: 10px; font-size: 16px; color: #555;">
        Cliquez ici pour consulter votre panier actuel.
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
            alertBox.textContent = data.message;
            document.body.appendChild(alertBox);

            setTimeout(() => alertBox.style.opacity = "1", 10);
            setTimeout(() => {
                alertBox.style.opacity = "0";
                setTimeout(() => alertBox.remove(), 500);
            }, 1000);
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