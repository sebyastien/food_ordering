<?php
include "connection.php";

$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon la page
include "auth_check.php";

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
                                <div>
                                    <button id="payment-button" type="submit" class="btn btn-lg btn-info btn-block" name="submit1">
                                        <span id="payment-button-amount">Submit</span>
                                    </button>
                                </div>
                                <br>
                                <div class="alert alert-success" role="alert" id="success" style="display: none">
                                    Category added successfully
                                </div>
                                <div class="alert alert-danger" role="alert" id="error" style="display: none">
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
                    <strong class="card-title">Categories</strong>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 0;
                            $res = mysqli_query($link, "SELECT * FROM food_categories");
                            while ($row = mysqli_fetch_array($res)) {
                                $count++;
                                echo "<tr>";
                                echo "<td>$count</td>";
                                echo "<td>{$row['food_categories']}</td>";
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
