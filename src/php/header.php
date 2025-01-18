<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'];
?>

<header>
    <div id="img_container">
        <img src="img/logo_ffe.png" alt="logo_ffe">
    </div>
    <div id="center">
        <h1>Poney Club Grand Galop</h1>
        <ul id="options">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="tarifs.php">Tarifs</a></li>
            <li><a href="page_horraires.php">Horaires</a></li>
            <?php
            if ($role == "admin") {
                echo '<li><a href="page_creer_cours.php">Ajouter un cours</a></li>';
            }
            ?>
        </ul>
    </div>
    <div>
        <?php
        

        if (!isset($_SESSION['login_session']) && !isset($_SESSION['password_session'])) {
            echo '<a href="page_connexion.php" id="connect">Se connecter</a>';
        } else {

            $identifiant = $_SESSION['login_session'];
            echo '<a href="deconnexion.php" id="connect">'. $identifiant .'</a>';
        }
        ?>
    </div>
</header>

