<?php
require "scripts/connexionBDD.php";
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
<?php require "header.php"; ?>
<article>
    <?php
    if (isset($_GET['heure']) && isset($_GET['id_cours'])) {
        if (isset($_GET['erreur'])) {
            echo "Erreur lors du choix du poney";
        }
        // Récupération des poneys disponibles pour l'heure et le cours
        $poneys = Database::getAvailablePonies($_GET['heure'], $_GET['id_cours']);
        if (count($poneys) > 0) {
            echo "<section id='section_poney_choisir'>";
            echo "<h1>Choisissez un poney pour le cours " . htmlspecialchars($_GET['id_cours']) .  " à " . htmlspecialchars($_GET['heure']) . "H</h1>";
            foreach ($poneys as $poney) {
                echo "<form class='pony' method='POST' action='reservation_cours.php'>";
                echo "<input type='hidden' name='id_poney' value='" . htmlspecialchars($poney['id_poney']) . "'>";
                echo "<input type='hidden' name='id_cours' value='" . htmlspecialchars($_GET['id_cours']) . "'>";
                echo "<input type='hidden' name='id_adherent' value='" . htmlspecialchars($_GET['id_adherent']) . "'>";
                echo "<input type='submit' name='reserve' value='Choisir ce poney'>";
                echo "</form>";
            }
            echo "</section>";
        } else {
            echo "<p>Il n'y a pas de poneys disponibles pour ce créneau.</p>";
        }
    } else {
        echo "<p>Nos poneys</p>";
    }
    ?>
</article>
</body>
</html>
