<?php
session_start();
require "scripts/connexionBDD.php";
$result = ConnectionBDD::connect($_POST['identifiant'], $_POST['password']);
if ($result) {
    $_SESSION['user'] = $result;
    header('Location: index.php');
} else {
    header('Location: connexion.php');
}
exit();
?>