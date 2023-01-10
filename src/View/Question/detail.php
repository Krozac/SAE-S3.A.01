<div class="detail_question ">
    <div class="infos">

        <div class="info_question ">
            <h1><strong class="custom_strong color-blue">Titre :</strong></h1>
            <p> <?= htmlspecialchars($question->getTitre()) ?></p>

            <h1><strong class="custom_strong color-blue">Description :</strong></h1>
            <p class = "mdparse"> <?= htmlspecialchars($question->getDescription()) ?></p>

        </div>

        <div id="participants" class="info">
            <h1><strong class=' custom_strong color-yellow'>Participants</strong></h1>
            <div>
                <h1><strong class=' custom_strong color-yellow'>Responsables</strong></h1>
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

            <div>
                <h1><strong class='custom_strong color-yellow'>Votants</strong></h1>
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

        <div class="sections  info">
            <h1><strong class='custom_strong color-orange'>Sections</strong></h1>

            <?php
            $i = 1;
            foreach ($sections as $Section) {
                echo '<div class = "section">';
                echo '<h3 style = "color:black; font-size: 21px;" > Section n° ' . $i . '</h3>';
                echo '<p style = "color:black; font-size: 20px;"> Titre :<br> ' . htmlspecialchars($Section->getTitre()) . '</p>';
                echo '<p style = "color:black; font-size: 20px;"> Description :<br> ' . htmlspecialchars($Section->getDescription()) . '</p>';
                echo '&nbsp;';
                echo '</div>';
                $i++;
            }
            ?>


        </div>
        <div class="date_creation info">
            <h1><strong class="custom_strong color-grey">Date de création :</strong></h1>
            <?= htmlspecialchars($question->getCreation()) ?>
        </div>
        <div class="info">
            <div class="calendrier">
                <h1><strong class='custom_strong color-green'>Calendrier</strong></h1>


                <?php
                $cercle = '<div id="cercle"></div>';
                $calendriers = $question->getCalendrier(true);
                $calendrierActuel = $question->getCalendrier();
                $i = 1;
                foreach ($calendriers as $calendrier) {
                    if ($i != 1) {
                        echo '<h2 style="color: #012e49">' . $i . '<sup>e</sup> phase</h2>';
                    }
                    if ($question->getPhase() == 'debut' && $calendrierActuel == $calendrier) {
                        echo $cercle;
                    }
                    echo '<span class="vertical-line-petite" style="background: grey"></span>';
                    ?>

                    <p style="background: #CE16169B; color: white; padding: 6px" class="cal">
                        Début
                        d'écriture des
                        propositions
                        : <br>
                        <?= htmlspecialchars($calendrier->getDebutEcriture()) ?></p>
                    <?php
                    if ($question->getPhase() == 'ecriture' && $calendrierActuel == $calendrier) {
                        echo '<span class="vertical-line-petite" style="background: rgba(206,22,22,0.61)"></span>';
                        echo $cercle;

                        echo '<span class="vertical-line-petite" style="background: #CE16169B"></span>';
                    } else {
                        echo '<span class="vertical-line" style="background: #CE16169B"></span>';
                    } ?>
                    <p style="background: #CE16169B; color: white; padding: 6px" class="cal" >Fin
                        d'écriture des
                        propositions :
                        <br>
                        <?= htmlspecialchars($calendrier->getFinEcriture()) ?></p>
                    <?php


                    if ($question->getPhase() == 'entre' && $calendrierActuel == $calendrier) {
                        echo '<span class="vertical-line-petite" style="background:grey " ></span>';
                        echo $cercle;
                        echo '<span class="vertical-line-petite" style="background:grey "></span>';
                    } else {
                        echo '<span class="vertical-line" style="background:grey "></span>';
                    }
                    ?>

                    <p style="background : rgba(65,112,56,0.76); color: white; padding: 6px" class="cal"
                       >Début des votes :
                        <br>
                        <?= htmlspecialchars($calendrier->getDebutVote()) ?></p>
                    <?php
                    if ($question->getPhase() == 'vote' && $calendrierActuel == $calendrier) {
                        echo '<span class="vertical-line-petite" style="background: rgba(65,112,56,0.76);"></span>';
                        echo $cercle;
                        echo '<span class="vertical-line-petite" style="background: rgba(65,112,56,0.76);"></span>';
                    } else {
                        echo '<span class="vertical-line" style="background: rgba(65,112,56,0.76);"></span>';
                    } ?>
                    <p style="background: rgba(65,112,56,0.76); color: white; padding: 6px" class="cal" >
                        Fin des votes : <br>
                        <?= htmlspecialchars($calendrier->getFinVote()) ?></p>
                    <?php
                    echo '<span class="vertical-line-petite" style="background:grey "></span>';
                    if ($question->getPhase() == 'fini' && $calendrierActuel == $calendrier) {
                        echo $cercle;
                    }
                    $i++;
                }
                ?>

            </div>
        </div>
    </div>
</div>


<script>
    Array.from(document.getElementsByClassName('mdparse')).forEach(elem => {
        elem.innerHTML = marked.parse(elem.innerHTML);
    });
</script>

