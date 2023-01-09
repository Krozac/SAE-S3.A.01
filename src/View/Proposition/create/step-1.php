<?php

use App\Vote\Config\FormConfig as FormConfig;
use App\Vote\Controller\ControllerAccueil;
use App\Vote\Lib\ConnexionUtilisateur;
use App\Vote\Model\Repository\PropositionRepository;


$readOnly = "";
if (isset($_GET['idProposition'])) {
    echo "<h1>Modification de la Proposition</h1>";
    if ($proposition->getIdResponsable() != ConnexionUtilisateur::getLoginUtilisateurConnecte()) {
        $readOnly = "readonly";
    }
} else {
    echo "<h1>Création d'une Proposition</h1>";
}
if (isset($_POST['titre'])) {
    FormConfig::postSession();
    $_SESSION[FormConfig::$arr]['step'][1] = 1;
    if (!isset($_SESSION[FormConfig::$arr]['co-auteur'])) {
        $_SESSION[FormConfig::$arr]['co-auteur'] = array();
    }
    FormConfig::redirect("index.php?controller=proposition&action=form&step=2&idQuestion=".rawurlencode($question->getId()));
}
?>
<h2>Titre : <?= htmlspecialchars($question->getTitre()) ?></h2>
<h2>Description : <?= htmlspecialchars($question->getDescription()) ?></h2>

<form method="post" class="custom-form">
    <p class="InputAddOn">
        <label class="InputAddOn-item" for="titre_id">Titre de votre proposition </label>
        <input class="InputAddOn-field" type="text" maxlength="480" id="titre_id" size="80"
               value="<?= FormConfig::TextField('titre') ?> " <?= $readOnly ?>
               name="titre">
        <label class="maximum">480 caractères maximum</label>
    </p>
    <!--<h2>Désigner les co-auteurs qui vous aideront à rédiger votre proposition :</h2>-->

    <?php
    $sections = $question->getSections();
    $i = 0;
    foreach ($sections as $section) {
        $i++;
        echo '<h2>Section n°' . $i . '</h2>';
        echo '<p>Titre : ' . htmlspecialchars($section->getTitre()) . ' </p > ';
        echo '<p>Description : ' . htmlspecialchars($section->getDescription()) . ' </p > ';
        echo '
    <p class="champ">
        <label class="InputAddOn-item" for=contenu_id> Contenu : </label > 
        <textarea name=contenu' . $section->getId() . ' id = contenu_id maxlength=1400 rows = 8 cols = 80 >' . FormConfig::TextField('contenu' . $section->getId()) . '</textarea >
        <label class="maximum">1400 caractères maximum</label>
    </p> ';
    }
    ?>
    <input type="submit" name="next" value="Suivant" class="nav" id="suivant">
</form>



