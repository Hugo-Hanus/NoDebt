<?php
use Groupe\Groupe;
use function dataSecurity\secureString;
include ("inc/header/header.inc.php");
if(isset($_SESSION['user'])&&(!empty($_SESSION))){
    $formDisplay="<p>Vous n'avez pas crée de groupe </p>";
    $Arraygroupe = $repoGroupe->getAllGroupCreateByUserID($user->uid,$message);
    if(sizeof($Arraygroupe)>0){
    $formDisplay=$repoGroupe->formModifierDisplay($Arraygroupe);
    }

if(isset($_POST['creer'])){
    if((!empty(trim($_POST['nomGroupe']))) ){
    if($repoGroupe->storeGroupe(new Groupe(0,secureString($_POST['nomGroupe']),$_POST['devise'],$user->uid,0),$message)){
        $idG=$repoGroupe->getGroupeIdbyCreatorAndLibelle($user->uid,secureString($_POST['nomGroupe']),$message);
        $repoGroupe->addUsertoParticipate($idG[0],$user->uid,$message);
        $message="Votre groupe a été crée";
        }else{
            $message="Le groupe n'a pas été crée";
        }
    }else{
        $message="Un champ du formulaire contient uniquement un texte vide !";
    }

}
if(isset($_POST['modifier'])){
    if((!empty(trim($_POST['nomGroupeM'])))){
        if($repoGroupe->updateGroupe($_POST['nomGroupeSelect'],secureString($_POST['nomGroupeM']),$_POST['deviseM'],$message)){
        $message= "Vote groupe a été modifié";
        }else{
            $message = "Votre Groupe n'a pas été modifié"; 
        }
    }else{
    $message="Un champ du formulaire contient uniquement un texte vide !";
    }
}
}else{
    header('location:connexion.php');
}


?>
    <main>
        <section class="Profil">
        <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
            <h1>Créer/Éditer - Groupe</h1>
            <h2>Création</h2>
            <article>
                <form  method="post" enctype="multipart/form-data">
                        <label for="nomGroupe">Nom du groupe: *</label><input id="nomGroupe" name="nomGroupe" type="text"  required>
                        <label for="devise">Devise: *</label>
                        <select name="devise">
                            <option value="€">Euro (€)</option>
                            <option value="$">Dollar ($)</option>
                            <option value="£">Livre (£)</option>
                        </select>
                        <input type="submit" name="creer" value="Créer" />
                        <input class="Annuler" type="reset" name="annuler" value="Annuler" />
                </form>
            </article>
            <h2>Édition</h2>
            <article>
                <?php echo $formDisplay; ?>
            </article>
        </section>
    </main>
<?php
include ("inc/footer/footer.inc.php");
?>