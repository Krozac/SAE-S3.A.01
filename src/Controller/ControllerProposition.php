<?php

namespace App\Vote\Controller;

use App\Vote\Lib\ConnexionUtilisateur;
use App\Vote\Config\FormConfig;
use App\Vote\Lib\MessageFlash;
use App\Vote\Lib\MotDePasse;
use App\Vote\Model\DataObject\Calendrier;
use App\Vote\Model\DataObject\CoAuteur;
use App\Vote\Model\DataObject\Proposition;
use App\Vote\Model\DataObject\PropositionSection;
use App\Vote\Model\DataObject\Question;
use App\Vote\Model\DataObject\Responsable;
use App\Vote\Model\HTTP\Session;
use App\Vote\Model\Repository\CalendrierRepository;
use App\Vote\Model\Repository\CoAuteurRepository;
use App\Vote\Model\Repository\PropositionRepository;
use App\Vote\Model\Repository\PropositionSectionRepository;
use App\Vote\Model\Repository\QuestionRepository;
use App\Vote\Model\Repository\ResponsableRepository;
use App\Vote\Model\Repository\UtilisateurRepository;
use App\Vote\Model\Repository\VoteRepository;

class ControllerProposition
{

    public static function create(): void
    {
        $bool = true;
        $question = (new QuestionRepository())->select($_GET["idQuestion"]);
        if (is_null($question)) {
            MessageFlash::ajouter("danger", "Question introuvable");
            Controller::redirect("index.php?controller=question&action=readAll");
        }
        if (!ConnexionUtilisateur::estConnecte() || !Responsable::estResponsable($question, ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas créer de proposition, 
            vous n'êtes pas responsable pour cette question.");
            $bool = false;
        }
        if ($question->getPhase() != 'ecriture' || $question->aPassePhase()) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas créer de proposition en dehors 
            de la phase d'écriture.");
            $bool = false;
        }
        if (Responsable::aCreeProposition($question, ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous avez déjà crée une proposition pour cette question.");
            $bool = false;
        }
        if (!$bool) {
            Controller::redirect("index.php?controller=question&action=readAll");
        } else {
            FormConfig::setArr('SessionProposition');
            FormConfig::startSession();
            self::form();
        }
    }


    public
    static function form()
    {
        Session::getInstance();
        FormConfig::setArr('SessionProposition');
        $view = "";
        $step = $_GET['step'] ?? 1;
        $params = array();
        $question = (new QuestionRepository())->select($_GET['idQuestion']);
        switch ($step) {
            case 1:
                $view = "step-1";
                break;
            case 2:
                if (isset($_POST["row"]) && isset($_POST["keyword"]) && "row" != "") {
                    $row = $_POST['row'];
                    $keyword = $_POST['keyword'];
                    $utilisateurs = (new UtilisateurRepository())->selectKeywordUtilisateur($keyword);
                    $params['utilisateurs'] = $utilisateurs;
                } else {
                    $utilisateurs = (new UtilisateurRepository())->selectAll();
                    $params['utilisateurs'] = $utilisateurs;
                }
                $view = "step-2";
                break;
        }

        Controller::afficheVue('view.php',
            array_merge(["pagetitle" => "Créer une proposition",
                "cheminVueBody" => "Proposition/create/" . $view . ".php",
                "question" => $question], $params));
    }


    public
    static function read()
    {
        $proposition = (new PropositionRepository())->select($_GET['idProposition']);
        $question = (new QuestionRepository())->select($proposition->getIdQuestion());
        $coAuts = (new CoAuteurRepository())->selectWhere($_GET['idProposition'], '*', 'idproposition', "Coauteurs");
        //var_dump((new CoAuteurRepository())->selectAll());
        $sections = $question->getSections();
        $idProposition = $_GET['idProposition'];
        Controller::afficheVue('view.php', [
            "question" => $question,
            "idProposition" => $idProposition,
            "proposition" => $proposition,
            "sections" => $sections,
            "coAuts" => $coAuts,
            "pagetitle" => "Detail proposition",
            "cheminVueBody" => "Proposition/detail.php"]);
    }

    public
    static function readAll()
    {
        if (!isset($_GET['idQuestion'])) {
            MessageFlash::ajouter("warning", "Veuillez renseigner un ID valide.");
            Controller::redirect('index.php?controller=question&action=readAll');
        }
        $question = (new QuestionRepository())->select($_GET['idQuestion']);


        // Au lieu de faire un appel supplémentaire à la base de donnée, on vérifie s'il existe une proposition,
        // si oui, on récupère la question grâce à l'objet Proposition.
        $votants = $question->getVotants();
        $propositions = $question->getPropositionsTrie();
        if (sizeof($propositions) == 0) {
            MessageFlash::ajouter('info', 'Il n\'y a pas de propositions pour cette question.');
            Controller::redirect('index.php?action=readAll&controller=question');
        }

        if ($question->getSystemeVote() == 'majoritaire' || $question->getSystemeVote() == 'valeur') {

            Controller::afficheVue('view.php', ["pagetitle" => "Liste des propositions",
                "cheminVueBody" => "Proposition/listMajoritaire.php",
                "votants" => $votants,
                "propositions" => $propositions, "question" => $question]);
        } else {

            Controller::afficheVue('view.php', ["pagetitle" => "Liste des propositions",
                "cheminVueBody" => "Proposition/listUnique.php",
                "votants" => $votants,
                "propositions" => $propositions, "question" => $question]);
        }
    }

    public
    static function created()
    {
        /* On vérifie au préalable si l'utilisateur a le droit de créer une proposition pour la question donnée
        dans l'éventualité où il a tenté de le faire depuis la barre d'adresse. */
        FormConfig::setArr('SessionProposition');
        Session::getInstance();
        $user = Session::getInstance()->lire('user');

        $question = (new QuestionRepository())->select($_GET["idQuestion"]);
        if (is_null($question)) {
            MessageFlash::ajouter("danger", "Question introuvable");
            Controller::redirect("index.php?controller=question&action=readAll");
        }
        $bool = true;

        if (!ConnexionUtilisateur::estConnecte() || !Responsable::estResponsable($question, ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas créer de proposition, 
            vous n'êtes pas responsable pour cette question.");
            $bool = false;
        }
        if ($question->getPhase() != 'ecriture' || $question->aPassePhase()) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas créer de proposition en dehors de la phase d'écriture.");
            $bool = false;
        }
        if (Responsable::aCreeProposition($question, ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous avez déjà crée une proposition pour cette question.");
            $bool = false;
        }
        if (strlen($_SESSION[FormConfig::$arr]['titre']) > 480) {
            MessageFlash::ajouter("danger", "Vous n'avez pas respecté les contraintes.");
            $bool = false;
        }
        if (!$bool) {
            Controller::redirect("index.php?controller=question&action=readAll");
        }
        $responsable = new Responsable($question);
        $responsable->setIdentifiant(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        $proposition = new Proposition($_SESSION[FormConfig::$arr]['titre'], $responsable->getIdentifiant(), $question->getId(), 0, 0, false);
        $propositionBD = (new PropositionRepository())->sauvegarder($proposition, true);

        $coAuteursSelec = $_SESSION[FormConfig::$arr]['co-auteur'];
        $proposition->setId($propositionBD);
        foreach ($coAuteursSelec as $coAutSelec) {
            $aut = new CoAuteur((new UtilisateurRepository())->select($coAutSelec), $proposition);
            (new CoAuteurRepository())->sauvegarder($aut);
        }
        $sections = $question->getSections();
        foreach ($sections as $section) {
            if (strlen($_SESSION[FormConfig::$arr]['contenu' . $section->getId()]) > 1500) {
                MessageFlash::ajouter("danger", "Vous n'avez pas respecté les contraintes.");
                Controller::redirect("index.php?controller=question&action=readAll");
            }
            $propositionSection = new PropositionSection($proposition, $section, $_SESSION[FormConfig::$arr]['contenu' . $section->getId()]);
            (new PropositionSectionRepository())->sauvegarder($propositionSection);
        }

        MessageFlash::ajouter("success", "La proposition a bien été crée.");
        Controller::redirect("index.php?controller=question&action=readAll");
    }


    /**
     * Met à jour une proposition
     * Vérifie si l'utilisateur le peut
     *
     * @return void
     */
    public
    static function update(): void
    {
        if (!isset($_GET['idProposition'])) {
            MessageFlash::ajouter("warning", "Veuillez renseigner un ID valide.");
            Controller::redirect('index.php');
        }
        $proposition = (new PropositionRepository())->select($_GET['idProposition']);
        $question = (new QuestionRepository)->select($proposition->getIdQuestion());
        $bool = true;
        $coauteurs = $proposition->getCoAuteurs();
        $coauteursid = array();
        foreach ($coauteurs as $coauteur) {
            $coauteursid[] = $coauteur->getUtilisateur()->getIdentifiant();
        }

        if (!ConnexionUtilisateur::estConnecte()) {
            MessageFlash::ajouter("warning", "Vous ne pouvez pas modifier une proposition si vous n'êtes pas connecté.");
            $bool = false;
        }
        if (!in_array(ConnexionUtilisateur::getLoginUtilisateurConnecte(), $coauteursid) && ConnexionUtilisateur::getLoginUtilisateurConnecte() != $proposition->getIdResponsable()) {
            MessageFlash::ajouter("warning", "Vous ne pouvez pas modifier une proposition dont vous n'êtes pas co-auteur ou représentant.");
            $bool = false;
        }
        if ($question->getPhase() != 'ecriture') {
            MessageFlash::ajouter("warning", "Vous ne pouvez pas modifier cette proposition en dehors de la phase d'écriture.");
            $bool = false;
        }
        if (!isset($_GET['idProposition'])) {
            MessageFlash::ajouter("warning", "Proposition introuvable");
            $bool = false;
        } else {
            $proposition = (new PropositionRepository())->select($_GET['idProposition']);
            if ($proposition == null) {
                MessageFlash::ajouter("warning", "Proposition introuvable");
                $bool = false;
            }
        }
        if (!$bool) {
            Controller::redirect("index.php?action=readAll&controller=proposition");
        } else {
            FormConfig::setArr('SessionProposition');
            FormConfig::startSession();
            $_SESSION[FormConfig::$arr]['idProposition'] = $_GET['idProposition'];
            FormConfig::initialiserSessionsProposition($proposition);
            Controller::afficheVue('view.php', ["pagetitle" => "Modifier une proposition",
                "cheminVueBody" => "Proposition/create/step-1.php",
                "proposition" => $proposition,
                "question" => $question]);
        }
    }


    public
    static function updated()
    {
        //session_start();
        FormConfig::setArr('SessionProposition');
        $proposition = (new PropositionRepository())->select($_SESSION[FormConfig::$arr]["idProposition"]);
        $idquestion = $proposition->getIdQuestion();
        $question = (new QuestionRepository)->select($idquestion);

        $bool = true;
        if (!ConnexionUtilisateur::estConnecte() || (!Responsable::estResponsable($question, ConnexionUtilisateur::getLoginUtilisateurConnecte()) && !CoAuteur::estCoAuteur(ConnexionUtilisateur::getLoginUtilisateurConnecte(), $_SESSION[FormConfig::$arr]["idProposition"]))) {
            MessageFlash::ajouter("warning", "Vous ne pouvez pas modifier cette proposition, 
        vous n'êtes ni responsable ni co-auteur pour cette proposition.");
            $bool = false;
        }
        if ($question->getPhase() != 'ecriture') {
            MessageFlash::ajouter("warning", "Vous ne pouvez pas modifier cette proposition en dehors de la phase d'écriture.");
            $bool = false;
        }
        if (strlen($_SESSION[FormConfig::$arr]['titre']) > 480) {
            MessageFlash::ajouter("danger", "Vous n'avez pas respecté les contraintes.");
            $bool = false;
        }
        if (!$bool) {
            Controller::redirect("index.php?controller=proposition&action=readAll");
        } else {
            $sections = $question->getSections();
            (new PropositionSectionRepository())->delete($_SESSION[FormConfig::$arr]["idProposition"]);
            foreach ($sections as $section) {
                if (strlen($_SESSION[FormConfig::$arr]['contenu' . $section->getId()]) > 1500) {
                    MessageFlash::ajouter("danger", "Vous n'avez pas respecté les contraintes.");
                    Controller::redirect("index.php?controller=question&action=readAll");
                }
                $propositionSection = new PropositionSection((new PropositionRepository())->select($_SESSION[FormConfig::$arr]["idProposition"]), $section, $_SESSION[FormConfig::$arr]['contenu' . $section->getId()]);
                (new PropositionSectionRepository())->sauvegarder($propositionSection);
            }
            $prop = new Proposition($_SESSION[FormConfig::$arr]['titre'], $proposition->getIdResponsable(), $idquestion, $proposition->getNbEtoiles(), $proposition->getNbVotes(), false);
            $prop->setId($_SESSION[FormConfig::$arr]["idProposition"]);
            (new PropositionRepository())->update($prop);

            if (Responsable::estResponsable($question, ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                $coAuteursSelec = $_SESSION[FormConfig::$arr]['co-auteur'];
                $coAuteurs = (new CoAuteurRepository())->selectWhere($_SESSION[FormConfig::$arr]["idProposition"], '*', "idproposition");
                foreach ($coAuteurs as $coAut) {
                    (new CoAuteurRepository())->deleteSpecific($coAut);
                }
                foreach ($coAuteursSelec as $coAutSelec) {
                    $aut = new CoAuteur((new UtilisateurRepository())->select($coAutSelec), (new PropositionRepository())->select($_SESSION[FormConfig::$arr]["idProposition"]));
                    (new CoAuteurRepository())->sauvegarder($aut);
                }
                unset($_SESSION[FormConfig::$arr]['co-auteur']);
            }

            MessageFlash::ajouter("success", "La proposition a bien été modifié.");
            Controller::redirect("index.php?controller=proposition&action=readAll&idQuestion=" . $proposition->getIdQuestion());
        }
    }

    /**
     * Supprime une proposition
     * Vérifie d'abord si l'utilisateur le peut et affiche ensuite la vue pour confimer la suppression
     * @return void
     */
    public
    static function delete(): void
    {
        /* On vérifie au préalable si l'utilisateur a le droit de supprimer une question
        dans l'éventualité où il a tenté de le faire depuis la barre d'adresse. */

        if (!isset($_GET['idProposition'])) {
            MessageFlash::ajouter("warning", "Veuillez renseigner un ID valide.");
            Controller::redirect('index.php?controller=question&action=readAll');
        }
        $proposition = (new PropositionRepository())->select($_GET['idProposition']);
        if (!ConnexionUtilisateur::estConnecte() ||
            ConnexionUtilisateur::getLoginUtilisateurConnecte() != $proposition->getIdResponsable()) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer une proposition dont vous n'êtes pas le responsable.");
            Controller::redirect('index.php?controller=question&action=readAll');
        } else if (!isset($_POST["cancel"]) && !isset($_POST["confirm"])) {
            Controller::afficheVue('view.php', ["pagetitle" => "Supprimer proposition",
                "cheminVueBody" => "confirm.php",
                "message" => "Êtes vous sûr de vouloir supprimer cette proposition?",
                "mdp" => true,
                "url" => 'index.php?controller=proposition&action=delete&idProposition=' . $_GET['idProposition']]);
        } else if (isset($_POST["cancel"])) {
            Controller::redirect('index.php?controller=question&action=readAll');
        } else if (isset($_POST["confirm"])) {
            $utilisateur = (new UtilisateurRepository())->select(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            if (!MotDePasse::verifier($_POST['mdp'], $utilisateur->getMdpHache())) {
                MessageFlash::ajouter('warning', 'Mot de passe incorrect.');
                Controller::redirect('index.php?controller=proposition&action=delete&idProposition=' . $_GET['idProposition']);
            } else {
                (new PropositionRepository())->delete($_GET['idProposition']);
                MessageFlash::ajouter('success', 'La proposition a bien été supprimée');
                Controller::redirect("index.php?controller=question&action=readAll");
            }
        }
    }

    public
    static function eliminer()
    {
        $bool = false;
        $proposition = (new PropositionRepository())->select($_GET['idProposition']);
        $question = (new QuestionRepository())->select($proposition->getIdQuestion());
        $propositions = $question->getPropositionsTrie();
        if (ConnexionUtilisateur::getLoginUtilisateurConnecte() == $question->getOrganisateur()->getIdentifiant()
            && ($question->getPhase() == 'entre' || $question->getPhase() == 'debut') & $question->aPassePhase()) {
            $proposition->setEstEliminee(true);
            (new PropositionRepository())->update($proposition);
            foreach ($propositions as $propo) {
                if ($propo->getId() == $_GET['idProposition']) {
                    $bool = true;
                }
                if ($bool && !$propo->isEstEliminee()) {
                    $propo->setEstEliminee(true);
                    (new PropositionRepository())->update($propo);
                }
            }
            MessageFlash::ajouter('success', 'Les propositions sélectionnées ont été éliminées.');
        } else {
            MessageFlash::ajouter('danger', 'Vous n\'êtes pas responsable de cette question');
        }
        Controller::redirect('index.php?controller=proposition&action=readAll&idQuestion=' . $question->getId());
    }

    public
    static function annulerEliminer()
    {
        $bool = true;
        $proposition = (new PropositionRepository())->select($_GET['idProposition']);
        $question = (new QuestionRepository())->select($proposition->getIdQuestion());
        $propositions = $question->getPropositionsTrie();
        if (ConnexionUtilisateur::getLoginUtilisateurConnecte() == $question->getOrganisateur()->getIdentifiant()
            && ($question->getPhase() == 'entre' || $question->getPhase() == 'debut') & $question->aPassePhase()) {
            $proposition->setEstEliminee(false);
            (new PropositionRepository())->update($proposition);
            foreach ($propositions as $propo) {
                if ($propo->getId() == $_GET['idProposition']) {
                    $bool = false;
                }
                if ($bool && $propo->isEstEliminee()) {
                    $propo->setEstEliminee(false);
                    (new PropositionRepository())->update($propo);
                }
            }
            MessageFlash::ajouter('success', 'Vous avez annulé l\'élimination de ces propositions.');
        } else {
            MessageFlash::ajouter('danger', 'Vous n\'êtes pas responsable de cette question');
        }
        Controller::redirect('index.php?controller=proposition&action=readAll&idQuestion=' . $question->getId());
    }


}