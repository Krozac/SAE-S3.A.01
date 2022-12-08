<?php

use App\Vote\Config\FormConfig as FormConfig;

require "../src/View/Utilisateurs/searchselect.php";
if (!isset($utilisateurs)) {
    $utilisateurs = [];
}

if (isset($_POST["keyword"])) {
    $keyword = $_POST["keyword"];
} else {
    $keyword = "";
}

?>

<form method=post>
    <?php
    foreach ($_SESSION[FormConfig::$arr][$_SESSION[FormConfig::$arr]['type']] as $responsable) {
        echo '
                <span id="utilisateurs" class="listes">
                <button type = submit value = "' . $responsable . '" name = "delete">' . $responsable . '</button>
                <img class="delete" src="../../web/images/delete.png" alt="supprimer">
                </span>
            ';

    }
    ?>
    <input type="hidden" name="row" value="nom"/>
    <input type="hidden" name="keyword" value="<?php echo $keyword ?>"/>
</form>
<?php
foreach ($utilisateurs as $utilisateur) {
    $nom = htmlspecialchars($utilisateur->getNom());
    $prenom = htmlspecialchars($utilisateur->getPrenom());
    $identifiant = $utilisateur->getIdentifiant();
    if (in_array($identifiant, $_SESSION[FormConfig::$arr][$_SESSION[FormConfig::$arr]['type']])) {
        $disable = "disabled";
    } else {
        $disable = "";
    }
    echo '

                    <form method= post>
                        <p><button type = submit value = "' . $identifiant . '" name = "user">' . $nom . ' ' . $prenom . '</button></p>
                        <input type ="hidden" name = "row" value = "nom" />

                        <input type = "hidden" name = "keyword" value ="' . $keyword . '"/> 
       
                        

                    </form>
                    ';
}
?>


