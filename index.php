<?php

require_once "src/Model/Model.php";
$pagetitle = "index";
$cheminVueBody = "Acceuil/acceuil.php";
if (Model::getPDO()!= NULL){
    echo "<p>connexion à la base de donnée confirmée</p>";
    $pdoStatement = Model::getPdo()->query('SELECT * FROM utilisateur WHERE id = 0');
    $tab = $pdoStatement->fetch();
    foreach ($tab as $var){
        echo $var . "    ";
    }
}
require "src/View/view.php";
?>