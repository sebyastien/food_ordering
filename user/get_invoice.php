<?php
// user/get_invoice.php

session_start();
include "header.php";
?>

<div class="container" style="max-width: 700px; margin: 50px auto; padding: 30px; border: 1px solid #ddd; border-radius: 8px;">
    <h2>Télécharger une facture</h2>
    <p>Entrez votre numéro de commande pour télécharger votre facture.</p>
    
    <form action="facture.php" method="GET">
        <input type="text" name="order_number" placeholder="Numéro de commande (ex: CMD123ABC)" required
               style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; margin-bottom: 20px;">
        <button type="submit" 
                style="padding: 12px 30px; background-color: #a41a13; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 1.1em; font-weight: 700;">
            Télécharger la facture
        </button>
    </form>
</div>

<?php
include "footer.php";
?>