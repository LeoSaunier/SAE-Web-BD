DROP TRIGGER IF EXISTS check_poids_reservation;
DROP TRIGGER IF EXISTS check_eligible;
DROP TRIGGER IF EXISTS check_paiement_factures;
DROP TRIGGER IF EXISTS resy_poney;
DROP EVENT IF EXISTS verifier_cotisation;
DROP EVENT IF EXISTS cours_recurant;

DROP TABLE IF EXISTS Reserve;
DROP TABLE IF EXISTS Appartient;
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

SET GLOBAL event_scheduler = ON;

CREATE TABLE Connexion (
    identifiant VARCHAR(20) PRIMARY KEY,
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
    identifiant VARCHAR(20),
    FOREIGN KEY (identifiant) REFERENCES Connexion(identifiant)
);

CREATE TABLE Adherant (
    id_adherant INT(6) PRIMARY KEY,
    id_personne INT(6),
    eligible BOOLEAN DEFAULT TRUE,
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
    id_cours INT(7) PRIMARY KEY,
    id_type_cours INT(1),
    nb_personnes INT(2) CHECK ((nb_personnes <= 10 AND id_type_cours = 1) OR (nb_personnes = 1 AND id_type_cours = 2)),
    heure_debut INT(2),
    heure_fin INT(2),
    recurrent BOOLEAN,
    duree INT(2) CHECK (0 < duree AND duree < 2),
    date_cours DATE,
    FOREIGN KEY (id_type_cours) REFERENCES Type_cours(id_type_cours)
);

CREATE TABLE Appartient (
    id_poney INT(5),
    id_adherant INT(6),
    PRIMARY KEY (id_poney, id_adherant),
    FOREIGN KEY (id_poney) REFERENCES Poney(id_poney),
    FOREIGN KEY (id_adherant) REFERENCES Adherant(id_adherant)
);

CREATE TABLE Reserve (
    id_adherant INT(6),
    id_cours INT(7),
    PRIMARY KEY (id_adherant, id_cours),
    FOREIGN KEY (id_adherant) REFERENCES Adherant(id_adherant),
    FOREIGN KEY (id_cours) REFERENCES Cours(id_cours)
);


DELIMITER //
-- Vérifie si l'adhérant est trop lourd pour le poney avant l'insertion
CREATE TRIGGER check_poids_reservation
BEFORE INSERT ON Appartient
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
-- Vérifie si l'adhérent est éligible à une réservation avant l'insertion
CREATE TRIGGER check_eligible
BEFORE INSERT ON Reserve
FOR EACH ROW
BEGIN
    DECLARE is_eligible BOOLEAN;

    -- Sélection de l'éligibilité de l'adhérent
    SELECT eligible INTO is_eligible
    FROM Adherant
    WHERE id_adherant = NEW.id_adherant;

    -- Vérification de l'éligibilité
    IF is_eligible = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "L'adhérent n'est pas éligible à une réservation pour cotisation impayée";
    END IF;
END //

DELIMITER ;




DELIMITER //

-- Événement pour vérifier la cotisation annuelle, si impayée, l'adhérent n'est plus éligible
CREATE EVENT verifier_cotisation
ON SCHEDULE EVERY 1 YEAR
STARTS '2025-09-01 00:00:00' -- Démarre le 1er Septembre 2025 à minuit
DO
BEGIN
    -- Mettre eligible à False pour les adhérents sans cotisation payée pour l'année courante
    UPDATE Adherant a
    SET a.eligible = 0
    WHERE NOT EXISTS (
        SELECT 
            1 
        FROM 
            Facture f
        JOIN 
            Type_facture tf ON f.id_type = tf.id_type
        WHERE 
            f.id_adherant = a.id_adherant
            AND tf.id_type = 1 -- Vérifie qu'il s'agit d'une cotisation
            AND YEAR(f.date) = YEAR(CURDATE()) -- Pour l'année courante
            AND f.payee = TRUE
    );
    
    -- Mettre eligible à True pour les adhérents ayant payé la cotisation pour l'année courante
    UPDATE Adherant a
    SET a.eligible = 1
    WHERE EXISTS (
        SELECT
            1 
        FROM 
            Facture f
        JOIN 
            Type_facture tf ON f.id_type = tf.id_type
        WHERE 
            f.id_adherant = a.id_adherant
            AND tf.id_type = 1
            AND YEAR(f.date) = YEAR(CURDATE())
            AND f.payee = TRUE
    );
    
END 

//

DELIMITER ;



DELIMITER //
-- Événement pour créer automatiquement les prochains cours si ceux-ci sont récurrents
CREATE EVENT cours_recurant
ON SCHEDULE EVERY 1 WEEK 
STARTS '2024-09-27 00:00:00'
DO 
BEGIN
    -- Insertion dans la table Cours
    INSERT INTO Cours (id_cours, id_type_cours, heure_debut, heure_fin, recurrent, duree, date_cours)
    SELECT 
        MAX(id_cours) + 1, -- id_cours incrémenté
        id_type_cours,
        heure_debut,
        heure_fin,
        recurrent,
        duree,
        DATE_ADD(date_cours, INTERVAL 4 WEEK) -- Ajout de 4 semaines à la date existante
    FROM 
        Cours
    WHERE 
        recurrent = 1
        AND date_cours >= DATE_SUB(NOW(), INTERVAL 1 WEEK) -- Cours depuis une semaine
        AND date_cours < NOW();
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
