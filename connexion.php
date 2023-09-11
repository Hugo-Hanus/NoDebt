<?php
include('inc/header/header.inc.php');
use function dataSecurity\secureString;
use function dataSecurity\generateNewMDP;

$form='<form  method="post">
<label for="mail">Email : *</label><input id="mail" name="mail" type="email" >
<label for="mdp">Mot de Passe: *</label><input id="mdp" name="mdp" type="password" >
<label class="Cache" for="spam"><input id="span" name="spam" type="text" ></label>
<input class="VersPage" type=submit name="resetMDP" value="Mot de passe oublié ?">
<input type="submit"  name="connexion" value="Connexion">
<a class="VersPage" href="inscription.php">Inscription</a></form>';
if(isset($_GET['courriel'])&& isset($_GET['active'])){
    if(filter_var(secureString($_POST['mail']), FILTER_VALIDATE_EMAIL)){
        if($repoUser->existsInDB(secureString($_GET['courriel']),$message)){
            if($repoUser->checkIfUserActif(secureString($_POST['mail']),$message)){
                if($repoUser->UpdateUserActifOrNotByMail(secureString($_POST['mail']),false,$message)){

                }
            }
        }else{
            $message="Votre compte n'exsite pas";
        }
    }
}

if(isset($_POST['connexion']) && empty($_POST['spam'])){
    if(filter_var(secureString($_POST['mail']), FILTER_VALIDATE_EMAIL)){
        if($repoUser->existsInDB(secureString($_POST['mail']),$message)){
            if($repoUser->checkIfUserActif(secureString($_POST['mail']),$message)){
                $sujet = "Récupération de votre compte";
                $to = secureString($_POST['mail']);
                $lien='http://192.168.128.13/~e190533/EVAL_V5/connexion.php?courriel='.$to.'&active=1';
                $messageMail = 'Voici le lien de récuprération de votre compte : '.$lien;
                $headers = 'From: Nodebt<'."h.hanus@student.helmo.be".">\r\n";
                $headers.= 'X-Mailer: PHP/' . phpversion();
                    if(mail($to, $sujet, $messageMail, $headers)){
                            $message .=" Mail envoyé ";
                    }else{
                    $message.="Erreur lors de l'envoie de mail" ;
                    }
                }else{
                    if($repoUser->userConnexion(secureString($_POST['mail']),secureString($_POST['mdp']),$message)){
                        header('location: index.php');
                }
        }
        }else{
            $mesage="Mail non valide";
        }
    }
}


if(isset($_POST['resetMDP']) && empty($_POST['spam']) && empty($_POST['mdp']) && empty($_POST['mail']) ){
    $form='<form  method="post">
    <label for="mail">Email : *</label><input id="mail" name="mailReset" type="email" >
    <input type="submit"  name="newMDP" value="Nouveau mot de passe">
    <input class="Annuler" type="submit"  name="reset" value="Annuler">
    </form>';
}

if(isset($_POST['newMDP'])&& isset($_POST['mailReset'])){
    if(filter_var(secureString($_POST['mailReset']), FILTER_VALIDATE_EMAIL)){
        if($repoUser->existsInDB(secureString($_POST['mailReset']),$message)){
            $mdp=generateNewMDP();
            $messageMail = "Voici votre nouveau MDP :".generateNewMDP();
                    $sujet = "Mot de passe changé sur noDebt";
                    $to = secureString($_POST['mailReset']);
                    $headers = 'From: Nodebt<'."h.hanus@student.helmo.be".">\r\n";
                    $headers.= 'X-Mailer: PHP/' . phpversion();
                        if(mail($to, $sujet, $messageMail, $headers)){
                            if($repoUser->newMDP(secureString($_POST['mailReset']),$mdp,$message)){
                                $message="Nouveau mot de passe dans votre Boite mail";
                            }
                                $message .=" et Mail envoyé ";
                        }else{
                        $message.="Erreur lors de l'envoie de mail" ;
                        }
                    }
            
        }else{
            $mesage="Mail non valide";
        }
    }




?>
<main>
    <h1>Connexion </h1>
    <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
    <section class="Profil">
        <?php
            echo $form;
        ?>
        </article>
    </section>
</main>
<?php
include ("inc/footer/footer.inc.php");
?>
