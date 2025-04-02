create table gestionnaire
(
    id varchar(30)  PRIMARY KEY,
    nom varchar(30),
    prenom varchar(30),
    login varchar(30),
    mdp varchar(30),
    ville varchar(30),
    adresse varchar(30),
    cp varchar(30),
    dateEmbauche date
);

insert into gestionnaire
(
    id,
    nom,
    prenom,
    login,
    mdp,
    ville,
    adresse,
    cp,
    dateEmbauche
)
VALUE
(
    'g101',
    'Test',
    'Test',
    'aaa',
    'aaa',
    'Montreuil',
    'FantasicShit',
    '93100',
    '2024-10-21'
);
-- Updated by ts1 
