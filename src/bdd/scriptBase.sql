drop table Cours ;
drop table Type_cours ;
drop table Race ;
drop table Poney ;
drop table Moniteur ;
drop table Facture ;
drop table Type_facture ;
drop table Adherant ;
drop table Personne ; 
drop table Reserve ;
drop table Appartient;
drop event verifier_cotisation ;
drop event cours_recurant ;

SET GLOBAL event_scheduler = ON;


create table Personne(
    id_personne int(6) PRIMARY KEY,
    nom varchar(20),
    prenom varchar(20),
    poids int(3),
    ddn DATE,
    niveau enum("débutant", "inité", "intermédiaire", "avancé" )
);

create table Moniteur(
    id_moniteur int(5) Primary key,
    id_personne int(6),
    salaire_heure float(4),
    foreign key (id_personne) references Personne(id_personne)
);

create table Adherant(
    id_adherant int(6) Primary key,
    id_personne int(6),
    eligible boolean,
    foreign key (id_personne) references Personne(id_personne)
);

create table Type_facture(
    id_type int(2) PRIMARY KEY,
    nom_type varchar(20)
); 

create table Facture(
    id_facture int(10) PRIMARY KEY,
    id_type int(2),
    id_adherant int(6),
    date Date,
    payee boolean, 
    montant int(4),
    foreign key (id_type) references Type_facture(id_type)
);

create table Poney(
    id_poney int(5) PRIMARY KEY,
    nom_poney varchar(20),
    poids_supportable int(3),
    temps_actif int(1) check (temps_actif <= 2),
    id_race int(3)
);

--car entre 150 à 200 races de poney reconnus
create table Race(
    id_race int(3) PRIMARY KEY, 
    nom_race varchar(20)
);

create table Type_cours(
    id_type_cours int(1) PRIMARY KEY,
    nom_type_cours varchar(20)
);

create table Cours(
    id_cours int(7) Primary key,
    id_type_cours int(1),
    nb_personnes int(2) check ((nb_personnes<=10 and id_type_cours = 1) or (nb_personnes=1 and id_type_cours = 2)),
    heure_debut int(2) check(heure_debut < heure_fin),
    heure_fin int(2),
    recurrent boolean,
    duree int(2) check (0 < duree < 2),
    date_cours DATE,
    foreign key (id_type_cours) references Type_cours(id_type_cours)
);

create table Appartient(
    id_poney int(5),
    id_adherant int(6)
);

create table Reserve(
    id_adherant int(6),
    id_cours int(7)
);

DELIMITER //
-- Vérifie si l'adhérant est trop lourd pour le poney avant l'insertion
CREATE TRIGGER check_poids_reservation
BEFORE INSERT ON Appartient
FOR EACH ROW
BEGIN
    DECLARE poids_adherant INT;
    DECLARE poids_supportable INT;

    -- Récupérer le poids de l'adhérant
    SELECT poids INTO poids_adherant 
    FROM Personne 
    JOIN Adherant ON Personne.id_personne = Adherant.id_personne
    WHERE Adherant.id_adherant = NEW.id_adherant;
    
    -- Récupérer le poids supportable du poney
    SELECT poids_supportable INTO poids_supportable 
    FROM Poney 
    WHERE Poney.id_poney = NEW.id_poney;
    
    -- Comparer les deux poids et lever une erreur si l'adhérant est trop lourd
    IF poids_adherant > poids_supportable THEN
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



