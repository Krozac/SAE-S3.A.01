<?php

namespace App\Vote\Model;

use App\Vote\Config\Conf as Conf;
use PDO;

class DatabaseConnection
{
    private PDO $pdo;
    private static ?DatabaseConnection $instance = null;

    /**
     * @param $pdo
     */
    public function __construct()
    {
        $login = Conf::getLogin();
        $hostname = Conf::getHostname();
        $password = Conf::getPassword();
        $databaseName = Conf::getDatabase();
        // Connexion à la base de données
        // Le dernier argument sert à ce que toutes les chaines de caractères
        // en entrée et sortie de MySql soit dans le codage UTF-8
        $this->pdo = new PDO("mysql:host=$hostname;dbname=$databaseName", $login, $password,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

        // On active le mode d'affichage des erreurs et le lancement d'exception en cas d'erreur
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @return mixed
     */
    public static function getPdo(): PDO
    {
        return static::getInstance()->pdo;
    }

    private static function getInstance(): DatabaseConnection
    {
        // L'attribut statique $pdo s'obtient avec la syntaxe static::$pdo
        // au lieu de $this->pdo pour un attribut non statique
        if (is_null(static::$instance))
            // Appel du constructeur
            static::$instance = new DatabaseConnection();
        return static::$instance;
    }

}

?>