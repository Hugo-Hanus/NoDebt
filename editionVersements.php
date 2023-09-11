<?php 
require_once("php/versement.php");
use Versement\Versement;
use Versement\VersementRepository;
include("inc/header/header.inc.php");
$admit=false;
$tabIdAdmit=$repoUser->userAccesToGroupe($_GET['groupe'],$message);
if($tabIdAdmit>0){
   in_array($user->uid,$tabIdAdmit)?$admit=true:$admit=false;
}
if(isset($_SESSION['user'])&&(!empty($_SESSION))&&$admit){
$form="";
if(isset($_GET['groupe']) && is_numeric($_GET['groupe'])){
    $groupe=$repoGroupe->getInfoGroupeID($_GET['groupe'],$message);
    $repoVersement=new VersementRepository();

    if(isset($_POST['versementAccepter'])){
        if(isset($_POST['versementId'])){
            $tabId=explode(',',$_POST['versementId']);
            if(is_numeric($tabId[0]) && is_numeric($tabId[1]) && is_numeric($tabId[2])){
            $repoVersement->confirmeVersement($tabId[0],$tabId[1],$tabId[2],$message);
            }
        }
    }
    if(isset($_POST['versementRefuser'])&& isset($_POST['versementId'])){
        $message= '<form  method="post" enctype="multipart/form-data">
        <label>Êtes-vous sur de refuser le versement ? </label>
        <input type="hidden" name="versementId" value"'.$_POST['versementId'].'">
        <input type="submit" name="refuserConfirmation" value="Confirmer"/>
        <input class="Annuler"  type="submit" name="annuler" value="Annuler"/>
        ';
    }
    if(isset($_POST['refuserConfirmation'])&& isset($_POST['versementId'])){
        $tabId=explode(',',$_POST['versementId']);
        if(is_numeric($tabId[0]) && is_numeric($tabId[1]) && is_numeric($tabId[2])){
        $repoVersement->refuseVersement($tabId[0],$tabId[1],$tabId[2],$message);
        }

    }
    $form=$repoVersement->displayVersmentToConfirm($_GET['groupe'],$user->uid,$message);
}else{
    
}
}else{header('location:connexion.php');}
?>
<main>
    <section>
    <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
        <?php echo '<h1>Groupe : <a href="groupeView.php?groupe='.$groupe[0].'">'.$groupe[1].'</a></h1>';?>
    </section>
    <section>
        <h2>Versements :</h2>
        <article class="Table">
            <?php
                echo $form
            ?>
        </article>
    </section>
</main>
<?php
include("inc/footer/footer.inc.php");
?>
