<?php
$cours = array();
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
    <div id="reservation">
        <h2>Cours</h2>
        <label for="coursS">Cours</label>
        <select name="coursS" id="coursS">
            <?php
                echo "<table>";
                echo "<tr> <th> Cours </th> <th> Annuler </th> </tr>";
            foreach ($cours as $key => $value) {
                echo "<tr>";
                echo " <td> <option value=" . $key . ">" . $value . "</option> </td>";
                echo " <td> <button> Annuler </button> </td>";
            }
            echo "</table>";
            ?>
        </select>
    </div>
</body>

</html>