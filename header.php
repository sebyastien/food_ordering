<?php
// On démarre la session pour accéder aux variables
// Cette ligne est maintenant supprimée car la session est démarrée dans les fichiers d'entrée (index.php, takeaway.php, etc.)

// Définir le type de commande comme 'table' par défaut
$order_type = isset($_SESSION['order_type']) ? $_SESSION['order_type'] : 'table';

// Définir les liens dynamiques
$home_link = '';
$cart_link = '';
$table_id = 0; // On initialise table_id pour éviter l'erreur

if ($order_type === 'takeaway') {
    $home_link = 'takeaway.php';
    $cart_link = 'view_carte_takeaway.php';
} else {
    // Si c'est une commande de type 'table', on utilise le table_id
    $table_id = isset($_SESSION['table_id']) ? intval($_SESSION['table_id']) : 0;
    $home_link = 'index.php?table_id=' . $table_id;
    $cart_link = 'view_carte.php?table_id=' . $table_id;
}


// Calcul du nombre total d'articles dans le panier
$total_items = 0;
// On utilise le bon nom de panier basé sur le mode
$cart_key = ($order_type === 'takeaway') ? 'cart_' . ($_SESSION['user_id'] ?? '') : 'cart';

if (isset($_SESSION[$cart_key]) && is_array($_SESSION[$cart_key])) {
    foreach ($_SESSION[$cart_key] as $item) {
        if (isset($item['qty_total'])) {
            $total_items += intval($item['qty_total']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/vendors/flat-icon/flaticon.css" rel="stylesheet">
    <link href="assets/vendors/revolution/css/settings.css" rel="stylesheet">
    <link href="assets/vendors/revolution/css/layers.css" rel="stylesheet">
    <link href="assets/vendors/revolution/css/navigation.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?= time() ?>" rel="stylesheet">
    <link href="assets/css/responsive.css" rel="stylesheet">

    <link rel="shortcut icon" href="assets/images/logo-02.png" type="image/x-icon">
    <link rel="icon" href="assets/images/logo-02.png" type="image/x-icon">

    <link
        href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;600;700&amp;family=Open+Sans:wght@400;600;700;800&amp;family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&amp;family=Poppins:wght@300;400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
</head>

<body>

    <div class="page-wrapper">

        <div class="preloader"></div>

        <header class="main-header">
            <div class="header-top" style="background-color:#f2e39c; color:black">
                <div class="auto-container clearfix">
                    <div class="top-left">
                        <ul class="info-list">
                            <li>
                                <a href="mailto:info@abc.co.in" style="color: black">
                                    <span class="icon far fa-envelope"></span> info@abc.co.in
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="top-right clearfix">
                        <ul class="social-box">
                            <li><a href="#" style="color: black"><span class="fa fa-user-alt"></span></a></li>
                        </ul>

                        <div class="option-list">
                            <div class="cart-btn">
                                <a href="<?= $cart_link ?>" class="icon flaticon-shopping-cart" style="color: black">
                                    <span class="total-cart" style="background-color: #a40301; color: white;">
                                        <?php echo $total_items > 0 ? $total_items : ''; ?>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-upper">
                <div class="inner-container">
                    <div class="auto-container clearfix">
                        <div class="logo-outer">
                            <div class="logo" style="margin-top: -20px;">
                                <a href="<?= $home_link ?>">
                                    <img src="assets/images/logo-02.png" alt="" title="">
                                </a>
                            </div>
                        </div>

                        <div class="nav-outer clearfix">
                            <nav class="main-menu navbar-expand-md navbar-light">
                                <div class="navbar-header">
                                    <button class="navbar-toggler" type="button" data-toggle="collapse"
                                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                        aria-expanded="false" aria-label="Toggle navigation">
                                        <span class="icon flaticon-menu"></span>
                                    </button>
                                </div>

                                <div class="collapse navbar-collapse clearfix" id="navbarSupportedContent">
                                    <ul class="navigation clearfix">
                                        <li class="current"><a href="<?= $home_link ?>">Home</a></li>
                                        <li><a href="gallery.html">Gallery</a></li>
                                        <li><a href="find_order.php">Suivre ma commande</a></li>
                                        <li><a href="about.html">About Us</a></li>
                                        <li><a href="contact.html">Contact</a></li>
                                    </ul>
                                </div>
                            </nav>
                            <div class="outer-box">
                                <div class="order">
                                    Order Now
                                    <span><a href="tel:1800-123-4567">1800 123 4567</a></span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="sticky-header">
                <div class="auto-container clearfix">
                    <div class="logo pull-left">
                        <a href="<?= $home_link ?>" class="img-responsive">
                            <img src="assets/images/logo-02.png" alt="" title="" height="90" width="90" style="margin-top: -10px;">
                        </a>
                    </div>

                    <div class="right-col pull-right">
                        <nav class="main-menu navbar-expand-md">
                            <button class="navbar-toggler" type="button" data-toggle="collapse"
                                data-target="#navbarSupportedContent1" aria-controls="navbarSupportedContent1"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>

                            <div class="navbar-collapse collapse clearfix" id="navbarSupportedContent1">
                                <ul class="navigation clearfix">
                                    <li class="current"><a href="<?= $home_link ?>">Home</a></li>
                                    <li><a href="gallery.html">Gallery</a></li>
                                    <li><a href="find_order.php">Suivre ma commande</a></li>
                                    <li><a href="about.html">About Us</a></li>
                                    <li><a href="contact.html">Contact</a></li>
                                </ul>
                            </div>
                        </nav></div>
                </div>
            </div>
            </header>
