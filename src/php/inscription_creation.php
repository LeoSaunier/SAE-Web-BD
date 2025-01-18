<?php
$pdo = new PDO('mysql:host=localhost;dbname=poney', 'root', 'root');
$id = $pdo->prepare('SELECT MAX(id_personne) FROM Personne');
$id->execute();
$id_personne = $id->fetch();
$id_personne = $id_personne['MAX(id_personne)'] + 1;
$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$date_naissance = $_POST['date-de-naissance'];
$poid = (int)$_POST['poids'];
$email = $_POST['email'];
$password = $_POST['password'];

$connexion = $pdo->prepare('INSERT INTO Connexion (identifiant, mot_de_passe) VALUES (:email, :password)');
$connexion->execute(['email' => $email, 'password' => $password]);

$personne = $pdo->prepare('INSERT INTO Personne (id_personne, nom, prenom, ddn, poids, niveau, identifiant) VALUES (:id_personne, :nom, :prenom, :date_naissance, :poid, :grade, :email)');
$personne->execute(['id_personne' => $id_personne, 'nom' => $nom, 'prenom' => $prenom, 'date_naissance' => $date_naissance, 'poid' => $poid, 'grade'=>'débutant', 'email' => $email]);

$id_adherant = $pdo->prepare('SELECT MAX(id_adherant) FROM Adherant');
$id_adherant->execute();
$id_adherant = $id_adherant->fetch();
$id_adherant = $id_adherant['MAX(id_adherant)'] + 1;

$adherant = $pdo->prepare('INSERT INTO Adherant (id_adherant, id_personne) VALUES (:id_adherant, :id_personne)');
$adherant->execute(['id_adherant' => $id_adherant, 'id_personne' => $id_personne]);

header('Location: page_connexion.php');
exit();
?>