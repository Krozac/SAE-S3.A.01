<ul class="propositionsResultat">

    <?php

    use App\Vote\Controller\Controller;
    use App\Vote\Lib\MessageFlash;

    $i = 1;
    if (sizeof($propositions) == 0) {
        MessageFlash::ajouter('info', "Il n'y a aucune propositions pour cette question");
        Controller::redirect('index.php?controller=question&action=readAll');
    }

    $idPropositionURL = rawurlencode($propositions[0]->getId());
    $titreHTML = htmlspecialchars($propositions[0]->getTitre());

    echo'<div class="podium">
            <div class="premier">
                <div class="proposition">
                    <a href= index.php?action=read&controller=proposition&idProposition=' .
                    $idPropositionURL . '> <h2>1. ' . $titreHTML . '</h2>   </a>
                    <br>
                    <h3>Mediane : ' . $propositions[0]->getVotemedian() . '</h3>
                    <a href="" id = "auteur">par ' . $propositions[0]->getIdResponsable() . ' </a >
                    <img src="images/premier.png">
                </div>
            </div>';
            if(sizeof($propositions)>=2){
                 echo '<div class="deuxTrois">
                     <div class="deuxieme">  
                        <div class=proposition>
                            <a href= index.php?action=read&controller=proposition&idProposition=' .
                            rawurlencode($propositions[1]->getId()) . '> <h2>2. ' . htmlspecialchars($propositions[1]->getTitre()) . '</h2>   </a>
                            <br>
                            <h3>Médiane : ' . $propositions[1]->getVotemedian() . '</h3>    
                            <a href="" id = "auteur">par ' . $propositions[1]->getIdResponsable() . ' </a >
                            <img src="images/deuxieme.png">
                            
                    </div>  
                     </div>';
            }
            if(sizeof($propositions)>=3) {
                echo '<div class="troisieme">  
                    <div class=proposition>
                        <a href= index.php?action=read&controller=proposition&idProposition=' .
                    rawurlencode($propositions[2]->getId()) . '> <h2>3. ' . htmlspecialchars($propositions[2]->getTitre()) . '</h2>   </a>
                        <br>
                        <h3>Médiane : ' . $propositions[2]->getVotemedian() . '</h3>
                        <a href="" id = "auteur">par ' . $propositions[2]->getIdResponsable() . ' </a >
                        <img src="images/troisieme.png">
                </div>  
                 </div>';
            }
            echo '</div></div>';

    echo '<div class="propositionsResultat">';
    for ($i=3;$i<count($propositions);$i++) {
        $idPropositionURL = rawurlencode($propositions[$i]->getId());
        $titreHTML = htmlspecialchars($propositions[$i]->getTitre());
        echo '<div class=proposition>';
        echo ' <a href= index.php?action=read&controller=proposition&idProposition=' .
            $idPropositionURL . '> <h2>' . $i+1 .'. '. $titreHTML . '</h2>   </a>';
        echo '<br > ';
        echo '<h3>Médiane : ' . $propositions[$i]->getVotemedian() . '</h3>';
        echo '<a href="" id = "auteur">par ' . $propositions[$i]->getIdResponsable() . ' </a >';
        echo '</div>';
    }
    echo '</div>';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <script>
            confetti({
                spread: 360,
                particleCount: 450,
                origin:{
                    x: 0.5,
                    y: 0.2
                }
            })
            setTimeout(function(){
                confetti({
                    spread: 120,
                    particleCount: 250,
                    origin:{
                        x: 0.2,
                        y: 1.2
                    }
                })

                confetti({
                    spread: 120,
                    particleCount: 250,
                    origin:{
                        x: 0.8,
                        y: 1.2
                    }
                })
            },1000);

    </script>
    <script type="text/javascript" src="js/jquery-3.4.2.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
</ul>
