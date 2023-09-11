<?php
require_once 'php/facture.php';
use Facture\Facture;
use Facture\FactureRepository;
include ("inc/header/header.inc.php");
if(isset($_SESSION['user'])&&(!empty($_SESSION))){
if(isset($_GET['depense']) && is_numeric($_GET['depense'])){
    $repoFacture= new FactureRepository();
    $depenseInfo=$repoDepense->getDepenseById($_GET['depense'],$message);
   
    $form=$repoFacture->updateContent($_GET['depense'],$message);
    if(isset($_POST['ajouterScan']) && (!empty($_FILES['facture']))){
        $file=$repoFacture->checkFile($depenseInfo,$_FILES,$message);
        if(!($file==="")){;
        $factureNew=new Facture(0,$file,$depenseInfo->did);
        $repoFacture->storeFacture($factureNew,$message)?"":unlink($factureNew->scan);
        $form=$repoFacture->updateContent($_GET['depense'],$message);
        }
    }
    if(isset($_POST['supprimerFile']) &&  !empty($_POST['factureId']) && is_numeric($_POST['factureId'])){
            $message= '<form  method="post" enctype="multipart/form-data">
                  <label>Êtes-vous sur de supprimer ce scan :</label>
                  <input type="hidden" name="factureIdConfirmation" value="'.$_POST['factureId'].'">
                  <input type="submit" name="confirmerSupp" value="Confirmer"/>
                  <input type="submit" name="annuler" value="Annuler"/>
                  </form>';
                }

    if(isset($_POST['confirmerSupp'])&& !empty($_POST['factureIdConfirmation']) && is_numeric($_POST['factureIdConfirmation'])){
       $facture= $repoFacture->getFactureByID($_POST['factureIdConfirmation'],$message);
        if($repoFacture->deleteFacture($facture->fid,$message)){
            unlink($facture->scan);
            $form=$repoFacture->updateContent($_GET['depense'],$message);
        }
    } 
}   
}else{header('location:connexion.php');}
?>
    <main>
        <section class="Profil">
        <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
            <?php
            echo '<h1>Gérer scan de facture de : '.$depenseInfo->libelle.'</h1>'?>
            <article>
                <h2>Ajout d'un scan</h2>
                <form  method="post" enctype="multipart/form-data">
                        <label for="Facture">Facture *</label>
                        <input type="file" name="facture" accept="image/*, .jpg, .png,.pdf" required>
                        <input type="submit" name="ajouterScan" value="Ajouter"  />
                        <input class="Annuler "type="reset" name="Annuler" value="Annuler"  />
                </form>
            </article>
        </section>
        <section class="Main">
            <article>
            <h2>Suppression d'un scan</h2>
            <?php echo $form;
            ?>
        </article>
        </section>
    </main>
<?php
include ("inc/footer/footer.inc.php");
?>