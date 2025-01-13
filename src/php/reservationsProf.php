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
    require_once "srcipts/connectionBDD.php";

    // Vérifier si un moniteur est connecté
    session_start();
    if (!isset($_SESSION['moniteur_id'])) {
        echo "Vous devez être connecté en tant que moniteur pour accéder à cette page.";
        exit;
    }

    // Créer une instance de la classe et récupérer les cours
    $moniteurId = $_SESSION['moniteur_id'];
    $connection = new connectionBDD();
    $cours = $connection->getCoursesByInstructor($connection->pdo, $moniteurId);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
        $courseId = $_POST['course_id'];
        try {
            $connection->deleteCourse($connection->pdo, $courseId);
            echo "<p>Le cours a été supprimé avec succès.</p>";
            // Recharger les cours après suppression
            $cours = $connection->getCoursesByInstructor($connection->pdo, $moniteurId);
        } catch (Exception $e) {
            echo "<p>Erreur lors de la suppression : " . $e->getMessage() . "</p>";
        }
    }
    ?>
    
    <div id="reservation">
        <h2>Vos Cours</h2>
        <table>
            <tr>
                <th>Cours</th>
                <th>Date</th>
                <th>Heure Début</th>
                <th>Heure Fin</th>
                <th>Nombre d'élèves</th>
                <th>Action</th>
            </tr>
            <?php
            if (!empty($cours)) {
                foreach ($cours as $coursInfo) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($coursInfo['id_cours']) . "</td>";
                    echo "<td>" . htmlspecialchars($coursInfo['date_cours']) . "</td>";
                    echo "<td>" . htmlspecialchars($coursInfo['heure_debut']) . "</td>";
                    echo "<td>" . htmlspecialchars($coursInfo['heure_fin']) . "</td>";
                    echo "<td>" . htmlspecialchars($coursInfo['students']) . "</td>";
                    echo "<td>";
                    echo "<form method='POST' action=''>";
                    echo "<input type='hidden' name='course_id' value='" . htmlspecialchars($coursInfo['id_cours']) . "'>";
                    echo "<button type='submit' name='delete_course'>Supprimer</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Aucun cours assigné pour le moment.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>

</html>
