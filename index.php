<?php

require_once "src/Model/DatabaseConnection.php";
$pagetitle = "index";
$cheminVueBody = "Acceuil/acceuil.php";
if (DatabaseConnection::getPDO()!= NULL){
    echo "<p>connexion à la base de donnée confirmée</p>";
    $pdoStatement = DatabaseConnection::getPdo()->query('SELECT * FROM utilisateur WHERE id = 0');
    $tab = $pdoStatement->fetch();
    foreach ($tab as $var){
        echo $var . "    ";
    }
}
require "src/View/view.php";
?>