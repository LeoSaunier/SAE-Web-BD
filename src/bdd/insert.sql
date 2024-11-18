-- Insérer des données dans la table Personne
INSERT INTO Personne (id_personne, nom, prenom, poids, ddn, niveau) VALUES
(1, 'Dupont', 'Jean', 60, '1990-05-12', 'débutant'),
(2, 'Martin', 'Pierre', 75, '1985-03-23', 'intermédiaire'),
(3, 'Leclerc', 'Marie', 95, '1992-09-15', 'avancé'),
(4, 'Durand', 'Sophie', 55, '2000-01-22', 'débutant');

-- Insérer des données dans la table Moniteur
INSERT INTO Moniteur (id_moniteur, id_personne, salaire_heure) VALUES
(1, 1, 25.5),
(2, 2, 30.0);

-- Insérer des données dans la table Adherant (l'adhérent avec l'id 3 est initialement non éligible)
INSERT INTO Adherant (id_adherant, id_personne, eligible) VALUES
(1, 3, 1),
(2, 4, 1),
(3, 2, 0); -- Cet adhérent n'est pas éligible, et les réservations échoueront

-- Insérer des types de factures
INSERT INTO Type_facture (id_type, nom_type) VALUES
(1, 'Cotisation annuelle'),
(2, 'Cours');

-- Insérer des factures (avec certains impayés)
INSERT INTO Facture (id_facture, id_type, id_adherant, date, payee, montant) VALUES
(1, 1, 1, '2024-01-15', 1, 100), -- Cotisation payée
(2, 1, 2, '2024-01-15', 1, 100), -- Cotisation payée
(3, 1, 3, '2024-01-15', 0, 100), -- Cotisation non payée pour l'adhérent non éligible

-- Factures impayées supplémentaires pour tester la limite des factures impayées
(4, 2, 1, '2024-02-10', 0, 50),
(5, 2, 1, '2024-03-15', 0, 50),
(6, 2, 1, '2024-04-20', 0, 50),
(7, 2, 1, '2024-05-25', 0, 50),
(8, 2, 1, '2024-06-30', 0, 50),
(9, 2, 1, '2024-07-05', 0, 50); -- Après cette insertion, l'adhérent 1 ne pourra plus réserver

-- Insérer des races de poney
INSERT INTO Race (id_race, nom_race) VALUES
(1, 'Shetland'),
(2, 'Welsh');

-- Insérer des poneys avec différentes capacités de poids
INSERT INTO Poney (id_poney, nom_poney, poids_supportable, temps_actif, id_race) VALUES
(1, 'PoneyA', 70, 2, 1),
(2, 'PoneyB', 10, 2, 1),
(3, 'PoneyC', 10, 2, 2);

-- Test pour Appartient : l'adhérent 3 a un poids trop élevé pour le poney 2 (trigger check_poids_reservation)
INSERT INTO Appartient (id_poney, id_adherant) VALUES
(2, 3); -- Échoue car le poids de l'adhérent est supérieur au poids supportable du poney

-- Test pour Reserve : l'adhérent 3 n'est pas éligible pour réserver (trigger check_eligible)
INSERT INTO Reserve (id_adherant, id_cours) VALUES
(3, 1); -- Échoue car l'adhérent 3 n'est pas éligible

-- Insérer des types de cours
INSERT INTO Type_cours (id_type_cours, nom_type_cours) VALUES
(1, 'Collectif'),
(2, 'Individuel');

-- Insérer des cours
INSERT INTO Cours (id_cours, id_type_cours, nb_personnes, heure_debut, heure_fin, recurrent, duree, date_cours) VALUES
(1, 1, 10, 9, 11, 1, 2, '2024-10-01'),
(2, 2, 1, 14, 15, 1, 1, '2024-10-01');

-- Test pour Facture : l'adhérent 1 a plus de 5 factures impayées, donc cette insertion échouera (trigger check_paiement_factures)
INSERT INTO Facture (id_facture, id_type, id_adherant, date, payee, montant) VALUES
(10, 2, 1, '2024-08-05', 0, 50); -- Échoue en raison de trop de factures impayées
