<?php
use App\Vote\Config\FormConfig as FormConfig;
if ((isset($_POST["keyword"]) && isset($_POST["row"]))) {
    $keyword = htmlspecialchars($_POST["keyword"]);
    $row = htmlspecialchars($_POST["row"]);
} else {
    $keyword = "";
    $row = "";
}
?>

<form method="post">
    <fieldset>
        <legend class="InputAddOn-item">Selectionner des <?php echo $_SESSION[FormConfig::$arr]['type'] ?> :</legend>
        <p  class="InputAddOn">
            <input type="text" placeholder="" name="keyword" id="motclef" value ="<?php echo $keyword ?>" required>
            <input type ="hidden" name = "row" value = "nom" >
            <input style="max-height: 25px" type="image" alt = "Submit" src="../web/images/search.png" class = "search">
        </p>
    </fieldset>
</form>