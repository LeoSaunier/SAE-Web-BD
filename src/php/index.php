<?php
session_start();

// Vérification de la connexion et récupération des informations de l'utilisateur
include "scripts/connexionBDD.php";
if ((isset($_SESSION['login_session']) && isset($_SESSION['password_session'])) && ($_SESSION['login_session'] !== null && $_SESSION['password_session'] !== null)) {
    $user = new User($_SESSION['login_session'], $_SESSION['password_session']);
    $role = $user->getRole();
    $_SESSION['role'] = $role;  // Ajout de la variable de session
    $_SESSION['account'] = $user;
    $login = $_SESSION['login_session'];
} else {
    $role = 'guest';
    $_SESSION['role'] = $role;  // Ajout de la variable de session
    $login = null;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Poney Club Grand Galop</title>
</head>

<body>
    <?php
    require "header.php";
    ?>
    <article>
        <img src="img/baniere.png" alt="baniere">
        <section id="a_propos">
            <h2>À propos de nous</h2>
            <p class="centered">Situé en Sologne, le poney-club Grand Galop accueille les cavaliers selon leur niveau (débutant ou
                confirmé) pour pratiquer l'équitation dans des cours adaptés et sécurisé avec leur animal favori, parmi
                nos 30 poney disponibles. Nous proposons des cours particuliers et collectifs, avec une cotisation
                annuelle et un système de réservation en ligne simple et efficace.</p>
        </section>
        <section id="services">
            <h2>Nos services</h2>
            <section class="service">
                <h3>Cours collectifs</h3>
                <p>Jusqu’à 10 personnes, participez à ces cours pour une ambiance conviviale.</p>
            </section>
            <section class="service">
                <h3>Cours particuliers</h3>
                <p>Des séances d’équitation sur mesure pour une progression rapide.</p>
            </section>
        </section>

        <section id="tarifsSec">
            <a class="button_article" href="tarifs.php">Voir les tarifs</a>
        </section>
    </article>
    <footer>
        <?php
        // Affichage du rôle et du login de l'utilisateur, selon son niveau d'accès
        if ($role !== 'guest') {
            echo '<p>Bienvenue, ' . htmlspecialchars($role) . '</p>';
        } else {
            echo '<p>Bienvenue, visiteur!</p>';
        }
        ?>
    </footer>
</body>

</html>
