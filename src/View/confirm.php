<style>
    .blur {
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: gray;
        opacity: 0.5;
        position: absolute;
    }

    .over {
        border-radius: 8px;
        position: absolute;
        top: 25%;
        left: 50%;
        transform: translate(-50%);
        background: #012e49;
        height: 25%;
        width: 50%;
    }

    #bt1 {
        position: absolute;
        bottom: 15px;
        left: 25px;
    }

    #bt2 {
        position: absolute;
        bottom: 25px;
        right: 15px;
    }

</style>
<div class="blur">
</div>
<div class="over">
    <form method="post" action=<?= $url ?>>
        <p style="padding: 20px; max-width: 80%; margin: auto"><label style="color: white"><?php echo $message ?></label></p>
        <?php if(isset($mdp)) {
            echo '<label style="color: white; margin: 30px" for="mdp_id">Confirmation du mot de passe : </label>
                  <input type="password" name="mdp" id="mdp_id">';
        }?>

        <Button id="bt1" class="nav" type="submit" name="cancel" value="Annuler">Annuler</Button>
        <Button id="bt2" class="nav" type="submit" name="confirm" value="Confirmer">Confirmer</Button>

    </form>
</div>
