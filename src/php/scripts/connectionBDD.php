<?php

class connectionBDD
{
    private $role;
    private $pdo;
    private $id;
    public function __construct($identifiant, $password)
    {
        $this->$pdo = new PDO('mysql:host=localhost;dbidentifiant');
        $role = $this->getRole($identifiant, $password);
        if($role == 'moniteur'){
            $this->id = $this->getIdMoniteur($identifiant, $password);
        } else if($role == 'adherant'){
            $this->id = $this->getIdAdherent($identifiant, $password);
        }
    }

    public function __construct(){
        $this->$pdo = new PDO('mysql:host=localhost;dbidentifiant');
        $this->role = 'guest';
    }

    public function getIdMoniteur($identifiant, $password)
    {
        $query = $this->pdo->prepare('SELECT id_moniteur FROM Moniteur natural join Personne natural join Connexion WHERE identifiant = :identifiant AND mot_de_passe = :password');
        $query->execute(array(
            'identifiant' => $identifiant,
            'password' => $password
        ));
        $result = $query->fetch();
        return $result['id_moniteur'];
    }

    public function getIdAdherent($identifiant, $password)
    {
        $query = $this->pdo->prepare('SELECT id_adherant FROM Adherant natural join Personne natural join Connexion WHERE identifiant = :identifiant AND mot_de_passe = :password');
        $query->execute(array(
            'identifiant' => $identifiant,
            'password' => $password
        ));
        $result = $query->fetch();
        return $result['id_adherant'];
    }

    public function getRole($identifiant, $password)
    {
        $query = $this->pdo->prepare('SELECT role FROM users WHERE identifiant = :identifiant AND password = :password');
        $query->execute(array(
            'identifiant' => $identifiant,
            'password' => $password
        ));
        $result = $query->fetch();
        return $result['role'];
    }

    // Fetch courses with available spots
    function getCoursesWithAvailability($pdo, $date) {
        $query = "
            SELECT c.id_cours, c.date_cours, c.heure_debut, c.heure_fin, c.nb_personnes - COUNT(r.id_adherant) AS spots_left
            FROM Cours c
            LEFT JOIN Reserve r ON c.id_cours = r.id_cours
            WHERE c.date_cours = :date
            GROUP BY c.id_cours
            HAVING spots_left > 0
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

// Create a private lesson
function createPrivateLesson($pdo, $date, $startTime, $endTime) {
    $query = "INSERT INTO Cours (id_type_cours, nb_personnes, heure_debut, heure_fin, recurrent, duree, date_cours) 
              VALUES (2, 1, :start_time, :end_time, 0, TIMESTAMPDIFF(HOUR, :start_time, :end_time), :date)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'start_time' => $startTime,
        'end_time' => $endTime,
        'date' => $date
    ]);
}

// Create a group lesson
function createGroupLesson($pdo, $date, $startTime, $endTime) {
    $query = "INSERT INTO Cours (id_type_cours, nb_personnes, heure_debut, heure_fin, recurrent, duree, date_cours) 
              VALUES (1, 10, :start_time, :end_time, 0, TIMESTAMPDIFF(HOUR, :start_time, :end_time), :date)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'start_time' => $startTime,
        'end_time' => $endTime,
        'date' => $date
    ]);
}

// Add a student to a course with a pony
function addStudentToCourse($pdo, $courseId, $adherantId, $ponyId) {
    $pdo->beginTransaction();
    try {
        $queryReserve = "INSERT INTO Reserve (id_adherant, id_cours) VALUES (:adherant_id, :course_id)";
        $stmtReserve = $pdo->prepare($queryReserve);
        $stmtReserve->execute([
            'adherant_id' => $adherantId,
            'course_id' => $courseId
        ]);

        $queryAppartient = "INSERT INTO Appartient (id_poney, id_adherant) VALUES (:pony_id, :adherant_id)";
        $stmtAppartient = $pdo->prepare($queryAppartient);
        $stmtAppartient->execute([
            'pony_id' => $ponyId,
            'adherant_id' => $adherantId
        ]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Fetch available ponies for a specific hour
function getAvailablePonies($pdo, $hour) {
    $query = "
        SELECT p.id_poney, p.nom_poney
        FROM Poney p
        WHERE p.id_poney NOT IN (
            SELECT a.id_poney
            FROM Appartient a
            JOIN Cours c ON a.id_adherant IN (
                SELECT r.id_adherant
                FROM Reserve r
                WHERE c.heure_debut <= :hour AND c.heure_fin > :hour
            )
        )
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['hour' => $hour]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Delete a course and its reservations
function deleteCourse($pdo, $courseId) {
    if ($role !== 'admin' or $role !== 'moniteur') {
        throw new Exception('You do not have the required permissions to delete a course');
    }
    $pdo->beginTransaction();
    try {
        $queryDeleteReservations = "DELETE FROM Reserve WHERE id_cours = :course_id";
        $stmtDeleteReservations = $pdo->prepare($queryDeleteReservations);
        $stmtDeleteReservations->execute(['course_id' => $courseId]);

        $queryDeleteCourse = "DELETE FROM Cours WHERE id_cours = :course_id";
        $stmtDeleteCourse = $pdo->prepare($queryDeleteCourse);
        $stmtDeleteCourse->execute(['course_id' => $courseId]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Fetch courses and student count by instructor
function getCoursesByInstructor($pdo, $instructorId) {
    $query = "
        SELECT c.id_cours, c.date_cours, c.heure_debut, c.heure_fin, COUNT(r.id_adherant) AS students
        FROM Cours c
        JOIN Moniteur m ON m.id_personne = :instructor_id
        LEFT JOIN Reserve r ON c.id_cours = r.id_cours
        GROUP BY c.id_cours
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['instructor_id' => $instructorId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}


        