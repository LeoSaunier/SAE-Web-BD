<?php
include "scripts/connexionBDD.php";
if (isset($_POST['login']) && isset($_POST['password'])) {
    $user = ConnectionBDD::connect($_POST['login'], $_POST['password']);
    if ($user === null) {
        header('Location: page_connexion.php');
        exit();
    }
    header('Location: index.php');
} else {
    header('Location: page_connexion.php');
}
exit();
?>