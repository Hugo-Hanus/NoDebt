<?php
include("inc/header/header.inc.php");
require_once("php/dataSecurity.php");
use function dataSecurity\secureString;
$message="";
$date = date('Y-m-d H:m');
$admit=false;
$tabIdAdmit=$repoUser->userAccesToGroupe($_GET['groupe'],$message);
if($tabIdAdmit>0){
   in_array($user->uid,$tabIdAdmit)?$admit=true:$admit=false;
}
if(isset($_SESSION['user'])&&(!empty($_SESSION))&& $admit ){
if(isset($_GET['groupe'])&& is_numeric($_GET['groupe'])){
    $idGroupe=$_GET['groupe'];
    $groupeInfo=$repoGroupe->getInfoGroupeID($idGroupe,$message);
    $tabUser=$repoDepense->displayAllUser($idGroupe);
    $displaySolderForm=$repoGroupe->displayFormSolder($idGroupe,$message);
    $solde="";
    $resultResearch="";
    $searchForm='<form class="Recherche"  method="post">
    <label class="Recherche" for="recherche">Dépense recherche : </label>
    <input  class="Recherche" id="recherche" name="recherche" type="textarea" placeholder="Que cherchez- vous ?"/>
    <input  class="BarreRecherche" type="submit" name="rechercheButton" value="Recherche"/>
    </form>
    <form method="post">
        <input type="submit" name="rechercheAvance" value="Recherche Avancée"/>
    </form>';
    if($repoGroupe->isAGroupeIsSolde($idGroupe,$message)){
        $solde= $repoGroupe->displayVersmentOfAGroup($idGroupe,$message);
    }

    if(isset($_COOKIE['advanceSearch'])){
        $arrayAdvanceSearch=json_decode($_COOKIE['advanceSearch'],true);
        $resultDepenseSearch= $repoDepense->advanceResearch($idGroupe,secureString($arrayAdvanceSearch[0]),$arrayAdvanceSearch[1],$arrayAdvanceSearch[2],$arrayAdvanceSearch[3],$arrayAdvanceSearch[4],$arrayAdvanceSearch[5],$message);
       if($resultDepenseSearch==null){$resultResearch="pas de dépense possèdant ce que vous recherchez";}else{$resultResearch=$repoDepense->displayResultSearch($idGroupe,$resultDepenseSearch,$message);} 
    }
    if((isset($_POST['solderGroupe'])) && (isset($_POST['groupeid']))){
        if(is_numeric($_POST['groupeid'])){
            $message= '<form  method="post" enctype="multipart/form-data">
                  <label>Êtes-vous sur de solder le groupe : '.$groupeInfo[1].' ? </label>
                  <input type="hidden" name="groupeConfirme" value="'.$groupeInfo[0].'">
                  <input type="submit" name="confirmerSolde" value="Confirmer"/>
                  <input type="submit" name="annuler" value="Annuler"/>
                  </form>';
                }else{
                    $message="Erreur Encodage Groupe";
                }
    }
    if(isset($_POST['groupeConfirme']) && isset($_POST['confirmerSolde'])){
        if(is_numeric($_POST['groupeConfirme'])){
            $repoGroupe->solderGroupe($idGroupe,$message); 
            $repoGroupe->solderGroupeDB($idGroupe,$message); 
            $solde= $repoGroupe->displayVersmentOfAGroup($idGroupe,$message);
            $displaySolderForm=$repoGroupe->displayFormSolder($idGroupe,$message);
        }else{
            $message="Erreur Encodage Groupe";
        }
    }
    if((isset($_POST['annulerSolderGroupe'])) && (isset($_POST['groupeid']))){
        if(is_numeric($_POST['groupeid'])){
            $message= '<form  method="post" enctype="multipart/form-data">
                  <label>Êtes-vous sur d\'annuler le solde du le groupe : '.$groupeInfo[1].' ? </label>
                  <input type="hidden" name="groupeConfirme" value="'.$groupeInfo[0].'">
                  <input type="submit" name="annulerSolder" value="Confirmer"/>
                  <input type="submit" name="annuler" value="Annuler"/>
                  </form>';
                }else{
                    $message="Erreur Encodage Groupe";
                }

    }

    if(isset($_POST['groupeConfirme']) && isset($_POST['annulerSolder'])){
        if(is_numeric($_POST['groupeConfirme'])){
           $message= $repoGroupe->cancelGroupe($idGroupe,$message);
           $solde="";
           $displaySolderForm=$repoGroupe->displayFormSolder($idGroupe,$message);
        }else{
            $message="Erreur Encodage Groupe";
        }
    }

    if(isset($_POST['rechercheButton'])&& isset($_POST['recherche']) && !empty(trim($_POST['recherche']))){
        $resultDepenseSearch=$repoDepense->researchDepense($idGroupe,$_POST['recherche'],$message);
        $resultResearch=$repoDepense->displayResultSearch($idGroupe,$resultDepenseSearch,$message);
    }

    if(isset($_POST['supprimer']) && isset($_POST['depenseID']) &&!empty($_POST['depenseID'])){
        if(is_numeric($_POST['depenseID'])){
           $depenseSupp= $repoDepense->getDepenseById($_POST['depenseID'],$message);
            $message= '<form  method="post" enctype="multipart/form-data">
                  <label>Êtes-vous sur de supprimer la dépense : '.$depenseSupp->libelle.' ? </label>
                  <input type="hidden" name="depenseConfirme" value="'.$depenseSupp->did.'">
                  <input type="submit" name="supprimerDepConfirm" value="Confirmer"/>
                  <input type="submit" name="annuler" value="Annuler"/>
                  </form>';
                }else{
                    $message="Erreur Encodage Groupe";
                }
    }

    if(isset($_POST['supprimerDepConfirm'])&& isset($_POST['depenseConfirme']) &&!empty($_POST['depenseConfirme'])){
        $repoDepense->deleteAllFactureAndCaracteriserOfDepense($_POST['depenseConfirme'],$message);
    }
    if(isset($_POST['rechercheAvance'])){
       $searchForm=$repoDepense->displayAdvanceSearchForm($idGroupe,$date,$message);
    }
    if(isset($_POST['rechercheAd'])){
        (sizeof($_POST)>6)?$tag= $_POST['tags']:$tag=array();
       $resultDepenseSearch=  $repoDepense->advanceResearch($idGroupe,$_POST['libelleAd'],$_POST['montantMin'],$_POST['montantMax'],$_POST['dateMin'],$_POST['dateMax'],$tag,$message);
       if($resultDepenseSearch==null){$resultResearch="pas de dépense possèdant ce que vous recherchez";}else{$resultResearch=$repoDepense->displayResultSearch($idGroupe,$resultDepenseSearch,$message);}
    }
    if(isset($_POST['annulerRechercheAd'])){
        $searchForm='<form class="Recherche"  method="post">
        <label class="Recherche" for="recherche">Dépense recherche : </label>
        <input  class="Recherche" id="recherche" name="recherche" type="textarea" placeholder="Que cherchez- vous ?"/>
        <input  class="BarreRecherche" type="submit" name="rechercheButton" value="Recherche"/>
        </form>
        <form method="post">
            <input type="submit" name="rechercheAvance" value="Recherche Avancée"/>
        </form>';
    }
    if(isset($_POST['supprimerGroupe'])){
        if(isset($_POST['groupeid']) && is_numeric($_POST['groupeid'])){
            $message= '<form  method="post" enctype="multipart/form-data">
                  <label>Êtes-vous sur de supprimer le groupe : '.$groupeInfo[1].' ? </label>
                  <input type="hidden" name="groupeConfirme" value="'.$_POST['groupeid'].'">
                  <input type="submit" name="supprimerGroupeConfirm" value="Confirmer"/>
                  <input type="submit" name="annuler" value="Annuler"/>
                  </form>';
        }
    }
    if(isset($_POST['supprimerGroupeConfirm'])){
        if(isset($_POST['groupeConfirme']) && is_numeric($_POST['groupeConfirme'])){
            ($repoGroupe->deleteGroupeByID($_POST['groupeConfirme'],$message))? header('location:index.php?message='.$message):"";
        }
    }

}else{
     $message="Erreur accès du groupe";
     echo'<style>section { display:none;}</style>';
}
}else{
    header('location:connexion.php');
}
?>
<main>
    <section>
    <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
        <h1>Groupe : <?php echo $groupeInfo[1];?></h1>
    </section>
    <section>
    <article class="Table">
           <?php echo $tabUser;?>
     </article>
     <article>
           <?php echo $solde;?>
     </article>
    </section>
    <section>
         <?php echo $displaySolderForm;?>
         
    </section>
    <section class="Main">
    <?php
      echo '<a class="ButtonAction" href="editionDepense.php?groupe='.$idGroupe.'&editionDepense=1">Ajouter un dépense</a>';
            echo '<a class="ButtonAction" href="groupementView.php?groupe='.$idGroupe.'">Groupement</a>';
        echo '<a class="ButtonAction" href="editionVersements.php?groupe='.$idGroupe.'">Versements</a>';
        echo '<a class="ButtonAction" href="invitationParticipant.php?groupe='.$idGroupe.'">Inviter un utilisateur</a>';
        ?>
    </section>
    <section >

        <?php echo $searchForm; ?>
       
    </section>
    <section >
        <article class="Table">
           <?php echo $resultResearch;?>
        </article>
    </section>
</main>
<?php
include("inc/footer/footer.inc.php");
?>