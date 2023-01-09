<?php

use App\Vote\Lib\ConnexionUtilisateur;

?>
<form class="custom-form" method="post" action="index.php?controller=utilisateur&action=updated">
    <fieldset>
        <?php
        if (ConnexionUtilisateur::getLoginUtilisateurConnecte() == $utilisateur->getIdentifiant()) {
            echo '<h2>Mise à jour de votre compte :</h2>';
        } else {
            echo '<h2>Mise à jour du compte de ' . htmlspecialchars($utilisateur->getIdentifiant()) . ':</h2>';
        }
        echo '
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="username">Identifiant : </label>
            <input class="InputAddOn-field" type="text" value="'. htmlspecialchars($utilisateur->getIdentifiant()) .'" name="identifiant"
                   id="username" maxlength="30"
                   readonly required>
        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="lastname">Nom&#42; : </label>
            <input class="InputAddOn-field" type="text" value="'.  htmlspecialchars($utilisateur->getNom()) .'" name="nom" id="lastname"
                   maxlength="30" required> 
            <span class="validity"></span>
        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="firstname">Prénom&#42; : </label>
            <input class="InputAddOn-field" type="text" value="'.  htmlspecialchars($utilisateur->getPrenom()) .'" name="prenom"
                   id="firstname" maxlength="30" required>
            <span class="validity"></span>

        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="mail">Email : </label>
            <input class="InputAddOn-field" type="text" value="'.  htmlspecialchars($utilisateur->getEmail()) .'" name="mail"
                   id="mail" maxlength="256" required>
        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="mdp_id1">Ancien mot de passe&#42; : </label>
            <input class="InputAddOn-field" type="password" name="ancienMDP" id="mdp_id1" maxlength="256"
                   required>
        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="mdp_id2">Mot de passe&#42; : </label>
            <input class="InputAddOn-field" type="password" name="mdp" id="mdp_id2" maxlength="256" required>

        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="mdp2_id">Vérification du mot de passe&#42; : </label>
            <input class="InputAddOn-field" type="password" name="mdp2" id="mdp2_id" maxlength="256" required>
        </p>';

        if (ConnexionUtilisateur::estAdministrateur()) {
            echo '<p class="InputAddOn">
                    <label class="InputAddOn-item" for="estAdmin_id">Administrateur&#42; : </label>
                    <input class="InputAddOn-field" type="checkbox" name="estAdmin" id="estAdmin_id"';
            if ($utilisateur->isEstAdmin()) {
                echo ' checked ';
            }
            echo '></p>';
        }


        $mdp = '';
        $message = 'Votre mot de passe doit contenir au moins 6 caractères, dont des chiffres et des lettres.';
        ?>

        <p>
            <input type="submit" value="Mettre à jour" class="nav">
        </p>

        <p class="survol">
            <img class="imageAide" src="images/aide_logo.png" alt="">
            <span class="messageInfo"><?= $message ?></span>
        </p>
    </fieldset>
</form>