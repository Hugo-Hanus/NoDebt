<?php
require_once 'php/dataSecurity.php';
use function dataSecurity\secureString;
include ("inc/header/header.inc.php");
$admit=false;
$tabIdAdmit=$repoUser->userAccesToGroupe($_GET['groupe'],$message);
if($tabIdAdmit>0){
   in_array($user->uid,$tabIdAdmit)?$admit=true:$admit=false;
}
if(isset($_SESSION['user'])&&(!empty($_SESSION))&&$admit){
if(isset($_GET['groupe'])&& is_numeric($_GET['groupe'])){
    $idGroupe=$_GET['groupe'];
    $groupeInfo=$repoGroupe->getInfoGroupeID($idGroupe,$message);
    $invited=false;
    if((isset($_POST['Ajouter']))){
        if(empty(trim($_POST['mail']))){
            $message='Email non valide vide';
        }else{
            $mail=secureString($_POST['mail']);
        $message= '<form method="post" enctype="multipart/form-data">
        <label>Êtes-vous sur d\'ajouter '.$mail.' à votre groupe ? </label>
        <input id="mailConfirme" name="mailConfirme" type="hidden" value="'.$mail.'">
        <input type="submit" name="confirmer" value="Confirmer"/>
        <input type="submit" name="annuler" value="Annuler"/>';
        }
    }
    if(isset($_POST['confirmer'])){
       if($repoUser->existsInDB($_POST['mailConfirme'],$message)){
        $userMail=$repoUser->getUserByMail($_POST['mailConfirme']);
            if($repoGroupe->userInvInGroupe($idGroupe,$userMail->uid)){
                $message = "Cette personne est déjà invité OU est le créateur de se groupe";
            }else{
                if($repoGroupe->inviteUsertoGroupe($idGroupe,$userMail->uid,$message)){
                $messageMail = "Vous êtes invité dans le groupe ".$groupeInfo[1]." de ". $user->courriel." Rendez vous sur NoDebt :http://192.168.128.13/~e190533/EVAL_V5/index.php";
                $sujet = "Vous êtes invité dans le groupe ".$groupeInfo[1]." de ". $user->courriel;
                $to = $userMail->courriel;
                $headers = 'From: Nodebt<'.$user->courriel.">\r\n";
                $headers.='Cc:<'.$user->courriel.">\r\n";
                $headers.= 'X-Mailer: PHP/' . phpversion();
                    if(mail($to, $sujet, $messageMail, $headers)){
                            $message .=" et Mail envoyé ";
                    }else{
                    $message.="Erreur lors de l'envoie de mail" ;
                    }
                }
            }
       
        }else{
                $messageMail = "Vous êtes invité dans le groupe".$groupeInfo[1]." de ". $user->courriel."Rendez vous sur NoDebt";
                $sujet = "Vous êtes invité dans le groupe".$groupeInfo[1]." de ". $user->courriel;;
                $to = $userMail->uid;
                $headers = 'From: Nodebt<'.$user->courriel.">\r\n";
                $headers.='Cc:<'.$user->courriel.">\r\n";
                $headers.='X-Mailer: PHP/' . phpversion();
                 if(mail($to, $sujet, $messageMail, $headers)){
                 $message ="Mail envoyé";
                  }else{
                $message="Erreur lors de l'envoie de mail" ;
                }
        }
    }
    
    if(isset($_POST['annuler'])){
       $message="Invitation annulée";
    }

}else{
    $message="Erreur accès du groupe";
    echo '<style>section { display:none;}</style>';
}
}else{header('location:connexion.php');}
?>
    <main>
    <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
        <section class="Profil">
            <?php echo '<h1>Ajouter un Utilisateur au Groupe: <a href="groupeView.php?groupe='.$groupeInfo[0].'">'.$groupeInfo[1].'</a></h1>';?>
            <article>
                <form  method="post" enctype="multipart/form-data">
                        <label for="mail">Courriel: *</label><input id="mail" name="mail" type="email">
                        <input type="submit" name="Ajouter" value="Ajouter"  />
                        <input type="reset" name="Annuler" value="Annuler"  />
                </form>
            </article>
        </section>
    </main>
<?php
include ("inc/footer/footer.inc.php");
?>