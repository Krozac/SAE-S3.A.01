<nav>
    <ul id="Menu" style="list-style-type: none">
        <li class="grosmenu"><a href="index.php?action=home&controller=accueil">Accueil</a></li>
        <li class="grosmenu"><a href="index.php?action=search&controller=utilisateur">Chercher un utilisateur</a></li>
        <li class="grosmenu"><a href="index.php?action=create&controller=question">Créer une question</a></li>
        <li class="grosmenu"><a href="index.php?action=readAll&controller=question">Liste des questions</a></li>
        <?php

        if (isset($_SESSION['user'])) {
            echo "<li class='grosmenu'><a href='index.php?action=read&controller=utilisateur'>
                                        <img class=profil src='images/profil.png' alt='Profil'></a></li>";
        } else {
            echo "<li class='grosmenu' ><a href = 'index.php?action=connexion&controller=utilisateur'>Connexion</a></li>";
        } ?>

    </ul>

</nav>