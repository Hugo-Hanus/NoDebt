<?php
include("inc/header/header.inc.php");
require_once("php/groupement.php");
require_once("php/dataSecurity.php");
use function dataSecurity\secureString;
use Groupement\Groupement;
use Groupement\GroupementRepository;
$repoGroupement=new GroupementRepository();
$admit=false;
$tabIdAdmit=$repoUser->userAccesToGroupe($_GET['groupe'],$message);
if($tabIdAdmit>0){
   in_array($user->uid,$tabIdAdmit)?$admit=true:$admit=false;
}
if(isset($_SESSION['user'])&&(!empty($_SESSION))&&$admit){
if(isset($_GET['groupe'])&& is_numeric($_GET['groupe'])){
    if(isset($_GET['message'])){
        $message=secureString($_GET['message']);
    }
    $groupe=$repoGroupe->getInfoGroupeID($_GET['groupe'],$message);
    $tabID=$repoGroupe->getAllUserByGroupeID($groupe[0],$message);
    $formCreate="";$formDelegate="";$groupementInfo="";$formDelete="";$groupementComposition="";$formDeleteParticipant="";$formAddUser="";
    $tabUserNotInGroupement=[];
    foreach($tabID as $userTab){
        if($repoGroupement->userNotInAGroupementInAGroupe($groupe[0],$userTab->uid,$message)){
        array_push($tabUserNotInGroupement,$userTab);
        }
    }
    $groupement=$repoGroupement->getGroupementByUser($user->uid,$groupe[0]);
    if($groupement==null){
        $formCreate=$repoGroupement->displayFormAddUserToGroupement($tabID,$user,1,$message);
    }else{
        $groupementComposition=$repoGroupement->diplayAllUserGroupement($groupement);
        if($groupement->uid==$user->uid){
            $tabUser=$repoGroupement->getParticipantOfGroupement($groupement->ggid,$message);
            $formAddUser=$repoGroupement->displayAddUser($tabUserNotInGroupement,$groupement->ggid,$user,$message);
            $formDeleteParticipant=$repoGroupement->displayFormDeleteParticipant($tabUser,$user,$message);
            $formDelegate=$repoGroupement->displayFormForDelegation($tabUser,$user,$message);
            $formDelete=$repoGroupement->displayFormDeleteGroup($groupement->ggid,$message);
        }
    }
  

    if(isset($_POST['creerGroupement'])){
        if((isset($_POST['nomGroupement'])) && (isset($_POST['participantAdd']))){
            if((!empty(trim($_POST['nomGroupement'])))&&(is_numeric($_POST['nomGroupement']))){
            if( $repoGroupement->storeGroupement(secureString($_POST['nomGroupement']),$user->uid,$groupe[0],$message)){
                    $groupement=$repoGroupement->getGroupementOfGestionnaire($user->uid,$groupe[0],$message);
                    if($repoGroupement->addUserIntoGroupement($user->uid,$groupement->ggid,$message)&&$repoGroupement->addUserIntoGroupement($_POST['participantAdd'],$groupement->ggid,$message)){
                        $message = "Groupement ".$_POST['nomGroupement']." cree";
                    }
            }
            }else{$message = "un champ du formulaire est vide";}
        
        }else{$message = "un champ du formulaire est vide";}
    }

    if(isset($_POST['AddParticipantGroupement'])){
        if((isset($_POST['userAdd'])) &&(is_numeric($_POST['userAdd']))){
           ($repoGroupement->addUserIntoGroupement($_POST['userAdd'],$groupement->ggid,$message))?$message="Participant(e) ajoute(e)":"";
           header('Refresh:0; url=groupementView.php?groupe='. $groupe[0].'&message='.$message);
        }
    }


    if(isset($_POST['changeGroupement'])){
        if((isset($_POST['userDelegate']))&&(is_numeric($_POST['userDelegate']))){
                $participant=$repoUser->getUserById($_POST['userDelegate'],$message);
               $message='<form  method="post" enctype="multipart/form-data">
               <label>Êtes-vous sur que : '.$participant->prenom.' devienne le gestionnaire du groupement ? </label>
               <input type="hidden" name="participantConfirme" value="'.$participant->uid.'">
               <input type="submit" name="changeGroupementConfirmation" value="Confirmer"/>
               <input type="submit" name="annuler" value="Annuler"/>
               </form>';
         }else{
            $message="Champ non sélectionné pour le changement";
         }
    }

    if(isset($_POST['changeGroupementConfirmation'])){
        if((isset($_POST['participantConfirme']))){
            if((is_numeric($_POST['participantConfirme']))){
            $repoGroupement->changeGestionnaireGroupement($gestionnaire->ggid,$_POST['participantConfirme'],$message)?$message="Gestionnaire Change":'';
            header('Refresh:0; url=groupementView.php?groupe='. $groupe[0].'&message='.$message);
            }
        }
    }

    


    if(isset($_POST['suppParticipantGroupement'])){
        if((isset($_POST['userSupp']))){
            if((is_numeric($_POST['userSupp']))){
                $participant=$repoUser->getUserById($_POST['userSupp'],$message);
               $message='<form  method="post" enctype="multipart/form-data">
               <label>Êtes-vous sur que : '.$participant->prenom.' devienne le gestionnaire du groupement ? </label>
               <input type="hidden" name="participantSuppConfirme" value="'.$participant->uid.'">
               <input type="submit" name="suppParticipantConf" value="Confirmer"/>
               <input type="submit" name="annuler" value="Annuler"/>
               </form>';
            }
         }else{
            $message="Champ non sélectionné pour la suppression d'un participant";
         }
    }

    if(isset($_POST['suppParticipantConf'])){
        if((isset($_POST['participantSuppConfirme']))&&(is_numeric($_POST['participantSuppConfirme']))){
                $repoGroupement->deleteParticipantGroupement($gestionnaire->ggid,$_POST['participantSuppConfirme'],$message)? $message="Participant supprimé avec succès":'';
                header('Refresh:0; url=groupementView.php?groupe='. $groupe[0].'&message='.$message);
         }else{
            $message="Champ non sélectionné pour la suppression";
         }
    }

    if(isset($_POST['suppGroupement'])){
        if(isset($_POST['groupementSupp']) && is_numeric($_POST['groupementSupp'])){
            if(($repoGroupe->isAGroupeIsSolde($groupe[0],$message) && $repoGroupe->verifyAllVersementConfirme($goupe[0],$message))||$repoGroupement->noDepenseInGroupement($_POST['groupementSupp'],$message)){
                echo "oui on peut supprimer";
              $message='<form  method="post" enctype="multipart/form-data">
               <label>Êtes-vous sur de supprimer votre groupement ? </label>
               <input type="hidden" name="groupementSuppConfirme" value="'.$_POST['groupementSupp'].'">
               <input type="submit" name="suppGroupementConf" value="Confirmer"/>
               <input type="submit" name="annuler" value="Annuler"/>
               </form>';
            }else{
                $message = "Le groupement ne peut pas être supprimer car il est soit non soldé et contient des versements non confirmer ou des dépenses sont liée au groupement";
            }
         }else{
            $message="Erreur récupération du groupement";
        }
    }

    if(isset($_POST['suppGroupementConf'])){
        if(isset($_POST['groupementSuppConfirme']) && is_numeric($_POST['groupementSuppConfirme'])){
            $repoGroupement->deleteGroupement( $gestionnaire->ggid,$message);
            header("location:".'groupeView.php?groupe='.$_GET['groupe']);
        }else{
            $message="Erreur récupération du groupement";
        }
    }

}
}else{
    header('location:connexion.php');
}

?>
<main>
        <section class="Profil">
        <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
        <?php echo '<h1>Créer/Éditer - <a href="groupeView.php?groupe='.$groupe[0].'">'.$groupe[1].'</a></h1>';?>
        <?php 
        echo $groupementComposition;
        echo $formCreate;
        echo $formAddUser;
        echo $formDelegate;
        echo $formDeleteParticipant;
        echo $formDelete;
        echo $groupementInfo;
        ?>
        </section>
    </main>
<?php
include ("inc/footer/footer.inc.php");
?>