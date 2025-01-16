<?php

class ConnectionBDD
{
    private $pdo;
    private $role;
    private $id;

    public static function connect($identifiant, $password)
    {
        $instance = new self();
        $role = $instance->getRole($identifiant, $password);

        if ($role === 'moniteur') {
            $instance->id = $instance->getIdMoniteur($identifiant);
        } elseif ($role === 'adherant') {
            $instance->id = $instance->getIdAdherent($identifiant);
        }

        $instance->role = $role;
        return $instance;
    }

    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=your_database_name', 'username', 'password', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $this->role = 'guest';
    }

    private function getIdMoniteur($identifiant)
    {
        $query = $this->pdo->prepare(
            'SELECT id_moniteur FROM Moniteur NATURAL JOIN Personne NATURAL JOIN Connexion WHERE identifiant = :identifiant'
        );
        $query->execute(['identifiant' => $identifiant]);
        $result = $query->fetch();
        return $result['id_moniteur'] ?? null;
    }

    private function getIdAdherent($identifiant)
    {
        $query = $this->pdo->prepare(
            'SELECT id_adherant FROM Adherant NATURAL JOIN Personne NATURAL JOIN Connexion WHERE identifiant = :identifiant'
        );
        $query->execute(['identifiant' => $identifiant]);
        $result = $query->fetch();
        return $result['id_adherant'] ?? null;
    }

    private function getRole($identifiant, $password)
    {
        $query = $this->pdo->prepare(
            'SELECT position FROM Connexion WHERE identifiant = :identifiant AND mot_de_passe = :password'
        );
        $query->execute([
            'identifiant' => $identifiant,
            'password' => $password
        ]);
        $result = $query->fetch();
        return $result['position'] ?? 'guest';
    }

    public function getCoursesWithAvailability($date)
    {
        $query = "
            SELECT c.id_cours, c.date_cours, c.heure_debut, c.heure_fin, c.nb_personnes - COUNT(r.id_adherant) AS spots_left
            FROM Cours c
            LEFT JOIN Reserve r ON c.id_cours = r.id_cours
            WHERE c.date_cours = :date
            GROUP BY c.id_cours
            HAVING spots_left > 0
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPrivateLesson($date, $startTime, $endTime)
    {
        $query = "INSERT INTO Cours (id_type_cours, nb_personnes, heure_debut, heure_fin, duree, date_cours) 
                  VALUES (2, 1, :start_time, :end_time, :duration, :date)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $endTime - $startTime,
            'date' => $date
        ]);
    }

    public function createGroupLesson($date, $startTime, $endTime)
    {
        $query = "INSERT INTO Cours (id_type_cours, nb_personnes, heure_debut, heure_fin, duree, date_cours) 
                  VALUES (1, 10, :start_time, :end_time, :duration, :date)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $endTime - $startTime,
            'date' => $date
        ]);
    }

    public function addStudentToCourse($courseId, $adherantId, $ponyId)
    {
        $this->pdo->beginTransaction();
        try {
            $queryReserve = "INSERT INTO Reserve (id_adherant, id_cours, id_poney) VALUES (:adherant_id, :course_id, :pony_id)";
            $stmtReserve = $this->pdo->prepare($queryReserve);
            $stmtReserve->execute([
                'adherant_id' => $adherantId,
                'course_id' => $courseId,
                'pony_id' => $ponyId
            ]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getAvailablePonies($hour, $date)
    {
        $query = "
            SELECT p.id_poney, p.nom_poney
            FROM Poney p
            WHERE p.id_poney NOT IN (
                SELECT r.id_poney
                FROM Reserve r
                JOIN Cours c ON r.id_cours = c.id_cours
                WHERE c.date_cours = :date AND c.heure_debut <= :hour AND c.heure_fin > :hour
            )
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'hour' => $hour,
            'date' => $date
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteCourse($courseId)
    {
        if ($this->role !== 'admin' && $this->role !== 'moniteur') {
            throw new Exception('You do not have the required permissions to delete a course');
        }
        $this->pdo->beginTransaction();
        try {
            $queryDeleteReservations = "DELETE FROM Reserve WHERE id_cours = :course_id";
            $stmtDeleteReservations = $this->pdo->prepare($queryDeleteReservations);
            $stmtDeleteReservations->execute(['course_id' => $courseId]);

            $queryDeleteCourse = "DELETE FROM Cours WHERE id_cours = :course_id";
            $stmtDeleteCourse = $this->pdo->prepare($queryDeleteCourse);
            $stmtDeleteCourse->execute(['course_id' => $courseId]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getCoursesByInstructor($instructorId)
    {
        $query = "
            SELECT c.id_cours, c.date_cours, c.heure_debut, c.heure_fin, COUNT(r.id_adherant) AS students
            FROM Cours c
            LEFT JOIN Reserve r ON c.id_cours = r.id_cours
            WHERE c.id_cours IN (
                SELECT a.id_cours
                FROM Assigner a
                WHERE a.id_moniteur = :instructor_id
            )
            GROUP BY c.id_cours
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['instructor_id' => $instructorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
