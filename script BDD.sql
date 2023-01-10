create or replace table Messages
(
    idAuteur       varchar(30)                            not null,
    idDestinataire varchar(30)                            not null,
    contenu        varchar(350)                           not null,
    date           timestamp  default current_timestamp() not null on update current_timestamp(),
    estVu          tinyint(1) default 0                   null,
    idMessage      int auto_increment
    primary key
);

create or replace table `Système de vote`
(
    nom varchar(30) not null
        primary key
);

create or replace table Utilisateurs
(
    identifiant   varchar(30)          not null
    primary key,
    nom           varchar(30)          null,
    prenom        varchar(30)          null,
    mdp           varchar(256)         null,
    estAdmin      tinyint(1) default 0 null,
    email         varchar(256)         not null,
    emailAValider varchar(256)         not null,
    nonce         varchar(32)          not null
);

create or replace table Questions
(
    idquestion     int auto_increment
    primary key,
    titre          varchar(80)                           null,
    description    varchar(360)                          null,
    idorganisateur varchar(30)                           null,
    creation       timestamp default current_timestamp() not null on update current_timestamp(),
    systemeVote    varchar(30)                           null,
    constraint `Questions_Système de vote_nom_fk`
    foreign key (systemeVote) references `Système de vote` (nom)
    on update cascade on delete cascade,
    constraint Questions_Utilisateurs_identifiant_fk
    foreign key (idorganisateur) references Utilisateurs (identifiant)
    on update cascade on delete cascade
);

create or replace table Calendriers
(
    idQuestion    int                                   null,
    idCalendrier  int auto_increment
    primary key,
    debutecriture timestamp                             null,
    finecriture   timestamp                             null,
    debutvote     timestamp default current_timestamp() not null,
    finvote       timestamp default current_timestamp() not null,
    constraint Calendriers_Questions_null_fk
    foreign key (idQuestion) references Questions (idquestion)
    on update cascade on delete cascade,
    constraint check_date_2phases
    check (`finecriture` <= `debutvote` or `finecriture` is null),
    constraint check_date_vote
    check (`debutvote` < `finvote`),
    constraint date_check_ecriture
    check (`debutecriture` < `finecriture` or `debutecriture` is null)
);

create or replace table Responsables
(
    idquestion    int         not null,
    idutilisateur varchar(30) not null,
    primary key (idquestion, idutilisateur),
    constraint Responsables_Questions_idquestion_fk
    foreign key (idquestion) references Questions (idquestion)
    on update cascade on delete cascade,
    constraint Responsables_Utilisateurs_identifiant_fk
    foreign key (idutilisateur) references Utilisateurs (identifiant)
    on update cascade on delete cascade
);

create or replace table Propositions
(
    idquestion    int                  not null,
    idresponsable varchar(30)          not null,
    titre         varchar(500)         null,
    idproposition int auto_increment,
    nbetoiles     int        default 0 null,
    nbvotes       int        default 0 not null,
    estEliminee   tinyint(1) default 0 null,
    primary key (idquestion, idresponsable),
    constraint Propositions_pk
    unique (idproposition),
    constraint Propositions_Questions_idquestion_fk
    foreign key (idquestion, idresponsable) references Responsables (idquestion, idutilisateur)
    on update cascade on delete cascade
);

create or replace table Coauteurs
(
    idauteur      varchar(30) not null,
    idproposition int         not null,
    primary key (idproposition, idauteur),
    constraint `Co-auteurs_Propositions_idproposition_fk`
    foreign key (idproposition) references Propositions (idproposition)
    on update cascade on delete cascade,
    constraint `Co-auteurs_Utilisateurs_identifiant_fk`
    foreign key (idauteur) references Utilisateurs (identifiant)
    on update cascade on delete cascade
);

create or replace table Sections
(
    idsection   int auto_increment,
    idquestion  int          not null,
    titre       varchar(80)  null,
    description varchar(360) null,
    primary key (idsection, idquestion),
    constraint Sections_Questions_idquestion_fk
    foreign key (idquestion) references Questions (idquestion)
    on update cascade on delete cascade
);

create or replace table Proposition_section
(
    idsection     int           not null,
    contenu       varchar(1500) null,
    idproposition int           not null,
    primary key (idsection, idproposition),
    constraint Proposition_section_Propositions_idproposition_fk
    foreign key (idproposition) references Propositions (idproposition)
    on update cascade on delete cascade,
    constraint Proposition_section_Sections_idsection_fk
    foreign key (idsection) references Sections (idsection)
    on update cascade on delete cascade
);

create or replace table Votants
(
    idquestion    int         not null,
    idutilisateur varchar(30) not null,
    primary key (idquestion, idutilisateur),
    constraint Votants_Questions_idquestion_fk
    foreign key (idquestion) references Questions (idquestion)
    on update cascade on delete cascade,
    constraint Votants_Utilisateurs_identifiant_fk
    foreign key (idutilisateur) references Utilisateurs (identifiant)
    on update cascade on delete cascade
);

create or replace table Votes
(
    idvotant      varchar(30) not null,
    idproposition int         not null,
    valeurvote    int         null,
    idvote        int auto_increment,
    primary key (idvotant, idproposition),
    constraint idvote
    unique (idvote),
    constraint Votes_Propositions_idproposition_fk
    foreign key (idproposition) references Propositions (idproposition)
    on update cascade on delete cascade,
    constraint Votes_Utilisateurs_identifiant_fk
    foreign key (idvotant) references Utilisateurs (identifiant)
    on update cascade on delete cascade
);

create or replace definer = dimeckr@`%` trigger tr_maj_nbEtoiles_DELETE
    after delete
          on Votes
              for each row
UPDATE Propositions
SET nbetoiles = nbetoiles - OLD.valeurVote
WHERE idproposition = OLD.idproposition;

create or replace definer = dimeckr@`%` trigger tr_maj_nbEtoiles_INSERT
    after insert
          on Votes
              for each row
UPDATE Propositions
SET nbetoiles = nbetoiles + NEW.valeurVote
WHERE idproposition = NEW.idproposition;

create or replace definer = dimeckr@`%` trigger tr_maj_nbEtoiles_UPDATE
    after update
                                on Votes
                                for each row
UPDATE Propositions
SET nbetoiles = nbetoiles - (OLD.valeurvote - NEW.valeurvote)
WHERE idproposition = OLD.idproposition;

create or replace definer = dimeckr@`%` trigger tr_maj_nbVotes_DELETE
    after delete
          on Votes
              for each row
UPDATE Propositions
SET nbvotes = nbvotes - 1
WHERE idproposition = OLD.idProposition;

create or replace definer = dimeckr@`%` trigger tr_maj_nbVotes_INSERT
    after insert
          on Votes
              for each row
UPDATE Propositions
SET nbvotes = nbvotes + 1
WHERE idproposition = NEW.idProposition;

create or replace definer = dimeckr@`%` view questions_ecriture as
select `q`.`idquestion`     AS `idquestion`,
       `q`.`titre`          AS `titre`,
       `q`.`description`    AS `description`,
       `q`.`idorganisateur` AS `idorganisateur`,
       `c`.`idCalendrier`   AS `idcalendrier`,
       `q`.`creation`       AS `creation`,
       `q`.`systemeVote`    AS `systemeVote`
from (`dimeckr`.`Questions` `q` join `dimeckr`.`Calendriers` `c` on (`c`.`idQuestion` = `q`.`idquestion`))
where `c`.`idCalendrier` = (select `dimeckr`.`getCurrentCalendar`(`q`.`idquestion`))
  and (select current_timestamp() AS `current_timestamp`) < `c`.`finecriture`
  and (select current_timestamp() AS `current_timestamp`) > `c`.`debutecriture`;

create or replace definer = dimeckr@`%` view questions_termines as
select `q`.`idquestion`     AS `idquestion`,
       `q`.`titre`          AS `titre`,
       `q`.`description`    AS `description`,
       `q`.`idorganisateur` AS `idorganisateur`,
       `c`.`idCalendrier`   AS `idcalendrier`,
       `q`.`creation`       AS `creation`,
       `q`.`systemeVote`    AS `systemeVote`
from (`dimeckr`.`Questions` `q` join `dimeckr`.`Calendriers` `c` on (`c`.`idQuestion` = `q`.`idquestion`))
where `c`.`idCalendrier` = (select `dimeckr`.`getCurrentCalendar`(`q`.`idquestion`))
  and (select current_timestamp() AS `current_timestamp`) > `c`.`finvote`;

create or replace definer = dimeckr@`%` view questions_vote as
select `q`.`idquestion`     AS `idquestion`,
       `q`.`titre`          AS `titre`,
       `q`.`description`    AS `description`,
       `q`.`idorganisateur` AS `idorganisateur`,
       `c`.`idCalendrier`   AS `idcalendrier`,
       `q`.`creation`       AS `creation`,
       `q`.`systemeVote`    AS `systemeVote`
from (`dimeckr`.`Questions` `q` join `dimeckr`.`Calendriers` `c` on (`c`.`idQuestion` = `q`.`idquestion`))
where `c`.`idCalendrier` = (select `dimeckr`.`getCurrentCalendar`(`q`.`idquestion`))
  and (select current_timestamp() AS `current_timestamp`) > `c`.`debutvote`
  and (select current_timestamp() AS `current_timestamp`) < `c`.`finvote`;

