<?php
namespace Groupe;

require_once 'inc/db/db_link.inc.php';
require_once 'inc/db/db_user.inc.php';
require_once 'php/depense.php';
require_once 'php/versement.php';
require_once 'php/groupement.php';
use DB\DBLink;
use PDO;
use PDOException;
use User\User;
use User\UserRepository;
use Depense\Depense;
use Depense\DepenseRepository;
use Versement\Versement;
use Versement\VersementRepository;
use Groupement\Groupement;
use Groupement\GroupementRepository;

class Groupe{

    public $gid;
    public $nom;
    public $devise;
    public $uid;
    public $estSolder;

    public function __construct($gid,$nom,$devise,$uid,$estSolder ) {
        $this->gid = $gid;
        $this->nom = $nom;
        $this->devise = $devise;
        $this->uid = $uid;
        $this->estSolder=$estSolder;
    }

    public function __get($prop){
        return $this->$prop;
    }


}

class GroupeRepository{
    const TABLE_NAME='groupe';


    public function storeGroupe($newGroupe, &$message)
    {
        $result = false;
        $bdd = null;
        try {

            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME . " VALUES (0,:nom,:devise,:uid,0)");
            $stmt->bindValue(':nom', $newGroupe->nom);
            $stmt->bindValue(':devise', $newGroupe->devise);
            $stmt->bindValue(':uid', $newGroupe->uid);
            if($stmt->execute()){ 
                return $result = true;
                $message="Groupe crée";
            }else{
                $message="Groupe non crée";
                return $result=false;
                }

        } catch (PDOException $e) {
            $message = "Erreur connexion à la base de donnée";

        return $result;
    }
    
    DBLink::disconnect($con);
    return $result;

}

    public function userInvInGroupe($gid,$uid){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT p.uid FROM participer p JOIN groupe g on g.gid=p.gid WHERE  (p.gid=:gid AND p.uid=:id)');
        $stmt->bindValue(':id', $uid);
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetchAll(PDO::FETCH_NUM);
        }
        if (count($result) >= 1) {
            DBLink::disconnect($bdd);
            return true;
        } else {
            DBLink::disconnect($bdd);
            return false;
        }
       
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    return $result;
    DBLink::disconnect($bdd);
    }


public function inviteUsertoGroupe($gid,$uid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('INSERT INTO participer VALUES (:uid,:gid,0)');
        $stmt->bindValue(':uid', $uid);
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $message='Utilisateur ajouté au groupe en attente d\'acceptation !';
            $result=true;
        } else {
            $result=false;
            $message .= "Une erreur systeme est survenue";
        }
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    return $result;
    DBLink::disconnect($bdd);
}

public function addUsertoParticipate($gid,$uid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('INSERT INTO participer VALUES (:uid,:gid,1)');
        $stmt->bindValue(':uid', $uid);
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=true;
            DBLink::disconnect($bdd);
        } else {
            $message .= "Une erreur systeme est survenue";
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

public function addUsertoGroupe($gid,$uid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('UPDATE participer SET estConfirmer=1 WHERE uid=:uid AND gid=:gid');
        $stmt->bindValue(':uid', $uid);
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=true;
            DBLink::disconnect($bdd);
        } else {
            $message .= "Une erreur systeme est survenue";
        }
        $stmt = null;
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    return $result;
}


public function updateGroupe($gid,$nom,$devise,&$message){
    $message = '';
    $bdd  = DBLink::connect2db(MYDB, $message);
    if ($bdd) {
        $stmt = $bdd->prepare("UPDATE ".self::TABLE_NAME. " SET nom=:nom,devise=:devise WHERE gid=:gid");
        $stmt->bindValue(':nom', $nom);
        $stmt->bindValue(':devise', $devise);
        $stmt->bindValue(':gid', $gid);
       
        if ($stmt->execute()) {
            DBLink::disconnect($bdd);
            return true;
        } else {
            $message = "Erreur lors de la Requête";
            DBLink::disconnect($bdd);
            return false;
        }
    } else {
        $message = "Impossible d'établir une connexion avec la base de données.";
        DBLink::disconnect($bdd);
        return false;
    }
}

public function inviteRefuseUsertoGroupe($gid,$uid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('DELETE FROM participer WHERE uid=:uid AND gid=:gid AND estConfirmer=0');
        $stmt->bindValue(':uid', $uid);
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=true;
            DBLink::disconnect($bdd);
        } else {
            $message .= "Une erreur systeme est survenue";
        }
        $stmt = null;
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    return $result;
}


    public function deleteAllDepense($gid,&$message){
        
            $result=null;
            $bdd= null;
            if(unlink('uploads/'.$did)){
            try {
                $bdd = DBLink::connect2db(MYDB, $message);
        
                $requete = $bdd->prepare("DELETE FROM facture where did=:did;DELETE FROM caracteriser where did=:did");
                $requete->bindValue(":did", $did);
                
                if( $requete->execute()){
                   $message="tout les factures supprimé avec succès";
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
            }
            return $result;
            DBLink::disconnect($bdd);
            
    }


public function deleteGroupeByID($gid, &$message)
{
    $result=null;
    $bdd= null;
    $repoDepense=new DepenseRepository();
    $repoGroupement=new GroupementRepository();

    $tabDepense=$repoDepense->getAllDepenseOfAGroupe($gid,$message);
    $tabGroupement=$repoGroupement->getGroupementByGroupe($gid);
    if($tabDepense>0){
    foreach($tabDepense as $depense){
        ($repoDepense->deleteAllFactureAndCaracteriserOfDepense($depense->did,$message))?"":$result= false;
    }
    }
    if($tabGroupement>0){
        foreach($tabGroupement as $groupement){
            ($repoGroupement->deleteGroupement($groupement->ggid,$message))?"":$result= false;
        } 
    }
    if($result===false){
        $message="Erreur lors de la suppression";
        return false;
    }
    try {
        $bdd = DBLink::connect2db(MYDB, $message);
        $requete = $bdd->prepare("DELETE FROM tag WHERE gid=:gid;DELETE FROM participer WHERE gid=:gid;DELETE FROM versement WHERE gid=:gid;DELETE FROM " . self::TABLE_NAME . " WHERE gid=:gid;");
        $requete->bindValue(":gid", $gid);
       if( $requete->execute()){
           $message="Groupe supprime avec succes";
           $result=true;
        }
         else{
           $message="Une erreur est survenue";
           $result=false;
         }
    } catch (PDOException $e) {
        $message .= $e->getMessage() . '<br>';
    }
    DBLink::disconnect($bdd);
    return $result;
}


    public function getGroupeIdbyCreatorAndLibelle($uid,$libelle,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT g.gid FROM ".self::TABLE_NAME." g where g.uid=:uid AND g.nom LIKE :libelle ORDER BY g.gid DESC");
            $stmt->bindValue(':uid', $uid);
            $stmt->bindValue(':libelle', $libelle);
            if($stmt->execute()){
                $result=$stmt->fetch();
            } else {
                $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
            }
            $stmt = null;
            return $result;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
        return $result;

    }

public function getInvitGroupeId($uid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT p.gid FROM participer p where p.uid=:uid AND p.estConfirmer=0;");
        $stmt->bindValue(':uid', $uid);
        if($stmt->execute()){
            $result=$stmt->fetchALL(PDO::FETCH_COLUMN);
        } else {
            $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
        }
        $stmt = null;
        return $result;
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    return $result;

}


public function getInfoGroupeID($gid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT g.gid,g.nom,g.devise,CONCAT(u.prenom,' ',u.nom),SUM(d.montant) FROM groupe g JOIN depense d on d.gid = g.gid JOIN utilisateur u on u.uid = g.uid  WHERE d.gid=:gid;");
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetch(PDO::FETCH_NUM);
        } else {
            $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
        }
        $stmt = null;
        return $result;
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    return $result;
   }
    
public function getAllGroupCreateByUserID($uid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT g.* FROM groupe g JOIN utilisateur u on u.uid = g.uid WHERE  g.uid=:uid ORDER BY gid');
        $stmt->bindValue(':uid', $uid);
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
}


public function getAllGroupIDByUserID($uid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT g.gid FROM groupe g JOIN utilisateur u on u.uid = g.uid WHERE  g.uid=:uid UNION SELECT g.gid FROM groupe g JOIN participer p on g.gid = p.gid WHERE (p.uid=:uid AND p.estConfirmer=1)ORDER BY gid');
        $stmt->bindValue(':uid', $uid);
        if($stmt->execute()){
            $result=$stmt->fetchALL(PDO::FETCH_NUM);
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
}

public function getAllUserIDByGroupeID($gid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT u.uid from utilisateur u join groupe g on u.uid = g.uid WHERE g.gid=:gid UNION SELECT p.uid FROM participer p JOIN utilisateur u on u.uid = p.uid  WHERE p.gid=:gid AND p.estConfirmer=1;');
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){

            $result=$stmt->fetchALL(PDO::FETCH_COLUMN);
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
}

public function getAllUserByGroupeID($gid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT u.* FROM participer p JOIN utilisateur u on u.uid = p.uid  WHERE p.gid=:gid AND p.estConfirmer=1;');
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetchALL(PDO::FETCH_OBJ);
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
}

public function getAllUserMailByGroupeID($gid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT u.courriel from utilisateur u join groupe g on u.uid = g.uid WHERE g.gid=:gid UNION SELECT u.courriel FROM participer p JOIN utilisateur u on u.uid = p.uid  WHERE p.gid=:gid AND p.estConfirmer=1;');
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetchALL(PDO::FETCH_COLUMN);
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
}

    public function isAGroupeIsSolde($gid,$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT g.gid from groupe g WHERE g.gid=:gid AND g.estSolder=1;');
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                $result=$stmt->fetchAll(PDO::FETCH_NUM);
                if (count($result) >= 1) {
                    DBLink::disconnect($bdd);
                    return true;
                } else {
                    DBLink::disconnect($bdd);
                    return false;
                }
            }else{
                $message="erreur lors de l'exécution";
            }
            
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
    }
    public function solderGroupeDB($gid,$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('UPDATE groupe SET estSolder=1 WHERE  gid=:gid');
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                $result=true;
                DBLink::disconnect($bdd);
            } else {
                $result=false;
                $message .= "Une erreur systeme est survenue";
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
    public function cancelSoldeGroupeDB($gid,$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('UPDATE groupe SET estSolder=0 WHERE  gid=:gid');
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                $result=true;
                DBLink::disconnect($bdd);
            } else {
                $result=false;
                $message .= "Une erreur systeme est survenue";
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
    public function haveNoDepense($gid,$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT g.gid from groupe g join depense d on g.gid = d.gid WHERE d.gid=:gid;');
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                $result=$stmt->fetchAll(PDO::FETCH_NUM);
                if (count($result) == 0) {
                    DBLink::disconnect($bdd);
                    return true;
                } else {
                    DBLink::disconnect($bdd);
                    return false;
                }
            }else{
                $message="erreur lors de l'exécution";
            }
            
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
    }
    


    /*public function isGroupeSoldeNoVersement($gid,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT g.gid from groupe g WHERE g.gid=:gid AND EXISTS(SELECT v.gid FROM versement v WHERE v.gid=:gid);');
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                $result=$stmt->fetchAll(PDO::FETCH_NUM);
                if (count($result) >= 1) {
                    DBLink::disconnect($bdd);
                    return true;
                } else {
                    DBLink::disconnect($bdd);
                    return false;
                }
            }else{
                $message="erreur lors de l'exécution";
            }
            
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
    }*/


public function getAllTags($gid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT  from utilisateur u join groupe g on u.uid = g.uid WHERE g.gid=:gid UNION SELECT u.courriel FROM participer p JOIN utilisateur u on u.uid = p.uid  WHERE p.gid=:gid AND p.estConfirmer=1;');
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetchALL(PDO::FETCH_COLUMN);
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
}

public function ecartMoyenEqualZero($gid,$message){
    $depenseRepository= new DepenseRepository();
    $tabUser=GroupeRepository::getAllUserIDByGroupeID($gid,$message);
    $total=0;
    foreach($tabUser as $uid){
        $result=$depenseRepository->getMoyenEcartOfAGroup($gid,$uid,$message);
        $result>0?$total+=$result:$total-=$result;
    }
    return $total==0 ? true:false;

}


public function solderGroupe($gid,&$message){
    $repoUser=new UserRepository();
    $depenseRepository = new DepenseRepository();
    $versementRepository = new VersementRepository();
    $repoGroupement = new GroupementRepository();
    $message="";
    $tabIDUser=GroupeRepository::getAllUserIDByGroupeID($gid,$message);
    $tabIDEcart=array();
    $arrayUserInGroupement=array();
    if($repoGroupement->existsGroupementInGroupe($gid,$message)){
        $tabGroupement=$repoGroupement->getGroupementByGroupe($gid);
        foreach($tabGroupement as $groupement){
            array_push($arrayUserInGroupement,$repoGroupement->getParticipantOfGroupement($groupement->ggid,$message));
            $tabGroupementDep=$depenseRepository->getAllDepenseOfAGroupementOfAGroupe($gid,$groupement->ggid,$message);
            $diffGroupement =$depenseRepository->getMoyenGroupementEcartOfAGroup($gid,$groupement->ggid,$message);
            $tabIDEcart+=[$groupement->uid=>floatval($diffGroupement)];
        }
        foreach($arrayUserInGroupement as $userTab){
            foreach($userTab as $user){
            if(in_array($user->uid,$tabIDUser)){
               $key= array_search($user->uid,$tabIDUser);
               unset($tabIDUser[$key]);
            }
        }
        }

    }
    foreach($tabIDUser as $uid){
        $ecart=$depenseRepository->getMoyenEcartOfAGroup($gid,$uid,$message);
        $tabIDEcart +=[$uid=>floatval($ecart)];
    }
     $copy=$tabIDEcart;
    while(true){
        $current= current($copy);
        $oppositeCurrent=-($current);
        if($current===0.00 && is_double($current)){
            next($copy); 
        }else if (is_bool($current)){
            if(!array_filter($copy)){
                break;
                 }
            ($keyMax= array_search(max($copy),$copy));
           ( $keyMin= array_search(min($copy),$copy));
           if(abs($copy[$keyMin])>$copy[$keyMax]){
            $copy[$keyMin]+= $copy[$keyMax];
            $copy[$keyMin]=round($copy[$keyMin],1);
            $versementRepository->storeVersement(new Versement($gid,$keyMin,$keyMax,date('d-m-y h:i:s'),$copy[$keyMax],0),$message);
          // echo $keyMin.' DOIT DONNé '.$copy[$keyMax].' à '.$keyMax.'<br>';
           $copy[$keyMax]-=$copy[$keyMax];
           }else{
            $versementRepository->storeVersement(new Versement($gid,$keyMin,$keyMax,date('d-m-y h:i:s'),abs($copy[$keyMin]),0),$message);
          //  echo $keyMin.' DOIT DONNé '.abs($copy[$keyMin]).' à '.$keyMax.'<br>';
           $copy[$keyMax]+=$copy[$keyMin];
           $copy[$keyMax]=round($copy[$keyMax],1);
           $copy[$keyMin]=0.00;
            }
            reset($copy);
        }else{
            if(array_search($oppositeCurrent,$copy,true)){
                $keyC=array_search($current,$copy);
                $KeyO=array_search($oppositeCurrent,$copy);
                if($current>0){
                   $versementRepository->storeVersement(new Versement($gid,$KeyO,$keyC,date('d-m-y h:i:s'),$copy[$keyC],0),$message);
                 //   echo $KeyO.' DOIT DONNé '.$copy[$keyC].' à '.$keyC.'<br>';
                }else{
                    $versementRepository->storeVersement( $versement = new Versement($gid,$keyC,$KeyO,date('d-m-y h:i:s'),$copy[$KeyO],0),$message);
                //    echo $keyC.' DOIT DONNé '.$copy[$KeyO].' à '.$KeyO.'<br>';
                }
                $copy[$keyC]=0.00;$copy[$KeyO]=0.00;
                reset($copy);
            }   
         next($copy);
        }
       
    }

    $message = "Solde Effectué";
    
}

public function cancelGroupe($gid,&$message){
    $repoVersement = new VersementRepository();
    if(GroupeRepository::cancelSoldeGroupeDB($gid,$message)){
    if($repoVersement->deleteAllVersement($gid,$message)){
     return   $message="Solde annulé";
    }else{
        return  $message="Erreur lors de l'annulation du solde";
    }
    }
}




public function formerHeaderAllEmail($gid,&$message){
    $tabMail=GroupeRepository::getAllUserMailByGroupeID($gid,$message);
    $headers='To:';
    if(sizeof($tabMail)>0){
        foreach ($tabMail as $mail){
            $headers.='<'.$mail.'>,';
        }
        return substr_replace($headers ,"",-1).'\r\n';

    }else{
     return   $message="Erreur-mail";
    }
}


public function getInfoIndexDisplay($uid,&$message){
    $depenseReop= new DepenseRepository();
    $message="";
    $idGroupe;
    $AllGroupeUserID=GroupeRepository::getAllGroupIDByUserID($uid,$message);

    $SizeTab=sizeof($AllGroupeUserID);
    $display="";
    if($SizeTab<=0){
        $display.= '<p>Vous n\'êtes pas dans un groupe</p>';
    }else{
    for($i=0;$i<$SizeTab;$i++){
        $idGroupe= $AllGroupeUserID[$i][0];
        $groupe =GroupeRepository::getInfoGroupeID($idGroupe,$message);
        $depense= $depenseReop->getThreeLastDepenseOfAGroupe($idGroupe,$message);
        $display.= '<a href="groupeView.php?groupe='.$idGroupe.'">  
        <article class="Groupe"
            <p>Nom :'. $groupe[1].'</p>
            <p>Créateur :'. $groupe[3].'</p>
            <p>Montant total des dépenses:'. $groupe[4] .$groupe[2].'</p>
            <p>Dernières dépenses:</p>
            <ul>';
                if(sizeof($depense)>0){
                for($j=0;$j<3;$j++){
                    $display.= "<li>".$depense[$j][0].' '.$depense[$j][1].$groupe[2]."</li>";
                }
                }else{
                    $display.= '<li><p>Aucune dépense</p></li>';
                }
        $display.= '</ul>
            </article></a>';

    }
    }
    return $display;
}

/*
public function getInfoGroupeDisplay($gid,&$message){
    $groupeRepository= new GroupeRepository();
    $depenseRepository= new DepenseRepository();
    $message="";
    $display="";
    $allIdUser=$groupeRepository->getAllUserIDByGroupeID($gid,$message);
      
    for($i=0;$i<sizeof($allIdUser);$i++){
        $idUser=$allIdUser[$i][0];
        if($idUser<0||$idUser==null){
            $display.= 'Pas de participant dans le groupe dans le groupe';}
        else{
         $allDepenseUser=$depenseRepository->getAllDepenseOfAGroupeOfAUser($idUser,$gid,$message);
            if(sizeof($allDepenseUser)<0|| $allDepenseUser==null){
                $display.= 'pas de dépense';
            }
        }

    }
       return $display;


    }*/


    public function formModifierDisplay($tab){
        $option=" ";
        foreach($tab as $groupe){
        $option.="<option value=\"".$groupe->gid."\">".$groupe->nom."</option>";
        }
        $display='<form  method="post" enctype="multipart/form-data">
                        <label for="nomGroupeSelect">Groupe Sélectionné: *</label>
                        <select name="nomGroupeSelect">'.
                          $option
                          .'</select>
                          <label for="nomGroupeM">Nom du groupe: *</label><input id="nomGroupeM" name="nomGroupeM" type="text"  required>
                            <label for="deviseM">Devise: *</label>
                            <select name="deviseM">
                            <option value="€">Euro (€)</option>
                            <option value="$">Dollar ($)</option>
                            <option value="£">Livre (£)</option>
                            </select>
                        <input type="submit" name="modifier" value="Modifier"  />
                        <input class="Annuler" type="reset" name="annuler" value="Annuler" />
                        <input class="Supprimer" type="submit" name="supprimer" value="Supprimer" />
                </form>';
    return $display;
    }

    public function displayInviteGroupe($uid,&$message){
        $tabIdGroupe=GroupeRepository::getInvitGroupeId($uid,$message);
        if(empty($tabIdGroupe)){
            return "<p>Vous n'avez pas invitation en attente</p>";
        }else if (sizeof($tabIdGroupe)<=0)
        {
            return "<p>Vous n'avez pas invitation en attente</p>";
        }else{
            $display='';
            foreach($tabIdGroupe as $groupeId){
            $groupe=GroupeRepository::getInfoGroupeID($groupeId,$message);
            $display.='<article class="Groupe">
            <form  method="post" enctype="multipart/form-data">
            <label>Nom : '.$groupe[1].' </label>
            <label>Créateur : '.$groupe[3].' </label>
            <label>Montant total des dépenses: '.(empty($groupe[4])? "0.00":$groupe[4]).' '.$groupe[2].'</label>
            <input type="hidden" name="groupe" value="'.$groupeId.'"  />
            <input type="submit" name="accepter" value="Accepter"  />
            <input class="Supprimer" type="submit" name="refuser" value="Refuser"  />
            </form>
            </article>';
            }

            return $display;
        }
    }

    public function displayVersmentOfAGroup($gid,&$message){
        $userRepo= new UserRepository();
        $versementRepo= new VersementRepository();
        $tabVersement = $versementRepo->getAllVersementOfAGroupe($gid,$message);
        $displayVersement="";
        if(sizeof($tabVersement)>0){

            foreach($tabVersement as $versement){
                $donneur=$userRepo->getUserById($versement->uidD);
                $receveur=$userRepo->getUserById($versement->uidR);
                $displayVersement.='<p>'.$donneur->prenom.'.'.$donneur->nom[0].' doit donné(e) '.$versement->montant.' à '.$receveur->prenom.'.'.$receveur->nom[0].'</p>';
            }
            return $displayVersement;

        }
        return "il n'y a pas de versement";





    }

    public function verifyAllVersementConfirme($gid,$message){
        $versementRepo= new VersementRepository();
        $tabVersement = $versementRepo->getAllVersementOfAGroupe($gid,$message);
        $lenght=sizeof($tabVersement);
        if(sizeof($tabVersement)>0){
            $total=0;
            foreach($tabVersement as $versement){
                $total+=$versement->estConfirmer;
            }
            return $lenght==$total?true:false;
        }else{
            return false;
        }
    }



        public function displayFormSolder($gid,&$message){
            $displaySolderForm='<form   method="post" enctype="multipart/form-data">
            <input  name="groupeid" type="hidden" value="'.$gid.'"/>
            <input  type="submit" name="solderGroupe" value="Solder du Groupe"/>
            </form>';
            $SupprimerGroupe='<form  method="post" enctype="multipart/form-data">
            <input  name="groupeid" type="hidden" value="'.$gid.'"/>
            <input class="Supprimer" name="supprimerGroupe" type="submit" value="Supprimer le Groupe"/></form>';
            if((GroupeRepository::isAGroupeIsSolde($gid,$message))){
                $displaySolderForm='<form   method="post" enctype="multipart/form-data">
                <input  name="groupeid" type="hidden" value="'.$gid.'"/>
                <input  type="submit" name="annulerSolderGroupe" value="Annuler le solde du Groupe"/>
                </form>';
                if(GroupeRepository::verifyAllVersementConfirme($gid,$message)){
                $displaySolderForm.=$SupprimerGroupe;
                }
            
            }
            if(GroupeRepository::haveNoDepense($gid,$message)){
                $displaySolderForm.=$SupprimerGroupe;
            }
            return $displaySolderForm;
        }


}
?>