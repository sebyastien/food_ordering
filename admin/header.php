<!doctype html>
<html class="no-js" lang=""> <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <title>Admin Template</title>
    <meta name="description" content="Sufee Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="apple-icon.png">
    <link rel="shortcut icon" href="favicon.ico">

    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/themify-icons.css">
    <link rel="stylesheet" href="assets/css/flag-icon.min.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/scss/style.css">
    <link href="assets/css/lib/vector-map/jqvmap.min.css" rel="stylesheet">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

    </head>
<body>


    <aside id="left-panel" class="left-panel">
        <nav class="navbar navbar-expand-sm navbar-default">

            <div class="navbar-header">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa fa-bars"></i>
                </button>
                 <a class="navbar-brand" href="./">Admin Panel</a>
                <a class="navbar-brand hidden" href="./">Admin Panel</a>
            </div>

            <div id="main-menu" class="main-menu collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li class="active">
                        <a href="acceuil.php"> <i class="menu-icon fa fa-dashboard"></i>Acceuil </a>
                    </li>
                    <?php 
                    $role_ok = ($_SESSION['admin_role'] !== 'patron' && $_SESSION['admin_role'] !== 'gérant' && $_SESSION['admin_role'] !== 'serveur');
                    if ($role_ok):  
                    ?>
                    <li class="active">
                        <a href="dashboard.php"> <i class="menu-icon fa fa-dashboard"></i>Dashboard </a>
                    </li>
                    <?php endif; ?>
                    <?php 
                    $role_ok = ($_SESSION['admin_role'] !== 'serveur');
                    if ($role_ok):  
                    ?>
                    <li>
                        <a href="food_categories.php"> <i class="menu-icon fa fa-dashboard"></i>Add / Edit Categories </a>
                    </li>
                    <?php endif; ?>
                    <?php 
                    $role_ok = ($_SESSION['admin_role'] !== 'serveur');
                    if ($role_ok):  
                    ?>
                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-th"></i>Food</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="menu-icon fa fa-th"></i><a href="add_new_food.php">Add New Food</a></li>
                            <li><i class="menu-icon fa fa-th"></i><a href="display_food.php">Display Food </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <?php 
                    $role_ok = ($_SESSION['admin_role'] !== 'serveur');
                    if ($role_ok):  
                    ?>
                    <li class="active">
                        <a href="orders.php"> <i class="menu-icon fa fa-dashboard"></i>All the Orders</a>
                    </li>
                    <?php endif; ?>
                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-th"></i>Vue Cuisine</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="menu-icon fa fa-th"></i><a href="kitchen_orders.php">Cuisine</a></li>
                            <li><i class="menu-icon fa fa-th"></i><a href="archived_orders.php">Archives commandes</a></li>
                        </ul>
                    </li>
                    <?php 
                    $role_ok = ($_SESSION['admin_role'] !== 'patron'  && $_SESSION['admin_role'] !== 'serveur');
                    if ($role_ok):  
                    ?>
                    <li class="active">
                        <a href="add_user.php"> <i class="menu-icon fa fa-dashboard"></i>Add User</a>
                    </li>
                    <li class="active">
                        <a href="all_user.php"> <i class="menu-icon fa fa-dashboard"></i>All User</a>
                    </li>
                    <li class="active">
                        <a href="qr_generator_page.php"> <i class="menu-icon fa fa-dashboard"></i>Génération de Qr Code</a>
                    </li>
                    <?php endif; ?>
                    <li class="active">
                        <a href="server_orders.php"> <i class="menu-icon fa fa-dashboard"></i>Serveur</a>
                    </li>
                    <!-- NOUVEAU: Lien vers la gestion des tables -->
                    <li class="active">
                        <a href="manage_tables.php"> <i class="menu-icon fa fa-table"></i>Gestion des Tables</a>
                    </li>
                </ul>
            </div></nav>
    </aside><div id="right-panel" class="right-panel">

        <header id="header" class="header">

            <div class="header-menu">

                <div class="col-sm-7">
                    <a id="menuToggle" class="menutoggle pull-left"><i class="fa fa fa-tasks"></i></a>
                    <div class="header-left">
                        <button class="search-trigger"><i class="fa fa-search"></i></button>
                        <div class="form-inline">
                            <form class="search-form">
                                <input class="form-control mr-sm-2" type="text" placeholder="Search ..." aria-label="Search">
                                <button class="search-close" type="submit"><i class="fa fa-close"></i></button>
                            </form>
                        </div>

                        
                    </div>
                </div>

                <div class="col-sm-5">
                    <div class="user-area dropdown float-right">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="user-avatar rounded-circle" src="images/admin.jpg" alt="User Avatar">
                        </a>

                        <div class="user-menu dropdown-menu">
                                <a class="nav-link" href="#"><i class="fa fa- user"></i>My Profile</a>

                                <a class="nav-link" href="#"><i class="fa fa- user"></i>Notifications <span class="count">13</span></a>

                                <a class="nav-link" href="#"><i class="fa fa -cog"></i>Settings</a>

                                <a class="nav-link" a href="logout.php"><i class="fa fa-power -off"></i>Logout</a>
                        </div>
                    </div>

                    

                </div>
            </div>

        </header>