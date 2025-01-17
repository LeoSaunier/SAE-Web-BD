<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Poney Club Grand Galop</title>
</head>

<body>
    <?php
    require "header.php";
    ?>
    <div id="table_tarifs" >
        <h2>Nos tarifs</h2>
        <table class="centered">
            <thead>
                <tr>
                    <th>Forfaits</th>
                    <th>1h</th>
                    <th>5h</th>
                    <th>10h</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>Débutant</td>
                <td>20€</td>
                <td>90€</td>
                <td>170€</td>
            </tr>
            <tr>
                <td>Intermédiaire</td>
                <td>25€</td>
                <td>110€</td>
                <td>200€</td>
            </tr>
            <tr>
                <td>Expert</td>
                <td>30€</td>
                <td>130€</td>
                <td>230€</td>
            </tr>
            </tbody>
        </table>
    </div>
    <p class="centered"><strong>Les tarifs sont sujet à changement</strong></p>

    <section id="adherantSec">
        <a class="button_article" href="inscription.php">Devenir adhérant</a>
    </section>
    <footer></footer>
</body>

</html>