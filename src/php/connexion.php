<?php
session_start();
include "scripts/connexionBDD.php";
if (isset($_POST['login']) && isset($_POST['password'])) {
    $user = new User($_POST['login'], $_POST['password']);
    if ($user->getRole() === 'guest') {
        echo "Couldn't connect";
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
