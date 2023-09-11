<?php
namespace Depense;

require_once 'inc/db/db_link.inc.php';
require_once 'php/tag.php';
require_once 'php/groupement.php';
require_once('php/dataSecurity.php');
use function dataSecurity\secureString;
use DB\DBLink;
use PDO;
use PDOException;
use User\User;
use User\UserRepository;
use Groupe\Groupe;
use Groupe\GroupeRepository;
use Tag\Tag;
use Tag\TagRepository;
use Groupement\Groupement;
use Groupement\GroupementRepository;

class Depense{
    public $did;    
    public $dateHeure;
    public $montant;
    public $libelle;
    public $gid;
    public $uid;


public function __get($prop){
    return $this->$prop;
}

public function __construct($did,$dateHeure,$montant,$libelle,$gid,$uid) {
    $this->did = $did;
    $this->dateHeure = $dateHeure;
    $this->montant = $montant;
    $this->libelle = $libelle;
    $this->gid = $gid;
    $this->uid = $uid;
}


}

class DepenseRepository{

    const TABLE_NAME='depense';

   public function storeDepense($newDepense,&$message){
        $result = false;
        $bdd = null;

        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('INSERT INTO ' . self::TABLE_NAME . ' VALUES (0,:dateHeure,:montant,:libelle,:gid,:uid)');
            $stmt->bindValue(':dateHeure', $newDepense->dateHeure);
            $stmt->bindValue(':montant',floatval( $newDepense->montant));
            $stmt->bindValue(':libelle', $newDepense->libelle);
            $stmt->bindValue(':gid', $newDepense->gid);
            $stmt->bindValue(':uid', $newDepense->uid);
            if ($stmt->execute()) {
                $message = 'Dépense : '.$newDepense->libelle.' ajoutée';
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

    
public function updateDepense($newDepense,&$message){
    $message = '';
    $bdd  = DBLink::connect2db(MYDB, $message);
    if ($bdd) {
        $stmt = $bdd->prepare("UPDATE ".self::TABLE_NAME. " d SET d.libelle=:libelle,d.montant=:montant,d.dateHeure=:dateHeure,d.uid=:uid WHERE d.did=:did");
        $stmt->bindValue(':libelle', $newDepense->libelle);
        $stmt->bindValue(':montant', $newDepense->montant);
        $stmt->bindValue(':dateHeure', $newDepense->dateHeure);
        $stmt->bindValue(':uid', $newDepense->uid);
        $stmt->bindValue(':did', $newDepense->did);
       
        if ($stmt->execute()) {
            $message = $newDepense->libelle." a été modifiée";
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

    public function updateTagOfDepense($did,$gid,$tabTag,&$message){
        $repoTag = new TagRepository();
        $newTag="";
        if(sizeof($tabTag)>1){
            $repoTag->deleteAllTagDid($did,$message);
                foreach($tabTag as $tagId){
                    if(!(empty(trim($tagId)))){
                        if(!is_numeric($tagId)){
                            if(!($repoTag->tagExistinGroup($gid,$tagId,$message))){
                                    $repoTag->storeTagGroupe($gid,$tagId,$message);
                                    $newTag=$tagId;
                            }
                        }
                    }
                }

            $allTag=$repoTag->getAllTagsOfGroupe($gid,$message);
            foreach($allTag as $tag){
                if($tag->tag==$newTag){
                    $repoTag->addTagToDepense($did,$tag->tid,$message);
                }else{
                    $repoTag->addTagToDepense($did,$tag->tid,$message);
                }
            }

            
        }else{
            $repoTag->deleteAllTagDid($did,$message);
        }

    }

    public function DepenseAsTag($did,$tid,&$message){
        $result=false;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT t.* FROM tag t JOIN caracteriser c on t.tid=c.tid WHERE  c.did=:did and c.tid =:tid");
            $stmt->bindValue(':did', $did);
            $stmt->bindValue(':tid', $tid);
            if($stmt->execute()){
                if (count($result) >= 1) {
                    DBLink::disconnect($bdd);
                    return true;
                } else {
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


    public function deleteDepense($did,&$message){
        $result=null;
        $bdd= null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
    
            $requete = $bdd->prepare("DELETE FROM " . self::TABLE_NAME . " WHERE did=:did;");
            $requete->bindValue(":did", $did);
            
           if( $requete->execute()){
               $message="Dépense supprimé avec succès";
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

    public function deleteAllFactureAndCaracteriserOfDepense($did,$message){
            $result=null;
            $bdd= null;
            $dir=glob('uploads/'.$did.'/*');
            if(is_dir('upload/'.$did)){
            foreach($dir as $file){
                    unlink($file);
            }
            if(rmdir('uploads/'.$did)){
            try {
                $bdd = DBLink::connect2db(MYDB, $message);
        
                $requete = $bdd->prepare("DELETE FROM facture where did=:did;DELETE FROM caracteriser where did=:did;DELETE FROM depense where did=:did;");
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
                $message = "Erreur connexion à la base de donnée";
                DBLink::disconnect($bdd);
            }
            }
            return $result;
            DBLink::disconnect($bdd);
            }else{
                DepenseRepository::deleteDepense($did,$message);
            }
        }
    

    public function getDepenseById($did,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT d.* FROM depense d  WHERE d.did=:did;");
            $stmt->bindValue(':did', $did);
            if($stmt->execute()){
                $stmt->setFetchMode(PDO::FETCH_CLASS| PDO::FETCH_PROPS_LATE,'Depense');
                $result= $stmt->fetchObject();
            } else {
                $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
            }
            return $result;
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    public function getLastDepenseByUid($uid,$gid,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT d.* FROM depense d  WHERE d.uid=:uid AND d.gid=:gid ORDER BY d.did DESC;");
            $stmt->bindValue(':uid', $uid);
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                $stmt->setFetchMode(PDO::FETCH_CLASS| PDO::FETCH_PROPS_LATE,'Depense');
                $result= $stmt->fetchObject();
            } else {
                $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
            }
            return $result;
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    public function researchDepense($gid,$search,&$message){
            $result=null;
            $bdd=null;
            try{
                $bdd = DBLink::connect2db(MYDB, $message);
                $stmt = $bdd->prepare("SELECT DISTINCT d.* FROM ".self::TABLE_NAME." d JOIN groupe g on g.gid = d.gid LEFT OUTER JOIN caracteriser c on d.did = c.did LEFT OUTER JOIN tag t on t.tid = c.tid WHERE g.gid=:gid AND (d.libelle LIKE CONCAT( '%', :search, '%')||t.tag LIKE CONCAT( '%', :search, '%'))");//AND (d.libelle LIKE '%:search%'||t.tag LIKE '%:search%')
                $stmt->bindValue(':gid', $gid);
                $stmt->bindValue(":search", secureString($search));
                if($stmt->execute()){
                    $result=$stmt->fetchALL(PDO::FETCH_OBJ);
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

    public function advanceResearch($gid,$search,$mMin,$mMax,$dMin,$dMax,$tags,$message){
    $result=null;
    $bdd=null;
    $date = date('Y-m-d H:m');
    $tagQuery="";
    if(!empty($tags)){
        foreach($tags as $tag){
            if(empty(trim($tag))){
                $tagQuery="";
            }else{
            $tagQuery.='AND ((c.tid='.$tag.')||';}
        }
        $tagQuery=substr($tagQuery, 0, -2);
        $tagQuery.=')';
    }else{
        $tagQuery="";
    }
    if(empty($mMin)){
        $mMin=1.00;
    }if(empty($mMax)){
        $mMax=9999999.99;
    }
    if(empty($dMin)){
        $dMin='2000-10-01 9:00:0';
    }if(empty($dMax)){
        $dMax=str_replace('T',' ',$date);
    }else{
        $dMax=str_replace('T',' ',$dMax);
    }
    $arraySearch=[];
        array_push($arraySearch,$search,$mMin,$mMax,$dMin,$dMax,$tags);
    setcookie("advanceSearch",json_encode($arraySearch),time()+3600);
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT DISTINCT d.*  FROM depense d join groupe g on g.gid = d.gid LEFT OUTER join caracteriser c on d.did = c.did
        WHERE d.gid=:gid AND ((d.libelle LIKE CONCAT( '%', :search, '%')) AND (d.montant BETWEEN :mMin and :mMax) AND (d.dateHeure BETWEEN :dMin and :dMax )".$tagQuery.") ORDER BY d.dateHeure");
        $stmt->bindValue(':gid', $gid);
        $stmt->bindValue(':mMin', $mMin);
        $stmt->bindValue(':mMax', $mMax);
        $stmt->bindValue(':dMin', $dMin);
        $stmt->bindValue(':dMax', $dMax);
        $stmt->bindValue(':search', secureString(trim($search)));
        if($stmt->execute()){
            $result=$stmt->fetchALL(PDO::FETCH_OBJ);
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

public function getAllDepenseOfAGroupe($gid,&$message){
    $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT d.* FROM depense d JOIN groupe g on d.gid = g.gid WHERE d.gid=:gid");
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                $result=$stmt->fetchAll(PDO::FETCH_OBJ);
            } else {
                $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
            }
            return $result;
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
        DBLink::disconnect($bdd);
        return $result;
}

    public function getAllDepenseOfAGroupeOfAUser($uid,$gid,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT DATE_FORMAT(d.dateHeure,'%H:%i %d/%m/%y'),d.libelle,d.montant,g.devise,u.nom FROM depense d JOIN groupe g on d.gid = g.gid JOIN utilisateur u on d.uid = u.uid WHERE d.gid=:gid AND d.uid=:uid;");
            $stmt->bindValue(':gid', $gid);
            $stmt->bindValue(':uid', $uid);
            if($stmt->execute()){
                $result=$stmt->fetchAll(PDO::FETCH_NUM);
            } else {
                $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
            }
            return $result;
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    public function getAllDepenseOfAGroupementOfAGroupe($gid,$ggid,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT DATE_FORMAT(d.dateHeure,'%d/%m/%y'),d.libelle,d.montant,g.devise,gr.uid FROM depense d JOIN groupe g on d.gid = g.gid JOIN grouper gr on d.uid = gr.uid WHERE d.gid=:gid AND gr.ggid=:ggid;
            ");
            $stmt->bindValue(':ggid', $ggid);
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                $result=$stmt->fetchAll(PDO::FETCH_NUM);
            } else {
                $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
            }
            return $result;
            $stmt = null;
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
        DBLink::disconnect($bdd);
        return $result;
    }





    public function getThreeLastDepenseOfAGroupe($gid,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT d.libelle,d.montant from depense d join groupe g on g.gid = d.gid WHERE g.gid=:gid ORDER BY d.dateHeure DESC LIMIT 3;");
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                $result=$stmt->fetchAll(PDO::FETCH_NUM);
                
            } else {
                $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
            }
            // echo $result[0][0].' '.$result[0][1];'<br>';
            return $result;

        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
    }
   



   //!---- Groupe Depense TAB ----- */ 

   /**
   * @return An array of date.
   */
   public function getAllDateToAllDepenseByGid($gid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT DISTINCT(DATE_FORMAT(d.dateHeure,'%d/%m/%y')) as allDate from depense d join groupe g on g.gid = d.gid WHERE g.gid=:gid ORDER BY allDate ASC;");
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetchALL(PDO::FETCH_COLUMN);
        } else {
            $message .= "Une erreur systeme est survenue";
        }
        return $result;
       
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    return $result;
    DBLink::disconnect($bdd);
   }
   
  /**
   * @return An array of objects(Depense).
   */
   public function getAllDepenseOfAUserofGroupe($uid,$gid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT d.did,DATE_FORMAT(d.dateHeure,'%d/%m/%y')as dateHeure,d.montant,d.libelle,d.gid,d.uid FROM ".self::TABLE_NAME." d WHERE d.uid=:uid AND d.gid=:gid");
        $stmt->bindValue(':uid', $uid);
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetchAll(PDO::FETCH_OBJ);
        } else {
            $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
        }
         
        return $result;
      
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }

   }

   public function getTotalDepenseOfAGroup($gid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT SUM(d.montant) FROM ".self::TABLE_NAME." d WHERE d.gid=:gid");
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetch();
        } else {
            $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
        }
         
        return $result;
      
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }

   }
   public function getMoyenDepenseOfAGroup($gid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT ROUND(SUM(d.montant) / COUNT(DISTINCT d.uid),2) FROM ".self::TABLE_NAME." d JOIN utilisateur u on d.uid = u.uid WHERE d.gid=:gid AND u.estActif=1;");
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetch();
        } else {
            $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
        }
         
        return $result;
      
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }

   }
  
   public function getMoyenEcartOfAGroup($gid,$uid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT((SELECT SUM(d.montant) FROM depense d JOIN utilisateur u on d.uid = u.uid WHERE d.gid=:gid AND d.uid=:id AND u.estActif=1)-(SELECT ROUND(SUM(d.montant) / COUNT(DISTINCT d.uid),2) FROM depense d JOIN utilisateur u on d.uid = u.uid WHERE d.gid=:gid AND u.estActif=1));");
        $stmt->bindValue(':id', $uid);
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetch();
           return $result[0];
        } else {
            $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
            return null;
        }
      
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
   }

    public function getMoyenGroupementEcartOfAGroup($gid,$ggid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT((SELECT SUM(d.montant) FROM depense d JOIN groupe g on d.gid = g.gid JOIN grouper gr on d.uid = gr.uid WHERE d.gid=:gid  AND gr.ggid=:ggid)-
        (SELECT ROUND(SUM(d.montant) / COUNT(DISTINCT d.uid),2) FROM depense d JOIN utilisateur u on d.uid = u.uid WHERE d.gid=:gid AND u.estActif=1)*(SELECT COUNT(DISTINCT d.uid)
 FROM depense d JOIN utilisateur u on u.uid = d.uid JOIN groupe g on g.gid = d.gid JOIN grouper g2 on u.uid = g2.uid WHERE g2.ggid=:ggid AND g.gid=:gid));");
        $stmt->bindValue(':ggid', $ggid);
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $result=$stmt->fetch();
           return $result[0];
        } else {
            $message .= "Une erreur systeme est survenue.<br> (code erreur:" . $stmt->errorCode() . "<br>";
            return null;
        }
      
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
   }


   public function displayDateColumn($tabDate){
    $display="";
    if(sizeof($tabDate)<=0){
        return "<h3>Vous n'avez pas de dépense enregistrée</h3>";
    }else{
        $dateLi=' ';
        foreach($tabDate as $date){
            $dateLi.='<li>'.$date.'</li>';
        }
       return $display='<ul class="TableDate">
        <li style=" font-weight: bold">Date</li>
        <li> - </li>'.
        $dateLi
       .'<li> - </li><li style=" font-weight: bold">Total</li><li style=" font-weight: bold">Écart Moyenne</li></ul>';
    }
   }

 
   public function displayUserColumn($uid,$tabDep,$tabDate,$difference) {
    $repoUser= new UserRepository();
    $temp='';
    $lengt=sizeof($tabDate);
    $total=0;
    $diffDisplay='';
    if($lengt<=0){
        return "";
    }else if (sizeof($tabDep)<=0){
        for($i=0;$i<$lengt;$i++){
            $temp.='<li> - </li>';
        }
        $user=$repoUser->getUserById($uid);
        return '<ul class="TableDate">
        <li style=" font-weight: bold">'.$user->prenom.'.'.$user->nom[0].'</li>
        <li> - </li>'.$temp.'</li>
        <li> - </li></li>
        <li> - </li></ul>';
    }else{
        $array=array();
        $user=$repoUser->getUserById($uid);
        foreach($tabDate as $date){
        $array+=[$date=>0];
        }
        foreach($tabDep as $depense){
            if(array_key_exists($depense->dateHeure,$array)){
            $array[$depense->dateHeure]+=$depense->montant;
            }
            $total+=$depense->montant;
        }
        foreach($array as $value){
            if($value==0){
                $temp.= '<li> - </li>';
            }else{
                $temp.= '<li >'.$value.' </li>';
            }
        }
        $difference>=0? $diffDisplay='<li>'.$total.' </li>'.'<li class=DepenseAdd>+'.$difference.'</li>':$diffDisplay='<li>'.$total.' </li>'.'<li class=DepenseMinus>'.$difference.'</li>';
      return  '<ul class="TableDate">
        <li style=" font-weight: bold">'.$user->prenom.'.'.$user->nom[0].'</li>
        <li> - </li>'.$temp.'<li> - </li>'.$diffDisplay.'</ul>';
    
   }
    }


    public function displayGroupementColumn($ggid,$tabDep,$tabDate,$difference) {
        $repoUser= new UserRepository();
        $repoGroupement = new GroupementRepository();
        $groupement =$repoGroupement->getGroupementById($ggid);
        $tabUser=$repoGroupement->getParticipantOfGroupement($ggid,$message);
        $temp='';
        $lengt=sizeof($tabDate);
        $total=0;
        $diffDisplay='';
        $display="";
        if($lengt<=0){
            return "";
        }else if (sizeof($tabDep)<=0){
            for($i=0;$i<$lengt;$i++){
                $temp.='<li> - </li>';
            }
            $allPrenom="";
            foreach($tabUser as $userPrenom){
                $allPrenom.=' '.$userPrenom->prenom.' et';
            }
            $allPrenom=substr_replace($allPrenom,"",-2);
            $display='<ul class="TableDate"><li style=" font-weight: bold">'.$groupement->libelle.'</li><li>';
            return $display.=$allPrenom.'</li>'.$temp.'</li><li> - </li></li><li> - </li></ul>';
        }else{
            $arrayUser=array();
            $array=array();
            foreach($tabUser as $user){
                foreach($tabDate as $date){
                    $array+=[$date=>0];
                    }
                    foreach($tabDep as $depense){
                        if($depense[4]==$user->uid){
                        if(array_key_exists($depense[0],$array)){
                        $array[$depense[0]]+=$depense[2];
                        }
                        $total+=$depense[2];
                    }
                    }
                 $arrayUser+=[$user->uid=>$array];
                 $array=array();
            }
            $lengthUser=sizeof($arrayUser);
            $temp='';
            foreach($tabDate as $date){    
                $temp.='<li>';
                foreach($arrayUser as $arrayOfDate){
                    $value =$arrayOfDate[$date];
                    if($value==0){
                        $temp.= '-|';
                    }else{
                        $temp.= $value.'|';
                    }
                }
                $temp=substr_replace($temp,"",-1);
                $temp.='</li>';
            }
            $difference>=0? $diffDisplay='<li>'.$total.' </li>'.'<li class=DepenseAdd>+'.$difference.'</li>':$diffDisplay='<li>'.$total.'</li>'.'<li class=DepenseMinus>'.$difference.'</li>';
            $allPrenom="";
            foreach($tabUser as $user){
                $allPrenom.=' '.$user->prenom.' et';
            }
            $allPrenom=substr_replace($allPrenom,"",-2);
         $display.='<ul class="TableDate"><li style=" font-weight: bold">'.$groupement->libelle.'</li><li>';
            return $display.=$allPrenom.'</li>'.$temp.'<li> - </li>'.$diffDisplay.'</ul>';
        
       }
        }


    public function displayAllUser($gid){
        $repoDepense = new DepenseRepository();
        $repoGroupe = new GroupeRepository();
        $repoGroupement= new GroupementRepository();
        $message="";
        $display='';
        $tabDate=$repoDepense->getAllDateToAllDepenseByGid($gid,$message);
        /*Display a les dates */
        $display=$repoDepense->displayDateColumn($tabDate);
        $tabUser = $repoGroupe->getAllUserIDByGroupeID($gid,$message);
        /*Moyenne + total*/ 
        $total=$repoDepense->getTotalDepenseOfAGroup($gid,$message);
    
        $moyenne=$repoDepense->getMoyenDepenseOfAGroup($gid,$message);

        $arrayUserInGroupement=array();
        if($repoGroupement->existsGroupementInGroupe($gid,$message)){
            $tabGroupement=$repoGroupement->getGroupementByGroupe($gid);
            foreach($tabGroupement as $groupement){
                array_push($arrayUserInGroupement,$repoGroupement->getParticipantOfGroupement($groupement->ggid,$message));
                $tabGroupementDep=$repoDepense->getAllDepenseOfAGroupementOfAGroupe($gid,$groupement->ggid,$message);
                $diffGroupement =$repoDepense->getMoyenGroupementEcartOfAGroup($gid,$groupement->ggid,$message);
                $display.= $repoDepense->displayGroupementColumn($groupement->ggid,$tabGroupementDep,$tabDate,$diffGroupement);
            }
            foreach($arrayUserInGroupement as $userTab){
                foreach($userTab as $user){
                if(in_array($user->uid,$tabUser)){
                   $key= array_search($user->uid,$tabUser);
                   unset($tabUser[$key]);
                }
            }
            }

        }

        
        /*Ajout des Users */
        foreach($tabUser as $uid){
            $tabDepUser=$repoDepense->getAllDepenseOfAUserofGroupe($uid,$gid,$message);
            $ecartMoyen=$repoDepense->getMoyenEcartOfAGroup($gid,$uid,$message);
            $display.=$repoDepense->displayUserColumn($uid,$tabDepUser,$tabDate,$ecartMoyen);
        }
        if(!empty($total[0])){
            $display.='<ul class="TableDate"><li style=" font-weight: bold">Total</li><li>'.$total[0].'</li></ul>'.'<ul class="TableDate"><li style=" font-weight: bold">Moyenne</li><li>'.$moyenne[0].'</li></ul>';
        }
        return $display;
    }



    //!---- EDIT DEPENSE-----------

    public function displayParticipantSelect($gid,$uid,&$message){
        $repoGroupe=new GroupeRepository();
        $repoUser=new UserRepository();
        $tabId=$repoGroupe->getAllUserIDByGroupeID($gid,$message);
        if(sizeof($tabId)>=1){
            $select='<label for="participants">Participant : *</label>
        <select name="participants">';
        $participant="";
            foreach($tabId as $id){
                $user = $repoUser->getUserById($id);
                if($uid==$id){
                    $participant.= '<option value="'.$user->uid.'" selected>'.$user->prenom.'.'.$user->nom.'</option>';
                }else{
                    $participant.= '<option value="'.$user->uid.'">'.$user->prenom.'.'.$user->nom.'</option>';
                }
            }
          return  $select.=$participant.'</select>';

        }else{
            $message="Il n'y a pas de participant dans le groupe";
        }
    }

    public function displayTagsSelect($gid,&$message){
        $repoTag=new TagRepository();
        $tab=$repoTag->getAllTagsOfGroupe($gid,$message);
        if(sizeof($tab)>0){
            $select='<label for="tags">Tags : </label><input id="tags" name="tags[]" type="text" ><ul class="Checkboxing">';
            foreach($tab as $tag){
                    $select.='<li><input type="checkbox" id="'.$tag->tid.'" name="tags[]" value="'.$tag->tid.'"><label for="'.$tag->tid.'">'.$tag->tag.'</label></li>';
            }
          return  $select.='</ul>';

        }else{
            return  '<label for="tags">Tags : *</label><input id="tags" name="tags[]" type="text" >';
        }
    }

    public function dislayFormCreate($gid,$uid,$date,&$message){
        $participant =DepenseRepository::displayParticipantSelect($gid,$uid,$message);
        $tag=DepenseRepository::displayTagsSelect($gid,$message);
      return  $form='<h2>Ajout d\'une dépense</h2><article><form  method="post" enctype="multipart/form-data">
        <label for="libelle">Libellé : *</label><input id="libelle" name="libelle" type="text" >
        <label  for="montant">Montant : *</label><input id="montant" step="0.01" name="montant" type="number" min="1" >
        <label for="date">Date : *</label><input id="date" name="date" type="datetime-local"  value="'.$date.'">'.
        $participant.$tag.'
        <input type="submit" name="ajouter" value="Ajouter"  />
        <input class="Annuler"  type="reset" name="annulerAjout" value="Annuler"  /></form></article>';
    }


    public function displayFormModification($gid,$did,$uid,&$message){
        $participant =DepenseRepository::displayParticipantSelect($gid,$uid,$message);
        $repoTag=new TagRepository();
        $tabGroupe=$repoTag->getAllTagsOfGroupe($gid,$message);
        $tabDepense=$repoTag->getAllTagsOfDepense($did,$message);
        $depenseInfo=DepenseRepository::getDepenseById($did,$message);
        $temp="";
        if(sizeof($tabGroupe)>0){
            $select='<label for="tagsM">Tags : </label><input id="tagsM" name="tagsM[]" type="text" ><ul class="Checkboxing">';
            foreach($tabGroupe as $tag){
                $find=true;
                foreach($tabDepense as $tagSelected){
                    if($tag->tid==$tagSelected->tid){
                        $temp.='<li><input type="checkbox" id="'.$tag->tid.'" name="tagsM[]" value="'.$tag->tid.'" checked><label for="'.$tag->tid.'">'.$tag->tag.'</label></li>';
                        $find=false;
                    }
                    }       
            if($find){
                $temp.='<li><input type="checkbox" id="'.$tag->tid.'" name="tagsM[]" value="'.$tag->tid.'"><label for="'.$tag->tid.'">'.$tag->tag.'</label></li>';
            }
            }
            $select.=$temp;
            $select.='</ul>';

        }else{
            $select= '<label for="tagsM">Tags : *</label><input id="tagsM" name="tagsM[]" type="text" >';
        }

        return $form='<article>
        <h2>Éditer d\'une dépense</h2><form  method="post" enctype="multipart/form-data">
        <label for="libelleM">Libellé : *</label><input id="libelleM" name="libelleM" type="text" value="'.$depenseInfo->libelle.'" >
        <label for="montantM">Montant : *</label><input id="montantM" step="0.01" name="montantM" type="number" value="'.$depenseInfo->montant.'">
        <label for="dateM">Date : *</label><input id="dateM" name="dateM" type="datetime-local"  value="'.$depenseInfo->dateHeure.'">'.$select.$participant.'
        <input type="submit" name="modifierDepense" value="Modifer"  />
        <input class="Annuler" type="reset" name="annulerDepense" value="Annuler"  />
        </form></article>';

    }   


    public function displayAdvanceSearchForm($gid,$date,&$message){
        $repoTag = new TagRepository();
        $tab=$repoTag->getAllTagsOfGroupe($gid,$message);
        if(sizeof($tab)>0){
            $select='<label for="tags">Tags : </label><ul class="Checkboxing">';
            foreach($tab as $tag){
                    $select.='<li><input type="checkbox" id="'.$tag->tid.'" name="tags[]" value="'.$tag->tid.'"><label for="'.$tag->tid.'">'.$tag->tag.'</label></li>';
            }
           $select.='</ul>';

        }else{
            $select= '<label for="tags">Tags : *</label><input id="tags" name="tags[]" type="text" >';
        }

        return $form='<h2>Recherche Avancée</h2><form  method="post" enctype="multipart/form-data">
        <label for="libelleAd">Libellé : *</label><input id="libelleAd" name="libelleAd" type="text" >
        <label for="montantMin">Montant minimum : *</label><input id="montantMin" step="0.01" name="montantMin"  type="number" min=1 >
        <label for="montantMax">Montant maximum : *</label><input id="montantMax" step="0.01" name="montantMax" type="number" min=1 >
        <label for="dateMin">Date minimum : *</label><input id="dateMin" name="dateMin" type="datetime-local" max="'.$date.'">
        <label for="dateMax">Date maximum: *</label><input id="dateMax" name="dateMax" type="datetime-local"  value="'.$date.'">
        '.$select.'
        <input type="submit" name="rechercheAd" value="Recherche Avancée"  />
        <input class="Annuler"  type="submit" name="annulerRechercheAd" value="Annuler"  /></form>';
    }

    public function displayResultSearch($gid,$tabDepense,&$message){
        $repoDepense=new DepenseRepository();
        $repoUser=new UserRepository();
        $display="";
        $libelle='<ul class="TableDate"><li style=" font-weight: bold">Libelle</li>';
        $montant='</ul><ul class="TableDate"><li style=" font-weight: bold">Montant</li>';
        $Heure='</ul><ul class="TableDate"><li style=" font-weight: bold">Heure</li>';
        $participant='</ul><ul class="TableDate"><li style=" font-weight: bold">Participant</li>';
        $tag='</ul><ul class="TableDate"><li style=" font-weight: bold">Tag</li>';
        $edition='</ul><ul class="TableDate"><li style=" font-weight: bold">Édition</li>';
        $suppression='</ul><ul class="TableDate"><li style=" font-weight: bold">Suppression</li>';
        $allTag="";
        if(sizeof($tabDepense)>0){
            $repoTag=new TagRepository();
          
            foreach($tabDepense as $depense){
                $tabTag=$repoTag->getAllTagsOfDepense($depense->did,$message);
               $user= $repoUser->getUserByID($depense->uid,$message);
                if(sizeof($tabTag)>0){
                    foreach($tabTag as $TAG){
                        $allTag.=$TAG->tag.' ';
                    }
                }else{$allTag="-";}
                $libelle.='<li>'.$depense->libelle.'</li>';
                $montant.='<li>'.$depense->montant.'</li>';
                $Heure.='<li>'.date_format(date_create($depense->dateHeure), 'd-m-Y H:i').'</li>';
                $participant.='<li>'.$user->prenom.'.'.$user->nom[0].'</li>';
                $tag.='<li>'.$allTag.'</li>';
                $edition.='<li>'.'<a class="ButtonEdition" href="editionDepense.php?groupe='.$gid.'&depense='.$depense->did.'">Édition</a>'.'</li>';
                $suppression.='<li> <form class="Mini" action="groupeView.php?groupe='.$gid.'" method="post"><input  class="Supprimer" type="hidden" name="depenseID" value="'.$depense->did.'"  /><input  class="Supprimer" type="submit" name="supprimer" value="Supprimer"  /></form></li>';
                $allTag="";
            }
         return   $display.=$libelle.$montant.$Heure.$participant.$tag.$edition.$suppression.'</ul>';
        }else{
           return "Pas de dépense possèdant ce que vous recherche dans son libelle ou comme tag";
        }
    }


}
?>