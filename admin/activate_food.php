<?php
include "connection.php";

$roles_autorises = ['admin', 'patron', 'gerant'];
include "auth_check.php";

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($link, $_GET['id']);
    mysqli_query($link, "UPDATE food SET is_active = 1 WHERE id = $id");
}

?>
<script type="text/javascript">
    window.location = "display_food.php";
</script>