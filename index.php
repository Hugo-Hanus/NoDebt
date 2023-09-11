<?php 
include("inc/header/header.inc.php");
if(isset($_SESSION['user'])&&(!empty($_SESSION))){

$displayGroup=$repoGroupe->getInfoIndexDisplay($user->uid,$message);    
$displayInv=$repoGroupe->displayInviteGroupe($user->uid,$message);

if((isset($_POST['accepter']) ) && (isset($_POST['groupe']))){
    if(is_numeric($_POST['groupe'])){
    $groupeInfo=$repoGroupe->getInfoGroupeID($_POST['groupe'],$message);
    $message= '<form action="index.php" method="post" enctype="multipart/form-data">
          <label>Êtes-vous sur de rejoindre le groupe : '.$groupeInfo[1].' ? </label>
          <input type="hidden" name="groupeConfirme" value="'.$_POST['groupe'].'">
          <input type="submit" name="confirmer" value="Confirmer"/>
          <input type="submit" name="annuler" value="Annuler"/>
          ';
        }else{
            $message="Erreur Encodage Groupe";
        }
  }
  if(isset($_POST['groupeConfirme']) && isset($_POST['confirmer'])){
    if(is_numeric($_POST['groupeConfirme'])){
        echo $_POST['groupeConfirme'];
        if($repoGroupe->addUsertoGroupe($_POST['groupeConfirme'],$user->uid,$message)){
            header("location:groupeView.php?groupe=".$_POST['groupeConfirme']);
        }else{
            $message="Erreur lors de L'ajout";
        }
    }else{
        $message="Erreur Encodage Groupe";
    }
  }
  if((isset($_POST['refuser']))&& (isset($_POST['groupe']))){
    if(is_numeric($_POST['groupe'])){
        $groupeInfo=$repoGroupe->getInfoGroupeID($_POST['groupe'],$message);
        $message= '<form action="index.php" method="post" enctype="multipart/form-data">
              <label>Êtes-vous sur de refuser l\'invitation du groupe : '.$groupeInfo[1].' ? </label>
              <input type="hidden" name="groupeRefuser" value="'.$_POST['groupe'].'">
              <input type="submit" name="confirmerRefus" value="Confirmer"/>
              <input type="submit" name="annuler" value="Annuler"/>
              ';
            }else{
                $message="Erreur Encodage Groupe";
            }
  }
  if(isset($_POST['groupeRefuser']) && isset($_POST['confirmerRefus'])){
    if(is_numeric($_POST['groupeRefuser'])){
        $groupeInfo=$repoGroupe->getInfoGroupeID($_POST['groupeRefuser'],$message);
        if($repoGroupe->inviteRefuseUsertoGroupe($_POST['groupeRefuser'],$user->uid,$message)){
            $allEmailFromAGroup=$repoGroupe->formerHeaderAllEmail($_POST['groupeRefuser'],$message);
            if(($allEmailFromAGroup!=="Erreur-mail")){
                $message="Vous avez refusé l'invitation ";
                $messageMail = $user->uid."a refusé l'invitation dans le groupe ".$groupeInfo[1]." de ". $user->courriel;
                $sujet = "Refus de l'invitation de ".$user->prenom." dans ".$groupeInfo[1]." de ". $user->courriel;
                $to = $allEmailFromAGroup;
                $headers = 'From: Nodebt<'.$user->courriel.">\r\n";
                $headers.='Cc:<'.$user->courriel.">\r\n";
                $headers.= 'X-Mailer: PHP/' . phpversion();
                    if(mail($to, $sujet, $messageMail, $headers)){
                            $message .=" et Mail envoyé ";
                    }else{
                    $message.=" Mais erreur lors de l'envoie de mail" ;
                }
                    $displayInv=$repoGroupe->displayInviteGroupe($user->uid,$message);
                }else{
                    $message="Erreur lors du récupérage d'adresse mail";
                }
            
        }else{
            $message="Erreur lors du Refus";
        }
    }else{
        $message="Erreur Encodage Groupe";
    }
  }
}else{header('location:connexion.php');}  
?>
<main>
    <section>
    <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
        <h1>Groupes</h1>
            <h2>Groupes:</h2>
            <section class="Main">
                <?= $displayGroup ?>
            </section>
            <h2>Invitation:</h2>
            <section class="Main">
            <?= $displayInv ?>
            </section>
    </section>
</main>
<?php
include("inc/footer/footer.inc.php");
?>
