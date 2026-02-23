<?php
<<<<<<< HEAD
$link = mysqli_connect("localhost","fooduser","password123","food_order");

if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

=======
$link=mysqli_connect("localhost","root","") or die (mysqli_error($link));
mysqli_select_db($link,"food_order") or die (mysqli_error($link));
?>
>>>>>>> 4470edb (maj)
