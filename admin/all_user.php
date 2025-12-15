<?php
// La session doit déjà être démarrée dans auth_check.php (à vérifier !)
$roles_autorises = ['admin', 'patron', 'gérant'];  // adapter selon besoin
include "auth_check.php";

include "connection.php";
include "header.php";

// Récupérer les messages de session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;

// Supprimer les messages après affichage
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Requête pour récupérer tous les utilisateurs avec leur ID
$query = "SELECT id, username, role FROM admin_login ORDER BY username ASC";
$result = mysqli_query($link, $query);

// Fonction pour afficher l'icône du rôle
function getRoleIcon($role) {
    $icons = [
        'admin' => '<i class="fa fa-crown text-danger" title="Admin"></i>',
        'patron' => '<i class="fa fa-briefcase text-primary" title="Patron"></i>',
        'gérant' => '<i class="fa fa-user-tie text-info" title="Gérant"></i>',
        'serveur' => '<i class="fa fa-concierge-bell text-success" title="Serveur"></i>'
    ];
    return $icons[$role] ?? '<i class="fa fa-user text-secondary"></i>';
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1><i class="fa fa-users"></i> Liste des utilisateurs</h1>
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
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success_message ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error_message ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="container bg-white p-3 rounded shadow">
        <table class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th width="5%">#</th>
                    <th width="50%"><i class="fa fa-user"></i> Nom d'utilisateur</th>
                    <th width="30%"><i class="fa fa-id-badge"></i> Rôle</th>
                    <th width="15%" class="text-center"><i class="fa fa-cogs"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    $count = 1;
                    while ($user = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td>
                                <?= getRoleIcon($user['role']) ?>
                                <strong class="ml-2"><?= htmlspecialchars($user['username']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td class="text-center">
                                <button type="button" 
                                        class="btn btn-danger btn-sm delete-user-btn" 
                                        data-id="<?= $user['id'] ?>" 
                                        data-username="<?= htmlspecialchars($user['username']) ?>">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center text-muted'>Aucun utilisateur trouvé.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="container bg-white p-3 rounded shadow mt-3">
        <h5 class="mb-3"><i class="fa fa-info-circle"></i> Légende des rôles</h5>
        <div class="row">
            <div class="col-md-6 col-lg-3 mb-2">
                <i class="fa fa-crown text-danger" style="font-size: 1.3em;"></i>
                <strong class="ml-2">Admin</strong>
                <p class="text-muted small mb-0 ml-4">Accès complet au système</p>
            </div>
            <div class="col-md-6 col-lg-3 mb-2">
                <i class="fa fa-briefcase text-primary" style="font-size: 1.3em;"></i>
                <strong class="ml-2">Patron</strong>
                <p class="text-muted small mb-0 ml-4">Gestion du restaurant</p>
            </div>
            <div class="col-md-6 col-lg-3 mb-2">
                <i class="fa fa-user-tie text-info" style="font-size: 1.3em;"></i>
                <strong class="ml-2">Gérant</strong>
                <p class="text-muted small mb-0 ml-4">Opérations quotidiennes</p>
            </div>
            <div class="col-md-6 col-lg-3 mb-2">
                <i class="fa fa-concierge-bell text-success" style="font-size: 1.3em;"></i>
                <strong class="ml-2">Serveur</strong>
                <p class="text-muted small mb-0 ml-4">Service et commandes</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fa fa-exclamation-triangle"></i> Confirmer la suppression
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="delete_user.php" id="delete-form">
                <div class="modal-body">
                    <p class="lead">Êtes-vous sûr de vouloir supprimer l'utilisateur <strong id="username-to-delete"></strong> ?</p>
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-circle"></i> <strong>Attention !</strong> Cette action est irréversible.
                    </div>
                    <input type="hidden" name="user_id" id="delete-user-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash"></i> Supprimer définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.fa-crown {
    font-size: 1.2em;
}

.fa-briefcase, .fa-user-tie, .fa-concierge-bell {
    font-size: 1.1em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

thead th {
    vertical-align: middle;
}

tbody td {
    vertical-align: middle;
}

.modal-header.bg-danger {
    border-bottom: none;
}

.close {
    text-shadow: none;
    opacity: 1;
}
</style>

<!-- Scripts JavaScript - IMPORTANT : À placer avant la fermeture de body -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script chargé !'); // Pour déboguer
    
    // Gestionnaire pour les boutons de suppression
    const deleteButtons = document.querySelectorAll('.delete-user-btn');
    console.log('Boutons trouvés:', deleteButtons.length); // Pour déboguer
    
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); // Empêcher tout comportement par défaut
            
            const userId = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');
            
            console.log('Clic détecté - ID:', userId, 'Username:', username); // Pour déboguer
            
            // Remplir le modal avec les infos
            document.getElementById('username-to-delete').textContent = username;
            document.getElementById('delete-user-id').value = userId;
            
            // Vérifier si jQuery et Bootstrap sont disponibles
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal !== 'undefined') {
                // Utiliser jQuery si disponible
                jQuery('#deleteModal').modal('show');
                console.log('Modal ouvert avec jQuery');
            } else if (typeof bootstrap !== 'undefined') {
                // Utiliser Bootstrap 5 natif si disponible
                const modalElement = document.getElementById('deleteModal');
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                console.log('Modal ouvert avec Bootstrap 5');
            } else {
                // Solution de secours avec confirmation simple
                console.warn('Bootstrap modal non disponible, utilisation de confirm()');
                if (confirm('Êtes-vous sûr de vouloir supprimer l\'utilisateur "' + username + '" ?\n\nCette action est irréversible.')) {
                    // Créer et soumettre un formulaire
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'delete_user.php';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_id';
                    input.value = userId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        });
    });

    // Auto-fermeture des alertes après 5 secondes
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            // Vérifier si jQuery est disponible
            if (typeof jQuery !== 'undefined') {
                jQuery(alert).fadeOut('slow');
            } else {
                // Solution native
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            }
        });
    }, 5000);
});
</script>

<?php
include "footer.php";
?>
