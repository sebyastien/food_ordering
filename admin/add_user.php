<?php
session_start();

// Vérification de sécurité : Seuls les admins peuvent accéder à cette page.
if (!isset($_SESSION['admin_username']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

include "connection.php";

<<<<<<< HEAD
$roles_autorises = ['admin', 'patron', 'gérant'];  // adapter selon la page
=======
$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon la page
>>>>>>> 4470edb (maj)
include "auth_check.php";

include "header.php";

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validation basique
    if (empty($username) || empty($password)) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs.</div>';
    } else {
        // Hacher le mot de passe avant l'insertion
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Vérifier si le nom d'utilisateur existe déjà
        $stmt_check = $link->prepare("SELECT id FROM admin_login WHERE username = ?");
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $message = '<div class="alert alert-danger">Ce nom d\'utilisateur existe déjà.</div>';
        } else {
            // Insérer le nouvel utilisateur avec son rôle
            $stmt_insert = $link->prepare("INSERT INTO admin_login (username, password, role) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $username, $hashed_password, $role);
            
            if ($stmt_insert->execute()) {
                $message = '<div class="alert alert-success">Utilisateur ajouté avec succès !</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de l\'utilisateur.</div>';
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<div class="container mt-5">
    <div class="card p-4">
        <h4 class="card-title text-center mb-4">Ajouter un utilisateur</h4>
        <?= $message ?>
        <form action="" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Rôle</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="gérant">Gérant</option>
                    <option value="patron">Patron</option>
<<<<<<< HEAD
                    <option value="serveur">Serveur</option>
=======
>>>>>>> 4470edb (maj)
                </select>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" name="add_user" class="btn btn-success">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<?php 
include "footer.php"; 
?>
