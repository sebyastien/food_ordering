<?php
session_start();


// Gérer l'ID de la table
if (isset($_GET['table_id'])) {
    $_SESSION['table_id'] = intval($_GET['table_id']);
}
$table_id = isset($_SESSION['table_id']) ? intval($_SESSION['table_id']) : 0;

// 🔑 Gérer l'ID de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = uniqid('user_', true);
}
$user_id = $_SESSION['user_id'];

// Le reste du code de la page...
// ...

include "header.php";
include "../admin/connection.php";
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

$food_name = $food_description = $food_image = $food_price = $food_ingredients = $food_category = "";

$res = mysqli_query($link, "SELECT * FROM food WHERE id = '$id'");
if ($row = mysqli_fetch_array($res)) {
    $food_name = $row["food_name"];
    $food_description = $row["food_description"];
    $food_image = $row["food_image"];
    $food_price = $row["food_discount_price"];
    $food_ingredients = $row["food_ingredients"];
    $food_category = $row["food_category"];
}
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet">




<title>Food Description</title>

<!-- Page Title -->
<section class="page-title" style="background-image: url(assets/images/background/11.jpg)">
    <div class="auto-container">
        <h1>Food Details</h1>
    </div>
</section>

<!-- Shop Single Section -->
<section class="shop-single-section">
    <div class="auto-container">
        <div class="shop-single">
            <div class="product-details">
                <!-- Basic Details -->
                <div class="basic-details">
                    <div class="row clearfix">
                        <div class="image-column col-lg-6 col-md-12 col-sm-12">
                            <figure class="image-box">
                                <a href="#" class="lightbox-image" title="<?php echo $food_name; ?>">
                                    <img src="../admin/<?php echo $food_image; ?>" alt="">
                                </a>
                            </figure>
                        </div>
                        <div class="info-column col-lg-6 col-md-12 col-sm-12">
                            <div class="inner-column">
                                <h2><?php echo $food_name; ?></h2>
                                <div class="text"><?php echo $food_description; ?></div>
                                <div class="text">Ingredients: <?php echo $food_ingredients; ?></div>
                                <div class="price">Price: <span><?php echo $food_price; ?></span></div>

                                <div class="other-options clearfix">
                                    <div class="item-quantity">
                                        <label class="field-label">Quantity :</label>
                                        <input class="quantity-spinner" type="number" min="1" value="1" name="quantity" id="qty">
                                    </div>
                                    <button type="button" class="theme-btn btn-style-five" onclick="add_to_cart('<?php echo $id; ?>', document.getElementById('qty').value);">
                                        <span class="txt">Order now</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Basic Details -->
            </div>
        </div>
    </div>
</section>
<!-- End Shop Single Section -->

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

<!-- Similar Products Section -->
<section class="similar-products-section">
    <div class="auto-container">
        <div class="sec-title centered">
            <h2>Similar Products</h2>
        </div>
        <div class="row clearfix">

            <?php
            $res = mysqli_query($link, "SELECT * FROM food WHERE food_category='$food_category' AND id!=$id");
            while ($row = mysqli_fetch_array($res)) {
            ?>
                <!-- Product Block -->
                <div class="product-block col-lg-3 col-md-6 col-sm-12">
                    <div class="inner-box">
                        <figure class="image-box">
                            <img src="../admin/<?php echo $row["food_image"]; ?>" alt="">
                        </figure>
                        <div class="lower-content">
                            <h4>
                                <a href="food_description.php?id=<?php echo $row["id"]; ?>&table_id=<?php echo $table_id; ?>">
                                    <?php echo $row["food_name"]; ?>
                                </a>
                            </h4>
                            <div class="text"><?php echo substr($row["food_description"], 0, 30); ?>...</div>
                            <div class="price"><?php echo $row["food_discount_price"]; ?> </div>
                            <div class="lower-box">
                                <a href="food_description.php?id=<?php echo $row["id"]; ?>&table_id=<?php echo $table_id; ?>" class="theme-btn btn-style-five">
                                    <span class="txt">Food description</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>

        </div>
    </div>
</section>
<!-- End Similar Products Section -->

<!-- JavaScript -->
<script type="text/javascript">
    function add_to_cart(id, qty) {
    var table_id = <?php echo $table_id; ?>;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "add_to_cart.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            try {
                var res = JSON.parse(xhr.responseText);

                // Création d’une alerte visuelle similaire à index.php
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
                alertBox.style.background = res.success ? "#28a745" : "#dc3545";
                alertBox.style.opacity = "0";
                alertBox.style.transition = "opacity 0.5s ease";
                alertBox.textContent = res.message;
                document.body.appendChild(alertBox);

                setTimeout(() => alertBox.style.opacity = "1", 10);
                setTimeout(() => {
                    alertBox.style.opacity = "0";
                    setTimeout(() => alertBox.remove(), 500);
                }, 1000);

            } catch {
                alert("Erreur lors de l'ajout au panier.");
            }
        }
    };
    xhr.send("id=" + encodeURIComponent(id) + "&qty=" + encodeURIComponent(qty) + "&table_id=" + encodeURIComponent(table_id));
}
</script>

<?php
// include "delivery_section.php";
// include "service_section.php";
include "footer.php";
?>

<!-- External JS files -->
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
