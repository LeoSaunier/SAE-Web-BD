<?php 
require "../scripts/date.php";
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="">
    <title>Poney Club Grand Galop</title>
</head>

<body>
    <?php
    require "header.php";
    ?>
    <div id="reservation">
        <h2>Réservations</h2>
        <label for="yearL">Année</label>
        <select name="yearS" id="yearS">
            <?php 
            if (date("m") == "12") {
                echo "<option value=" . getYear() . ">" . getYear() . "</option>";
                echo "<option value=" . getNextYear() . ">" . getNextYear() . "</option>";
            } else {
                echo "<option value=" . getYear() . ">" . getYear() . "</option>";
            }
            ?>
        </select>
        <label for="MonthL">Mois</label>
        <select name="MonthS" id="MonthS">
           <?php
           $month = getRemainingMonths($year);
           foreach ($month as $key => $value) {
               echo "<option value=" . $key . ">" . $value . "</option>";
           }
           ?>
        </select>
        <label for="DaysL">Jours</label>
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
        </select>
        <label for="CoursL">Cours</label>
        <select name="CoursS" id="CoursS">
            <?php 
            ?>
        </select>
    </div>
</body>

</html>