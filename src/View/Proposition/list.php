<ul class="propositions">
    <?php

    use App\Vote\Lib\ConnexionUtilisateur;
    use App\Vote\Model\DataObject\Calendrier;
    use App\Vote\Model\DataObject\CoAuteur;
    use App\Vote\Model\DataObject\Votant;

    $i = 1;
    $peutVoter = false;
    $calendrier = $question->getCalendrier();
    if(sizeof($propositions) == 0){
        echo'<h2>Il n\'y a pas de propositions pour cette question</h2>';
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
        echo ' <a href= index.php?action=read&controller=proposition&idProposition=' .
            $idPropositionURL . '> <h2>' . $titreHTML . '</h2>   </a>';
        if ($peutVoter) {
            $vote = Votant::aVote($proposition, $votes);
            if (!is_null($vote)) {
                for ($val = 1; $val <= $vote->getValeur(); $val++) {
                    echo '<a id=vote style="background:#a94442" href=index.php?controller=vote&action=choix&idProposition=' . $proposition->getId() . '&valeur=' . $val . '>
                        <img src=../web/images/coeur_logo.png alt=""></a>';
                }
                for ($val = $vote->getValeur() + 1; $val <= 5; $val++) {
                    echo '<a id=vote href=index.php?controller=vote&action=choix&idProposition=' . $proposition->getId() . '&valeur=' . $val . '>
                        <img src=../web/images/coeur_logo.png alt=""></a>';
                }
            } else {
                for ($val = 1; $val <= 5; $val++) {
                    echo '<a id=vote href=index.php?controller=vote&action=choix&idProposition=' . $proposition->getId() . '&valeur=' . $val . '>
                        <img src=../web/images/coeur_logo.png alt=""></a>';
                }
            }
        }


        echo '<br > ';
        echo '<h3>Nombre de votes : ' . htmlspecialchars($proposition->getNbVotes()) . '</h3>';
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
        echo '<a href="" id = "auteur">par ' .htmlspecialchars($proposition->getIdResponsable()) . ' </a >';
        echo '</div>';
    }
    ?>
</ul>
