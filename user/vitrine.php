<?php
// vitrine.php

session_start();
include "header.php";
?>

<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }

    .hero-section {
        position: relative;
        height: 80vh;
        background-color: #a41a13;
        color: white;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .hero-section::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.2);
    }
    
    .hero-content {
        position: relative;
        z-index: 1;
        padding: 0 20px;
    }

    .hero-content h1 {
        font-size: 4em;
        margin: 0;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
    }

    .hero-content p {
        font-size: 1.5em;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.7);
    }

    .content-section {
        padding: 60px 20px;
        max-width: 1200px;
        margin: auto;
        text-align: center;
    }

    .content-section h2 {
        font-size: 2.5em;
        margin-bottom: 20px;
        color: #333;
    }
    
    .content-section p {
        color: #333;
        line-height: 1.6;
        font-size: 1.1em;
    }

    .cta-section {
        background-color: #a41a13;
        color: white;
        padding: 60px 20px;
        text-align: center;
    }
    
    .cta-section h2 {
        color: white;
        font-size: 2.5em;
        margin-bottom: 20px;
    }

    .cta-section p {
        color: #cacad1ff;
    }

    .cta-section form {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .cta-section input {
        padding: 10px;
        font-size: 1.2em;
        border-radius: 5px;
        border: none;
        width: 200px;
    }

    .cta-section button {
        padding: 12px 30px;
        font-size: 1.2em;
        border-radius: 5px;
        border: none;
        background-color: #333;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .cta-section button:hover {
        background-color: #000;
    }

    .order-options {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .order-option {
        flex: 1;
        min-width: 250px;
        max-width: 400px;
    }

    /* Styles Mobile Responsive */
    @media screen and (max-width: 768px) {
        /* Hero Section */
        .hero-section {
            height: 60vh;
            padding: 20px;
        }

        .hero-content h1 {
            font-size: 2em !important;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.1em !important;
            margin-top: 15px;
        }

        /* Content Section */
        .content-section {
            padding: 40px 20px;
        }

        .content-section h2 {
            font-size: 1.8em !important;
            margin-bottom: 15px;
        }

        .content-section p {
            font-size: 1em !important;
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            padding: 40px 20px;
        }

        .cta-section h2 {
            font-size: 1.8em !important;
            margin-bottom: 15px;
        }

        .cta-section p {
            font-size: 1em !important;
        }

        /* Order Options */
        .order-options {
            flex-direction: column;
            gap: 20px;
            padding: 0 10px;
        }

        .order-option {
            min-width: 100%;
            max-width: 100%;
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
        }

        .order-option h4 {
            font-size: 1.3em;
            margin-bottom: 10px;
        }

        .order-option p {
            font-size: 0.95em;
            margin-bottom: 15px;
        }

        /* Boutons */
        .cta-section button {
            width: 100%;
            padding: 15px 20px !important;
            font-size: 1.1em !important;
            margin-top: 10px;
        }

        .cta-section input {
            width: 100%;
            padding: 12px !important;
            font-size: 1em !important;
        }
    }

    /* Styles pour très petits écrans */
    @media screen and (max-width: 480px) {
        .hero-section {
            height: 50vh;
        }

        .hero-content h1 {
            font-size: 1.6em !important;
        }

        .hero-content p {
            font-size: 1em !important;
        }

        .content-section h2,
        .cta-section h2 {
            font-size: 1.5em !important;
        }

        .order-option {
            padding: 15px;
        }

        .order-option h4 {
            font-size: 1.2em;
        }
    }
</style>

<div class="vitrine-page">
    <div class="hero-section">
        <div class="hero-content">
            <h1>Bienvenue chez [Nom de votre restaurant]</h1>
            <p>Découvrez notre cuisine authentique et commandez directement à votre table.</p>
        </div>
    </div>

    <section class="content-section about-us">
        <h2>Notre Histoire</h2>
        <p>
            Nous sommes fiers de vous accueillir dans notre restaurant. Fondé avec la passion de la bonne cuisine, nous nous efforçons d'offrir une expérience culinaire inoubliable avec des ingrédients frais et des saveurs uniques.
        </p>
    </section>

   
    <section class="cta-section">
        <h2>Prêt à commander ?</h2>
        <p>Choisissez comment vous souhaitez passer votre commande.</p>
        <div class="order-options">
            <div class="order-option">
                <h4>Commander à domicile</h4>
                <p>Commandez en ligne et faites-vous livrer chez vous.</p>
                <form action="takeaway.php" method="GET">
                    <button type="submit">Commander à domicile</button>
                </form>
            </div>
        </div>
    </section>

</div>

<?php
include "footer.php";
?>