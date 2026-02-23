
<?php
include "connection.php";

<<<<<<< HEAD
$roles_autorises = ['admin', 'patron', 'gérant'];  // adapter selon la page
=======
$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon la page
>>>>>>> 4470edb (maj)
include "auth_check.php";

include "header.php";

$id = intval($_GET["id"]);
$category_name = "";

// Récupération de la catégorie existante
$res = mysqli_query($link, "SELECT * FROM food_ingredients WHERE id=$id");
if ($row = mysqli_fetch_array($res)) {
    $category_name = $row["food_ingredients"];
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
                                    <input id="food_ingredients" name="ingredients_name" type="text" class="form-control" placeholder="Enter Category" required value="<?php echo htmlspecialchars($category_name); ?>">
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
    $new_category = mysqli_real_escape_string($link, $_POST["ingredients_name"]);
    $id = intval($_POST["id"]);

    $res = mysqli_query($link, "SELECT * FROM food_ingredients WHERE food_ingredients='$new_category' AND id!=$id");
    if (mysqli_num_rows($res) > 0) {
        echo "<script>document.getElementById('error').style.display = 'block';</script>";
    } else {
        mysqli_query($link, "UPDATE food_ingredients SET food_ingredients='$new_category' WHERE id=$id");
        echo "<script>document.getElementById('success').style.display = 'block';</script>";
        echo "<script>setTimeout(() => window.location = 'food_ingredients.php', 1000);</script>";
    }
}
include "footer.php";
?>

