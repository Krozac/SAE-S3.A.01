<?php

echo '<div class = "custom-form">';

use App\Vote\Config\FormConfig as FormConfig;
use App\Vote\Lib\MessageFlash;


$_SESSION[FormConfig::$arr]['type'] = 'votants';

if (array_key_exists('user', $_POST)) {
    adduser($_POST["user"]);
}
if (array_key_exists('delete', $_POST)) {
    removeuser($_POST["delete"]);
}
if (isset($_POST['next'])) {
    FormConfig::postSession();
    $_SESSION['step'][3] = 3;
    FormConfig::redirect("index.php?controller=question&action=form&step=6");
} else if (isset($_POST['previous'])) {
    FormConfig::postSession();
    FormConfig::redirect("index.php?controller=question&action=form&step=4");
}


function adduser(string $id): void
{
    if (!in_array($id, $_SESSION[FormConfig::$arr][$_SESSION[FormConfig::$arr]['type']])) {
        $_SESSION[FormConfig::$arr][$_SESSION[FormConfig::$arr]['type']][] = $id;
    } else {
        MessageFlash::ajouter('warning', "Cet utilisateur est déjà sélectionné en tant que votant");
    }
}

function removeuser(string $id): void
{
    if (($key = array_search($id, $_SESSION[FormConfig::$arr][$_SESSION[FormConfig::$arr]['type']])) !== false) {
        unset($_SESSION[FormConfig::$arr][$_SESSION[FormConfig::$arr]['type']][$key]);
    }
}


require_once "../src/View/Utilisateurs/select.php";
?>
    <form method="post">
        <p>
            <label for="systemeVote">Système de vote : </label>
            <select name="systemeVote" id="systemeVote">
                <option value="valeur" <?=FormConfig::DropDown("systemeVote", "valeur")?>>Vote par valeur </option>
                <option value="majoritaire"  <?=FormConfig::DropDown("systemeVote", "majoritaire")?>>Jugement majoritaire </option>
            </select>
        </p>
        <input type="submit" name=previous value="Retour" class="nav" id="precedent" formnovalidate>
        <input type="submit" name=next value="Suivant" class="nav" id="suivant">
    </form>
</div>
