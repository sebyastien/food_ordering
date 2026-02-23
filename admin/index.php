<?php
session_start();
include "connection.php";

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit1"])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // On récupère le mot de passe ET le rôle
    $stmt = mysqli_prepare($link, "SELECT password, role FROM admin_login WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    // Vérification du mot de passe
    if ($row && password_verify($password, $row['password'])) {
        // Connexion réussie : on stocke l'username ET le rôle dans la session
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role'] = $row['role'];
        $_SESSION['last_activity'] = time();

        ?>
        <script type="text/javascript">
<<<<<<< HEAD
            window.location = "acceuil.php";
=======
            window.location = "dashboard.php";
>>>>>>> 4470edb (maj)
        </script>
        <?php
    } else {
        // Mauvais identifiants
        $error = "Nom d'utilisateur ou mot de passe invalide.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!doctype html>
<html class="no-js" lang="">
<head>
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

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

    <style>
        #logout-message, #login-error {
            transition: opacity 1s ease;
            opacity: 1;
        }
        #logout-message.fade-out, #login-error.fade-out {
            opacity: 0;
        }
    </style>
</head>
<body class="bg-dark">

    <div class="sufee-login d-flex align-content-center flex-wrap">
        <div class="container">
            <div class="login-content">
                <div class="login-logo">
                    <a href="" style="font-size:large; color: white">
                        Admin Login
                    </a>
                </div>
                <div class="login-form">

                    <?php if (isset($_GET['logged_out'])): ?>
                        <div id="logout-message" class="alert alert-success" style="margin-top:15px;">
                            Vous avez été déconnecté avec succès.
                        </div>
                    <?php endif; ?>

                    <form name="form1" action="" method="post">
                        <div class="form-group">
                            <label>User Name</label>
                            <input type="text" class="form-control" placeholder="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" placeholder="Password" name="password" required>
                        </div>

                        <button type="submit" name="submit1" class="btn btn-success btn-flat m-b-30 m-t-30">Sign in</button>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" id="login-error" role="alert" style="margin-top:15px;">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/vendor/jquery-2.1.4.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Message logout : disparition avec transition + suppression param URL
            const logoutMessage = document.getElementById('logout-message');
            if (logoutMessage) {
                setTimeout(() => {
                    logoutMessage.classList.add('fade-out');
                    logoutMessage.addEventListener('transitionend', () => {
                        logoutMessage.style.display = 'none';
                    }, { once: true });

                    // Supprime paramètre logged_out de l'URL sans recharger la page
                    const url = new URL(window.location);
                    if (url.searchParams.has('logged_out')) {
                        url.searchParams.delete('logged_out');
                        window.history.replaceState({}, document.title, url.pathname + url.search);
                    }
                }, 3000);
            }

            // Message erreur login : disparition avec transition
            const loginError = document.getElementById('login-error');
            if (loginError) {
                setTimeout(() => {
                    loginError.classList.add('fade-out');
                    loginError.addEventListener('transitionend', () => {
                        loginError.style.display = 'none';
                    }, { once: true });
                }, 3000);
            }
        });
    </script>

</body>
</html>
