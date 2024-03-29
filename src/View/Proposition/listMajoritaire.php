<link href="css/ListePropositions.css" rel="stylesheet">
<div class="propositions">
    <?php

    use App\Vote\Lib\ConnexionUtilisateur;
    use App\Vote\Model\DataObject\Calendrier;
    use App\Vote\Model\DataObject\CoAuteur;
    use App\Vote\Model\DataObject\Votant;
    use App\Vote\Model\Repository\VoteRepository;

    if ($question->getSystemeVote() == 'majoritaire') {
        $methodeTrie = 'en fonction de leur vote médian';
        $inferieur = 'un vote médian';
        $modeScrutin = 'Scrutin par jugement majoritaire (médiane) ';
        $message = 'Le scrutin majoritaire établit un \'vote médian\' pour chaque proposition, 
                    par défaut, la mention \'passable\' est sélectionnée.<br>
                    Il est nécessaire de choisir une mention pour chaque proposition.';
    } else if ($question->getSystemeVote() == 'valeur') {
        $methodeTrie = 'par moyenne de votes';
        $inferieur = 'une moyenne de votes';
        $modeScrutin = 'Scrutin par jugement majoritaire (moyenne) ';
        $message = 'Ce système de vote établit une moyenne de vote pour chaque proposition, 
                    par défaut, la mention \'passable\' est sélectionnée.<br>
                    Il est nécessaire de choisir une mention pour chaque proposition.';
    }
    if (ConnexionUtilisateur::getLoginUtilisateurConnecte() == $question->getOrganisateur()->getIdentifiant() &&
        ($question->getPhase() == 'debut') && $question->aPassePhase()) {
        $organisateurRole = 'Vous êtes responsable pour cette question multiphase';
        $messageOrganisateur = 'Vous pouvez éliminer les propositions les moins attractives. <br>
                Par défaut, elles sont triées ' . $methodeTrie . ', si vous éliminez
            une proposition, vous éliminez aussi celles qui ont ' . $inferieur . ' inférieurs.<br>
            A l\'inverse si vous annulez l\'élimination d\'une proposition, vous annulerez également l\'élimination de celles qui ' . $inferieur . ' supérieurs.';
        ?>
        <h2 class="custom_titre"><?= $organisateurRole ?></h2>
        <p class="survol">
            <img class="imageAide" src="images/aide_logo.png" alt="aide">
            <span class="messageInfo"><?= $messageOrganisateur ?></span>
        </p>
        <?php
    }

    ?>
    <h2 class="custom_titre"><?= $modeScrutin ?></h2>
    <p class="survol">
        <img class="imageAide" src="images/aide_logo.png" alt="aide">
        <span class="messageInfo"><?= $message ?></span>
    </p>
    <?php
    $i = 1;
    $peutVoter = false;
    $calendrier = $question->getCalendrier();
    if ($question->getPhase() == 'vote' && ConnexionUtilisateur::estConnecte()
        && Votant::estVotant($votants, ConnexionUtilisateur::getLoginUtilisateurConnecte())
    ) {
        $votes = Votant::getVotes(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        $peutVoter = true;
        $interval = (new DateTime(date("d-m-Y H:i")))->diff(new DateTime($calendrier->getFinVote(true)));
        echo '<h2 class="custom_titre">Il vous reste ' . Calendrier::diff($interval) . ' pour voter ! </h2>';
    }
    foreach ($propositions as $proposition) {
        $idPropositionURL = rawurlencode($proposition->getId());
        $titreHTML = htmlspecialchars($proposition->getTitre());
        if ($proposition->isEstEliminee()) {
            echo '<div style="background: rgba(58,69,75,0.55)" class="proposition shadow-effect eliminee">';
            echo '<h1 class="estEliminee"><strong class="custom_strong color-grey " style="color: rgba(37,47,47,0.66);" >Eliminé</strong></h1>';
        } else {
            echo '<div class="proposition shadow-effect">';
        }

        echo '<h2 class = "Titre_proposition">' . $titreHTML . ' </h2>';
        if ($peutVoter && !$proposition->isEstEliminee()) {
            $vote = Votant::aVote($proposition, $votes, 'majoritaire');
            for ($val = 1; $val <= 6; $val++) {
                switch ($val) {
                    case 1 :
                        $attribut = 'À rejeter';
                        break;
                    case 2  :
                        $attribut = 'Insuffisant';
                        break;
                    case 3 :
                        $attribut = 'Passable';
                        break;
                    case 4:
                        $attribut = 'Assez bien';
                        break;
                    case 5 :
                        $attribut = 'Bien';
                        break;
                    case 6 :
                        $attribut = 'Très bien';
                        break;
                }
                if ($val <= $vote->getValeur()) {
                    echo '<a class=vote style="background:#a94442" 
                        href="index.php?controller=vote&action=choix&idProposition=' . rawurlencode($proposition->getId()) . '&valeur=' . $val . '">
                        <img src=../web/images/coeur_logo.png alt="coeur">';
                } else {
                    echo '<a class=vote 
                    href="index.php?controller=vote&action=choix&idProposition=' . rawurlencode($proposition->getId()) . '&valeur=' . $val . '">
                        <img src=../web/images/coeur_logo.png alt="coeur">
                        ';
                }

                echo '<span style="font-size: 18px">' . $attribut . '</span></a>';
            }
        }
        $nbEtoiles = htmlspecialchars($proposition->getNbEtoiles());

        $votesProposition = $proposition->getVotes();


        echo '<h3>Nombre de votes : ' . sizeof($votesProposition) . '</h3>';
        if (sizeof($votesProposition) > 0) {
            if ($question->getSystemeVote() == 'majoritaire') {
                if (sizeof($votesProposition) == 1) {
                    $median = $votesProposition[0];
                } else {
                    if (sizeof($votesProposition) % 2 == 0) {
                        $median = $votesProposition[(sizeof($votesProposition) / 2) - 1];
                    } else {
                        $median = $votesProposition[((sizeof($votesProposition) + 1) / 2) - 1];
                    }
                }
                echo '<h3>Vote médian :  ' . htmlspecialchars(number_format($median->getValeur(), 3)) . '</h3>';
            } else {
                echo '<h3>Moyenne des votes : ' . htmlspecialchars(number_format($nbEtoiles / sizeof($votesProposition), 3),) . '</h3>';
            }
        }


        if (ConnexionUtilisateur::getLoginUtilisateurConnecte() == $question->getOrganisateur()->getIdentifiant() &&
            ($question->getPhase() == 'debut') && $question->aPassePhase()) {
            if ($proposition->isEstEliminee()) {
                echo '<p><a class="link-custom" href="index.php?controller=proposition&action=annulerEliminer&idProposition=' . $idPropositionURL . '">Annuler l\'élimination</a></p>';
            } else {
                echo '<p><a class="link-custom" href="index.php?controller=proposition&action=eliminer&idProposition=' . $idPropositionURL . '">Eliminer</a></p>';
            }
        }
        echo '<p>';
        if (ConnexionUtilisateur::estConnecte() && (CoAuteur::estCoAuteur(ConnexionUtilisateur::getLoginUtilisateurConnecte(), $proposition->getId()) ||
                $proposition->getIdResponsable() == ConnexionUtilisateur::getLoginUtilisateurConnecte()) &&
            $question->getPhase() == 'ecriture') {

            echo ' <a href="index.php?action=update&controller=proposition&idProposition=' .
                rawurlencode($proposition->getId()) . '"><img class="modifier" src = "../web/images/modifier.png"  alt="modifier"></a > ';
        }

        if (ConnexionUtilisateur::estConnecte() && ConnexionUtilisateur::getLoginUtilisateurConnecte() == $proposition->getIdResponsable() && $question->getPhase() != 'vote') {
            echo ' <a href="index.php?controller=proposition&action=delete&idProposition=' . rawurlencode($proposition->getId()) . '">
                        <img style="margin-left: 10px" class="delete" src = "../web/images/delete.png"  alt="supprimer"></a>';
        }
        echo '</p>';
        $i++;
        echo '<p><a style="float: left" class = "link-custom" href="index.php?action=read&controller=utilisateur&idUtilisateur=' . rawurlencode($proposition->getIdResponsable()) . '" >
        par ' . htmlspecialchars($proposition->getIdResponsable()) . ' </a>';
        echo '<a style="float: right" class = "link-custom" href= "index.php?action=read&controller=proposition&idProposition=' .
            $idPropositionURL . '">Lire plus</a></p>';
        echo '</div>';
    }
    ?>
</div>
