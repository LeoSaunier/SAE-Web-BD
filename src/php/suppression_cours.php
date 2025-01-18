<?php
session_start();
include "scripts/connexionBDD.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification si la durée est renseignée
    if (isset($_POST['id_cours']) && isset($_POST['role'])) {
        $id_cours = $_POST['id_cours'];
        $role = $_POST['role'];
        try {
            Database::deleteCourse($id_cours, $role);
            header('Location: page_horraires.php');
        } catch (Exception $e) {
            error_log($e->getMessage());
            header('Location: page_horraires.php?error=2');
            exit();
        }
    }
} else {
    // Requête non POST
    echo 'Requête invalide';
    header('Location: index.php');
    exit();
}
?>
