<?php
use Depense\Depense;
use Depense\DepenseRepository;
require_once('php/dataSecurity.php');
use function dataSecurity\secureString;
include ("inc/header/header.inc.php");
$date = date('Y-m-d H:m');
$admit=false;
$tabIdAdmit=$repoUser->userAccesToGroupe($_GET['groupe'],$message);
if($tabIdAdmit>0){
   in_array($user->uid,$tabIdAdmit)?$admit=true:$admit=false;
}
if(isset($_SESSION['user'])&&(!empty($_SESSION))&&$admit){
if(isset($_GET['groupe'])&& is_numeric($_GET['groupe'])){
    $groupeInfo=$repoGroupe->getInfoGroupeID($_GET['groupe'],$message);
    $formCreate=$repoDepense->dislayFormCreate($groupeInfo[0],$user->uid,$date,$message);
   $message="";
   if(isset($_GET['editionDepense'])&& is_numeric($_GET['editionDepense'])){
    $modifForm="";
    $depenseInfo="";
    
    echo '<style>.Main { display:none;}</style>';
   }else{
    $formCreate="";
   }
    if(isset($_POST['ajouter'])){
                if(((!empty($_POST['date'])) && (!empty(floatval($_POST['montant']))) && (!empty(trim($_POST['libelle']))) && (!empty($_POST['participants'])) && ((empty($_POST['tags']))||(!empty($_POST['tags']))))){
              $repoDepense->storeDepense(new Depense(0,secureString($_POST['date']),secureString($_POST['montant']),secureString($_POST['libelle']),$groupeInfo[0],secureString($_POST['participants'])),$message);            
                    $lastDepense=$repoDepense->getLastDepenseByUid(secureString($_POST['participants']),$groupeInfo[0],$message);
                    $repoDepense->updateTagOfDepense($lastDepense->did,$groupeInfo[0],$_POST['tags'],$message);
            }else{
            $message="Des champs du formulaire sont vides";
        }
    }
    if(isset($_GET['depense']) && is_numeric($_GET['depense'])){
        $depenseInfo=$repoDepense->getDepenseById($_GET['depense'],$message);
       $modifForm= $repoDepense->displayFormModification($_GET['groupe'],$_GET['depense'],$user->uid,$message);
       if(isset($_POST['modifierDepense'])){
            if((!empty(trim($_POST['libelleM']))) && (!empty(floatval($_POST['montantM'])))&& (!empty($_POST['dateM'])) && (!empty($_POST['participants'])) && ((empty($_POST['tagsM']))||(!empty($_POST['tagsM'])))){
            $repoDepense->updateDepense(new Depense($_GET['depense'],$_POST['dateM'],$_POST['montantM'],secureString($_POST['libelleM']),$groupeInfo,$_POST['participants']),$message);
            $repoDepense->updateTagOfDepense($_GET['depense'],$_GET['groupe'],$_POST['tagsM'],$message);
            }
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
        <?php empty($depenseInfo )?$libelle="":$libelle=$depenseInfo->libelle;
        echo '<h1>Gérer les dépenses de <a href="groupeView.php?groupe='.$groupeInfo[0].'">'.$groupeInfo[1].'</a>: - '.$libelle.'</h1>'?>
            <?php echo $formCreate;?>
    </section>
    <section>
            <?php echo $modifForm;?>
    </section>
    <section class="Main">
            <?php echo '<article><h2>Édition Scan</h2><a class="ButtonAction" href="editionScan.php?depense='.$depenseInfo->did.'">Ajouter un scan</a></article>';?>
    </section>
</main>
<?php
include ("inc/footer/footer.inc.php");
?>
