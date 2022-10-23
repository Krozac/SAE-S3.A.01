<?php
session_start();

use App\Vote\Config\FormConfig as FormConfig;

if (isset($_POST['Titre'])) {
    FormConfig::postSession();
    $_SESSION['step'][1] = 1;
    if (!isset($_SESSION['auteurs']) && !isset($_SESSION['votants'])) {
        $_SESSION['auteurs'] = array();
        $_SESSION['votants'] = array();

    }
    FormConfig::redirect("index.php?controller=question&action=form&step=2");
}

?>

<h1>Création d'un question</h1>


<form method="post">

    <p>
        <label for="titre_id">Titre</label> :
        <input type="text" placeholder="Ex : " name="Titre" id="titre_id" value="<?= FormConfig::TextField('Titre') ?>"
               required/>
    </p>
    <p>
        <label for="nbSections_select">Nombre de sections</label>
        <select name="nbSections" id="nbSections_select">
            <?php
            for ($i = 1; $i <= 10; $i++) {
                echo "<option value=" . $i . FormConfig::DropDown("nbSections", $i) . " >" . $i . "</option>";

            } ?>
        </select>
    </p>

    <input type="submit" value="Suivant" class="nav"/>
</form>

<?php
if (isset($message)) {
    echo "<p><img src=\"/web/images/attention.png\" class=\"attention\" > " . $message . "</p>";
} ?>


