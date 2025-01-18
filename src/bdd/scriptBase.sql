DROP TRIGGER IF EXISTS check_poids_reservation;
DROP TRIGGER IF EXISTS check_paiement_factures;
DROP TRIGGER IF EXISTS resy_poney;
DROP TRIGGER IF EXISTS verifier_cotisations_adherent;
DROP PROCEDURE IF EXISTS creer_cours_recurrents_semaine;
DROP TRIGGER IF EXISTS check_poids_moniteur;

DROP TABLE IF EXISTS Reserve;
DROP TABLE IF EXISTS Assigner;
DROP TABLE IF EXISTS Cours;
DROP TABLE IF EXISTS Type_cours;
DROP TABLE IF EXISTS Facture;
DROP TABLE IF EXISTS Type_facture;
DROP TABLE IF EXISTS Moniteur;
DROP TABLE IF EXISTS Adherant;
DROP TABLE IF EXISTS Personne;
DROP TABLE IF EXISTS Poney;
DROP TABLE IF EXISTS Race;
DROP TABLE IF EXISTS Connexion;

CREATE TABLE Connexion (
    identifiant VARCHAR(60) PRIMARY KEY,
    mot_de_passe VARCHAR(20),
    position ENUM('admin', 'moniteur', 'adherant')
);

CREATE TABLE Personne (
    id_personne INT(6) PRIMARY KEY,
    nom VARCHAR(20),
    prenom VARCHAR(20),
    poids INT(3),
    ddn DATE,
    niveau ENUM('débutant', 'inité', 'intermédiaire', 'avancé'),
    identifiant VARCHAR(60),
    FOREIGN KEY (identifiant) REFERENCES Connexion(identifiant)
);

CREATE TABLE Adherant (
    id_adherant INT(6) PRIMARY KEY,
    id_personne INT(6),
    FOREIGN KEY (id_personne) REFERENCES Personne(id_personne)
);

CREATE TABLE Moniteur (
    id_moniteur INT(5) PRIMARY KEY,
    id_personne INT(6),
    salaire_heure FLOAT(4),
    FOREIGN KEY (id_personne) REFERENCES Personne(id_personne)
);

CREATE TABLE Race (
    id_race INT(3) PRIMARY KEY, 
    nom_race VARCHAR(20) UNIQUE
);

CREATE TABLE Poney (
    id_poney INT(5) PRIMARY KEY,
    nom_poney VARCHAR(20),
    poids_supportable INT(3),
    temps_actif INT(1) CHECK (temps_actif <= 2),
    id_race INT(3),
    FOREIGN KEY (id_race) REFERENCES Race(id_race)
);

CREATE TABLE Type_facture (
    id_type INT(2) PRIMARY KEY,
    nom_type VARCHAR(20) UNIQUE
);

CREATE TABLE Facture (
    id_facture INT(10) PRIMARY KEY,
    id_type INT(2),
    id_adherant INT(6),
    date DATE,
    payee BOOLEAN, 
    montant INT(4),
    FOREIGN KEY (id_type) REFERENCES Type_facture(id_type),
    FOREIGN KEY (id_adherant) REFERENCES Adherant(id_adherant)
);

CREATE TABLE Type_cours (
    id_type_cours INT(1) PRIMARY KEY,
    nom_type_cours VARCHAR(20) UNIQUE
);

CREATE TABLE Cours (
    id_cours INT(7) PRIMARY KEY auto_increment,
    id_type_cours INT(1),
    nb_personnes INT(2) CHECK ((nb_personnes <= 10 AND id_type_cours = 1) OR (nb_personnes = 1 AND id_type_cours = 2)),
    heure_debut INT(2),
    heure_fin INT(2),
    duree INT(2) CHECK (0 < duree AND duree <= 2),
    date_cours DATE,
    FOREIGN KEY (id_type_cours) REFERENCES Type_cours(id_type_cours)
);

CREATE TABLE Reserve (
    id_poney INT(5),
    id_adherant INT(6),
    id_cours INT(7),
    PRIMARY KEY (id_poney, id_adherant, id_cours),
    FOREIGN KEY (id_poney) REFERENCES Poney(id_poney),
    FOREIGN KEY (id_adherant) REFERENCES Adherant(id_adherant),
    FOREIGN KEY (id_cours) REFERENCES Cours(id_cours)
);

create table Assigner(
    id_moniteur INT(5),
    id_cours INT(7),
    id_poney INT(5),
    primary key(id_moniteur, id_cours, id_poney),
    foreign key(id_moniteur) references Moniteur(id_moniteur),
    foreign key(id_cours) references Cours(id_cours),
    foreign key(id_poney) references Poney(id_poney)
);


DELIMITER //
-- Vérifie si l'adhérant est trop lourd pour le poney avant l'insertion
CREATE TRIGGER check_poids_reservation
BEFORE INSERT ON Reserve
FOR EACH ROW
BEGIN
    DECLARE Vpoids_adherant INT;
    DECLARE Vpoids_supportable INT;

    -- Récupérer le poids de l'adhérant
    SELECT poids INTO Vpoids_adherant 
    FROM Personne 
    NATURAL JOIN Adherant
    WHERE Adherant.id_adherant = NEW.id_adherant;
    
    -- Récupérer le poids supportable du poney
    SELECT poids_supportable INTO Vpoids_supportable 
    FROM Poney 
    WHERE Poney.id_poney = NEW.id_poney;
    
    -- Comparer les deux poids et lever une erreur si l'adhérant est trop lourd
    IF Vpoids_adherant > Vpoids_supportable THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'L adhérant est trop lourd pour ce poney.';
    END IF;
END 

//

DELIMITER ;








DELIMITER //

CREATE PROCEDURE creer_cours_recurrents_semaine(
    IN p_id_type_cours INT,      -- Paramètre : id du type de cours
    IN p_nb_personnes INT,       -- Paramètre : nombre de personnes dans le cours
    IN p_heure_debut INT,        -- Paramètre : heure de début du cours
    IN p_heure_fin INT,          -- Paramètre : heure de fin du cours
    IN p_duree INT,              -- Paramètre : durée du cours en heures
    IN p_date_cours DATE         -- Paramètre : date du premier cours
)
BEGIN
    DECLARE prochaine_date DATE;
    DECLARE fin_recurrence DATE;
    DECLARE nouveau_id INT;

    -- Initialisation de la date de départ et de fin (un an à partir de la date du cours inséré)
    SET prochaine_date = p_date_cours;
    SET fin_recurrence = DATE_ADD(p_date_cours, INTERVAL 1 YEAR);

    

    -- Boucle pour insérer les cours récurrents chaque semaine, jusqu'à un an
    WHILE prochaine_date <= fin_recurrence DO
        SELECT MAX(id_cours) + 1 INTO nouveau_id FROM Cours;
        -- Insertion du cours dans la table Cours
        INSERT INTO Cours (id_cours, id_type_cours, nb_personnes, heure_debut, heure_fin, duree, date_cours)
        VALUES (
            nouveau_id,
            p_id_type_cours,
            p_nb_personnes,
            p_heure_debut,
            p_heure_fin,
            p_duree,
            prochaine_date
        );

        -- Mise à jour de la prochaine date (ajouter une semaine)
        SET prochaine_date = DATE_ADD(prochaine_date, INTERVAL 1 WEEK);
    END WHILE;
END //

DELIMITER ;







DELIMITER //

CREATE TRIGGER verifier_cotisations_adherent
BEFORE INSERT ON Reserve
FOR EACH ROW
BEGIN
    DECLARE mois_retard INT;
    DECLARE mois_courants INT;
    DECLARE mois_payes_mensuels INT;
    DECLARE annees_payees INT;

    -- Calculer le nombre total de mois depuis l'adhésion jusqu'à aujourd'hui
    SELECT TIMESTAMPDIFF(MONTH, MIN(f.date), CURDATE())
    INTO mois_courants
    FROM Facture f
    WHERE f.id_adherant = NEW.id_adherant;

    -- Vérifier combien de mois ont été payés (cotisations mensuelles)
    SELECT COUNT(*)
    INTO mois_payes_mensuels
    FROM Facture f
    INNER JOIN Type_facture tf ON f.id_type = tf.id_type
    WHERE f.id_adherant = NEW.id_adherant 
      AND f.payee = TRUE 
      AND tf.nom_type = 'MENSUEL';

    -- Vérifier combien d'années ont été payées (cotisations annuelles)
    SELECT COUNT(*)
    INTO annees_payees
    FROM Facture f
    INNER JOIN Type_facture tf ON f.id_type = tf.id_type
    WHERE f.id_adherant = NEW.id_adherant 
      AND f.payee = TRUE 
      AND tf.nom_type = 'ANNUEL';

    -- Calculer le nombre total de mois payés, en tenant compte des années payées (1 année = 12 mois)
    SET mois_payes_mensuels = mois_payes_mensuels + (annees_payees * 12);

    -- Calculer le nombre de mois de retard
    SET mois_retard = mois_courants - mois_payes_mensuels;

    -- Si l'adhérent a 3 mois de retard ou plus, empêcher l'insertion
    IF mois_retard >= 3 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "L'adhérent ne peut pas être ajouté au cours : 3 cotisations ou plus en retard.";
    END IF;
END //

DELIMITER ;



DELIMITER //
--Vérifie le nombre de factures impayées (si supérieur à 5, l'adhérent n'est plus éligible)
CREATE TRIGGER check_paiement_factures
BEFORE INSERT ON Facture
FOR EACH ROW
BEGIN
    DECLARE impayees int;

    -- Sélection de l'éligibilité de l'adhérent
    SELECT count(payee) INTO impayees
    FROM Facture
    NATURAL JOIN Adherant
    WHERE id_adherant = NEW.id_adherant
    AND payee = FALSE;

    -- Vérification de l'éligibilité
    IF impayees > 5 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "L'adhérent n'est pas éligible à une réservation pour factures impayées";
    END IF;
END //

DELIMITER ;


DELIMITER //
create trigger resy_poney
before insert on Cours
for each row
begin
    declare duree_cour int;
    declare duree_cour_prochain int;
    declare duree_cour_prec int;

    select duree into duree_cour
    from Cours
    where id_cours = NEW.id_cours;

    select duree into duree_cour_prochain
    from Cours
    where heure_debut = NEW.heure_fin and date_cours = NEW.date_cours;

    select duree into duree_cour_prec
    from Cours
    where heure_fin = NEW.heure_debut and date_cours = NEW.date_cours;

    -- Verification si il y a couor apres ou cour avant et si la somme des duree ne dépasse pas 3
    if duree_cour_prec is not null and duree_cour_prec + duree_cour >= 3 or duree_cour_prochain is not null and duree_cour_prochain + duree_cour >= 3 then
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Le cour ne peux pas être ajouté car les poney doivent se reposer";
    end if;
end;
//

DELIMITER ;

DELIMITER //

CREATE TRIGGER check_poids_moniteur
BEFORE INSERT ON Assigner
FOR EACH ROW
BEGIN
    DECLARE Vpoids_moniteur INT;
    DECLARE Vpoids_supportable INT;

    -- Récupérer le poids du moniteur
    SELECT poids INTO Vpoids_moniteur
    FROM Personne
    NATURAL JOIN Moniteur
    WHERE Moniteur.id_moniteur = NEW.id_moniteur;

    -- Récupérer le poids supportable du poney
    SELECT poids_supportable INTO Vpoids_supportable
    FROM Poney
    WHERE Poney.id_poney = NEW.id_poney;

    -- Comparer les deux poids et lever une erreur si le moniteur est trop lourd
    IF Vpoids_moniteur > Vpoids_supportable THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Le moniteur est trop lourd pour ce poney.';
    END IF;
END;
//

DELIMITER ;
