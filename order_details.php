<?php
session_start();
include "connection.php";

$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon la page
include "auth_check.php";

include "header.php";

if (!isset($_GET['id'])) {
    echo "ID de commande manquant.";
    exit;
}

$order_id = (int)$_GET['id'];

$order_res = mysqli_query($link, "SELECT * FROM orders WHERE id = $order_id");
if (mysqli_num_rows($order_res) == 0) {
    echo "Commande introuvable.";
    exit;
}
$order = mysqli_fetch_assoc($order_res);

$items_res = mysqli_query($link, "SELECT * FROM order_items WHERE order_id = $order_id");
?>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Détails Commande #<?= $order['id'] ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-lg-12">

            <div class="card mb-3">
                <div class="card-header">
                    <strong>Informations Client</strong>
                </div>
                <div class="card-body">
                    <p><strong>Client :</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p><strong>Date :</strong> <?= $order['order_date'] ?></p>
                    <p><strong>Méthode de paiement :</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                    <p><strong>Total :</strong> <?= number_format($order['total_price'], 2) ?> €</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <strong>Articles commandés</strong>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Plat</th>
                                <th>Quantité</th>
                                <th>Prix Unitaire (€)</th>
                                <th>Sous-total (€)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($items_res && mysqli_num_rows($items_res) > 0) {
                                while ($item = mysqli_fetch_assoc($items_res)) {
                                    $subtotal = $item['quantity'] * $item['price'];
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($item['food_name']) . "</td>";
                                    echo "<td>" . (int)$item['quantity'] . "</td>";
                                    echo "<td>" . number_format($item['price'], 2) . "</td>";
                                    echo "<td>" . number_format($subtotal, 2) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>Aucun article trouvé.</td></tr>";
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
mysqli_close($link);
include "footer.php";
?>
