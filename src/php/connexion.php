<?php
session.start();
$_SESSION= connectionBDD->Connected($_POST['identifiant'], $_POST['password']);
header('Location: index.php');
?>