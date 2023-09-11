<?php
namespace Facture;

require_once 'inc/db/db_link.inc.php';
require_once 'inc/db/db_user.inc.php';
require_once 'php/depense.php';
require_once 'php/dataSecurity.php';
use function dataSecurity\secureString;

use DB\DBLink;
use PDO;
use PDOException;
use Depense\Depense;
use Depense\DepenseRepository;
use Versement\Versement;
use Versement\VersementRepository;

class Facture{

    public $fid;
    public $scan;
    public $did;
   

    public function __construct($fid,$scan,$did) {
        $this->fid = $fid;
        $this->scan = $scan;
        $this->did = $did;
    }

    public function __get($prop){
        return $this->$prop;
}


}

class FactureRepository{
    const TABLE_NAME='facture';
    const  TARGETDIR="uploads/";
    
    
    public function storeFacture($newFacture,&$message){
        $result = false;
        $bdd = null;

        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('INSERT INTO ' . self::TABLE_NAME . ' VALUES (:fid,:scan,:did)');
            $stmt->bindValue(':fid', $newFacture->fid);
            $stmt->bindValue(':scan', $newFacture->scan);
            $stmt->bindValue(':did', $newFacture->did);
            if ($stmt->execute()) {
                $message = 'Le scan a été ajouté';
                $result = true;
            } else {
                $message .= 'Une erreur système est survenue.<br>';
            }
            $stmt = null;
        } catch (PDOException $e) {
            $message = "Erreur connexion à la base de donnée";
        }
        DBLink::disconnect($con);
        return $result;
    }
    
    
    
    public function getFactureByID($fid,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT f.* FROM '.self::TABLE_NAME.' f WHERE  f.fid=:fid');
            $stmt->bindValue(':fid', $fid);
            if($stmt->execute()){
                     $result= $stmt->fetch(PDO::FETCH_OBJ);
                return $result;
                DBLink::disconnect($bdd);
            } else {
                $message .= "Une erreur systeme est survenue";
                DBLink::disconnect($bdd);
                return $result;
            }
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
    }
    
    
    public function getAllFactureOfDepense($did,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT f.* FROM '.self::TABLE_NAME.' f WHERE  f.did=:did');
            $stmt->bindValue(':did', $did);
            if($stmt->execute()){
                     $result= $stmt->fetchALL(PDO::FETCH_OBJ);
                return $result;
                DBLink::disconnect($bdd);
            } else {
                $message .= "Une erreur systeme est survenue";
                DBLink::disconnect($bdd);
                return $result;
            }
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
    }


    public function deleteFacture($fid,&$message){
        $result=null;
        $bdd= null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
    
            $requete = $bdd->prepare("DELETE FROM " . self::TABLE_NAME . " WHERE fid=:fid;");
            $requete->bindValue(":fid", $fid);
           if( $requete->execute()){
               $message="Le scan a été supprimé";
               $result=true;}
             else{
               $message="Une erreur est survenue";
               $result=false;
             }
        } catch (PDOException $e) {
            $message = "Erreur connexion à la base de donnée";
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    
    public function updateContent($did,&$message){
        $allFacture= FactureRepository::getAllFactureOfDepense($did,$message);
        if(sizeof($allFacture)>0){
            return FactureRepository::displayFactureForm($did,$allFacture,$message);
        }else{
            return "Vous n'avez pas de scan pour cette dépense";
        }
    }

    public function displayFactureForm($did,$tabFacture,&$message){
        $dir="uploads/".$did.'/';
        $display='';
        if(sizeof($tabFacture)>0){
            $display='<ul>';
            foreach($tabFacture as $facture){
                $display.=' <li  class="TableDate">
                <img src="'.$facture->scan.'">
                <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="factureId" value="'.$facture->fid.'">
                <input class="Supprimer" name="supprimerFile" type="submit" value="Supprimer">
                </form></li>';
            }
            return $display.' </ul>';
        }else{
            return "<p>Vous n'avez pas de scan pour cette dépense</p>";
        }

        return $display;

    }
    

    public function checkFile($depense,$file,&$message){
        $imageFileType = strtolower(pathinfo(basename($file["facture"]["name"]),PATHINFO_EXTENSION));
        if ($_FILES["facture"]["size"] > 500000) {
           $message="Votre image dépasse la taille acceptée MAX : 500000";
            return "";
          }
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif") {
            $message= "Votre scan ne possède pas la bonne extension | Accepté : jpg, png, jpeg, gif";
            return "";
        }else{
            $targetDirDepense=self::TARGETDIR.$depense->did;
            $filedir=$targetDirDepense.'/'.secureString(basename($file["facture"]["name"]));
            if(file_exists($targetDirDepense) && is_dir($targetDirDepense)){
                if(!file_exists($filedir)){
                    if(move_uploaded_file($file["facture"]["tmp_name"], $filedir)){
                        $message=secureString(basename($file["facture"]["name"])).' a été télécharger';
                   return $filedir;
                    }else{
                        $message="Erreur lors du téléchargement";
                        return "";
                    }
                 }else{
                    $message="La facture existe déjà";
                    return "";
                 }
            }else{
                mkdir(("uploads/".$depense->did), 555);
                if(!file_exists($filedir)){
                    if(move_uploaded_file($file["facture"]["tmp_name"], $filedir)){
                        $message=secureString(basename($file["facture"]["name"])).' a été télécharger';
                        return $filedir;
                    }else{
                        $message="Erreur lors du téléchargement";
                        return "";
                    }
                }else{
                    $message="La facture existe déjà";
                    return "";
                }
            }
        }
    }


}

