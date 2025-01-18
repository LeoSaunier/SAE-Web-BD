<?php
session_start();
include "scripts/connexionBDD.php";
if (isset($_POST['login']) && isset($_POST['password'])) {
    $user = ConnectionBDD::connect($_POST['login'], $_POST['password']);
    if ($user === null) {
        echo "Couldn't connect to database";
        header('Location: page_connexion.php');
        exit();
    } else {
        echo "Connected";
        $_SESSION['login_session'] = $_POST['login'];
        $_SESSION['password_session'] = $_POST['password'];
        header('Location: index.php');
        exit();
    }
} else {
    echo 'No information found';
    header('Location: page_connexion.php');
    exit();
}
?>
