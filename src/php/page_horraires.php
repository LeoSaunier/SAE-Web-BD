<?php
$user = $_SESSION["user"];
$cours = $user->getCours();
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
    <section class="centered">
        <h2>Les horraires des cours</h2>
        <section class="centered">
            <table class="centered">
                <thead>
                <tr>
                    <th>Heures</th>
                    <th>Lundi</th>
                    <th>Mardi</th>
                    <th>Mercredi</th>
                    <th>Jeudi</th>
                    <th>Vendredi</th>
                </tr>
                </thead>
                <tbody>
                <?php
                // Créer un tableau des heures disponibles
                $heures = [];
                foreach ($cours as $cours_du_jour) {
                    $heure_debut = $cours_du_jour['heure_debut'];
                    if (!in_array($heure_debut, $heures)) {
                        $heures[] = $heure_debut;
                    }
                }

                // Trier les heures
                sort($heures);

                // Affichage des lignes pour chaque heure
                foreach ($heures as $heure) {
                    echo "<tr>";
                    echo "<td>$heure</td>";

                    // Affichage des cours pour chaque jour à l'heure donnée
                    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
                    foreach ($jours as $jour) {
                        $cours_du_jour = array_filter($cours, function($c) use ($jour, $heure) {
                            // Vérifier si le cours correspond à l'heure et au jour
                            return $c['heure_debut'] == $heure && $c['type_cours'] == $jour;
                        });

                        if (count($cours_du_jour) > 0) {
                            // Afficher le nom du cours
                            $cours_du_jour = array_values($cours_du_jour)[0];
                            echo "<td>" . $cours_du_jour['type_cours'] . " - " . $cours_du_jour['nb_personnes'] . " pers.</td>";
                        } else {
                            echo "<td>-</td>"; // Si pas de cours à cette heure
                        }
                    }
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </section>

    </section>
</article>
</html>

