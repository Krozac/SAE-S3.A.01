<ul class="questions">
    <?php

    use App\Vote\Model\DataObject\CoAuteur;
    use App\Vote\Model\DataObject\Votant;

    $i = 1;
    $calendrier = $question->getCalendrier();
    $date = date("Y-m-d H:i:s");
    $aVote = Votant::aVote($propositions, $_SESSION['user']['id']);
    foreach ($propositions as $proposition) {
        if ($aVote == $proposition->getId()) {
            echo '<h2>Vous avez déjà voté pour cette proposition.</h2>';
        }
        $idPropositionURL = rawurlencode($proposition->getId());
        $titreHTML = htmlspecialchars($proposition->getTitre());
        echo '<p class = "listes">
            <a href= index.php?action=read&controller=proposition&idProposition=' .
            $idPropositionURL . '>' . $i . ' : ' . $titreHTML . '  </a>';
        if ($date >= $calendrier->getDebutVote() && $date < $calendrier->getFinVote() &&
            isset($_SESSION['user']) && Votant::estVotant($question, $_SESSION['user']['id']) && $aVote == null) {
            echo '<a class="vote" href= index.php?action=create&controller=vote&idproposition=' .
                $idPropositionURL . '>Voter</a>';
        }
        //if(!CoAuteur::estCoAuteur($question, $_SESSION['user']['id'],$proposition->getResponsable()->getIdentifiant())){
            echo '<a href = index.php?action=update&controller=proposition&idProposition=' .
                $proposition->getId() . ' ><img class="modifier" src = "..\web\images\modifier.png" ></a >';
        //}
        echo 'Nombre de votes : ' . $proposition->getNbVotes();
        echo '</p>';
        $i++;
    }
    ?>
</ul>
