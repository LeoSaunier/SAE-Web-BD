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
(3, 'Lemoine', 'Marie', 65, '1992-03-10', 'intermédiaire', 'adherant2'),
(4, 'Durand', 'Paul', 85, '1988-08-25', 'inité', 'adherant3'),
(5, 'Clément', 'Lucie', 55, '1997-12-15', 'avancé', 'moniteur2');

-- Adherant
INSERT INTO Adherant (id_adherant, id_personne, eligible)
VALUES 
(2, 3, TRUE),
(3, 4, FALSE);

-- Moniteur
INSERT INTO Moniteur (id_moniteur, id_personne, salaire_heure)
VALUES 
(2, 5, 30.0);

-- Race
INSERT INTO Race (id_race, nom_race)
VALUES 
(3, 'Poney Connemara'),
(4, 'Poney Dartmoor');

-- Poney
INSERT INTO Poney (id_poney, nom_poney, poids_supportable, temps_actif, id_race)
VALUES 
(3, 'Poney3', 70, 0, 3),
(4, 'Poney4', 90, 2, 4),
(5, 'Poney5', 60, 1, 2);

-- Type_facture
INSERT INTO Type_facture (id_type, nom_type)
VALUES 
(3, 'Cours avancé');

-- Facture
INSERT INTO Facture (id_facture, id_type, id_adherant, date, payee, montant)
VALUES 
(4, 1, 2, '2024-01-01', TRUE, 100),
(5, 2, 2, '2024-02-01', FALSE, 30),
(6, 2, 2, '2024-03-01', FALSE, 30),
(7, 1, 3, '2024-01-01', TRUE, 100);

-- Type_cours
INSERT INTO Type_cours (id_type_cours, nom_type_cours)
VALUES 
(3, 'Stage intensif');

-- Cours
INSERT INTO Cours (id_cours, id_type_cours, nb_personnes, heure_debut, heure_fin, recurrent, duree, date_cours)
VALUES 
(3, 3, 10, 9, 12, FALSE, 3, '2024-01-20'),
(4, 2, 1, 14, 15, TRUE, 1, '2024-01-22'),
(5, 1, 7, 10, 11, TRUE, 1, '2024-01-25');

-- Reserve
INSERT INTO Reserve (id_poney, id_adherant, id_cours)
VALUES 
(2, 1, 1),
(3, 2, 3),
(1, 1, 4),
(5, 2, 5);

-- Assigner
INSERT INTO Assigner (id_moniteur, id_cours, id_poney)
VALUES 
(1, 3, 3),
(2, 4, 5),
(1, 5, 2);
