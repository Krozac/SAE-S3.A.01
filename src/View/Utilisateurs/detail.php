<div class="barre_utilisateur">
    <h1>Profil de <?= $utilisateur->getPrenom() ?> <?= $utilisateur->getNom() ?></h1>
    <a href='index.php?action=disconnected&controller=utilisateur'>
        <img class="sortie" src="..\web\images\logo_sortie.png" alt="Déconnexion"></a>
</div>
<div style="margin-bottom: 60px">
    <a class="lien"
       href="index.php?controller=utilisateur&action=update&idUtilisateur=<?= $utilisateur->getIdentifiant() ?>">
        Modifier les informations</a>
</div>
<h2>Mes questions : </h2>
<ul class=" listes_sans_puces">
    <?php foreach ($questions as $question) {
        echo '<li><p><a href = index.php?controller=question&action=read&idQuestion=' . $question->getId() . '>
    ' . $question->getTitre() . '</a></p></li>';
    }
    ?>
</ul>

<h2>Mes propositions : </h2>
<ul class="listes_sans_puces">
    <?php foreach ($propositions as $proposition) {
        echo '<li><p><a href = index.php?controller=proposition&action=read&idProposition=' . $proposition->getId() . '>
    ' . $proposition->getTitre() . '</a></p></li>';
    }
    ?>
</ul>