<?php
session_start();
include "scripts/connexionBDD.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id_poney'])) {
        $id_poney = $_POST['id_poney'];
        $id_cours = $_POST['id_cours'];
        $id_adherent = $_POST['id_adherent'];
        try {
            Database::addStudentToCourse($id_cours, $id_adherent, $id_poney);
        } catch (Exception $e) {
            header('Location: page_poney_list.php?error=1');
            exit();
        }
        header('Location: page_horraires.php');
    } else {
        var_dump($_POST);
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>
