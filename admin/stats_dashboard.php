<?php
// Vérification d'accès
<<<<<<< HEAD
$roles_autorises = ['admin', 'patron', 'gérant'];
=======
$roles_autorises = ['admin', 'patron', 'gerant'];
>>>>>>> 4470edb (maj)
include "auth_check.php";
include "connection.php"; // connexion MySQL

// Total des ventes
$sql_total_sales = "
    SELECT SUM(oi.quantity * food_discount_price) AS total_sales
    FROM order_items oi
    INNER JOIN food f ON oi.food_id = f.id
";
$result_total_sales = mysqli_query($link, $sql_total_sales);
$row_total_sales = mysqli_fetch_assoc($result_total_sales);
$total_sales = $row_total_sales['total_sales'] ?? 0;

// Nombre total de commandes
$sql_total_orders = "SELECT COUNT(*) AS total_orders FROM orders";
$result_total_orders = mysqli_query($link, $sql_total_orders);
$row_total_orders = mysqli_fetch_assoc($result_total_orders);
$total_orders = $row_total_orders['total_orders'] ?? 0;

// Plat le plus vendu
$sql_top_food = "
    SELECT f.name, SUM(oi.quantity) AS total_qty
    FROM order_items oi
    INNER JOIN food f ON oi.food_id = f.id
    GROUP BY f.name
    ORDER BY total_qty DESC
    LIMIT 1
";
$result_top_food = mysqli_query($link, $sql_top_food);
$row_top_food = mysqli_fetch_assoc($result_top_food);
$top_food = $row_top_food['name'] ?? "Aucun";
$top_food_qty = $row_top_food['total_qty'] ?? 0;

// Commandes en attente
$sql_pending_orders = "SELECT COUNT(*) AS pending_orders FROM orders WHERE status = 'En attente'";
$result_pending_orders = mysqli_query($link, $sql_pending_orders);
$row_pending_orders = mysqli_fetch_assoc($result_pending_orders);
$pending_orders = $row_pending_orders['pending_orders'] ?? 0;
?>

<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Total ventes</h5>
                <h2><?php echo number_format($total_sales, 2); ?> €</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5>Commandes</h5>
                <h2><?php echo $total_orders; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5>Plat le plus vendu</h5>
                <h2><?php echo $top_food; ?> (<?php echo $top_food_qty; ?>)</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5>En attente</h5>
                <h2><?php echo $pending_orders; ?></h2>
            </div>
        </div>
    </div>
</div>
