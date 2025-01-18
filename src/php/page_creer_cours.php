<?php
session_start();
$curr_date = date("Y-m-d");
if (isset($_SESSION['login_session']) && isset($_SESSION['password'])) {
    if (Database::getRole($_SESSION['login_session'], $_SESSION['password_session']) != "admin") {
        header('Location: index.php');
        exit();
    }
}
?>

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
    <section id="create_cours" class="centered section_form">
        <h2>Inscription</h2>
        <h2 class="centered">Créer un cours</h2>
        <form method="post" id="type_cours">
            <div>
                <input type="radio" id="type_cours_publique" name="type_cours" value="publique" onclick="this.form.submit()"
                    <?php if (!isset($_POST['type_cours']) || $_POST['type_cours'] == 'publique') echo 'checked'; ?>>
                <label for="type_cours_publique">Publique</label>
            </div>
            <div>
                <input type="radio" id="type_cours_prive" name="type_cours" value="prive" onclick="this.form.submit()"
                    <?php if (isset($_POST['type_cours']) && $_POST['type_cours'] == 'prive') echo 'checked'; ?>>
                <label for="type_cours_prive">Privée</label>
            </div>
        </form>

        <form action="creer_cours.php" method="post" class="centered">
            <label for="date_cours">Date du cours</label>
            <input type="date" id="date_cours" name="date_cours"
                   value="<?php echo htmlspecialchars($curr_date); ?>"
                   min="<?php echo htmlspecialchars($curr_date); ?>"/>

            <label for="heure_deb">Heure de début</label>
            <input type="time" id="heure_deb" name="heure_deb"
                   value="<?php echo htmlspecialchars('08:00'); ?>" required/>

            <label for="duree">Durée du cours </label>
            <select id="duree" name="duree" required="required">
                <option value="">--Choisissez une durée</option>
                <option value="1">1H</option>
                <option value="2">2H</option>
            </select>

            <?php
            if (!isset($_POST["type_cours"]) || $_POST["type_cours"] == "publique") {
                echo "<label for='recurrence'>Cours récurrent ?</label>";
                echo "<input type='checkbox' id='recurrence' name='recurrence' value='true'>";
            }
            ?>

            <button type="submit" class="button_article">Créer le cours</button>
            <?php
            if (isset($_GET['error'])) {
                echo "<p class='error'>Il y a eu une erreur lors de l'insertion dans la base de données</p>";
            }
            ?>
        </form>
    </section>
</article>
</body>

</html>
