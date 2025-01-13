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
        <section id="connexion_form" class="centered section_form">
            <h2>Se connecter</h2>
            <form action="connexion.php" method="post">
                <label for="login">Login</label>
                <input type="text" name="login" id="login" required>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <button type="submit" class="button_article">Se connecter</button>
            </form>
        </section>
    </article>
</html>