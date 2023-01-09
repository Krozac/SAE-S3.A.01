<?php

use App\Vote\Lib\ConnexionUtilisateur;
use App\Vote\Model\DataObject\Calendrier;
use App\Vote\Model\DataObject\Message;

// Cette fonction compare deux objets Message en fonction de leur date
// Retourne -1 si la date de $message1 est antérieure à celle de $message2, 0 si elles sont égales, 1 sinon
function compare(Message $message1, Message $message2): int
{
    if ($message1->getDate() == $message2->getDate()) {
        return 0;
    }
    return ($message1->getDate() < $message2->getDate()) ? -1 : 1;
}

// Fusionne les tableaux $recus et $envoyes en un seul tableau $messages

$messages = array_merge($recus, $envoyes);
usort($messages, "compare");
// Trie le tableau $messages en utilisant la fonction compare()

echo '<h2>Votre conversation avec ' . htmlspecialchars($contact->getPrenom()) . ' ' . htmlspecialchars($contact->getNom()) . ' </h2>
<hr style="margin-bottom: 20px">
<div id="conversation">';

echo '';
$i = 1;
$id = '';
foreach ($messages as $message) {
    // Calcul de la différence de temps entre la date actuelle et la date du message
    $interval = (new DateTime(date("d-m-Y H:i")))->diff(new DateTime($message->getDate()));
    if (Calendrier::diff($interval) == "") {
        $diff = 'quelques secondes . ';
        // Si la différence de temps est inférieure à 1 minute, affiche "quelques secondes"
    } else {
        $diff = Calendrier::diff($interval);
    }
    // Si c'est le dernier message du tableau, ajoute l'identifiant "dernierMessage"
    if ($i == sizeof($messages)) {
        $id = 'id = "dernierMessage"';
    }
    if ($message->getAuteur()->getIdentifiant() == ConnexionUtilisateur::getLoginUtilisateurConnecte()) {
        echo ' <p ' . $id . '  style = "margin-left: 50%" class="date" >Vous, il y a ' . $diff . ' </p > ';
        echo '<div style = "margin-left: 60%;" class="messageChat" > ' . htmlspecialchars($message->getContenu()) . '</div > ';

    } else {
        echo '<p ' . $id . ' class="date" >' . htmlspecialchars($message->getAuteur()->getPrenom()) . ', il y a ' . $diff . ' </p > ';
        echo '<div class="messageChat" > ' . htmlspecialchars($message->getContenu()) . '</div > ';
    }
    $i++;
}
?>
</div>
<form class="zoneTexte" method="post" action="index.php?action=created&controller=message">
    <input type="hidden" name="idContact" value="<?php echo htmlspecialchars($_GET['idContact']) ?>">
    <p class="champ">
        <label for="message_id">Message : </label>
        <textarea id="message_id" maxlength="350" name="message" rows="7" cols="50" required> </textarea>
        <label class="maximum">350 caractères maximum</label>
    </p>
    <input id="suivant" type="submit" value="Envoyer" class="nav">

</form>

