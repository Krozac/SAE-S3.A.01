<?php

namespace App\Vote\Model\DataObject;

use App\Vote\Model\Repository\CalendrierRepository;
use App\Vote\Model\Repository\CoAuteurRepository;
use App\Vote\Model\Repository\PropositionRepository;
use App\Vote\Model\Repository\SectionRepository;
use App\Vote\Model\Repository\ResponsableRepository;
use App\Vote\Model\Repository\VotantRepository;
use App\Vote\Model\Repository\VoteRepository;
use Exception;
use mysql_xdevapi\XSession;

class Question extends AbstractDataObject
{

    private int $id;
    private string $titre;
    private string $description;
    private string $creation;
    private Utilisateur $organisateur;
    private string $systemeVote;
    private array $calendriers;


    public function __construct(string      $titre, string $description, string $creation,
                                Utilisateur $organisateur, string $systemeVote)
    {
        $this->titre = $titre;
        $this->description = $description;
        $this->creation = $creation;
        $this->organisateur = $organisateur;
        $this->systemeVote = $systemeVote;
    }

    /**
     * @return string
     */
    public function getSystemeVote(): string
    {
        return $this->systemeVote;
    }

    /**
     * @param string $systemeVote
     */
    public function setSystemeVote(string $systemeVote): void
    {
        $this->systemeVote = $systemeVote;
    }


    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCreation(): string
    {
        $date = date_create($this->creation);
        return date_format($date, 'd/m/Y à H:i:s');
    }

    /**
     * @param string $creation
     */
    public function setCreation(string $creation): void
    {
        $this->creation = $creation;
    }

    /**
     * @return Utilisateur
     */

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Utilisateur
     */
    public function getOrganisateur(): Utilisateur
    {
        return $this->organisateur;
    }

    /**
     * @param Utilisateur $organisateur
     */
    public function setOrganisateur(Utilisateur $organisateur): void
    {
        $this->organisateur = $organisateur;
    }

    /**
     * @return string
     */
    public function getTitre(): string
    {
        return $this->titre;
    }

    /**
     * @param string $titre
     */
    public function setTitre(string $titre): void
    {
        $this->titre = $titre;
    }

    /* On obtient les sections pour une question*/
    public function getSections(): array
    {
        return (new SectionRepository())->selectWhere($this->id, '*', "idQuestion", "Sections");
    }

    /* On obtient les responsables pour une question*/
    public function getResponsables(): array
    {
        return (new ResponsableRepository())->selectWhere($this->id, '*', "idQuestion", "Responsables");
    }

    /* On obtient les votants pour une question*/
    public function getVotants(): array
    {
        return (new VotantRepository())->selectWhere($this->id, '*', "idQuestion", "Votants");
    }

    /* On obtient les propositions pour une question*/
    public function getPropositions(): array
    {
        $propositions = (new PropositionRepository())->selectWhere($this->id, '*', "idQuestion", 'Propositions');
        return $propositions;
    }

    public function getCoAuteurs(): array
    {
        $coAuts = array();
        $propositions = $this->getPropositions();
        foreach ($propositions as $prop){
            $coAuts[] = $prop->getCoAuteurs();
        }
        return $coAuts;
    }

    public function getPropositionsNonEliminees(array $propositions): array
    {
        //prend en paramètre un tableau de propositions et retourne un tableau
        // contenant uniquement les propositions qui n'ont pas été éliminées.
        // La méthode parcourt chaque proposition du tableau en utilisant une boucle "foreach",
        // et vérifie si la propriété "estEliminee" de chaque proposition est égale à false.
        // Si c'est le cas, elle ajoute la proposition au tableau de propositions non éliminées.
        // La méthode retourne finalement le tableau de propositions non éliminées.

        $propositionsNonEliminees = array();
        foreach ($propositions as $proposition) {
            if (!$proposition->isEstEliminees) {
                $propositionsNonEliminees[] = $proposition;
            }
        }
        return $propositionsNonEliminees;
    }

    public function getPropositionsTrie()
    {
        $propositions = (new PropositionRepository())->selectWhere($this->id, '*', "idQuestion",
            'Propositions', 'nbEtoiles', 'DESC');
        if ($this->systemeVote == 'majoritaire') {
            $propositionsTrie = array();
            foreach ($propositions as $proposition) {
                $votesProposition = (new VoteRepository())->selectWhere($proposition->getId(), '*',
                    'idProposition', 'Votes', 'valeurvote');
                $nbVotes = $proposition->getNbVotes();
                if ($nbVotes > 0) {
                    if ($nbVotes == 1) {
                        $proposition->setVoteMedian($votesProposition[0]->getValeur());
                    } else {
                        if (sizeof($votesProposition) % 2 == 0) {
                            $proposition->setVoteMedian($votesProposition[(sizeof($votesProposition) / 2) - 1]->getValeur());
                        } else {
                            $proposition->setVoteMedian($votesProposition[((sizeof($votesProposition) + 1) / 2) - 1]->getValeur());
                        }
                    }
                }
                $propositionsTrie[] = $proposition;
            }
            usort($propositionsTrie, array($this, "trieMedianne"));
        } else if ($this->systemeVote == 'valeur') {
            usort($propositions, array($this, "trieMoyenne"));
        }
        return $propositions;
    }

    function trieMoyenne(Proposition $proposition1, Proposition $proposition2)
    {
        if ($proposition1->getNbVotes() == 0 && $proposition2->getNbVotes() == 0) {
            return 0;
        }
        $moyenneProposition1 = $proposition1->getNbEtoiles() / $proposition1->getNbVotes();
        $moyenneProposition2 = $proposition2->getNbEtoiles() / $proposition2->getNbVotes();
        if ($moyenneProposition1 == $moyenneProposition2) {
            return 0;
        }
        return ($moyenneProposition1 > $moyenneProposition2) ? -1 : 1;
    }

    function trieMedianne(Proposition $proposition1, Proposition $proposition2)
    {
        if ($proposition1->getVotemedian() == $proposition2->getVotemedian()) {
            return 0;
        }
        return ($proposition1->getVotemedian() < $proposition2->getVotemedian()) ? -1 : 1;
    }


    /**
     * On obtient la phase en cours pour une question
     * @return string
     * @throws Exception
     */
    public function getPhase(): string
    {
        $date = date('Y-m-d H:i');
        if ($date < $this->getCalendrier()->getDebutEcriture(true)) {
            return 'debut';
        } else if ($date >= $this->getCalendrier()->getDebutEcriture(true) && $date < $this->getCalendrier()->getFinEcriture(true)) {
            return 'ecriture';
        } else if ($date > $this->getCalendrier()->getFinEcriture(true) && $date < $this->getCalendrier()->getDebutVote(true)) {
            return 'entre';
        } else if ($date >= $this->getCalendrier()->getDebutVote(true) && $date < $this->getCalendrier()->getFinVote(true)) {
            return 'vote';
        } else {
            return 'fini';
        }
    }

    /*
     * Retourne le calendrier courant pour une question donnée.
     * C'est à -dire le calendrier en cours ou alors le calendrier prochain.
     * Si $tous true, on retourne tous les calendriers de la question,
     * utilisé notamment dans la vue de détail d'une question.
    */
    public function getCalendrier(bool $tous = false)
    {
        if (!isset($this->calendriers)) {
            $this->calendriers = (new CalendrierRepository())->selectWhere($this->id, '*', 'idQuestion', 'Calendriers', 'debutVote');
        }
        if ($tous) {
            return $this->calendriers;
        }
        $date = date('Y-m-d H:i:s');
        foreach ($this->calendriers as $calendrier) {
            if ($date <= $calendrier->getDebutVote(true) || ($date >= $calendrier->getDebutEcriture(true) && $date <= $calendrier->getFinVote(true))) {
                return $calendrier;
            }// Si la date courante est comprise dans le calendrier, on retourne le calendrier.
        }
        /*
         * Si on est à ce stade, c'est que la date courante est entre 2 calendriers, avant ou après.
         * On retourne donc le premier calendrier qui a une date de début d'écriture des propositions
         * supérieure à la date courante.
         * */
        foreach ($this->calendriers as $calendrier) {
            if ($date <= $calendrier->getDebutEcriture(true)) {
                return $calendrier;
            }
        }
        // Si on arrive ici, c'est qu'on est dans le cas où la question est terminée. On retourne le dernier calendrier.
        return $this->calendriers[sizeof($this->calendriers) - 1];
    }

    /*
     * Permet de savoir si une question donnée a déjà passé une première phase de vote et d'écriture
    */

    public function aPassePhase(): bool
    {
        $calendriers = $this->getCalendrier(true);
        $calendrierActuel = $this->getCalendrier();
        if (sizeof($this->calendriers) > 1 && $calendriers[0] != $calendrierActuel) {
            return true;
        }
        return false;
    }

    // Cette fonction vérifie si la phase actuelle est la dernière phase du calendrier.
    // Elle retourne un booléen : vrai si c'est la dernière phase, faux sinon.
    public function estDernierePhase(): bool
    {
        // Récupère tous les calendriers
        $calendriers = $this->getCalendrier(true);
        // Récupère le calendrier actuel
        $calendrierActuel = $this->getCalendrier();

        // Si le dernier élément du tableau des calendriers est égal au calendrier actuel
        if ($calendriers[sizeof($this->calendriers) - 1] == $calendrierActuel) {
            // Alors c'est la dernière phase
            return true;
        }
        // Sinon, ce n'est pas la dernière phase
        return false;
    }

    public function formatTableau($update = false): array
    {
        $tab = array(
            "titreTag" => $this->titre,
            "descriptionTag" => $this->description,
            "creationTag" => $this->creation,
            "idOrganisateurTag" => $this->organisateur->getIdentifiant(),
            "systemeVoteTag" => $this->systemeVote
        );
        if ($update) {
            $tab["idQuestionTag"] = $this->id;
        }
        return $tab;
    }
}
