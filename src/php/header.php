<header>
    <?php
    include "scripts/connexionBDD.php";
    ?>
    <div id="img_container">
        <img src="img/logo_ffe.png" alt="logo_ffe">
    </div>
    <div id="center">
        <h1>Poney Club Grand Galop</h1>
        <ul id="options">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="tarifs.php">Tarifs</a></li>
        </ul>
    </div>
    <div>
        <?php
        $identifiant = ConnectionBDD::getInstance()->getIdentifiant();

        if ($identifiant == null) {
            echo '<a href="page_connexion.php" id="connect">Se connecter</a>';
        } else {
            echo '<a href="page_profil.php" id="connect">'. $identifiant .'</a>';
        }
        ?>
    </div>
</header>

