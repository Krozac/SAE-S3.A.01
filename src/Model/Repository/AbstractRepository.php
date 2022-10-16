<?php

namespace App\Vote\Model\Repository;
use App\Vote\Model\DatabaseConnection as DatabaseConnection;
abstract class AbstractRepository
{

    public function selectAll(): array
    {
        $ADonnees = array();
        $pdoStatement = DatabaseConnection::getPdo()->query('SELECT * FROM '.($this->getNomTable()));

        foreach ($pdoStatement as $donneesFormatTableau) {

            $ADonnees[] = $this::construire(json_decode(json_encode($donneesFormatTableau), true));
        }
        return $ADonnees;
    }

    public function selectKeyword($motclef,$row)
    {
        $ADonnees = array();
        $sql = 'SELECT * from '.$this->getNomTable() .' WHERE LOWER('.$row .') LIKE LOWER(:motclef) ';
        // Préparation de la requête
        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);

        $values = array(
            "motclef" => $motclef.'%',
        );

        $pdoStatement->execute($values);

        foreach ($pdoStatement as $donneesFormatTableau) {
            $ADonnees[] = $this::construire(json_decode(json_encode($donneesFormatTableau), true));
        }

        return $ADonnees;
    }

    protected abstract function getNomTable(): string;
    protected abstract function construire(array $objetFormatTableau);
    protected abstract function getNomClePrimaire(): string;
}