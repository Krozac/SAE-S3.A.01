<h2>Votre messagerie</h2>
<?php
$contacts = array();
foreach ($recus as $recu) {
    if (!in_array($recu->getAuteur(), $contacts)) {
        $contacts[] = $recu->getAuteur();
    }
}
foreach ($contacts as $contact) {
    echo '<a class="contact" href=index.php?controller=message&action=read&idContact=' . $contact->getIdentifiant() . '
         > ' . $contact->getIdentifiant() . '</a > ';
}

?>
<a style="display: block; margin-top: 100px" href="index.php?action=create&controller=message">Envoyer un message</a>
