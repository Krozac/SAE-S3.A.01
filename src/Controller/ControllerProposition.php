<?php

namespace App\Vote\Controller;

use App\Vote\Model\DataObject\Calendrier;
use App\Vote\Model\DataObject\Proposition;
use App\Vote\Model\DataObject\PropositionSection;
use App\Vote\Model\DataObject\Responsable;
use App\Vote\Model\HTTP\Session;
use App\Vote\Model\Repository\AuteurRepository;
use App\Vote\Model\Repository\CalendrierRepository;
use App\Vote\Model\Repository\PropositionRepository;
use App\Vote\Model\Repository\PropositionSectionRepository;
use App\Vote\Model\Repository\QuestionRepository;
use App\Vote\Model\Repository\ResponsableRepository;
use App\Vote\Model\Repository\SectionRepository;
use App\Vote\Model\Repository\UtilisateurRepository;

class ControllerProposition
{

    public static function create()
    {
        Session::getInstance();
        $question = (new QuestionRepository())->select($_GET['idQuestion']);
        if ($question == null) {
            ControllerAccueil::erreur();
        } else {
            $date = date("Y/m/d H:i:s");
            $calendrier = $question->getCalendrier();
            if (!isset($_SESSION['user']) || !Responsable::estResponsable($question, $_SESSION['user']['id'])) {
                ControllerAccueil::erreur();
            }
//        else if ($calendrier->getDebutEcriture() > $date || $calendrier->getFinEcriture() < $date) {
//            ControllerAccueil::erreur();
//        }
            else {
                Controller::afficheVue('view.php', ["pagetitle" => "Accueil",
                    "cheminVueBody" => "Proposition/create.php",
                    "question" => $question]);
            }
        }
    }

    public static function read()
    {
        $proposition = (new PropositionRepository())->select($_GET['idProposition']);
        $question = $proposition->getQuestion();
        $sections = $question->getSections();
        $idProposition = $_GET['idProposition'];
        Controller::afficheVue('view.php', [
            "question" => $question,
            "idProposition" => $idProposition,
            "proposition" => $proposition,
            "sections" => $sections,
            "pagetitle" => "Detail proposition",
            "cheminVueBody" => "Proposition/detail.php"]);
    }

    public static function readAll()
    {
        $propositions = (new PropositionRepository())->selectWhere($_GET['idQuestion'], '*', 'idquestion');
        $question = (new QuestionRepository())->select($_GET['idQuestion']);
        $votants = $question->getVotants();
        Controller::afficheVue('view.php', ["pagetitle" => "Liste des propositions",
            "votants" => $votants,
            "cheminVueBody" => "Proposition/list.php",
            "propositions" => $propositions, "question" => $question]);
    }


    public static function created()
    {
        session_start();
        $question = (new QuestionRepository())->select($_GET["idQuestion"]);
        $responsable = (new ResponsableRepository())->select($_SESSION['user']['id']);
        $proposition = new Proposition($_POST['titre'], $responsable, $question, 0);
        $propositionBD = (new PropositionRepository())->sauvegarder($proposition);
        $proposition->setId($propositionBD);
        $sections = $question->getSections();
        foreach ($sections as $section) {
            $propositionSection = new PropositionSection($proposition, $section, $_POST['contenu' . $section->getId()]);
            (new PropositionSectionRepository())->sauvegarder($propositionSection);

        }

        $questions = (new QuestionRepository())->selectAll();
        Controller::afficheVue('view.php', ["pagetitle" => "Accueil",
            "cheminVueBody" => "Proposition/created.php",
            "questions" => $questions]);
    }

    public static function update()
    {
        session_start();
        $propositionSections = (new PropositionSectionRepository())->selectWhere($_GET['idProposition'], '*', 'idproposition');
        $proposition = (new PropositionRepository())->select($_GET['idProposition']);
        Controller::afficheVue('view.php', ["pagetitle" => "Modifier la proposition",
            "cheminVueBody" => "Proposition/update.php",
            "propositionSections" => $propositionSections, "proposition" => $proposition]);
    }

    public static function updated()
    {
        session_start();
        $responsable = (new ResponsableRepository())->select($_SESSION['user']['id']);
        $propositionSections = (new PropositionSectionRepository())->selectWhere($_GET['idProposition'], '*', 'idproposition');
        $proposition = (new PropositionRepository())->select($_GET["idProposition"]);
        $question = $proposition->getQuestion();
        //var_dump($proposition->getQuestion());
        $sections = $question->getSections();
        //var_dump($question->getSections());
        (new PropositionSectionRepository())->delete($_GET["idProposition"]);
        foreach ($sections as $section) {
            $propositionSection = new PropositionSection((new PropositionRepository())->select($_GET["idProposition"]), $section, $_POST['contenu' . $section->getId()]);
            echo "tessst";
            //(new PropositionSectionRepository())->delete($_GET["idProposition"]);
            (new PropositionSectionRepository())->sauvegarder($propositionSection);
        }
        Controller::afficheVue('view.php', ["pagetitle" => "Accueil",
            "cheminVueBody" => "Proposition/updated.php",
            "responsable" => $responsable,
            "question" => $question]);
    }
}
