<?php
namespace Tag;

require_once 'inc/db/db_link.inc.php';
require_once 'inc/db/db_user.inc.php';
require_once 'php/depense.php';
require_once 'php/versement.php';

use DB\DBLink;
use PDO;
use PDOException;
use User\User;
use User\UserRepository;
use Depense\Depense;
use Depense\DepenseRepository;
use Versement\Versement;
use Versement\VersementRepository;

class Tag{

    public $tid;
    public $tag;
    public $gid;
   

    public function __construct($tid,$tag,$gid) {
        $this->tid = $tid;
        $this->tag = $tag;
        $this->gid = $gid;
    }

    public function __get($prop){
        return $this->$prop;
}


}

class TagRepository{
    const TABLE_NAME='tag';


    public function storeTagGroupe($gid,$tag,&$message){
        $result = false;
        $bdd = null;
        try {

            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME . " VALUES (0,:tag,:gid)");
            $stmt->bindValue(':tag', $tag);
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){ 
                return $result = true;
                $message="Tag crée";
            }else{
                $message="Tag non crée";
                return $result=false;
                }

        } catch (PDOException $e) {
           echo $message .= $e->getMessage() . '<br>';
        return $result;
    }
    DBLink::disconnect($con);
    return $result;
    }

    public function tagExistinGroup($gid,$tag,&$message){
        $result=false;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT t.* FROM tag t WHERE  t.gid=:gid and t.tag like ':tag' ORDER BY t.gid");
            $stmt->bindValue(':gid', $gid);
            $stmt->bindValue(':tag', $tag);
            if($stmt->execute()){
                if (count($result) >= 1) {
                    DBLink::disconnect($bdd);
                    return true;
                } else {
                    $message="Votre email n'existe pas";
                    DBLink::disconnect($bdd);
                    return false;
                }
                return $result;
                DBLink::disconnect($bdd);
            } else {
                $message .= "Une erreur systeme est survenue";
                return $result;
            }
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
        DBLink::disconnect($bdd);
    }


    public function addTagToDepense($did,$tid,&$message){
        $bdd  = DBLink::connect2db(MYDB, $message);
        if ($bdd) {
            $stmt = $bdd->prepare("INSERT INTO caracteriser(did,tid) VALUES (:did,:tid);");
            $stmt->bindValue(':did', $did);
            $stmt->bindValue(':tid', $tid);
            if ($stmt->execute()) {
                DBLink::disconnect($bdd);
                return true;
            } else {
                DBLink::disconnect($bdd);
                return false;
            }
        } else {
            $message = "Impossible d'établir une connexion avec la base de données.";
            DBLink::disconnect($bdd);
            return false;
        }
    }


    public function getAllTagsOfGroupe($gid,&$message){
        $result=false;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT t.* FROM tag t WHERE  t.gid=:gid ORDER BY t.gid');
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                     $result= $stmt->fetchALL(PDO::FETCH_OBJ);
                return $result;
                DBLink::disconnect($bdd);
            } else {
                $message .= "Une erreur systeme est survenue";
                return $result;
            }
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
        return $result;
        DBLink::disconnect($bdd);
    }


    public function getAllTagsOfDepense($did,&$message){
        $result=false;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT t.* FROM tag t join caracteriser c on t.tid = c.tid WHERE  c.did=:did ORDER BY t.tid');
            $stmt->bindValue(':did', $did);
            if($stmt->execute()){
                     $result= $stmt->fetchALL(PDO::FETCH_OBJ);
                return $result;
                DBLink::disconnect($bdd);
            } else {
                $message .= "Une erreur systeme est survenue";
                return $result;
            }
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
        DBLink::disconnect($bdd);
    }

    public function deleteAllTagGid($gid, &$message){
    $result=null;
    $bdd= null;
    try {
        $bdd = DBLink::connect2db(MYDB, $message);

        $requete = $bdd->prepare("DELETE FROM " . self::TABLE_NAME . " WHERE gid=:gid;");
        $requete->bindValue(":gid", $gid);
       if( $requete->execute()){
           $message="tout les tags supprimé avec succès";
           $result=true;}
         else{
           $message="Une erreur est survenue";
           $result=false;
         }
    } catch (PDOException $e) {
        return $result;
        $message .= $e->getMessage() . '<br>';
        DBLink::disconnect($bdd);
    }
    return $result;
    DBLink::disconnect($bdd);
    }

    public function deleteAllTagDid($did, &$message){
        $result=null;
        $bdd= null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
    
            $requete = $bdd->prepare("DELETE FROM caracteriser WHERE did=:did;");
            $requete->bindValue(":did", $did);
           if( $requete->execute()){
               $message="tout les tags supprimé avec succès";
               $result=true;}
             else{
               $message="Une erreur est survenue";
               $result=false;
             }
        } catch (PDOException $e) {
            return $result;
            $message .= $e->getMessage() . '<br>';
            DBLink::disconnect($bdd);
        }
        return $result;
        DBLink::disconnect($bdd);
        }

}
?>