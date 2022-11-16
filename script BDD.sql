DROP TABLE Utilisateurs CASCADE ;
DROP TABLE Calendriers CASCADE;
DROP TABLE Sections CASCADE;
DROP TABLE Questions CASCADE;
DROP TABLE Propositions CASCADE;
DROP TABLE Votants CASCADE;
DROP TABLE Auteurs CASCADE;


CREATE TABLE Utilisateurs
(
	identifiant VARCHAR(30),
	nom VARCHAR(30),
	prenom VARCHAR(30),
	email VARCHAR(80) UNIQUE,
	CONSTRAINT pk_utilisateur PRIMARY KEY (identifiant),
	CONSTRAINT chk_email check (email like '%_@__%.__%')
);



CREATE TABLE Calendriers (
    idCalendrier serial PRIMARY KEY,
    debutEcriture date,
    finEcriture date,
    debutVote date,
    finVote date
);


CREATE TABLE Questions (
    idQuestion serial PRIMARY KEY,
    titre varchar(30),
    description varchar(100),
    idAuteur VARCHAR(30),
    idCalendrier int,
    FOREIGN KEY (idCalendrier)
          REFERENCES Calendriers (idCalendrier)
);


CREATE TABLE Sections (
    idSection serial,
    idQuestion int,
    titre VARCHAR(30),
    desciption VARCHAR(100),
    PRIMARY KEY (idSection, idQuestion),
    FOREIGN KEY (idQuestion)
          REFERENCES Questions (idQuestion)
);


CREATE TABLE Propositions (
    idQuestion int,
    idUtilisateur VARCHAR(30),
    titre VARCHAR(30),
    contenu VARCHAR(1000),
    PRIMARY KEY (idQuestion,idUtilisateur),
    FOREIGN KEY (idQuestion)
        REFERENCES Questions (idQuestion),
    FOREIGN KEY (idUtilisateur)
        REFERENCES Utilisateurs (identifiant)
);


CREATE TABLE Auteurs (
    idQuestion int,
    idUtilisateur VARCHAR(30),
    PRIMARY KEY (idQuestion,idUtilisateur),
    FOREIGN KEY (idQuestion)
        REFERENCES Questions (idQuestion),
    FOREIGN KEY (idUtilisateur)
        REFERENCES Utilisateurs (identifiant)
);


CREATE TABLE Votants (
    idQuestion int,
    idUtilisateur VARCHAR(30),
    PRIMARY KEY (idQuestion,idUtilisateur),
    FOREIGN KEY (idQuestion)
        REFERENCES Questions (idQuestion),
    FOREIGN KEY (idUtilisateur)
        REFERENCES Utilisateurs (identifiant)
);

CREATE OR REPLACE VIEW Questions_Vote AS
SELECT q.* FROM QUESTIONS q
JOIN CALENDRIERS c
ON q.idcalendrier = c.idcalendrier
WHERE (SELECT CURRENT_DATE)>c.debutvote AND(SELECT CURRENT_DATE)<c.finvote


CREATE OR REPLACE VIEW Questions_Ecriture AS
SELECT q.* FROM QUESTIONS q
JOIN CALENDRIERS c
ON q.idcalendrier = c.idcalendrier
WHERE (SELECT CURRENT_DATE)>c.debutecriture AND(SELECT CURRENT_DATE)<c.finecriture

CREATE OR REPLACE VIEW Questions_Termines AS
SELECT q.* FROM QUESTIONS q
JOIN CALENDRIERS c
ON q.idcalendrier = c.idcalendrier
WHERE (SELECT CURRENT_DATE)>c.finvote