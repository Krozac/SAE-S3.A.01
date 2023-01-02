<div class="propositions">
    <?php

    use App\Vote\Lib\ConnexionUtilisateur;
    use App\Vote\Model\DataObject\Calendrier;
    use App\Vote\Model\DataObject\CoAuteur;
    use App\Vote\Model\DataObject\Votant;
    use App\Vote\Model\Repository\VoteRepository;

    $modeScrutin = 'Scrutin par vote unique';
    $message = 'Vous pouvez voter pour une seule et unique proposition, la proposition qui emporte le plus
        de voix est désignée gagnante.';
    ?>
    <h2><?= $modeScrutin ?></h2>
    <p class="survol">
        <img class="imageAide" src="images/aide_logo.png" alt=""/>
        <span><?= $message ?></span>
    </p>
    <?php
    $i = 1;
    $peutVoter = false;
    $calendrier = $question->getCalendrier();
    if (sizeof($propositions) == 0) {
        echo '<h2>Il n\'y a pas de propositions pour cette question</h2>';
    }
    if ($question->getPhase() == 'vote' && ConnexionUtilisateur::estConnecte()
        && Votant::estVotant($votants, ConnexionUtilisateur::getLoginUtilisateurConnecte())
    ) {
        $votes = Votant::getVotes(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        $peutVoter = true;
        $interval = (new DateTime(date("d-m-Y H:i")))->diff(new DateTime($calendrier->getFinVote(true)));
        echo '<h2>Il vous reste ' . Calendrier::diff($interval) . ' pour voter ! </h2>';
    }
    foreach ($propositions as $proposition) {
        $idPropositionURL = rawurlencode($proposition->getId());
        $titreHTML = htmlspecialchars($proposition->getTitre());
        echo '<div class=proposition>';
        echo ' <a href= "index.php?action=read&controller=proposition&idProposition=' .
            $idPropositionURL . '"> <h2>' . $titreHTML . '</h2>   </a>';
        if ($peutVoter) {
            $vote = Votant::aVote($proposition, $votes);
            if (is_null($vote)) {
                echo '<form method="get" action="../web/index.php">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="controller" value="vote">
                <input type="hidden" name="idProposition" value="' . $proposition->getId() . '">
                <input type="submit" value="Voter" class="nav">
                    </form>';
            } else {
                echo '<form method="get" action="../web/index.php">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="controller" value="vote">
                <input type="hidden" name="idProposition" value="' . $proposition->getId() . '">
                <input type="submit" value="Supprimer le vote" class="nav">
                    </form>';
            }
            $nbVotes = htmlspecialchars($proposition->getNbVotes());

            echo '<br > ';
            echo '<h3>Nombre de votes : ' . $nbVotes . '</h3>';
        }


        if (CoAuteur::estCoAuteur(ConnexionUtilisateur::getLoginUtilisateurConnecte(), $proposition->getId()) ||
            $proposition->getIdResponsable() == ConnexionUtilisateur::getLoginUtilisateurConnecte() &&
            $question->getPhase() == 'ecriture') {

            echo ' <a href="index.php?action=update&controller=proposition&idProposition=' .
                rawurlencode($proposition->getId()) . '"><img class="modifier" src = "..\web\images\modifier.png" ></a ><br> ';
        }
        if (ConnexionUtilisateur::estConnecte() && ConnexionUtilisateur::getLoginUtilisateurConnecte() == $proposition->getIdResponsable() && $question->getPhase() == 'ecriture') {

            echo ' <a class="nav suppProp" 
            href=index.php?controller=proposition&action=delete&idProposition=' . rawurlencode($proposition->getId()) . '>Supprimer</a>';
        }
        $i++;
        echo '<a href="" >par ' . htmlspecialchars($proposition->getIdResponsable()) . ' </a >';
        echo '</div>';
    }
    ?>
</div>