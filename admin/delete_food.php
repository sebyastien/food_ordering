<?php
include "connection.php";

$roles_autorises = ['admin', 'patron', 'gerant'];
include "auth_check.php";

// Vérifiez si l'ID a été passé dans l'URL
if (isset($_GET["id"])) {
    $id = mysqli_real_escape_string($link, $_GET["id"]);

    // Remplacez la requête DELETE par une requête UPDATE pour désactiver le plat
    mysqli_query($link, "UPDATE food SET is_active = 0 WHERE id = $id");
}

?>
<script type="text/javascript">
    window.location = "display_food.php";
</script>