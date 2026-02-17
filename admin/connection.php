<?php
$link = mysqli_connect("localhost","fooduser","password123","food_order");

if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

