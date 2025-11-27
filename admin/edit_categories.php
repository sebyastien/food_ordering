<?php
include "connection.php";

$roles_autorises = ['admin', 'patron', 'gérant'];
include "auth_check.php";

include "header.php";

$id = intval($_GET["id"]);
$category_name = "";
$ordre = 0;

// Récupération de la catégorie existante
$res = mysqli_query($link, "SELECT * FROM food_categories WHERE id=$id");
if ($row = mysqli_fetch_array($res)) {
    $category_name = $row["food_categories"];
    $ordre = $row["ordre"];
}
?>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Edit Category</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Edit Category</strong>
                </div>
                <div class="card-body">
                    <div id="pay-invoice">
                        <div class="card-body">
                            <form name="form1" action="" method="post">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <div class="form-group">
                                    <label class="control-label mb-1">Category Name</label>
                                    <input id="food_category" name="food_category" type="text" class="form-control" placeholder="Enter Category" required value="<?php echo htmlspecialchars($category_name); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="control-label mb-1">Order (Position d'affichage)</label>
                                    <input id="ordre" name="ordre" type="number" class="form-control" placeholder="Ex: 1, 2, 3..." value="<?php echo $ordre; ?>">
                                    <small class="form-text text-muted">Plus le numéro est petit, plus la catégorie apparaîtra en premier</small>
                                </div>
                                <div>
                                    <button id="payment-button" type="submit" class="btn btn-lg btn-info btn-block" name="submit1">
                                        <span id="payment-button-amount">Update</span>
                                    </button>
                                </div>
                                <br>
                                <div class="alert alert-success" role="alert" id="success" style="display: none">
                                    Category updated successfully
                                </div>
                                <div class="alert alert-danger" role="alert" id="error" style="display: none">
                                    Duplicate category found
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($_POST["submit1"])) {
    $new_category = mysqli_real_escape_string($link, $_POST["food_category"]);
    $ordre = intval($_POST["ordre"]);
    $id = intval($_POST["id"]);

    $res = mysqli_query($link, "SELECT * FROM food_categories WHERE food_categories='$new_category' AND id!=$id");
    if (mysqli_num_rows($res) > 0) {
        echo "<script>document.getElementById('error').style.display = 'block';</script>";
    } else {
        mysqli_query($link, "UPDATE food_categories SET food_categories='$new_category', ordre=$ordre WHERE id=$id");
        echo "<script>document.getElementById('success').style.display = 'block';</script>";
        echo "<script>setTimeout(() => window.location = 'food_categories.php', 1000);</script>";
    }
}
include "footer.php";
?>