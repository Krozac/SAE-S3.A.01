<?php

use App\Vote\Config\FormConfig as FormConfig;

if (!isset($_SESSION[FormConfig::$arr]['step'][2])) {
    FormConfig::redirect("index.php?controller=question&action=create");
}

if (isset($_POST['next'])) {
    FormConfig::postSession();
    $_SESSION[FormConfig::$arr]['step'][3] = 3;
    FormConfig::redirect("index.php?controller=question&action=form&step=4");
} else if (isset($_POST['previous'])) {
    FormConfig::postSession();
    FormConfig::redirect("index.php?controller=question&action=form&step=2");
}

$nbSection = $_SESSION[FormConfig::$arr]['nbSections'];
?>
<h1>Organisation des sections</h1>


<form method=post class="custom-form">


    <?php

    if (!isset($nbSection)) {
        $nb = 0;
    } else {
        $nb = $nbSection;
    }
    for ($i = 1; $i <= $nb; $i++) {
        echo '  
                <h2>Section n°' . $i . '</h2>
                <p  class="InputAddOn">
                    <label class="InputAddOn-item">Titre :</label>
                    <input class="InputAddOn-field" type="text" name=titre' . $i . '  size="50" maxlength="70" style="min-width: 40%"
                    value = "' . FormConfig::TextField('titre' . $i) . '" 
                    required>
                    <label class="maximum">70 caractères maximum</label>
                </p>
                <p>
                    <label class="InputAddOn-item" >Description :</label>
                    <textarea  maxlength="350" name=description' . $i . ' rows="7" cols="50" required id = "ta' . $i . '">' . FormConfig::TextField('description' . $i) . '</textarea>
                    <script>
                         const easyMDE' . $i . ' = new createMarkdownEditor({forceSync: true, element: document.getElementById("ta' . $i . '")});
                    </script>
                    <label class="maximum">350 caractères maximum</label>
               </p>';
    }

    ?>

    <input type="submit" name=previous value="Retour" id="precedent" class="nav" formnovalidate>
    <input type="submit" name=next value="Suivant" id="suivant" class="nav">
</form>
