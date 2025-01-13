<?php
session_start();
require "scripts/connectionBDD.php";
$result = connectionBDD::Connected($_POST['identifiant'], $_POST['password']);
if ($result) {
    $_SESSION['user'] = $result;
    header('Location: index.php');
} else {
    header('Location: connexion.php');
}
exit();
?>