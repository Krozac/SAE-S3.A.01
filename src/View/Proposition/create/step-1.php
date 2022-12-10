<?php

use App\Vote\Config\FormConfig as FormConfig;
use App\Vote\Model\Repository\QuestionRepository;
use App\Vote\Model\DataObject\Question;

if (isset($_POST['next'])) {
    if ($_GET['action'] == "create") {
            FormConfig::redirect('index.php?controller=proposition&step=2&action=create&idQuestion' . $_GET['idQuestion']);
    } else if($_GET['action'] == "update"){
        FormConfig::postSession();
        if(CoAuteur::estCoAuteur()){
            FormConfig::redirect('index.php?controller=proposition&action=updated&idProposition' . $_GET['idQuestion']);
        }
        FormConfig::redirect("index.php?controller=proposition&step=2&action=update&idQuestion" . $_GET['idQuestion']);
    }
}
?>

<h1>Création d'une proposition</h1>

<h2>Titre : <?= $question->getTitre() ?></h2>
<h2>Description : <?= $question->getDescription() ?></h2>
<form method="post" action=index.php?controller=proposition&action=created&idQuestion=<?= $question->getId() ?>>

    <p>
        <label for="titre_id">Titre de votre proposition
            <input type="text" maxlength="500" size="80" value="" name="titre">
        </label>
        <label for="max_id">480 caractères maximum</label>
    </p>
    <h2>Désigner les co-auteurs qui vous aideront à rédiger votre proposition :</h2>

    <?php
    $sections = $question->getSections();
    $i = 0;
    foreach ($sections as $section) {
        $i++;
        echo '<h2>Section n°' . $i . '</h2>';
        echo '<p>Titre : ' . $section->getTitre() . ' </p > ';
        echo '<p>Description : ' . $section->getDescription() . ' </p > ';
        echo '
    <p class="champ">
        <label for=contenu_id> Contenu</label > :
        <textarea name=contenu' . $section->getId() . ' id = contenu_id maxlength=1500 rows = 8 cols = 80 ></textarea >
        <label for=max_id>1400 caractères maximum</label>
    </p> ';
    }
    ?>
    <input type="submit" value="Suivant" CLASS="nav"/>
</form>



