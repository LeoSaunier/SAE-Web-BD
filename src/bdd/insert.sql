-- Connexion
INSERT INTO Connexion (identifiant, mot_de_passe, position) 
VALUES 
('admin2', 'password4', 'admin'),
('moniteur2', 'password5', 'moniteur'),
('adherant2', 'password6', 'adherant'),
('adherant3', 'password7', 'adherant');

-- Personne
INSERT INTO Personne (id_personne, nom, prenom, poids, ddn, niveau, identifiant)
VALUES 
(1, 'Lemoine', 'Marie', 65, '1992-03-10', 'intermédiaire', 'adherant2'),
(2, 'Durand', 'Paul', 85, '1988-08-25', 'inité', 'adherant3'),
(3, 'Clément', 'Lucie', 55, '1997-12-15', 'avancé', 'moniteur2');

-- Adherant
INSERT INTO Adherant (id_adherant, id_personne)
VALUES 
(1, 1),
(2, 2);

-- Moniteur
INSERT INTO Moniteur (id_moniteur, id_personne, salaire_heure)
VALUES 
(1, 3, 30.0);

-- Race
INSERT INTO Race (id_race, nom_race)
VALUES 
(1, 'Poney Shetland'),
(2, 'Poney Connemara'),
(3, 'Poney Dartmoor');


-- Type_facture
INSERT INTO Type_facture (id_type, nom_type)
VALUES 
(1, 'ANNUEL'),
(2, 'MENSUEL');

-- Poney
INSERT INTO Poney (id_poney, nom_poney, poids_supportable, temps_actif, id_race)
VALUES 
(1, 'Poney1', 70, 0, 1),
(2, 'Poney2', 85, 1, 2),
(3, 'Poney3', 90, 2, 3);

-- Facture
INSERT INTO Facture (id_facture, id_type, id_adherant, date, payee, montant)
VALUES 
(1, 1, 1, '2024-01-01', TRUE, 100),
(2, 2, 1, '2024-02-01', TRUE, 30),
(3, 2, 1, '2024-03-01', TRUE, 50),
(4, 1, 2, '2024-01-01', TRUE, 100);

-- Type_cours
INSERT INTO Type_cours (id_type_cours, nom_type_cours)
VALUES 
(1, 'Cours collectif'),
(2, 'Cours particulier');

-- Cours
INSERT INTO Cours (id_cours, id_type_cours, nb_personnes, heure_debut, heure_fin, duree, date_cours)
VALUES 
(1, 1, 10, 9, 11, 2, '2024-01-20');
Call creer_cours_recurrents_semaine( 2, 1, 14, 15, 1, '2024-01-22');
Call creer_cours_recurrents_semaine( 1, 7, 10, 12, 2, '2024-01-25');

-- Reserve
INSERT INTO Reserve (id_poney, id_adherant, id_cours)
VALUES 
(1, 1, 1),
(2, 1, 2),
(3, 2, 3);

-- Assigner
INSERT INTO Assigner (id_moniteur, id_cours, id_poney)
VALUES 
(1, 1, 1),
(1, 2, 2),
(1, 3, 3);
