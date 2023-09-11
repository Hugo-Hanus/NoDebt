<?php
require_once 'php/dataSecurity.php';
use function dataSecurity\secureString;
include ("inc/header/header.inc.php");
if(isset($_SESSION['user'])&&(!empty($_SESSION))){
    if(isset($_POST['modifier'])){
        if((!empty(trim($_POST['nom']))) && (!empty(trim($_POST['prenom']))) && (!empty(trim($_POST['mail']))) ){
            if(filter_var(secureString($_POST['mail']), FILTER_VALIDATE_EMAIL)){
                if($repoUser->updateUserInDB($user->uid,secureString($_POST['nom']),secureString($_POST['prenom']),secureString($_POST['mail']),$message)){
                $message ="Votre profil a été modifié";
                }
            }else{
                $message="Format email invalide";
            }
    }else{
        $message="Un champ du formulaire contient uniquement un texte vide !";
        }
    }
    if(isset($_POST['supprimer'])){
        if(!($repoUser->userInNotSoldeGroupe($user->uid,$message))){
            $message= '<form  method="post" enctype="multipart/form-data">
                <label>Êtes-vous sur de supprimer votre profil ? </label>
                <input type="submit" name="confirmer" value="Confirmer"/>
                <input class="Annuler"  type="submit" name="annuler" value="Annuler"/>
                ';
        }
    }
    if(isset($_POST['confirmer'])){
            if($repoUser->UpdateUserActifOrNotByMail($courriel,false,$message)){
                header('location: deconnexion.php');
            }else{
            $message='Erreur profil non supprimer';
            }
    }
    if(isset($_POST['annuler'])){
    $message="Suppression annulée";
    }
}else{header('location:connexion.php');}
?>
    <main>
        <section>
        <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
            <h1>Éditer-Profil</h1>
            <article>
                <form  method="post" enctype="multipart/form-data">
                        <label for="nom">Nom : *</label><input id="nom" name="nom" type="text" value="<?php echo $user->nom;?>"  required>
                        <label for="prenom">Prénom: *</label><input id="prenom" name="prenom" type="text" value="<?php echo $user->prenom;?>" required>
                        <label for="mail">Courriel: *</label><input id="mail" name="mail" type="email" value="<?php echo $user->courriel;?>" required>
                        <input type="submit" name="modifier" value="Modifier"  />
                        <input class="Annuler" type="reset" name="annuler" value="Annuler"  />
                        <input class="Supprimer" type="submit" name="supprimer" value="Supprimer mon Profil"  />
                </form>
            </article>
        </section>
    </main>
<?php
include ("inc/footer/footer.inc.php");
?>