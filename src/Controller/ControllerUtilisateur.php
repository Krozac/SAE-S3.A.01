<?php

namespace App\Vote\Controller;

use App\Vote\Lib\ConnexionUtilisateur;
use App\Vote\Lib\MessageFlash;
use App\Vote\Lib\MotDePasse;
use App\Vote\Lib\VerificationEmail;
use App\Vote\Model\DataObject\Utilisateur;
use App\Vote\Model\HTTP\Session;
use App\Vote\Model\Repository\PropositionRepository;
use App\Vote\Model\Repository\QuestionRepository;
use App\Vote\Model\Repository\UtilisateurRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ControllerUtilisateur
{
    public static function readAll()
    {
        $utilisateurs = (new UtilisateurRepository())->selectAll();     //appel au modèle pour gerer la BD
        Controller::afficheVue('view.php',
            ["utilisateurs" => $utilisateurs,
                "pagetitle" => "Liste des Utilisateurs",
                "cheminVueBody" => "Utilisateurs/list.php"]); //"redirige" vers la vue
    }

    public static function connexion()
    {
        Controller::afficheVue('view.php',
            ["pagetitle" => "Connexion",
                "cheminVueBody" => "Utilisateurs/formulaireConnexion.php"]);
    }

    public static function disconnected()
    {
        if (!ConnexionUtilisateur::estConnecte()) {
            MessageFlash::ajouter("warning", "Vous pouvez pas vous déconnecter sans être connecté");
            Controller::redirect("index.php?action=connexion&controller=utilisateur");
        }
        Session::getInstance();
        ConnexionUtilisateur::deconnecter();
        MessageFlash::ajouter("success", "Vous avez été déconnecté");
        Controller::redirect('index.php?controller=utilisateur&action=connexion');
    }

    public static function connecter()
    {
        $mdp = $_POST['mdp'];
        $id = $_POST['identifiant'];
        $utilisateur = ((new UtilisateurRepository())->select($id));

        if (!isset($utilisateur)) {
            MessageFlash::ajouter('warning', 'Utilisateur introuvable');
            Controller::redirect('index.php?controller=utilisateur&action=connexion');
        } else {
            if (!MotDePasse::verifier($mdp, $utilisateur->getMdpHache())) {
                MessageFlash::ajouter('warning', 'Mot de passe incorrect');
                Controller::redirect('index.php?controller=utilisateur&action=connexion');
            }
            if (!VerificationEmail::aValideEmail($utilisateur)) {
                MessageFlash::ajouter('warning', 'Vous devez valider votre compte, vérifiez votre boite mail, ainsi que vos spams.');
                MessageFlash::ajouter('info', 'Cliquez <a style="color: black" href="index.php?action=renvoyerMailValidation&controller=utilisateur&idUtilisateur=' . $utilisateur->getIdentifiant() . '">
                ici</a> pour renvoyer un mail.');
                Controller::redirect('index.php?controller=utilisateur&action=connexion');
            } else {
                ConnexionUtilisateur::connecter($utilisateur->getIdentifiant());
                MessageFlash::ajouter('success', 'Vous êtes connecté');
                Controller::redirect("index.php?controller=accueil&action=home");
            }
        }
    }

    public static function renvoyerMailValidation()
    {
        try {
            VerificationEmail::envoiEmailValidation((new UtilisateurRepository())->select($_GET['idUtilisateur']));
        } catch (TransportExceptionInterface $e) {
            MessageFlash::ajouter('warning', 'L\'envoie du mail a échoué');
            Controller::redirect('index.php?action=connexion&controller=utilisateur');
        }
        MessageFlash::ajouter('info', 'Le mail a été renvoyé, vérifiez vos spams');
        Controller::redirect('index.php?action=connexion&controller=utilisateur');
    }

    public static function read()
    {
        if (!isset($_GET['idUtilisateur']) || !Utilisateur::identifiantExiste($_GET['idUtilisateur'])) {
            MessageFlash::ajouter('warning', 'Utilisateur introuvable');
            Controller::redirect('index.php');
        }
        Session::getInstance();
        $utilisateur = ((new UtilisateurRepository))->select($_GET['idUtilisateur']);
        $questions = (new QuestionRepository())->selectWhere($_GET['idUtilisateur'], '*', 'idorganisateur');
        $propositions = (new PropositionRepository())->selectWhere($_GET['idUtilisateur'], '*', 'idresponsable');
        Controller::afficheVue('view.php', ['pagetitle' => "Profil",
            "cheminVueBody" => "Utilisateurs/detail.php", "utilisateur" => $utilisateur,
            "questions" => $questions, "propositions" => $propositions]);
    }

    public static function create()
    {
        Controller::afficheVue('view.php',
            ["pagetitle" => "Inscription",
                "cheminVueBody" => "Utilisateurs/create.php"]);
    }

    public static function created()
    {
        $mdp = $_POST['mdp'];
        if (strlen($mdp) < 6) {
            MessageFlash::ajouter('info', 'Votre mot de passe doit contenir au moins 6 caractères.');
            Controller::redirect('index.php?controller=utilisateur&action=create');
        }
        $bool = false;
        for ($i = 0; $i < 10 && !$bool; $i++) {
            if (strpos($mdp, $i)) {
                $bool = true;
            }
        }
        if (!$bool) {
            MessageFlash::ajouter('info', 'Votre mot de passe doit contenir au moins 1 chiffre et une lettre.');
            Controller::redirect('index.php?controller=utilisateur&action=create');
        }
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            MessageFlash::ajouter('warning', 'Le format du mail saisi est invalide');
            Controller::redirect('index.php?controller=utilisateur&action=create');
        }
        if ($_POST['mdp'] != $_POST['mdp2']) {
            MessageFlash::ajouter('warning', 'Les mots de passes sont différents');
            Controller::redirect('index.php?controller=utilisateur&action=create');
        } else if (Utilisateur::identifiantExiste($_POST['identifiant'])) {
            MessageFlash::ajouter('warning', 'Cet identifiant existe déjà');
            Controller::redirect('index.php?controller=utilisateur&action=create');
        } else if (strlen($_POST['identifiant']) > 30 || strlen($_POST['nom']) > 30 || strlen($_POST['prenom']) > 30 ||
            strlen($_POST['mdp']) > 256 || strlen($_POST['mdp2']) > 256 || strlen($_POST['email']) > 256) {
            MessageFlash::ajouter('danger', 'Vous n\'avez pas respecté les contraintes');
            Controller::redirect('index.php?controller=utilisateur&action=create');
        } else {
            $utilisateur = Utilisateur::construireDepuisFormulaire($_POST);
            try {
                VerificationEmail::envoiEmailValidation($utilisateur);
            } catch (TransportExceptionInterface $e) {
                MessageFlash::ajouter('warning', 'L\'envoie du mail a échoué');
                Controller::redirect('index.php');
            }
            (new UtilisateurRepository())->sauvegarder($utilisateur);
            MessageFlash::ajouter("success", "Le compte a bien été crée");
            MessageFlash::ajouter("info", "Pour valider votre compte, vérifiez votre boite mail et vos spams");
            //ConnexionUtilisateur::connecter($utilisateur->getIdentifiant());
            Controller::redirect("index.php?controller=utilisateur&action=connexion");
        }
    }


    public static function validerEmail()
    {
        echo "test";
        if (!isset($_GET['login']) || !isset($_GET['nonce'])) {
            MessageFlash::ajouter('warning', 'Login ou nonce incorrect');
            Controller::redirect('index.php?controller=accueil');
        }
        if (VerificationEmail::traiterEmailValidation($_GET['login'], $_GET['nonce'])) {
            MessageFlash::ajouter('success', 'Votre e-mail a été validé.');
            ConnexionUtilisateur::connecter($_GET['login']);
            Controller::redirect('index.php?action=read&controller=utilisateur&idUtilisateur=' . $_GET['login']);
        } else {
            Controller::redirect('index.php?');
        }
    }

    public static function search()
    {
        Controller::afficheVue('view.php',
            ["pagetitle" => "Rechercher un utilisateur",
                "cheminVueBody" => "Utilisateurs/search.php"]);
    }

    public static function readKeyword()
    {
        $keyword = $_POST['keyword'];
        $utilisateurs = (new UtilisateurRepository())->selectKeywordUtilisateur($keyword);
        Controller::afficheVue('view.php',
            ["utilisateurs" => $utilisateurs,
                "pagetitle" => "Liste des Utilisateurs",
                "cheminVueBody" => "Utilisateurs/list.php"]);
    }

    public static function select()
    {
        $row = $_POST['row'];
        $keyword = $_POST['keyword'];
        $utilisateurs = (new UtilisateurRepository())->selectKeyword($keyword, $row);
        Controller::afficheVue('view.php',
            ["utilisateurs" => $utilisateurs,
                "pagetitle" => "Liste des Utilisateurs",
                "cheminVueBody" => "Utilisateurs/step-4.php"]);
    }

    public static function update()
    {
        $utilisateur = (new UtilisateurRepository())->select($_GET['idUtilisateur']);
        if (is_null($utilisateur)) {
            MessageFlash::ajouter('danger', 'Utilisateur introuvable');
        }
        if (!ConnexionUtilisateur::estAdministrateur() && (!ConnexionUtilisateur::estConnecte() ||
                ConnexionUtilisateur::getLoginUtilisateurConnecte() != $utilisateur->getIdentifiant())) {
            MessageFlash::ajouter("danger", "Connectez-vous à votre compte pour le modifier.");
            Controller::redirect("index.php?action=connexion&controller=utilisateur");
        } else {
            Controller::afficheVue('view.php',
                ["utilisateur" => $utilisateur,
                    "pagetitle" => "Modifier les informations",
                    "cheminVueBody" => "Utilisateurs/update.php"]
            );
        }
    }

    public static function updated()
    {
        /*permet de mettre à jour les informations d'un utilisateur dans une base de données.
        La méthode récupère d'abord les informations de l'utilisateur à mettre à jour en utilisant
         le repository "UtilisateurRepository". Elle vérifie ensuite si l'utilisateur est connecté,
        La méthode vérifie également si l'utilisateur connecté est l'utilisateur à mettre à jour ou s'il
         s'agit d'un administrateur.
        vérifie si l'ancien mot de passe saisi est valide en utilisant la classe "MotDePasse".
        vérifie également si les mots de passe saisis sont identiques
        Si toutes les vérifications précédentes sont passées, la méthode met à jour les
         informations de l'utilisateur dans la base de données*/

        $utilisateur = (new UtilisateurRepository())->select($_POST['identifiant']);
        if (is_null($utilisateur)) {
            MessageFlash::ajouter('danger', 'utilisateur introuvable');
            Controller::redirect('index.php');
        }
        if (!ConnexionUtilisateur::estConnecte()) {
            MessageFlash::ajouter("danger", "Connectez-vous à votre compte pour le modifier.");
            Controller::redirect("index.php?action=connexion&controller=utilisateur");
        }
        if (ConnexionUtilisateur::getLoginUtilisateurConnecte() != $utilisateur->getIdentifiant() && !ConnexionUtilisateur::estAdministrateur()) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas modifier un compte qui ne vous appartient pas.");
            Controller::redirect("index.php?action=readAll&controller=question");
        }
        if (!MotDePasse::verifier($_POST['ancienMDP'], $utilisateur->getMdpHache())) {
            MessageFlash::ajouter('warning', 'L\'ancien mot de passe n\'est pas valide');
            Controller::redirect('index.php?controller=utilisateur&action=update&idUtilisateur=' . $utilisateur->getIdentifiant());
        }
        if ($_POST['mdp'] != $_POST['mdp2']) {
            MessageFlash::ajouter('warning', 'Les mots de passes sont différents');
            Controller::redirect('index.php?controller=utilisateur&action=update&idUtilisateur=' . $utilisateur->getIdentifiant());
        }
        if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {
            MessageFlash::ajouter('warning', 'Le format du mail saisi est invalide');
            Controller::redirect('index.php?controller=utilisateur&action=update');
            Controller::redirect('index.php?controller=utilisateur&action=update&idUtilisateur=' . $utilisateur->getIdentifiant());
        }
        if (strlen($_POST['mdp']) < 6) {
            MessageFlash::ajouter('info', 'Votre mot de passe doit contenir au moins 6 caractères.');
            Controller::redirect('index.php?controller=utilisateur&action=update&idUtilisateur=' . $utilisateur->getIdentifiant());
        }
        if (strlen($_POST['identifiant']) > 30 || strlen($_POST['nom']) > 30 || strlen($_POST['prenom']) > 30 ||
            strlen($_POST['mdp']) > 256 || strlen($_POST['mdp2']) > 256 || strlen($_POST['email']) > 256) {
            MessageFlash::ajouter('danger', 'Vous n\'avez pas respecté les contraintes');
            Controller::redirect('index.php?controller=utilisateur&action=create');
        }
        $bool = false;
        for ($i = 0; $i < 10 && !$bool; $i++) {
            if (strpos($_POST['mdp'], $i)) {
                $bool = true;
            }
        }
        if (!$bool) {
            MessageFlash::ajouter('info', 'Votre mot de passe doit contenir au moins 1 chiffre et une lettre.');
            Controller::redirect('index.php?controller=utilisateur&action=update&idUtilisateur=' . $utilisateur->getIdentifiant());
        } else {
            $utilisateur->setNom($_POST['nom']);
            $utilisateur->setPrenom($_POST['prenom']);
            $utilisateur->setMdpHache($_POST['mdp']);
            if ($utilisateur->getEmail() != $_POST['mail']) {
                $utilisateur->setEmailAValider($_POST['mail']);
                try {
                    $utilisateur->setNonce(MotDePasse::genererChaineAleatoire());
                    VerificationEmail::envoiEmailValidation($utilisateur);
                    MessageFlash::ajouter('info', 'Un mail a été envoyé pour valider votre nouvelle adresse e-mail');

                } catch (TransportExceptionInterface $e) {
                    Controller::redirect('index.php');
                }
            }
            if (!isset($_POST['estAdmin'])) {
                $utilisateur->setEstAdmin(false);
            } else {
                $utilisateur->setEstAdmin(true);
            }
            (new UtilisateurRepository())->update($utilisateur);
            if (ConnexionUtilisateur::getLoginUtilisateurConnecte() != $utilisateur->getIdentifiant()) {
                MessageFlash::ajouter('success', 'Les informations de ' . $utilisateur->getIdentifiant() . ' ont été mises à jour');
            } else {
                MessageFlash::ajouter('success', 'Vos informations ont été mises à jour');
            }
            Controller::redirect("index.php?controller=utilisateur&action=read&idUtilisateur=" . ConnexionUtilisateur::getLoginUtilisateurConnecte());
        }

    }

    public static function delete()
    {
        /*
        Permet de supprimer un utilisateur d'une base de données.
        La méthode vérifie tout d'abord si l'identifiant de l'utilisateur à supprimer a été fourni en tant que paramètre GET.
         Si ce n'est pas le cas, elle affiche un message d'erreur et redirige l'utilisateur vers la page d'accueil.

            Ensuite, la méthode vérifie si l'utilisateur connecté est un administrateur ou s'il s'agit de l'utilisateur à supprimer.
         Si l'utilisateur clique sur "Annuler", il est redirigé vers sa page
        de profil ou vers la liste des utilisateurs, selon le cas.
         Si l'utilisateur clique sur "Confirmer", le compte est supprimé de la base de données et l'utilisateur
        est redirigé vers la page d'accueil ou vers la liste des utilisateurs, selon le cas.
         */


        if (!isset($_GET['idUtilisateur'])) {
            MessageFlash::ajouter('info', 'Veuillez saisir un identifiant valide');
            Controller::redirect('index.php?controller=accueil');
        }
        if (!ConnexionUtilisateur::estAdministrateur() && (!ConnexionUtilisateur::estConnecte() || ConnexionUtilisateur::getLoginUtilisateurConnecte() != $_GET['idUtilisateur'])) {
            MessageFlash::ajouter('danger', 'Vous ne pouvez pas supprimer ce compte');
            Controller::redirect('index.php?controller=accueil');
        } else if (!isset($_POST["cancel"]) && !isset($_POST["confirm"])) {
            if (ConnexionUtilisateur::getLoginUtilisateurConnecte() == $_GET['idUtilisateur']) {
                $message = "Êtes vous sûr de vouloir supprimer votre compte ?";
            } else {
                $message = "Êtes vous sûr de vouloir supprimer ce compte ?";
            }
            Controller::afficheVue('view.php', ["pagetitle" => "Demande de confirmation ",
                "cheminVueBody" => "confirm.php",
                "url" => "index.php?action=delete&controller=utilisateur&idUtilisateur=" . $_GET['idUtilisateur'],
                "mdp" => true,
                "message" => $message]);
        } else if (isset($_POST["cancel"])) {
            if (ConnexionUtilisateur::getLoginUtilisateurConnecte() == $_GET['idUtilisateur']) {
                Controller::redirect("index.php?controller=utilisateur&action=read&idUtilisateur=" . $_GET['idUtilisateur']);
            } else {
                Controller::redirect("index.php?controller=utilisateur&action=readAll");
            }
        } else if (isset($_POST["confirm"])) {
            $utilisateur = (new UtilisateurRepository())->select($_GET['idUtilisateur']);
            if (!MotDePasse::verifier($_POST['mdp'], $utilisateur->getMdpHache())) {
                MessageFlash::ajouter('warning', 'Mot de passe incorrect.');
                Controller::redirect("index.php?action=delete&controller=utilisateur&idUtilisateur=" . $_GET['idUtilisateur']);
            } else {
                (new UtilisateurRepository())->delete($_GET['idUtilisateur']);
                if (ConnexionUtilisateur::getLoginUtilisateurConnecte() == $_GET['idUtilisateur']) {
                    MessageFlash::ajouter('success', "Votre compte a bien été supprimé");
                    ConnexionUtilisateur::deconnecter();
                    Controller::redirect("index.php?controller=accueil");
                } else {
                    MessageFlash::ajouter('success', "Ce compte a bien été supprimé");
                    Controller::redirect("index.php?controller=utilisateur&action=readAll");
                }
            }
        }
    }
}