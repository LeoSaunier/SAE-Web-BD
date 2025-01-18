<?php
require "scripts/connexionBDD.php";
date_default_timezone_set('Europe/Paris');
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');

function rendreDateValide($date, $format = "d-m-Y"): string
{
    $dateTime = DateTime::createFromFormat($format, $date);

    if ($dateTime === false) {
        throw new Exception("Date invalide : $date");
    }

    return $dateTime->format($format);
}

$curr_date = date("Y-m-d");
$actual_year = date("Y");

if (isset($_POST["prev_week"])) {
    try {
        $curr_date = rendreDateValide($_POST['prev_week'], "Y-m-d");
    } catch (Exception $e) {
        $curr_date = date("Y-m-d");
    }
} else if (isset($_POST["next_week"])) {
    try {
        $curr_date = rendreDateValide($_POST['next_week'], "Y-m-d");
    } catch (Exception $e) {
        $curr_date = date("Y-m-d");
    }
} else if (isset($_POST['current_week'])) {
    try {
        $curr_date = rendreDateValide($_POST['current_week'], "Y-m-d");
    } catch (Exception $e) {
        $curr_date = date("Y-m-d");
    }
}

$dateObj = new DateTime($curr_date);
$prev_week = $dateObj->modify('-7 days')->format("Y-m-d");
$dateObj->modify('+7 days'); // Revenir à la date initiale
$next_week = $dateObj->modify('+7 days')->format("Y-m-d");

$curr_day = date("d", strtotime($curr_date));
$curr_month = date("m", strtotime($curr_date));
$curr_year = date("Y", strtotime($curr_date));

$day = date("w", strtotime($curr_date));
$first_day_week = date("d M Y", strtotime('monday this week', strtotime($curr_date)));
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
    <section class="centered">
        <form method="POST" id="datePicker">
            <label for="week">Choisissez une semaine</label>
            <button type="submit" name="prev_week" value="<?php echo $prev_week; ?>">&#60;</button>
            <input type="date" id="week" name="current_week"
                   value="<?php echo htmlspecialchars($curr_date); ?>"
                   min="<?php echo htmlspecialchars((intval($actual_year) - 1) . '-01-01'); ?>"
                   max="<?php echo htmlspecialchars((intval($actual_year) + 1) . '-12-31'); ?>"
                   onchange="this.form.submit()"/>
            <button type="submit" name="next_week" value="<?php echo $next_week; ?>">&#62;</button>
        </form>
        <h2>Les horaires des cours</h2>
        <section class="centered">
            <table class="centered schedule">
                <thead>
                <tr>
                    <th>Heures</th>
                    <th>Lundi<br/><?php echo date("d M Y", strtotime('monday this week', strtotime($curr_date))); ?></th>
                    <th>Mardi<br/><?php echo date("d M Y", strtotime('tuesday this week', strtotime($curr_date))); ?></th>
                    <th>Mercredi<br/><?php echo date("d M Y", strtotime('wednesday this week', strtotime($curr_date))); ?></th>
                    <th>Jeudi<br/><?php echo date("d M Y", strtotime('thursday this week', strtotime($curr_date))); ?></th>
                    <th>Vendredi<br/><?php echo date("d M Y", strtotime('friday this week', strtotime($curr_date))); ?></th>
                    <th>Samedi<br/><?php echo date("d M Y", strtotime('saturday this week', strtotime($curr_date))); ?></th>
                    <th>Dimanche<br/><?php echo date("d M Y", strtotime('sunday this week', strtotime($curr_date))); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                $hours = range(8, 21);
                $coursesByDay = [];

                for ($hour = 8; $hour <= 21; $hour++) {
                    echo '<tr>';
                    echo "<th>{$hour}H</th>";

                    foreach ($daysOfWeek as $day) {
                        $dayDate = date("Y-m-d", strtotime("$day this week", strtotime($curr_date)));
                        $coursesByDay[$day] = Database::getCoursesWithAvailability($dayDate);
                        $dayCourses = $coursesByDay[$day];
                        $courseDetails = '';

                        foreach ($dayCourses as $course) {
                            if ((int)$course['heure_debut'] <= $hour && $hour < (int)$course['heure_fin']) {
                                $courseDetails .= "Cours ID: {$course['id_cours']}<br/>";
                                $courseDetails .= "Places restantes: {$course['spots_left']}";
                            }
                        }

                        if ($courseDetails === '') {
                            echo "<td>-<br/></td>";
                        } else {
                            echo "<td>{$courseDetails}</td>";
                        }
                    }
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>
        </section>
    </section>
</article>
</body>
</html>

