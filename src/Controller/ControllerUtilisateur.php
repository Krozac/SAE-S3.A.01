<?php

namespace App\Vote\Controller;

use App\Vote\Config\FormConfig;
use App\Vote\Lib\MessageFlash;
use App\Vote\Lib\MotDePasse;
use App\Vote\Model\DataObject\Utilisateur;
use App\Vote\Model\HTTP\Session;
use App\Vote\Model\Repository\PropositionRepository;
use App\Vote\Model\Repository\QuestionRepository;
use App\Vote\Model\Repository\UtilisateurRepository;

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
        Session::getInstance();
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
        self::connexion();
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
            }else{
                Session::getInstance()->enregistrer('user', array('id' => $utilisateur->getIdentifiant()));
                ControllerAccueil::home();
            }
        }
    }

    public static function read()
    {
        Session::getInstance();
        $utilisateur = ((new UtilisateurRepository))->select($_SESSION['user']['id']);
        $questions = (new QuestionRepository())->selectWhere($_SESSION['user']['id'], '*', 'idorganisateur');
        $propositions = (new PropositionRepository())->selectWhere($_SESSION['user']['id'], '*', 'idresponsable');
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
        if ($_POST['mdp'] != $_POST['mdp2']) {
            ControllerAccueil::erreur();
        }
        $utilisateur = Utilisateur::construireDepuisFormulaire($_POST);
        (new UtilisateurRepository())->sauvegarder($utilisateur);
        Controller::afficheVue('view.php',
            ["pagetitle" => "Compte crée",
                "cheminVueBody" => "Utilisateurs/formulaireConnexion.php"]);
    }

    public static function search()
    {
        Controller::afficheVue('view.php',
            ["pagetitle" => "Rechercher un utilisateur",
                "cheminVueBody" => "Utilisateurs/search.php"]);
    }

    public static function readKeyword()
    {
        $row = $_POST['row'];
        $keyword = $_POST['keyword'];
        $utilisateurs = (new UtilisateurRepository())->selectKeyword($keyword, $row);
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


}