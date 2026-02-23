<?php
session_start();
include "connection.php";

<<<<<<< HEAD
$roles_autorises = ['admin', 'patron', 'gérant'];
=======
$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon la page
>>>>>>> 4470edb (maj)
include "auth_check.php";

include "header.php";

// Traitement du formulaire
if (isset($_POST["submit1"])) {
    $food_name = mysqli_real_escape_string($link, $_POST["food_name"]);
    $food_category = mysqli_real_escape_string($link, $_POST["food_category"]);
    $food_description = mysqli_real_escape_string($link, $_POST["food_description"]);
    $food_original_price = mysqli_real_escape_string($link, $_POST["food_original_price"]);
<<<<<<< HEAD
    $food_veg_nonveg = mysqli_real_escape_string($link, $_POST["food_veg_nonveg"]);
    $ingredients = mysqli_real_escape_string($link, $_POST["ingredients"] ?? '');
=======
    $food_discount_price = mysqli_real_escape_string($link, $_POST["food_discount_price"]);
    $food_avaibility = mysqli_real_escape_string($link, $_POST["food_avaibility"]);
    $food_veg_nonveg = mysqli_real_escape_string($link, $_POST["food_veg_nonveg"]);
    $ingredients = mysqli_real_escape_string($link, $_POST["ingredients"]); // Modifié pour traiter une seule chaîne de caractères
>>>>>>> 4470edb (maj)

    // Gestion de l'image croppée
    $dst1 = "";
    if (isset($_SESSION["image_name01"])) {
        $src = 'temp_photo/' . $_SESSION["image_name01"];
        $dst1 = 'images/' . $_SESSION["image_name01"];
        copy($src, $dst1);
        unset($_SESSION["image_name01"]);
    }

<<<<<<< HEAD
    // Vérifie si l'aliment existe déjà
=======
    // Vérifie si l’aliment existe déjà
>>>>>>> 4470edb (maj)
    $res = mysqli_query($link, "SELECT * FROM food WHERE food_name='$food_name'");
    if (mysqli_num_rows($res) > 0) {
        $error = "Duplicate Food found";
    } else {
<<<<<<< HEAD
        // INSERT avec les noms de colonnes explicites (IMPORTANT!)
        $query = "INSERT INTO food (food_name, food_category, food_description, food_original_price, 
                  food_veg_nonveg, food_ingredients, food_image) 
                  VALUES ('$food_name', '$food_category', '$food_description', '$food_original_price', 
                  '$food_veg_nonveg', '$ingredients', '$dst1')";
        
        mysqli_query($link, $query) or die(mysqli_error($link));
=======
        mysqli_query($link, "INSERT INTO food VALUES(NULL, '$food_name', '$food_category', '$food_description', '$food_original_price', '$food_discount_price', '$food_avaibility', '$food_veg_nonveg', '$ingredients','$dst1')") or die(mysqli_error($link));
>>>>>>> 4470edb (maj)
        $success = "Food added successfully";
        echo "<script>setTimeout(() => window.location = 'add_new_food.php', 1000);</script>";
    }
}
?>

<link rel="stylesheet" href="cropping_css/croppie.css"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Add New Food</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Add New Food</strong>
                </div>
                <div class="card-body">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php elseif (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Image</label><br>
                            <div id="uploaded_image" style="cursor:pointer;" onclick="document.getElementById('upload_image').click();">
                                <img src="camera.jpg" id="image1" height="100" width="100">
                            </div>
                            <input type="file" name="upload_image" id="upload_image" style="display:none;" required>
                        </div>

                        <div class="form-group">
                            <label>Food Name</label>
                            <input type="text" name="food_name" class="form-control" placeholder="Enter Food Name" required>
                        </div>

                        <div class="form-group">
                            <label>Food Category</label>
                            <select name="food_category" class="form-control">
                                <?php
                                $res = mysqli_query($link, "SELECT * FROM food_categories ORDER BY food_categories ASC");
                                while ($row = mysqli_fetch_array($res)) {
                                    echo "<option>{$row['food_categories']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Food Description</label>
                            <textarea name="food_description" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
<<<<<<< HEAD
                            <label>Price</label>
=======
                            <label>Original Price</label>
>>>>>>> 4470edb (maj)
                            <input type="text" name="food_original_price" class="form-control" required>
                        </div>

                        <div class="form-group">
<<<<<<< HEAD
=======
                            <label>Discount Price</label>
                            <input type="text" name="food_discount_price" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Availability</label>
                            <select name="food_avaibility" class="form-control">
                                <option>Yes</option>
                                <option>No</option>
                            </select>
                        </div>

                        <div class="form-group">
>>>>>>> 4470edb (maj)
                            <label>Veg / NonVeg</label>
                            <select name="food_veg_nonveg" class="form-control">
                                <option>Veg</option>
                                <option>NonVeg</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Ingredients</label>
                            <textarea name="ingredients" class="form-control" placeholder="Entrez les ingrédients ici"></textarea>
                        </div>

                        <div>
                            <button type="submit" name="submit1" class="btn btn-info btn-block">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="uploadimageModal" class="modal" role="dialog">
    <div class="modal-dialog" style="width:auto">
        <div class="modal-content" style="width:1000px;">
            <div class="modal-header">
                <h4 class="modal-title">Upload & Crop Image</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <div id="image_demo" style="width:350px;"></div>
                <br>
                <button class="btn btn-success crop_image">Crop & Upload Image</button>
            </div>
        </div>
    </div>
</div>

<script src="cropping_js/croppie.js"></script>
<script src="cropping_js/exif.js"></script>
<script>
    $(document).ready(function () {
        let $image_crop = $('#image_demo').croppie({
            enforceBoundary: false,
            enableOrientation: true,
            viewport: { width: 270, height: 230, type: 'square' },
            boundary: { width: 300, height: 250 }
        });

        $('#upload_image').on('change', function () {
            let reader = new FileReader();
            reader.onload = function (event) {
                $image_crop.croppie('bind', { url: event.target.result });
            }
            reader.readAsDataURL(this.files[0]);
            $('#uploadimageModal').modal('show');
        });

        $('.crop_image').click(function () {
            $image_crop.croppie('result', {
                type: 'canvas',
                size: 'viewport'
            }).then(function (response) {
                $.ajax({
                    url: "crop_and_upload01.php",
                    type: "POST",
                    data: { image: response },
                    success: function (data) {
                        $('#uploadimageModal').modal('hide');
                        $('#uploaded_image').html(data);
                    }
                });
            });
        });
    });
</script>

<?php include "footer.php"; ?>