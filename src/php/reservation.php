<?php 
require "./scripts/date.php";
?>

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
    <article>
        <section id="reservation_form" class="centered">
            <h2>Réservations</h2>
            <p>Bénéficiez d'un cours particulier en réservant un cours et améliorez votre niveau plus rapidement</p>
            <form action="index.php" method="post">
                <label for="yearS">Année</label>
                <select name="yearS" id="yearS">
                    <?php
                    if (date("m") == "12") {
                        echo "<option value=" . getYear() . ">" . getYear() . "</option>";
                        echo "<option value=" . getNextYear() . ">" . getNextYear() . "</option>";
                    } else {
                        echo "<option value=" . getYear() . ">" . getYear() . "</option>";
                    }
                    ?>
                </select><br><br>
                <label for="MonthS">Mois</label>
                <select name="MonthS" id="MonthS">
                    <?php
                    $month = getRemainingMonths(getYear());
                    foreach ($month as $key => $value) {
                        echo "<option value=" . $key . ">" . $value . "</option>";
                    }
                    ?>
                </select><br><br>
                <label for="DaysS">Jour</label>
                <select name="DaysS" id="DaysS">
                    <?php
                    $days = getNext30Days();
                    foreach ($days as $key => $value) {
                        if ($key == "currentMonth") {
                            foreach ($value as $day) {
                                echo "<option value=" . $day . ">" . $day . "</option>";
                            }
                        }
                    }
                    ?>
                </select><br><br>
                <!--        <label for="CoursS">Cours</label>-->
                <!--        <select name="CoursS" id="CoursS">-->
                <!--            --><?php //
                //            ?>
                <!--        </select>-->
                <button type="submit" class="button_article">Réserver</button>
            </form>
        </section>
    </article>
</body>

</html>