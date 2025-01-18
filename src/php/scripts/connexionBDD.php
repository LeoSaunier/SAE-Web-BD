<?php

class Database {
    private static $pdo = null;

    // Méthode pour initialiser la connexion PDO
    private static function getConnection() {
        if (self::$pdo === null) {
            self::$pdo = new PDO('mysql:host=localhost;dbname=poney', 'root', 'root');
        }
        return self::$pdo;
    }

    // Méthode pour initialiser la connexion PDO

    // Méthode pour exécuter une requête préparée
    public static function executeQuery($query, $params = []) {
        $stmt = self::getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    // Méthode pour récupérer un résultat unique
    public static function fetchOne($query, $params = []) {
        return self::executeQuery($query, $params)->fetch(PDO::FETCH_ASSOC);
    }

    // Méthode pour récupérer plusieurs résultats
    public static function fetchAll($query, $params = []) {
        return self::executeQuery($query, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRole($login, $password) {
        $query = "SELECT position FROM Connexion WHERE identifiant = :login AND mot_de_passe = :password";
        $result = self::fetchOne($query, ['login' => $login, 'password' => $password]);
        return $result['position'] ?? 'guest';
    }

    public static function getIdMoniteur($login) {
        $query = "SELECT id_moniteur FROM Moniteur NATURAL JOIN Personne NATURAL JOIN Connexion WHERE identifiant = :login";
        $result = self::fetchOne($query, ['login' => $login]);
        return $result['id_moniteur'] ?? null;
    }

    public static function getIdAdherent($login) {
        $query = "SELECT id_adherant FROM Adherant NATURAL JOIN Personne NATURAL JOIN Connexion WHERE identifiant = :login";
        $result = self::fetchOne($query, ['login' => $login]);
        return $result['id_adherant'] ?? null;
    }

    public static function getCoursesWithAvailability($date) {
        $query = "
            SELECT c.id_cours, c.date_cours, c.heure_debut, c.heure_fin, c.nb_personnes - COUNT(r.id_adherant) AS spots_left
            FROM Cours c
            LEFT JOIN Reserve r ON c.id_cours = r.id_cours
            WHERE c.date_cours = :date
            GROUP BY c.id_cours
            HAVING spots_left > 0
        ";
        return self::fetchAll($query, ['date' => $date]);
    }

    public static function createPrivateLesson($date, $startTime, $endTime) {
        $query = "INSERT INTO Cours (id_type_cours, nb_personnes, heure_debut, heure_fin, duree, date_cours) 
                  VALUES (2, 1, :start_time, :end_time, :duration, :date)";
        self::executeQuery($query, [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $endTime - $startTime,
            'date' => $date
        ]);
    }

    public static function createGroupLesson($date, $startTime, $endTime, $recurence) {
        if ($recurence) {
            $query = "CALL creer_cours_recurrents_semaine(1, 10, :start_time, :end_time, :duration, :date)";
            self::executeQuery($query, [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration' => $endTime - $startTime,
                'date' => $date
            ]);
        } else {
            $query = "INSERT INTO Cours (id_type_cours, nb_personnes, heure_debut, heure_fin, duree, date_cours) 
                      VALUES (1, 10, :start_time, :end_time, :duration, :date)";
            self::executeQuery($query, [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration' => $endTime - $startTime,
                'date' => $date
            ]);
        }
    }

    public static function addStudentToCourse($courseId, $adherantId, $ponyId) {
        $pdo = self::getConnection();
        $pdo->beginTransaction();
        try {
            $queryReserve = "INSERT INTO Reserve (id_adherant, id_cours, id_poney) VALUES (:adherant_id, :course_id, :pony_id)";
            self::executeQuery($queryReserve, [
                'adherant_id' => $adherantId,
                'course_id' => $courseId,
                'pony_id' => $ponyId
            ]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getAvailablePonies($hour, $date) {
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
        return self::fetchAll($query, ['hour' => $hour, 'date' => $date]);
    }

    public static function deleteCourse($courseId, $role) {
        if ($role !== 'admin' && $role !== 'moniteur') {
            throw new Exception('You do not have the required permissions to delete a course');
        }
        $pdo = self::getConnection();
        $pdo->beginTransaction();
        try {
            $queryDeleteReservations = "DELETE FROM Reserve WHERE id_cours = :course_id";
            self::executeQuery($queryDeleteReservations, ['course_id' => $courseId]);

            $queryDeleteCourse = "DELETE FROM Cours WHERE id_cours = :course_id";
            self::executeQuery($queryDeleteCourse, ['course_id' => $courseId]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getCoursesByInstructor($instructorId) {
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
        return self::fetchAll($query, ['instructor_id' => $instructorId]);
    }
}

class User {
    private $id;
    private $role;
    private $login;

    public function __construct($login, $password) {
        $this->login = $login;
        $this->role = Database::getRole($login, $password);

        if ($this->role === 'moniteur') {
            $this->id = Database::getIdMoniteur($login);
        } elseif ($this->role === 'adherant') {
            $this->id = Database::getIdAdherent($login);
        } elseif ($this->role === 'admin') {
            $this->id = 0;
        } else {
            $this->role = 'guest';
            $this->id = null;
        }
    }

    public function getRole() {
        return $this->role;
    }

    public function getId() {
        return $this->id;
    }

    public function getLogin() {
        return $this->login;
    }
}

?>
