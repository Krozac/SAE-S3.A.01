<?php

use App\Vote\Model\Repository\PropositionRepository;
use \App\Vote\Model\Repository\PropositionSectionRepository;
use App\Vote\Model\DataObject\AbstractDataObject;
use App\Vote\Model\DataObject\Question;


?>

<h1><strong class="custom_strong color-orange">Détail de la proposition</strong></h1>
<h1><strong class="custom_strong color-grey">Titre question : </strong></h1>
<h2><?= htmlspecialchars($question->getTitre()) ?></h2>
<h1><strong class="custom_strong color-grey">Description question : </strong></h1>
<h2 id="description"><?= htmlspecialchars($question->getDescription()) ?></h2>

<?php

$i = 1;
?>
<h1><strong class="custom_strong color-blue">Titre de la proposition : </strong></h1>
<?php
echo '<h1>' . htmlspecialchars($proposition->getTitre()) . '</h1>';
echo '
<div id="participants" class="detail_question">';
echo '
    <div>';
echo '<h1><strong class="custom_strong color-yellow">Auteur</strong></h1>';

if (!is_null($proposition->getIdResponsable())) {
    echo "<p>" . htmlspecialchars($proposition->getIdResponsable()) . "</p>";
}

echo '
    </div>
    ';
echo '
    <div id="votants">';
echo '<h1><strong class=" custom_strong color-yellow">Co-Auteurs</strong></h1>';
if (!is_null($coAuts)) {
    if (is_array($coAuts)) {
        foreach ($coAuts as $coAut) {
            echo "<p>" . htmlspecialchars($coAut->getUtilisateur()->getIdentifiant()) . "</p>";
        }
    } else {
        echo "<p>" . htmlspecialchars($coAuts->getIdentifiant()) . "</p>";
    }
} else {
    echo "<p>Aucun co-auteur</p>";
}
echo '
    </div>
    ';
echo '
</div>';

$propSection = (new PropositionSectionRepository())->selectWhere($proposition->getId(), '*', 'idproposition', 'Proposition_section');
foreach ($sections as $section) {
    echo '
<div id="detail_section" class="detail_question" >';
    echo '<h1><strong class=" custom_strong color-yellow">Section n°' . $i . '</strong></h1></h1>';
    echo '<h2><strong >Titre Section : </strong></h2><p>' . htmlspecialchars($section->getTitre()) . ' </p> ';
    echo '<h2><strong >Description Section : </strong></h2><p>' . htmlspecialchars($section->getDescription()) . ' </p> ';
    echo '
    <p>
        <h2><strong >Contenu : </strong></h2><p>
        ' . htmlspecialchars($propSection[$i - 1]->getContenu()) . '
    </p></div>';

    $i = $i + 1;
}
?>



