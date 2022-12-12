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
    public function sauvegarder(AbstractDataObject $object): ?int
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
        if (get_class($object) == Question::class || get_class($object) == Proposition::class
            || get_class($object) == Section::class || get_class($object) == Calendrier::class) {
            $sql = $sql . " RETURNING " . $this->getNomClePrimaire();
        }
        echo $sql;
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
            if($i != sizeof($this->getNomsColonnes())-1){
                $sql = $sql . " AND ";
            }
            $i = $i+1;
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

    /**
     * Permet de construire une requete sql grace aux contraintes passées en paramètres
     * @param $clef             //Clef primaire
     * @param $rowSelect        //Les colonnes que vous voulez selectionner '*' par défaut si le parametre n'est pas renseigné
     * @param $whereCondition   //La condition qui sera placée dans le WHERE,
                                //null par défaut qui sera remplacé dans la requete pas la clé primaire si le parametre n'est pas renseigné
     * @param $nomTable         //le nom de la table, null par défaut qui sera remplacé dans la requete par la table par défaut
     * @return array
     */
    public function selectWhere($clef, $rowSelect = '*', $whereCondition = null, $nomTable = null): array
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
                } else {
                    $sql = $sql . ';';
                }
                $i++;
            }
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


    /**
     * Permet de construire une requete sql grace aux contraintes passées en paramètres
     * @param $clef             //Clef primaire
     * @param $rowSelect        //Les colonnes que vous voulez selectionner '*' par défaut si le parametre n'est pas renseigné
     * @param $whereCondition   //La condition qui sera placée dans le WHERE,
    //null par défaut qui sera remplacé dans la requete pas la clé primaire si le parametre n'est pas renseigné
     * @param $nomTable         //le nom de la table, null par défaut qui sera remplacé dans la requete par la table par défaut
     * @return array
     */
    public function selectWhereTrie($clef, $rowSelect = '*', $whereCondition = null, $nomTable = null): array
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
        $sql = $sql . ' ORDER BY nbvotes DESC;';
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