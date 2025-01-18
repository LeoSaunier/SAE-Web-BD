<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Inscription - Poney Club Grand Galop</title>
</head>

<body>
    <?php
    require "header.php";
    ?>
    <article>
        <section id="inscription_form" class="centered section_form">
            <h2>Inscription</h2>
            <p class="centered">Devenez adhérent et participez aux cours de poney dans notre poney club.</p>

            <form action="inscrition_creation.php" method="post" class="centered">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" required><br><br>

                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" placeholder="Votre nom" required><br><br>

                <label for="poids">Poids :</label>
                <input type="text" id="poids" name="poids" placeholder="Votre poids en kg" required><br><br>

                <label for="email">Email :</label>
                <input type="email" id="email" name="email" placeholder="Votre adresse email" required><br><br>

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" placeholder="Créez un mot de passe sécurisé" required><br><br>

                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez votre mot de passe" required><br><br>
                <a href="page_connexion.php">Déjà un compte ?</a>

                <button type="submit" class="button_article">S'inscrire</button>
            </form>
        </section>
    </article>
    <footer></footer>
</body>

</html>
