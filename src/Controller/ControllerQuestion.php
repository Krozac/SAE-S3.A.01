<?php

namespace App\Vote\Controller;


use App\Vote\Lib\ConnexionUtilisateur;
use App\Vote\Config\FormConfig;
use App\Vote\Lib\MessageFlash;
use App\Vote\Lib\MotDePasse;
use App\Vote\Model\DataObject\Calendrier;
use App\Vote\Model\DataObject\Question;
use App\Vote\Model\DataObject\Responsable;
use App\Vote\Model\DataObject\Section;
use App\Vote\Model\DataObject\Votant;
use App\Vote\Model\HTTP\Session;
use App\Vote\Model\Repository\CalendrierRepository;
use App\Vote\Model\Repository\QuestionRepository;
use App\Vote\Model\Repository\ResponsableRepository;
use App\Vote\Model\Repository\SectionRepository;
use App\Vote\Model\Repository\UtilisateurRepository;
use App\Vote\Model\Repository\VotantRepository;

class ControllerQuestion
{

    /*
     * Réinitialise les variables de session et
     * lance le formulaire de création
     */
    public static function create(): void
    {

        /* Il faut obligatoirement être connecté pour créer une question*/
        if (ConnexionUtilisateur::estConnecte()) {
            FormConfig::setArr('SessionQuestion');
            FormConfig::startSession();
            self::form();
        } else {
            MessageFlash::ajouter("warning", "Vous ne pouvez pas créer une question si vous n'êtes pas connecté.");
            Controller::redirect("index.php?action=connexion&controller=utilisateur");
        }
    }

    public static function read(): void
    {
        if (!isset($_GET['idQuestion'])) {
            MessageFlash::ajouter("warning", "Veuillez renseigner un ID valide.");
            Controller::redirect('index.php?controller=question&action=readAll');
        }
        $question = (new QuestionRepository())->select($_GET['idQuestion']);
        $propositions = $question->getPropositions();
        $sections = $question->getSections();
        $responsables = $question->getResponsables();
        $votants = $question->getVotants();
        Controller::afficheVue('view.php', ["question" => $question,
            "sections" => $sections,
            "responsables" => $responsables,
            "propositions" => $propositions,
            "votants" => $votants,
            "pagetitle" => "Detail question",
            "cheminVueBody" => "Question/detail.php"]);
    }


    /*
     * Liste les questions
     */

    public static function readAll()
    {
        //A optimiser
        if (!isset($_GET["selection"])) {
            $_GET["selection"] = "toutes";
        }

        if ($_GET["selection"] == "vote") {
            $questions = (new QuestionRepository())->getPhaseVote();
        } else if ($_GET["selection"] == "ecriture") {
            $questions = (new QuestionRepository())->getPhaseEcriture();
        } else if ($_GET["selection"] == "terminees") {
            $questions = (new QuestionRepository())->getTerminees();
        } else {
            $questions = (new QuestionRepository())->selectAll();
        }

        Controller::afficheVue('view.php',
            ["questions" => $questions,
                "pagetitle" => "Liste des questions",
                "cheminVueBody" => "Question/list.php"]);
    }

    /*
     * Lancement des pages du formulaire de création de la Question
     */
    public static function form(): void
    {
        // Récupère l'instance de la session
        Session::getInstance();

        // Définit la configuration du formulaire
        FormConfig::setArr('SessionQuestion');

        // Initialise les variables de vue et de paramètres
        $view = "";
        $params = array();

        // Récupère la variable étape de la requête GET, ou la définit à 1 par défaut si elle n'est pas définie
        $step = $_GET['step'] ?? 1;

        // Selon la valeur de la variable étape, détermine quelle vue afficher
        switch ($step) {
            case 1:
                $view = "step-1";
                break;
            case 2:
                $view = "step-2";
                break;
            case 3:
                $view = "step-3";
                break;
            case 4:
                // Si les variables POST row et keyword sont définies et que la variable row n'est pas vide,
                // récupère la variable utilisateurs et la définit dans le tableau de paramètres
                if (isset($_POST["row"]) && isset($_POST["keyword"])) {
                    $row = $_POST['row'];
                    $keyword = $_POST['keyword'];
                    $utilisateurs = (new UtilisateurRepository())->selectKeywordUtilisateur($keyword);
                    $params['utilisateurs'] = $utilisateurs;
                } else {
                    $utilisateurs = (new UtilisateurRepository())->selectAll();
                    $params['utilisateurs'] = $utilisateurs;
                }
                $view = "step-4";
                break;
            case 5:
                // Si les variables POST row et keyword sont définies et que la variable row n'est pas vide,
                // récupère la variable utilisateurs et la définit dans le tableau de paramètres
                if (isset($_POST["row"]) && isset($_POST["keyword"])) {
                    $row = $_POST['row'];
                    $keyword = $_POST['keyword'];
                    $utilisateurs = (new UtilisateurRepository())->selectKeywordUtilisateur($keyword);
                    $params['utilisateurs'] = $utilisateurs;
                } else {
                    $utilisateurs = (new UtilisateurRepository())->selectAll();
                    $params['utilisateurs'] = $utilisateurs;
                }
                $view = "step-5";
                break;
            case 6:
                $view = "step-6";
                break;

        }

        // Affiche la vue avec le titre de page et le chemin de vue spécifiés, et passe le tableau de paramètres en tant que variables
        Controller::afficheVue('view.php',
            array_merge(["pagetitle" => "Créer une question",
                "cheminVueBody" => "Question/create/" . $view . ".php"], $params));
    }


    /*
     * Recherche de Question
     */
    public static function search()
    {
        $utilisateurs = array();
        Controller::afficheVue('view.php',
            ["utilisateurs" => $utilisateurs,
                "pagetitle" => "Rechercher un utilisateur",
                "cheminVueBody" => "Question/create/step-4.php"]);
    }

    /*
     * Enregistre dans la base de donnée toutes les données relatives à la Question:
     * - Calendrier
     * - Auteurs
     * - Sections
     * - Votants
     */
    public static function created(): void
    {
        /* Avant l'enregistrement de chacun des éléments, on rajoute une vérification supplémentaire pour respecter les contraintes
        présentes dans la base de donnée.
        Théoriquement, un utilisateur lambda qui arrive à cette étape n'a pas pu déroger à ces contraintes.
        */

        //On vérifie si l'utilisateur est connecté
        if (!ConnexionUtilisateur::estConnecte()) {
            MessageFlash::ajouter("warning", "Vous ne pouvez pas créer une question si vous n'êtes pas connecté.");
            Controller::redirect("index.php?action=readAll&controller=question");
        }
        FormConfig::setArr('SessionQuestion');

        $creation = date("Y/m/d H:i:s");
        $organisateur = (new UtilisateurRepository)->select(ConnexionUtilisateur::getLoginUtilisateurConnecte());

        $question = new Question($_SESSION[FormConfig::$arr]['Titre'], $_SESSION[FormConfig::$arr]['Description'],
            $creation, $organisateur, $_SESSION[FormConfig::$arr]['systemeVote']);
        $questionBD = (new QuestionRepository())->sauvegarder($question, true);
        if ($questionBD != null) {
            $question->setId($questionBD);
        } else {
            Controller::afficheVue('view.php', ["pagetitle" => "erreur", "cheminVueBody" => "Accueil/erreur.php"]);
        }

        self::verifBD($question);


        $responsables = $_SESSION[FormConfig::$arr]['responsables'];

        foreach ($responsables as $responsable) {
            $utilisateur = new Responsable($question);
            $utilisateur->setIdentifiant($responsable);
            $responsableBD = (new ResponsableRepository())->sauvegarder($utilisateur);
        }

        $votants = $_SESSION[FormConfig::$arr]['votants'];

        foreach ($votants as $votant) {
            $utilisateur = new Votant($question);
            $utilisateur->setIdentifiant($votant);
            $votantBD = (new VotantRepository())->sauvegarder($utilisateur);
        }

        $sections = $_SESSION[FormConfig::$arr]['Sections'];
        if (sizeof($sections) > 10) {
            MessageFlash::ajouter('danger', 'Le nombre de section est invalide');
            Controller::redirect("index.php?action=form&controller=question&step=3");
        }
        foreach ($sections as $value) {
            $section = new Section($value['titre'], $value['description'], $question);
            if (strlen($value['titre']) > 80 || strlen($value['description']) > 360) {
                MessageFlash::ajouter("danger", "Les contraintes de taille maximales des champs de textes n'ont pas été respectées.");
                Controller::redirect("index.php?action=form&controller=question&step=3");
            }
            $sectionBD = (new SectionRepository())->sauvegarder($section, true);
            if ($sectionBD != null) {
                $section->setId($sectionBD);
            } else {
                Controller::afficheVue('view.php', ["pagetitle" => "erreur", "cheminVueBody" => "Accueil/erreur.php"]);
            }
        }

        MessageFlash::ajouter('success', 'La question a bien été crée');
        Controller::redirect("index.php?controller=question&action=readAll");
        FormConfig::startSession();
    }


    public static function update(): void
    {
        /* On vérifie au préalable si l'utilisateur a le droit de modifier une question
        dans l'éventualité où il a tenté de le faire depuis la barre d'adresse. */
        if (!isset($_GET['idQuestion'])) {
            MessageFlash::ajouter("danger", "Veuillez renseigner un ID valide.");
            Controller::redirect('index.php?controller=question&action=readAll');
        }
        $question = (new QuestionRepository())->select($_GET['idQuestion']);
        if (is_null($question)) {
            MessageFlash::ajouter("danger", "Question introuvable");
            Controller::redirect("index.php?controller=question&action=readAll");
        }
        $bool = true;
        if ($question->getPhase() != 'debut') {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas modifier une question dont la phase d'écriture a déjà commencée.");
            $bool = false;
        }
        if (!ConnexionUtilisateur::estConnecte() ||
            ConnexionUtilisateur::getLoginUtilisateurConnecte() != $question->getOrganisateur()->getIdentifiant() &&
            !ConnexionUtilisateur::estAdministrateur()) {
            MessageFlash::ajouter("warning", "Vous ne pouvez pas modifier une question dont vous n'êtes par l'organisateur.");
            $bool = false;
        }

        if (!$bool) {
            Controller::redirect("index.php?action=readAll&controller=question");
        } else {
            FormConfig::setArr('SessionQuestion');
            FormConfig::startSession();
            Controller::afficheVue('view.php', ["pagetitle" => "Modifier une question",
                "cheminVueBody" => "question/create/step-1.php",
                "idQuestion" => $_GET['idQuestion']]);
        }
    }


    public static function updated(): void
    {
        /* On vérifie au préalable si l'utilisateur a le droit de modifier une question
        dans l'éventualité où il a tenté de le faire depuis la barre d'adresse. */
        $bool = true;
        FormConfig::setArr('SessionQuestion');
        Session::getInstance();
        // var_dump($_SESSION);
        $question = (new QuestionRepository())->select($_SESSION[FormConfig::$arr]['idQuestion']);
        if (is_null($question)) {
            MessageFlash::ajouter("danger", "Question introuvable");
            Controller::redirect("index.php?controller=question&action=readAll");
        }
        if ($question->getPhase() != 'debut') {
            MessageFlash::ajouter("warning", "Vous ne pouvez pas modifier une question dont la phase d'écriture a déjà commencée.");
            $bool = false;
        }
        if (!ConnexionUtilisateur::estConnecte() ||
            ConnexionUtilisateur::getLoginUtilisateurConnecte() != $question->getOrganisateur()->getIdentifiant() &&
            !ConnexionUtilisateur::estAdministrateur()) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas modifier une question dont vous n'êtes par l'organisateur.");
            $bool = false;
        }
        if (!$bool) {
            Controller::redirect("index.php?action=readAll&controller=question");
        }
        FormConfig::setArr('SessionQuestion');
        foreach ($question->getCalendrier(true) as $calendrier) {
            (new CalendrierRepository())->delete($calendrier->getId());
        }
        self::verifBD($question);
        $question->setTitre($_SESSION[FormConfig::$arr]['Titre']);
        $question->setDescription($_SESSION[FormConfig::$arr]['Description']);
        (new QuestionRepository())->update($question);


        $ancSections = $question->getSections();
        $nouvSections = $_SESSION[FormConfig::$arr]['Sections'];
        for ($i = 0; $i < count($nouvSections); $i++) {
            if (count($ancSections) <= $i) {
                $section = new Section($nouvSections[$i]['titre'], $nouvSections[$i]['description'], $question);
                if (strlen($nouvSections[$i]['titre']) > 80 || strlen($nouvSections[$i]['description']) > 360) {
                    MessageFlash::ajouter("danger", "Les contraintes de taille maximales des champs de textes n'ont pas été respectées.");
                    Controller::redirect("index.php?action=form&controller=question&step=3");
                }
                $sectionBD = (new SectionRepository())->sauvegarder($section, true);
                if ($sectionBD != null) {
                    $section->setId($sectionBD);
                } else {
                    Controller::afficheVue('view.php', ["pagetitle" => "erreur", "cheminVueBody" => "Accueil/erreur.php"]);
                }
            } else {
                $ancSections[$i]->setTitre($nouvSections[$i]['titre']);
                $ancSections[$i]->setDescription($nouvSections[$i]['description']);
                (new SectionRepository())->update($ancSections[$i]);
            }
        }
        if (count($ancSections) > count($nouvSections)) {
            for ($diff = count($ancSections) - count($nouvSections); $diff > 0; $diff--) {
                (new SectionRepository())->delete($ancSections[count($ancSections) - 1]->getId());
                unset($ancSections[count($ancSections) - 1]);
            }
        }

        $responsables = $question->getResponsables();
        $ancResponsables = array();

        foreach ($responsables as $responsable) {
            $ancResponsables[] = $responsable->getIdentifiant();
        }
        $tab = array();
        $tab = $_SESSION[FormConfig::$arr]['responsables'];
        $nouvResponsables = array();
        foreach ($tab as $val) {
            $nouvResponsables[] = $val;
        }
        for ($i = 0; $i < sizeof($nouvResponsables); $i++) {
            if (!in_array($nouvResponsables[$i], $ancResponsables)) {
                $utilisateur = new Responsable($question);
                $utilisateur->setIdentifiant($nouvResponsables[$i]);
                $responsableBD = (new ResponsableRepository())->sauvegarder($utilisateur);
            }
        }
        for ($i = 0; $i < sizeof($ancResponsables); $i++) {
            if (!in_array($ancResponsables[$i], $nouvResponsables)) {
                (new ResponsableRepository())->delete($ancResponsables[$i]);
            }
        }

        $votants = $question->getVotants();
        $ancVotants = array();
        foreach ($votants as $val) {
            $ancVotants[] = $val->getIdentifiant();
        }
        $tab2 = $_SESSION[FormConfig::$arr]['votants'];
        $nouvVotants = array();
        foreach ($tab2 as $val) {
            $nouvVotants[] = $val;
        }
        for ($i = 0; $i < sizeof($nouvVotants); $i++) {
            if (!in_array($nouvVotants[$i], $ancVotants)) {
                $utilisateur = new Votant($question);
                $utilisateur->setIdentifiant($nouvVotants[$i]);
                $votantBD = (new VotantRepository())->sauvegarder($utilisateur);
            }
        }

        for ($i = 0; $i < sizeof($ancVotants); $i++) {
            if (!in_array($ancVotants[$i], $nouvVotants)) {
                (new VotantRepository())->delete($ancVotants[$i]);
            }
        }

        MessageFlash::ajouter('success', 'La question a bien été modifiée');
        Controller::redirect("index.php?controller=question&action=readAll");
        FormConfig::startSession();
    }

    public static function delete(): void
    {
        /* On vérifie au préalable si l'utilisateur a le droit de supprimer une question
        dans l'éventualité où il a tenté de le faire depuis la barre d'adresse. */

        if (!isset($_GET['idQuestion'])) {
            MessageFlash::ajouter("warning", "Veuillez renseigner un ID valide.");
            Controller::redirect('index.php?controller=question&action=readAll');
        }
        $question = (new QuestionRepository())->select($_GET['idQuestion']);
        if (is_null($question)) {
            MessageFlash::ajouter("danger", "Question introuvable");
            Controller::redirect("index.php?controller=question&action=readAll");
        }
        if ((!ConnexionUtilisateur::estConnecte() ||
                ConnexionUtilisateur::getLoginUtilisateurConnecte() != $question->getOrganisateur()->getIdentifiant()) &&
            !ConnexionUtilisateur::estAdministrateur()) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer une question dont vous n'êtes par l'organisateur.");
            Controller::redirect('index.php?controller=question&action=readAll');
        } else if (!isset($_POST["cancel"]) && !isset($_POST["confirm"])) {
            Controller::afficheVue('view.php', ["pagetitle" => "Demande de confirmation",
                "cheminVueBody" => "confirm.php",
                "message" => "Êtes vous sûr de vouloir supprimer cette question?",
                "mdp" => true,
                "url" => 'index.php?action=delete&controller=question&idQuestion=' . $_GET['idQuestion']]);
        } else if (isset($_POST["cancel"])) {
            Controller::redirect('index.php?controller=question&action=readAll');
        } else if (isset($_POST["confirm"])) {
            $utilisateur = (new UtilisateurRepository())->select(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            if (!MotDePasse::verifier($_POST['mdp'], $utilisateur->getMdpHache())) {
                MessageFlash::ajouter('warning', 'Mot de passe incorrect.');
                Controller::redirect('index.php?action=delete&controller=question&idQuestion=' . $_GET['idQuestion']);
            } else {
                (new QuestionRepository())->delete($_GET['idQuestion']);
                MessageFlash::ajouter('success', 'La question a bien été supprimée');
                Controller::redirect("index.php?controller=question&action=readAll");
            }
        }
    }

    public static function readKeyword(): void
    {
        if (!isset($_POST['keyword'])) {
            Controller::redirect('index.php?action=create&controller=message');
        }
        $keyword = $_POST['keyword'];
        $questions = (new QuestionRepository())->selectKeyword($keyword, 'titre');
        Controller::afficheVue('view.php',
            ["questions" => $questions,
                "pagetitle" => "Liste des questions",
                "cheminVueBody" => "Question/list.php"]);
    }

    public static function result(): void
    {
        /* On vérifie au préalable si la question est terminée dans l'éventualité où il
        a tenté d'accéder à cette page depuis la barre d'adresse. */

        $bool = true;
        if (!isset($_GET['idQuestion'])) {
            MessageFlash::ajouter("warning", "Veuillez renseigner un ID valide.");
            $bool = false;
        }
        $question = (new QuestionRepository())->select($_GET['idQuestion']);
        if (is_null($question)) {
            MessageFlash::ajouter("danger", "Question introuvable");
            $bool = false;
        }
        if ($question->getPhase() != 'fini') {
            MessageFlash::ajouter("warning", "Vous ne pouvez pas consulter la page des résultats pour l'instant.");
            $bool = false;
        }
        if (!$bool) {
            Controller::redirect('index.php?controller=question&action=readAll');
        }
        $propositions = $question->getPropositionsTrie();

        if ($question->getSystemeVote() == 'majoritaire') {
            Controller::afficheVue('view.php', ['pagetitle' => 'Page de résultat',
                'cheminVueBody' => "Question/resultatMajoritaire.php",
                'propositions' => $propositions]);
        } else {
            Controller::afficheVue('view.php', ['pagetitle' => 'Page de résultat',
                'cheminVueBody' => "Question/resultat.php",
                'propositions' => $propositions]);
        }
    }

    /**
     * @return void
     */

    /*
     *
     * vérifie la validité des données de la question passée en paramètre.
     * Si la longueur du titre ou de la description dépasse une certaine limite,
     * Si le mode de scrutin n'est pas "valeur", "majoritaire" ou "unique",
     * Si le calendrier n'est pas valide (par exemple si la fin de la période d'écriture précède
     * le début de la période d'écriture, ou si la fin de la période de vote précède le début de la
     * période de vote),
     * Si toutes les vérifications sont passées avec succès, le calendrier est enregistré
     * en base de données.
     */
    private static function verifBD(Question $question): void
    {
        if (strlen($_SESSION[FormConfig::$arr]['Titre']) > 80 || strlen($_SESSION[FormConfig::$arr]['Description']) > 360) {
            MessageFlash::ajouter("danger", "Les contraintes de taille maximales des champs de textes n'ont pas été respectées.");
            Controller::redirect("index.php?action=form&controller=question&step=2");
        }
        if ($_SESSION[FormConfig::$arr]['systemeVote'] != "valeur" &&
            $_SESSION[FormConfig::$arr]['systemeVote'] != "majoritaire" &&
            $_SESSION[FormConfig::$arr]['systemeVote'] != "unique") {
            MessageFlash::ajouter("danger", $_SESSION[FormConfig::$arr]['systemeVote']);
            MessageFlash::ajouter("danger", "Veuillez vérifier le mode de scrutin.");
            Controller::redirect("index.php?action=form&controller=question&step=5");
        }
        $nbCalendriers = $_SESSION[FormConfig::$arr]['nbCalendriers'];
        if ($nbCalendriers > 7) {
            MessageFlash::ajouter("danger", "Le nombre de calendrier est invalide");
            Controller::redirect("index.php?action=form&controller=question&step=2");
        }

        for ($i = 1; $i <= $nbCalendriers; $i++) {
            $calendrier = new Calendrier($question, FormConfig::TextField('debutEcriture' . $i), FormConfig::TextField('finEcriture' . $i),
                FormConfig::TextField('debutVote' . $i), FormConfig::TextField('finVote' . $i));
            if ($calendrier->getDebutEcriture(true) >= $calendrier->getFinEcriture(true)
                || $calendrier->getDebutVote(true) < $calendrier->getFinEcriture(true)) {
                MessageFlash::ajouter("danger", "Les contraintes du calendrier n'ont pas été respectées.");
                Controller::redirect("index.php?action=form&controller=question&step=2");
            }
            if ($calendrier->getDebutVote(true) >= $calendrier->getFinVote(true)) {
                MessageFlash::ajouter("danger", "Les contraintes du calendrier n'ont pas été respectées.");
                Controller::redirect("index.php?action=form&controller=question&step=2");
            }
            if ($i < $nbCalendriers && $calendrier->getFinVote(true) > FormConfig::TextField('debutEcriture' . $i + 1)) {
                MessageFlash::ajouter("danger", "Les contraintes du calendrier n'ont pas été respectées.");
                Controller::redirect("index.php?action=form&controller=question&step=2");
            }
            if (FormConfig::TextField('debutEcriture' . $i) < date("d-m-Y") ||
                FormConfig::TextField('finEcriture' . $i) < date("d-m-Y") ||
                FormConfig::TextField('debutVote' . $i) < date("d-m-Y") ||
                FormConfig::TextField('finVote' . $i) < date("d-m-Y")) {
                MessageFlash::ajouter('warning', "test");
                Controller::redirect('index.php?controller=question&action=form&step=2');
            }
            $calendrierBD = (new CalendrierRepository())->sauvegarder($calendrier, true);
            if ($calendrierBD != null) {
                $calendrier->setId($calendrierBD);
            } else {
                (new QuestionRepository())->delete($question->getId());
                MessageFlash::ajouter("danger", "Les contraintes du calendrier n'ont pas été respectées.");
                Controller::redirect("index.php?action=form&controller=question&step=2");
            }
        }
    }

    public static function passerPhase()
    {
        $bool = true;
        if (!isset($_GET['idQuestion'])) {
            MessageFlash::ajouter("warning", "Veuillez renseigner un ID valide.");
            $bool = false;
        }
        $question = (new QuestionRepository())->select($_GET['idQuestion']);
        if (is_null($question)) {
            MessageFlash::ajouter("danger", "Question introuvable");
            $bool = false;
        }
        if (!ConnexionUtilisateur::estConnecte() || ConnexionUtilisateur::getLoginUtilisateurConnecte() != $question->getOrganisateur()->getIdentifiant()) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas modifier cette question.");
            $bool = false;
        }
        if (!$bool) {
            Controller::redirect('index.php?controller=question&action=readAll');
        }
        $phase = $question->getPhase();
        $calendrier = $question->getCalendrier();
        $nexPhase = "";
        if ($phase == 'debut') {
            $nextPhase = "d'écriture";
        } else if ($phase == 'ecriture' || $phase == 'entre') {
            $nextPhase = "de vote";
        } else if ($phase == 'vote') {
            $nextPhase = "de dépouillement des votes";

        }
        if (!isset($_POST["cancel"]) && !isset($_POST["confirm"])) {
            Controller::afficheVue('view.php', ["pagetitle" => "Demande de confirmation",
                "cheminVueBody" => "confirm.php",
                "message" => "Êtes vous sûr de vouloir passer directement à la phase " . $nextPhase . " ?",
                "url" => 'index.php?action=passerPhase&controller=question&idQuestion=' . $_GET['idQuestion']]);
        } else if (isset($_POST["cancel"])) {
            Controller::redirect('index.php?controller=question&action=readAll');
        } else if (isset($_POST["confirm"])) {
            if ($phase == 'debut') {
                MessageFlash::ajouter('success', 'La question est passée en phase d\'écriture');
                $calendrier->setDebutEcriture(date("Y-m-d H:i"));
            } else if ($phase == 'entre') {
                MessageFlash::ajouter('success', 'La question est passée en phase de vote');
                $calendrier->setDebutVote(date("Y-m-d H:i"));
            } else if ($phase == 'ecriture') {
                MessageFlash::ajouter('success', 'La question est passée en phase de vote');
                $calendrier->setFinEcriture(date("Y-m-d H:i"));
                $calendrier->setDebutVote(date("Y-m-d H:i"));
            } else if ($phase == 'vote') {
                MessageFlash::ajouter('success', 'La question est passée en phase de dépouillement des votes');
                $calendrier->setFinVote(date("Y-m-d H:i"));
            }
            (new CalendrierRepository())->update($calendrier);
            Controller::redirect('index.php?controller=question&action=readAll');
        }
    }
}