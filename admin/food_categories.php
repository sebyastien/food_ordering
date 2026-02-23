<?php
include "connection.php";

<<<<<<< HEAD
$roles_autorises = ['admin', 'patron', 'gérant'];
include "auth_check.php";

// ⚠️ IMPORTANT : Gérer les actions AVANT d'inclure header.php pour éviter "headers already sent"

// Gestion du déplacement des catégories
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    // Récupérer l'ordre actuel
    $res = mysqli_query($link, "SELECT ordre FROM food_categories WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    $current_ordre = $row['ordre'];
    
    if ($action == 'move_up') {
        // Trouver la catégorie juste au-dessus
        $res = mysqli_query($link, "SELECT id, ordre FROM food_categories WHERE ordre < $current_ordre ORDER BY ordre DESC LIMIT 1");
        if ($row_above = mysqli_fetch_assoc($res)) {
            // Échanger les ordres
            mysqli_query($link, "UPDATE food_categories SET ordre={$row_above['ordre']} WHERE id=$id");
            mysqli_query($link, "UPDATE food_categories SET ordre=$current_ordre WHERE id={$row_above['id']}");
        }
    } elseif ($action == 'move_down') {
        // Trouver la catégorie juste en-dessous
        $res = mysqli_query($link, "SELECT id, ordre FROM food_categories WHERE ordre > $current_ordre ORDER BY ordre ASC LIMIT 1");
        if ($row_below = mysqli_fetch_assoc($res)) {
            // Échanger les ordres
            mysqli_query($link, "UPDATE food_categories SET ordre={$row_below['ordre']} WHERE id=$id");
            mysqli_query($link, "UPDATE food_categories SET ordre=$current_ordre WHERE id={$row_below['id']}");
        }
    }
    
    header("Location: food_categories.php");
    exit;
}

// Ajout d'une nouvelle catégorie
if (isset($_POST["submit1"])) {
    $category = mysqli_real_escape_string($link, $_POST["food_category"]);
    $ordre = intval($_POST["ordre"]);
    
    $res = mysqli_query($link, "SELECT * FROM food_categories WHERE food_categories='$category'");
    if (mysqli_num_rows($res) > 0) {
        $error_message = true;
    } else {
        mysqli_query($link, "INSERT INTO food_categories(food_categories, ordre) VALUES('$category', $ordre)");
        $success_message = true;
        echo "<script>setTimeout(() => window.location = 'food_categories.php', 1000);</script>";
    }
}

=======
$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon la page
include "auth_check.php";

>>>>>>> 4470edb (maj)
include "header.php";
?>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Add / Edit Categories</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Add / Edit Categories</strong>
                </div>
                <div class="card-body">
                    <div id="pay-invoice">
                        <div class="card-body">
                            <form name="form1" action="" method="post">
                                <div class="form-group">
                                    <label class="control-label mb-1">Category Name</label>
                                    <input id="food_category" name="food_category" type="text" class="form-control" placeholder="Enter Category" required>
                                </div>
<<<<<<< HEAD
                                <div class="form-group">
                                    <label class="control-label mb-1">Order (Position d'affichage)</label>
                                    <input id="ordre" name="ordre" type="number" class="form-control" placeholder="Ex: 1, 2, 3..." value="0">
                                    <small class="form-text text-muted">Plus le numéro est petit, plus la catégorie apparaîtra en premier</small>
                                </div>
=======
>>>>>>> 4470edb (maj)
                                <div>
                                    <button id="payment-button" type="submit" class="btn btn-lg btn-info btn-block" name="submit1">
                                        <span id="payment-button-amount">Submit</span>
                                    </button>
                                </div>
                                <br>
<<<<<<< HEAD
                                <div class="alert alert-success" role="alert" id="success" style="<?= isset($success_message) ? 'display: block' : 'display: none' ?>">
                                    Category added successfully
                                </div>
                                <div class="alert alert-danger" role="alert" id="error" style="<?= isset($error_message) ? 'display: block' : 'display: none' ?>">
=======
                                <div class="alert alert-success" role="alert" id="success" style="display: none">
                                    Category added successfully
                                </div>
                                <div class="alert alert-danger" role="alert" id="error" style="display: none">
>>>>>>> 4470edb (maj)
                                    Duplicate category found
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table of categories -->
            <div class="card">
                <div class="card-header">
<<<<<<< HEAD
                    <strong class="card-title">Categories (triées par ordre)</strong>
=======
                    <strong class="card-title">Categories</strong>
>>>>>>> 4470edb (maj)
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
<<<<<<< HEAD
                                <th>Order</th>
                                <th>↑↓</th>
=======
>>>>>>> 4470edb (maj)
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 0;
<<<<<<< HEAD
                            $res = mysqli_query($link, "SELECT * FROM food_categories ORDER BY ordre ASC, id ASC");
=======
                            $res = mysqli_query($link, "SELECT * FROM food_categories");
>>>>>>> 4470edb (maj)
                            while ($row = mysqli_fetch_array($res)) {
                                $count++;
                                echo "<tr>";
                                echo "<td>$count</td>";
                                echo "<td>{$row['food_categories']}</td>";
<<<<<<< HEAD
                                echo "<td><span class='badge badge-primary'>{$row['ordre']}</span></td>";
                                echo "<td>";
                                echo "<a href='?action=move_up&id={$row['id']}' style='color:blue; margin-right:10px; text-decoration:none; font-size:18px;' title='Monter'>▲</a>";
                                echo "<a href='?action=move_down&id={$row['id']}' style='color:blue; text-decoration:none; font-size:18px;' title='Descendre'>▼</a>";
                                echo "</td>";
=======
>>>>>>> 4470edb (maj)
                                echo "<td><a href='edit_categories.php?id={$row['id']}' style='color:green'>Edit</a></td>";
                                echo "<td><a href='delete_categories.php?id={$row['id']}' style='color:red' onclick=\"return confirm('Confirm delete?');\">Delete</a></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
<<<<<<< HEAD
include "footer.php";
?>
=======
if (isset($_POST["submit1"])) {
    $category = mysqli_real_escape_string($link, $_POST["food_category"]);
    $res = mysqli_query($link, "SELECT * FROM food_categories WHERE food_categories='$category'");
    if (mysqli_num_rows($res) > 0) {
        echo "<script>document.getElementById('error').style.display = 'block';</script>";
    } else {
        mysqli_query($link, "INSERT INTO food_categories(food_categories) VALUES('$category')");
        echo "<script>document.getElementById('success').style.display = 'block';</script>";
        echo "<script>setTimeout(() => window.location = 'food_categories.php', 1000);</script>";
    }
}
include "footer.php";
?>
>>>>>>> 4470edb (maj)
