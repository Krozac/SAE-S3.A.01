<div class="detail_question ">
    <div class="infos">

        <div class = "question info">
            <h4>Titre :</h4>
            <p> <?= htmlspecialchars($question->getTitre()) ?></p>
            <h4>Description :</h4>
            <p> <?= htmlspecialchars($question->getDescription()) ?></p>

        </div>

        <div id = "participants" class="info">
            <h1><strong  class ='color-yellow'>Participants</strong></h1>
            <div id = "responsables">
                <h4><strong  class ='color-yellow'>Responsables</strong></h4>
                <?php
                if (is_array($responsables)) {
                    foreach ($responsables as $responsable) {
                        echo "<p>" . htmlspecialchars($responsable->getIdentifiant()) . "</p>";
                    }
                } else {
                    echo "<p>" . htmlspecialchars($responsables->getIdentififant()) . "</p>";
                }
                ?>
            </div>

            <div id="votants">
                <h4><strong class='color-yellow'>Votants</strong></h4>
                <?php
                if (is_array($votants)) {
                    foreach ($votants as $votant) {
                        echo "<p>" . htmlspecialchars($votant->getIdentifiant()) . "</p>";
                    }
                } else {
                    echo "<p>" . htmlspecialchars($votants->getIdentifiant()) . "</p>";
                }

                ?>
            </div>
        </div>

        <div class = "sections  info">
        <h1><strong  class ='color-orange'>Sections</strong></h1>

        <?php
        $i = 1;
        foreach ($sections as $Section) {
            echo '<div class = "section">';
            echo '<h3 style = "color:black"> Section n° ' . $i . '</h3>';
            echo '<p> Titre : ' . htmlspecialchars($Section->getTitre()) . '</p>';
            echo '<p> Description : ' . htmlspecialchars($Section->getDescription()) . '</p>';
            echo '&nbsp;';
            echo '</div>';
            $i++;
        }
        ?>



        </div>
        <div class = "date_creation info">
            <h4 style = "color:black">Date de création :</h4>
            <p>
                <?= htmlspecialchars($question->getcreation()); ?>
            </p>
        </div>
        <div class="info">
    <div class="calendrier">
        <h1><strong  class ='color-green'>Calendrier</strong></h1>



        <?php
        echo '<span class="vertical-line-petite" style="background:grey "></span>';
        $cercle = '<div id="cercle"></div>';
        if ($question->getPhase() == 'debut') {
            echo $cercle;
            echo '<span class="vertical-line-petite" style="background: grey"></span>';
        }
        ?>

        <p style="background: #CE16169B; color: white" class="cal" id="ecriture_debut">Début d'écriture des propositions
            : <br>
            <?= htmlspecialchars($question->getCalendrier()->getDebutEcriture()) ?></p>
        <?php
        if ($question->getPhase() == 'ecriture') {
            echo '<span class="vertical-line-petite" style="background: rgba(206,22,22,0.61)"></span>';
            echo $cercle;
            ?>

            <?php
            echo '<span class="vertical-line-petite" style="background: #CE16169B"></span>';
        } else {
            echo '<span class="vertical-line" style="background: #CE16169B"></span>';
        } ?>
        <p style="background: #CE16169B; color: white" class="cal" id="ecriture_fin">Fin d'écriture des propositions :
            <br>
            <?= htmlspecialchars($question->getCalendrier()->getFinEcriture()) ?></p>

        <?php
        if ($question->getPhase() == 'entre') {
            echo '<span class="vertical-line-petite" style="background:grey " ></span>';
            echo $cercle;
            echo '<span class="vertical-line-petite" style="background:grey "></span>';
        } else {
            echo '<span class="vertical-line" style="background:grey "></span>';
        }
        ?>

        <p style="background : rgba(65,112,56,0.76); color: white" class="cal" id="vote_debut">Début des votes : <br>
            <?= htmlspecialchars($question->getCalendrier()->getDebutVote()) ?></p>
        <?php
        if ($question->getPhase() == 'vote') {
            echo '<span class="vertical-line-petite" style="background: rgba(65,112,56,0.76);"></span>';
            echo $cercle;
            echo '<span class="vertical-line-petite" style="background: rgba(65,112,56,0.76);"></span>';
        } else {
            echo '<span class="vertical-line" style="background: rgba(65,112,56,0.76);"></span>';
        } ?>
        <p style="background: rgba(65,112,56,0.76); color: white" class="cal" id="vote_fin">Fin des votes : <br>
            <?= htmlspecialchars($question->getCalendrier()->getFinVote()) ?></p>
        <?php
        echo '<span class="vertical-line-petite" style="background:grey "></span>';
        if ($question->getPhase() == 'fini') {
            echo $cercle;
        }
        ?>
    </div></div></div>
</div>




