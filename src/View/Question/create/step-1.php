<?php

use App\Vote\Config\FormConfig as FormConfig;
use App\Vote\Model\Repository\QuestionRepository;

if (isset($_GET['idQuestion']) or isset($_SESSION[FormConfig::$arr]['idQuestion']) ) {
    echo "<h1>Modification de la question</h1>";
    if (isset($_GET['idQuestion'])) {
        $question = (new QuestionRepository())->select($_GET['idQuestion']);
        if ($question == null) {
            \App\Vote\Controller\ControllerAccueil::erreur();
        } else {
            $_SESSION['SessionQuestion']['idQuestion'] = $_GET['idQuestion'];
            FormConfig::initialiserSessions($question);
        }
    }

} else {
    echo "<h1 class = 'custom_titre'>Création d'une question</h1>";
}



if (isset($_POST['Titre'])) {
    FormConfig::postSession();
    $_SESSION[FormConfig::$arr]['step'][1] = 1;
    if (!isset($_SESSION[FormConfig::$arr]['responsables']) && !isset($_SESSION[FormConfig::$arr]['votants'])) {
        $_SESSION[FormConfig::$arr]['responsables'] = array();
        $_SESSION[FormConfig::$arr]['votants'] = array();

    }
    FormConfig::redirect("index.php?controller=question&action=form&step=2");
}

?>


<form method="post" class = "custom-form">

    <p class="InputAddOn">
        <label class="InputAddOn-item" for="titre_id">Titre : </label>
        <input type="text" placeholder="L'oeuf ou la poule ? " size="70" maxlength="70" name="Titre" id="titre_id"
               value="<?= FormConfig::TextField('Titre') ?>"
               required>
        <label class="maximum">70 caractères maximum</label>
    </p>
    <p class ="champ">
        <label class="InputAddOn-item" for="description_id">Description : </label>
        <textarea id="description_id" maxlength="350" name="Description" rows="7" cols="50" required><?= FormConfig::TextField('Description'); ?></textarea>
        <script>
            const easyMDE = new createMarkdownEditor({ forceSync: true});
        </script>
        <label class="maximum">350 caractères maximum</label>
    </p>
    <p>
        <label for="nbSections_select">Nombre de sections : </label>
        <select name="nbSections" id="nbSections_select">
            <?php
            for ($i = 1; $i <= 10; $i++) {
                echo "<option value=" . $i . FormConfig::DropDown("nbSections", $i) . " >" . $i . "</option>";

            } ?>
        </select>
    </p>

    <input type="submit" value="Suivant" id="suivant"  class="nav">
</form>

