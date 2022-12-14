<?php

namespace App\Vote\Model\Repository;

use App\Vote\Model\DataObject\Auteur;
use App\Vote\Model\DataObject\Utilisateur;

class AuteurRepository extends AbstractRepository
{
    protected function construire(array $questionTableau): Auteur
    {
        $auteur = new Auteur(
            (new QuestionRepository())->select($questionTableau['idquestion']),
            (new UtilisateurRepository())->select($questionTableau['idutilisateur'])
        );
        return $auteur;
    }

    protected function getNomTable(): string
    {
        return "Auteurs";
    }

    protected function getNomClePrimaire(): string
    {
        return "idUtilisateur";
    }

    protected function getNomsColonnes(): array
    {
        return array("idquestion", "titre", "description");

    }
}
