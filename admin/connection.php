<?php
$link=mysqli_connect("localhost","root","") or die (mysqli_error($link));
mysqli_select_db($link,"food_order") or die (mysqli_error($link));
// AJOUTER CETTE LIGNE
mysqli_set_charset($link, "utf8mb4"); 
?>