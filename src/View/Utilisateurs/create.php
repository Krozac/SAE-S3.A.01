<form class="custom-form" method="post" action="index.php?controller=utilisateur&action=created">
    <fieldset>
        <h2 style="color: #022234">Formulaire d'inscription :</h2>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="username">Identifiant&#42; : </label>
            <input class="InputAddOn-field" maxlength="30" type="text" placeholder="" name="identifiant" id="username" required>

        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="lastname">Nom&#42; : </label>
            <input class="InputAddOn-field" maxlength="30" type="text" placeholder="" name="nom" id="lastname" required>

        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="firstname">Prénom&#42; : </label>
            <input class="InputAddOn-field" maxlength="30" type="text" placeholder="" name="prenom" id="firstname" required>
        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="mdp_id">Mot de passe&#42; : </label>
            <input class="InputAddOn-field" maxlength="256" type="password" value="" placeholder="" name="mdp" id="mdp_id" required>
        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="mdp2_id">Vérification du mot de passe&#42; : </label>
            <input class="InputAddOn-field" maxlength="256" type="password" value="" placeholder="" name="mdp2" id="mdp2_id" required>
        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="email_id">Email&#42; : </label>
            <input class="InputAddOn-field" maxlength="256" type="email" value="" placeholder="toto@yopmail.com" name="email"
                   id="email_id" required>
        </p>
        <?php

        use App\Vote\Lib\ConnexionUtilisateur;

        if (ConnexionUtilisateur::estAdministrateur()) {
            echo '<p class="InputAddOn">
                    <label class="InputAddOn-item" for="estAdmin_id">Administrateur&#42; : </label>
                    <input class="InputAddOn-field" type="checkbox" placeholder="" name="estAdmin" id="estAdmin_id">
                </p>';
        }

        $message = 'Votre mot de passe doit contenir au moins 6 caractères, dont des chiffres et des lettres.';
        ?>
        <input id="suivant" type="submit" value="Inscription" class="nav">
        <p class="survol">
            <img class="imageAide" src="images/aide_logo.png" alt="">
            <span class="messageInfo"><?= $message ?></span>
        </p>
    </fieldset>
</form>