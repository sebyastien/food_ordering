<?php
include "connection.php";

$roles_autorises = ['admin', 'patron', 'gérant'];
include "auth_check.php";

include "header.php";
?>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Display Added Foods</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <a href="display_food.php" class="btn btn-info">Voir les Plats Actuels</a>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>food image</th>
                        <th>food name</th>
                        <th>food category</th>
                        <th>food description</th>
                        <th>food original price</th>
                        <th>food discount price</th>
                        <th>food availibility</th>
                        <th>food veg / nonveg</th>
                        <th>food ingredients</th>
                        <th>Réactiver</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 0;
                    $res = mysqli_query($link, "SELECT * FROM food WHERE is_active = 0");
                    while ($row = mysqli_fetch_array($res)) {
                        $count++;
                        echo "<tr>";
                        echo "<td>$count</td>";
                        echo "<td><img src='{$row['food_image']}' height='100' width='100'></td>";
                        echo "<td>{$row['food_name']}</td>";
                        echo "<td>{$row['food_category']}</td>";
                        echo "<td>{$row['food_description']}</td>";
                        echo "<td>{$row['food_original_price']}</td>";
                        echo "<td>{$row['food_discount_price']}</td>";
                        echo "<td>{$row['food_avaibility']}</td>";
                        echo "<td>{$row['food_veg_nonveg']}</td>";
                        echo "<td>{$row['food_ingredients']}</td>";
                        echo "<td><a href='activate_food.php?id={$row['id']}' style='color:blue' onclick=\"return confirm('Confirmer la réactivation ?');\">Réactiver</a></td>";
                        echo "<td><a href='delete_food_permanently.php?id={$row['id']}' style='color:red' onclick=\"return confirm('Confirmer la suppression définitive ?');\">Supprimer</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include "footer.php";
?>