<?php

namespace App\Vote\Model\Repository;

use App\Vote\Model\DatabaseConnection as DatabaseConnection;
use App\Vote\Model\DataObject\AbstractDataObject;
use App\Vote\Model\DataObject\Calendrier;
use App\Vote\Model\DataObject\Proposition;
use App\Vote\Model\DataObject\Question;
use App\Vote\Model\DataObject\Section;
use App\Vote\Model\DataObject\Utilisateur;
use PDOException;

abstract class AbstractRepository
{
    /**
     * Sauvegarde l'objet dans la base de données
     * en récupérant les colonnes et la table associés à la classe
     * @param AbstractDataObject $object
     * @return ?int
     */
    public function sauvegarder(AbstractDataObject $object, $return = false): ?int
    {
        $sql = "INSERT INTO " . $this->getNomTable();
        $sql = $sql . " (";
        foreach ($this->getNomsColonnes() as $colonne) {
            $sql = $sql . $colonne . ", ";
        }
        $sql = substr($sql, 0, -2);
        $sql = $sql . ") VALUES (";
        foreach ($this->getNomsColonnes() as $colonne) {
            $sql = $sql . ":" . $colonne . "Tag, ";
        }
        $sql = substr($sql, 0, -2);
        $sql = $sql . ")";
        if ($return) {
            $sql = $sql . " RETURNING " . $this->getNomClePrimaire();
        }
        $sql = $sql . ";";
        // Préparation de la requête
        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);
        // On donne les valeurs et on exécute la requête
        try {
            $pdoStatement->execute($object->formatTableau());
            foreach ($pdoStatement as $clePrimaire) {
                if (isset($clePrimaire[0])) {
                    return $clePrimaire[0];
                }
            }
        } catch (PDOException $e) {
            echo($e->getMessage());
        }
        return null;
    }

    /**
     * Supprime la ligne de la base de données grâce à la clef primaire
     * @param string $valeurClePrimaire
     */
    public function delete(string $valeurClePrimaire)
    {
        $sql = "DELETE FROM " . $this->getNomTable() . " WHERE " . $this->getNomClePrimaire() . " =:clePrimaireTag";
        $value = array(
            "clePrimaireTag" => $valeurClePrimaire
        );
        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);
        try {
            $pdoStatement->execute($value);
        } catch (PDOException $e) {
            echo($e->getMessage());
        }
    }

    /**
     * Supprime la ligne de la base de données grâce à la clef primaire
     * @param AbstractDataObject $object
     */
    public function deleteSpecific(AbstractDataObject $object)
    {
        $sql = "DELETE FROM " . $this->getNomTable() . " WHERE ";
        $i = 0;
        foreach ($this->getNomsColonnes() as $colonne) {
            $sql = $sql . $colonne . " =:" . $colonne . "Tag";
            if ($i != sizeof($this->getNomsColonnes()) - 1) {
                $sql = $sql . " AND ";
            }
            $i = $i + 1;
        }
        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);
        try {
            $pdoStatement->execute($object->formatTableau(true));
        } catch (PDOException $e) {
            echo($e->getMessage());
        }
    }

    /**
     * met à jour l'objet dans la base de données
     * @param AbstractDataObject $object
     */
    public function update(AbstractDataObject $object)
    {
        $sql = "UPDATE " . $this->getNomTable() . "
                SET ";
        foreach ($this->getNomsColonnes() as $colonne) {
            $sql = $sql . $colonne . " =:" . $colonne . "Tag, ";
        }
        $sql = substr($sql, 0, -2);
        $sql = $sql . " WHERE " . $this->getNomClePrimaire() . "=:" . $this->getNomClePrimaire() . "Tag;";
        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);
        // On donne les valeurs et on exécute la requête
        try {
            $pdoStatement->execute($object->formatTableau(true));
        } catch (PDOException $e) {
            echo($e->getMessage());
        }
    }

    /**
     * Sélectionne toutes les lignes de la table associée à la classe
     * @return array
     */
    public function selectAll(): array
    {
        $ADonnees = array();
        $pdoStatement = DatabaseConnection::getPdo()->query('SELECT * FROM ' . ($this->getNomTable()));

        foreach ($pdoStatement as $donneesFormatTableau) {

            $ADonnees[] = $this::construire(json_decode(json_encode($donneesFormatTableau), true));
        }
        return $ADonnees;
    }


    /**
     * Sélectionne les lignes par rapport à un mot clef
     * @param string $motclef
     * @param string $row
     * @return array
     */
    public function selectKeyword($motclef, $row)
    {
        $ADonnees = array();
        $sql = 'SELECT * from ' . $this->getNomTable() . ' WHERE ' . $row . ' LIKE :motclef';
        // Préparation de la requête
        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);

        $values = array(
            "motclef" => '%' . $motclef . '%',
        );
        $pdoStatement->execute($values);

        foreach ($pdoStatement as $donneesFormatTableau) {
            $ADonnees[] = $this::construire(json_decode(json_encode($donneesFormatTableau), true));
        }

        return $ADonnees;
    }


    /**
     * Sélectionne une ligne par rapport à la clef primaire
     * @param string $clef
     * @return ?AbstractDataObject
     */

    public function select($clef): ?AbstractDataObject
    {
        $sql = 'SELECT * from ' . $this->getNomTable() . ' WHERE ' . $this->getNomClePrimaire() . '=:clef';
        // Préparation de la requête
        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);

        $values = array(
            "clef" => $clef,
        );
        // On donne les valeurs et on exécute la requête
        $pdoStatement->execute($values);
        // On récupère les résultats comme précédemment
        // Note : fetch() renvoie false si pas d'objet correspondant
        $data = $pdoStatement->fetch();
        if (!$data) {
            return null;
        }
        return $this->construire($data);
    }


        // Cette fonction sélectionne des données dans une base de données en utilisant une condition WHERE
        // $clef : valeur à chercher dans la colonne de la clé primaire ou dans les colonnes spécifiées dans $whereCondition
        // $rowSelect : les colonnes à sélectionner (par défaut : * pour toutes)
        // $whereCondition : les colonnes à utiliser dans la condition WHERE (si null, utilise la clé primaire)
        // $nomTable : le nom de la table où sélectionner les données (si null, utilise la table de l'objet courant)
        // $conditionTrie : colonne sur laquelle trier les résultats
        // $ordre : ordre de tri ('ASC' ou 'DESC', optionnel)
    public function selectWhere($clef, string $rowSelect = '*', $whereCondition = null, $nomTable = null,
                                    $conditionTrie = null, $ordre = null): array
    {
        $ADonnees = array();
        if (is_null($nomTable)) {
            $sql = 'SELECT ' . $rowSelect . ' from ' . $this->getNomTable();
        } else {
            $sql = 'SELECT ' . $rowSelect . ' from ' . $nomTable;
        }
        if (is_null($whereCondition)) {
            $sql = $sql . ' WHERE ' . $this->getNomClePrimaire() . ' =:clef';
        } else if (!is_array($whereCondition)) {
            $sql = $sql . ' WHERE ' . $whereCondition . ' =:clef';
        } else {
            $nbCases = sizeof($whereCondition);
            $i = 0;
            foreach ($whereCondition as $where) {
                if ($i == 0) {
                    $sql = $sql . ' WHERE ';
                }
                $sql = $sql . $where . ' =:clef' . $i . ' ';
                if ($i != $nbCases - 1) {
                    $sql = $sql . 'AND ';
                }
                $i++;
            }
        }
        if ($conditionTrie){
            $sql = $sql . ' ORDER BY ' . $conditionTrie . ' ' . $ordre;
        }
        // Préparation de la requête
        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);
        if (!is_array($clef)) {
            $values = array(
                "clef" => $clef,
            );
            $pdoStatement->execute($values);
        } else {
            $pdoStatement->execute($clef);
        }
        foreach ($pdoStatement as $donneesFormatTableau) {
            $ADonnees[] = $this::construire(json_decode(json_encode($donneesFormatTableau), true));
        }

        return $ADonnees;
    }

    protected abstract function getNomTable(): string;

    protected abstract function construire(array $objetFormatTableau);

    protected abstract function getNomClePrimaire(): string;

    protected abstract function getNomsColonnes(): array;
}