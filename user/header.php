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
    <link href="assets/css/responsive.css?v=<?php echo filemtime('assets/css/responsive.css'); ?>" rel="stylesheet">

    <link rel="shortcut icon" href="assets/images/logo-02.png" type="image/x-icon">
    <link rel="icon" href="assets/images/logo-02.png" type="image/x-icon">

    <link
        href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;600;700&amp;family=Open+Sans:wght@400;600;700;800&amp;family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&amp;family=Poppins:wght@300;400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    
    <style>
        /* Augmente l'espace autour du contenu de l'en-tête principal */
        .main-header .header-upper .inner-container {
            padding-top: 5px; /* Ajout d'espace en haut */
            padding-bottom: 5px; /* Ajout d'espace en bas */
        }

        /* Ajuste la hauteur de l'en-tête collant (sticky) pour qu'il soit aussi un peu plus grand */
        .main-header .sticky-header {
            padding: 10px 0; /* Augmente le padding vertical */
        }
        
    </style>
    </head>

<body>

    <div class="page-wrapper">

        <div class="preloader"></div>

        <header class="main-header">
            <div class="header-upper">
                <div class="auto-container clearfix">
                    <div class="top-left">    
                    </div>
                    <div class="top-right clearfix">

                        <div class="option-list">
                            <div class="cart-btn">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-upper">
                <div class="inner-container">
                    <div class="auto-container clearfix header-mobile-content">
    <div class="logo-outer">
        <div class="logo"> <a href="<?= $home_link ?>">
                <img class="header-logo" src="assets/images/logo-02.png" alt="" title="">
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
                            <div class="option-list">
                                <div class="cart-btn">
                                    <a href="<?= $cart_link ?>" title="Shopping Cart">
                                        <i class="flaticon-shopping-bag"></i>
                                        <span class="count"><?= $total_items ?></span>
                                    </a>
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
                            <img src="assets/images/logo-02.png" alt="" title="" height="90" width="90"> 
                        </a>
                    </div>

                    <div class="right-col pull-right">
                        <div class="option-list pull-left" style="margin-right: 15px;">
                            
                        </div>
                        <nav class="main-menu navbar-expand-md pull-right">
                            <div class="navbar-header"> 
                                <button class="navbar-toggler" type="button" data-toggle="collapse"
                                    data-target="#navbarSupportedContent1" aria-controls="navbarSupportedContent1"
                                    aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="icon flaticon-menu"></span> 
                                </button>
                            </div>

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