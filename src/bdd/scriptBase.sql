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
    foreign key id_personne references Personne(id_personne)
)

create table Adherant(
    id_adherant int(6) Primary key,
    id_personne int(6),
    eligible boolean,
    foreign key id_personne references Personne(id_personne)
)

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
    temps_repos int(1),
    id_race int(3), 

);

create table Race(
    id_race int(3) PRIMARY KEY, --car entre 150 à 200 races de poney reconnus
    nom_race varchar(20)
);

create table Type_cours(
    id_type_cours int(1) PRIMARY KEY,
    nom_type_cours varchar(20)
);

create table Cours(
    id_cours int(7) Primary key,
    id_type_cours int(1),
    nb_personnes int(2),
    heure_debut int(2),
    heure_fin int(2),
    recurant boolean, --0 non, 1 oui
    duree int(2),

);

create table Appartient(
    id_poney int(5),
    id_adherant int(6)
);

create table Reserve(
    id_adherant int(6),
    id_cours int(7)
);





