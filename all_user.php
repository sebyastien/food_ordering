<?php
// La session doit déjà être démarrée dans auth_check.php (à vérifier !)
$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon besoin
include "auth_check.php";

include "connection.php";
include "header.php";

// Requête pour récupérer tous les utilisateurs
$query = "SELECT username, role FROM admin_login ORDER BY username ASC";
$result = mysqli_query($link, $query);
?>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Liste des utilisateurs</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Utilisateurs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="container bg-white p-3 rounded shadow">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Rôle</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($user = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>Aucun utilisateur trouvé.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include "footer.php";
?>
