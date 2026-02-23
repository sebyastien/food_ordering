<?php
include "connection.php";

<<<<<<< HEAD
$roles_autorises = ['admin', 'patron', 'gérant'];  // adapter selon la page
=======
$roles_autorises = ['admin', 'patron', 'gerant'];  // adapter selon la page
>>>>>>> 4470edb (maj)
include "auth_check.php";

$id = $_GET["id"];
mysqli_query($link, "delete from food_ingredients where id=$id");
?>

<script type="text/javascript">
    window.location = "food_ingredients.php";
</script>