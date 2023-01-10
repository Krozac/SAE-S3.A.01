<?php

use App\Vote\Config\FormConfig as FormConfig;


if (isset($_POST['previous'])) {
    FormConfig::postSession();
    FormConfig::redirect("index.php?controller=question&action=form&step=5");
} else if (isset($_POST['next'])) {
    if (isset($_SESSION[FormConfig::$arr]['idQuestion'])) {
        FormConfig::redirect('index.php?controller=question&action=updated');
    } else {
        FormConfig::postSession();
        FormConfig::redirect("index.php?controller=question&action=created");
    }
}


extract($_SESSION[FormConfig::$arr]);
var_dump($_SESSION[FormConfig::$arr]['systemeVote']);
$_SESSION[FormConfig::$arr]['Sections'] = array();
$ct = 0;
foreach ($_SESSION[FormConfig::$arr] as $key => $value) {
    if (str_starts_with($key, "titre")) {
        $_SESSION[FormConfig::$arr]['Sections'][$ct]['titre'] = $value;
    }
    if (str_starts_with($key, "description")) {
        $_SESSION[FormConfig::$arr]['Sections'][$ct]['description'] = $value;
        $ct++;
    }
}

if (count($_SESSION[FormConfig::$arr]['Sections']) > $_SESSION[FormConfig::$arr]['nbSections']) {
    for ($diff = count($_SESSION[FormConfig::$arr]['Sections']) - $_SESSION[FormConfig::$arr]['nbSections']; $diff > 0; $diff--) {
        unset($_SESSION[FormConfig::$arr]['Sections'][count($_SESSION[FormConfig::$arr]['Sections']) - 1]);
    }
}


?>
<div class="detail_question ">
    <div class="infos">
        <div class="info_question ">
            <h1><strong class='custom_strong color-blue'>Titre</strong></h1>
            <p><?= htmlspecialchars($Titre) ?></p>
            <h1><strong class='custom_strong color-blue'>Description</strong></h1>
            <p class="mdparse"><?= htmlspecialchars($Description) ?></p>
        </div>
        <div id="participants" class="info">
            <h1><strong class='custom_strong color-yellow'>Participants</strong></h1>
            <div>
                <h1><strong class='custom_strong color-yellow'>Responsables</strong></h1>
                <?php
                foreach ($_SESSION[FormConfig::$arr]['responsables'] as $responsable) {
                    echo "<p> " . htmlspecialchars($responsable) . " </p>";
                }
                ?>
            </div>

            <div>
                <h1><strong class='custom_strong color-yellow'>Votants</strong></h1>
                <?php
                foreach ($_SESSION[FormConfig::$arr]['votants'] as $votant) {
                    echo "<p> " . htmlspecialchars($votant) . " </p>";
                }
                ?>
            </div>
        </div>


        <div class="sections  info">
            <h1><strong class='custom_strong color-orange'>Sections</strong></h1>
            <?php
            $i = 1;
            foreach ($_SESSION[FormConfig::$arr]['Sections'] as $Section) {
                echo '<div class = "section">';
                echo '<h3 style = "color:black"> Section n° ' . $i . '</h3>';
                echo '<p>Titre : ' . htmlspecialchars($Section["titre"]) . '  </p>';
                echo '<p>Description : ' . htmlspecialchars($Section["description"]) . '  </p>';
                echo '&nbsp;';
                echo '</div>';
                $i++;
            }
            ?>
        </div>
        <div class="info">
            <div class="calendrier">
                <h1><strong class='custom_strong color-green'>Calendrier</strong></h1>
                <?php
                for ($i = 1; $i <= $_SESSION[FormConfig::$arr]['nbCalendriers']; $i++) {

                    if ($i != 1) {
                        echo '<h2 style="color: #012e49">' . $i . '<sup>e</sup> phase</h2>';
                    }


                    echo '<span class="vertical-line-petite" style="background:grey "></span>';
                    $cercle = '<div id="cercle"></div>';
                    ?>

                    <?php
                    echo '<p style="background: #CE16169B; color: white; padding: 6px" class="cal" id="ecriture_debut' . $i . '">
                                            Début d\'écriture des propositions : <br>
                                    ' . (new DateTime(htmlspecialchars(FormConfig::TextField("debutEcriture" . $i))))->format("d-m-Y à H:i:s") . '</p>
                                    <span class="vertical-line" style="background: #CE16169B"></span>
                                    
                                <p style="background: #CE16169B; color: white; padding: 6px" class="cal" id="ecriture_fin' . $i . '">
                                            Fin d\'écriture des propositions : <br>
                                    ' . (new DateTime(htmlspecialchars(FormConfig::TextField("finEcriture" . $i))))->format("d-m-Y à H:i:s") . '</p>
                        <span class="vertical-line" style="background:grey "></span>';

                    ?>
                    <p style="background : rgba(65,112,56,0.76); color: white; padding: 6px" class="cal"
                       id="vote_debut<?= $i ?>">Début des votes : <br>
                        <?= (new DateTime(htmlspecialchars(FormConfig::TextField('debutVote' . $i))))->format('d-m-Y à H:i:s') ?>
                    </p>
                    <span class="vertical-line" style="background: rgba(65,112,56,0.76);"></span>
                    <p style="background: rgba(65,112,56,0.76); color: white; padding: 6px" class="cal"
                       id="vote_fin<?= $i ?>">
                        Fin des votes : <br>
                        <?= (new DateTime(htmlspecialchars(FormConfig::TextField('finVote' . $i))))->format('d-m-Y à H:i:s') ?>
                    </p>
                    <span class="vertical-line-petite" style="background:grey "></span>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<form method="post" class="nav">
    <input type="submit" name=previous value="Retour" id="precedent" formnovalidate>
    <input type="submit" name=next value="Suivant" id="suivant">
</form>
<script>
    Array.from(document.getElementsByClassName('mdparse')).forEach(elem => {
        elem.innerHTML = marked.parse(elem.innerHTML);
    });
</script>
