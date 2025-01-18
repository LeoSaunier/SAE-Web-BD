<?php
session_start();
include "scripts/connexionBDD.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification si la durée est renseignée
    if (!empty($_POST['duree'])) {
        // Récupération des données
        $date = $_POST['date_cours'];
        $heure_deb = intval($_POST['heure_deb']); // Conversion en entier
        $duree = intval($_POST['duree']);
        $recurrence_exists = isset($_POST['recurrence']);

        // Calcul de l'heure de fin
        $heure_fin = $heure_deb + $duree;

        // Vérification de la plage horaire
        if ($heure_fin <= 21) { // Comparer avec 21 (21h)
            echo "Date : $date, Heure début : $heure_deb, Heure fin : $heure_fin";

            if ($recurrence_exists) {
                $recurrence = $_POST['recurrence'];
                try {
                    Database::createGroupLesson($date, $heure_deb, $heure_fin, $recurrence);
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    header('Location: page_creer_cours.php?error=1');
                    exit();
                }
            } else {
                try {
                    Database::createPrivateLesson($date, $heure_deb, $heure_fin);
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    header('Location: page_creer_cours.php?error=1');
                    exit();
                }
            }

            // Redirection après succès
            header('Location: page_horraires.php');
            exit();
        } else {
            // Horaire invalide
            header('Location: page_creer_cours.php');
            exit();
        }
    } else {
        // Durée non spécifiée
        header('Location: page_creer_cours.php');
        exit();
    }
} else {
    // Requête non POST
    echo 'Requête invalide';
    header('Location: index.php');
    exit();
}
?>
