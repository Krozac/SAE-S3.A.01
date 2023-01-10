<?php
echo '<div  class ="custom-form">';

use App\Vote\Config\FormConfig as FormConfig;
use App\Vote\Model\DataObject\Responsable;

$_SESSION[FormConfig::$arr]['type'] = "co-auteur";


if (isset($_POST['next'])) {
    if (isset($_SESSION[FormConfig::$arr]['idProposition'])) {
        FormConfig::redirect('index.php?controller=proposition&action=updated');
    } else {
        FormConfig::postSession();
        FormConfig::redirect("index.php?controller=proposition&action=created&idQuestion=" . $question->getId());
    }
} else if (isset($_POST['previous'])) {
    FormConfig::postSession();
    if (isset($_GET['idProposition'])) {
        FormConfig::redirect("index.php?controller=proposition&action=form&step=1&idProposition=" . $_GET['idProposition'] . "&idQuestion=" . $question->getId());
    } else {
        FormConfig::redirect("index.php?controller=proposition&action=form&step=1&idQuestion=". $question->getId());

    }
}


if (array_key_exists('user', $_POST)) {
    adduser($_POST["user"]);
}
if (array_key_exists('delete', $_POST)) {
    removeuser($_POST["delete"]);
}

function adduser(string $id): void
{
    if (!in_array($id, $_SESSION[FormConfig::$arr][$_SESSION[FormConfig::$arr]['type']])) {
        $_SESSION[FormConfig::$arr][$_SESSION[FormConfig::$arr]['type']][] = $id;
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
<form method="post" class="nav">
    <input type="submit" name=previous id="precedent" value="Retour">
    <input type="submit" name=next id="suivant" value="Valider">
</form>
</div>