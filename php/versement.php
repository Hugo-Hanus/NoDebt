<?php
namespace Versement;

require_once 'inc/db/db_link.inc.php';
require_once 'inc/db/db_user.inc.php';
use DB\DBLink;
use PDO;
use PDOException;
use User\User;
use User\UserRepository;
use Groupe\Groupe;
use Groupe\GroupeRepository;

class Versement{

    public $gid;
    public $uidD;
    public $uidR;
    public $dateHeure;
    public $montant;
    public $estConfirmer;

    public function __construct($gid,$uidD,$uidR,$dateHeure,$montant,$estConfirmer) {
        $this->gid = $gid;
        $this->uidD = $uidD;
        $this->uidR = $uidR;
        $this->dateHeure = $dateHeure;
        $this->montant = $montant;
        $this->estConfirmer = $estConfirmer;
    }

    public function __get($prop){
        return $this->$prop;
}


}

class VersementRepository{
    const TABLE_NAME='versement';

    public function storeVersement($versement,$message){
        $result = false;
        $bdd = null;
        try {

            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME . " VALUES (:gid,:uidD,:uidR,:dateHeure,:montant,:estConfirmer)");
            $stmt->bindValue(':gid', $versement->gid);
            $stmt->bindValue(':uidD', $versement->uidD);
            $stmt->bindValue(':uidR', $versement->uidR);
            $stmt->bindValue(':dateHeure', $versement->dateHeure);
            $stmt->bindValue(':montant', $versement->montant);
            $stmt->bindValue(':estConfirmer', $versement->estConfirmer);
            $stmt->execute()? $result = true: $result=false;

        } catch (PDOException $e) {
           echo $message .= $e->getMessage() . '<br>';

        return $result;
         }
    
    DBLink::disconnect($con);
    return $result;

    }

    public function deleteAllVersement($gid,$message){
        $result = false;
        $bdd = null;
        try {

            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("DELETE FROM " . self::TABLE_NAME . " WHERE gid=:gid");
            $stmt->bindValue(':gid', $gid);
            $stmt->execute()? $result = true: $result=false;
        } catch (PDOException $e) {
           echo $message .= $e->getMessage() . '<br>';

        return $result;
         }
    
    DBLink::disconnect($con);
    return $result;

    }

    public function confirmeVersement($gid,$uidD,$uidR,$message){
        $result = false;
        $bdd = null;
        try {

            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("UPDATE " . self::TABLE_NAME . " SET estConfirme=1 WHERE gid=:gid AND uid=:uidD AND uid_1=:uidR");
            $stmt->bindValue(':gid', $gid);
            $stmt->bindValue(':uidD', $uidD);
            $stmt->bindValue(':uidR', $uidR);
            if($stmt->execute()){ 
                $result = true;
                $message="Versement accepté";    
            }else{
                $message="Erreur lors de la modification";    
                $result=false;
            }

        } catch (PDOException $e) {
           echo $message .= $e->getMessage() . '<br>';

        return $result;
         }
    
    DBLink::disconnect($con);
    return $result;

    }
    public function refuseVersement($gid,$uidD,$uidR,$message){
        $result = false;
        $bdd = null;
        try {

            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("DELETE FROM " . self::TABLE_NAME . " WHERE gid=:gid AND uid=:uidD AND uid_1=:uidR ");
            $stmt->bindValue(':gid', $gid);
            $stmt->bindValue(':uidD', $uidD);
            $stmt->bindValue(':uidR', $uidR);
            if($stmt->execute()){ 
                $result = true;
                $message="Versement Refusé";    
            }else{
                $message="Erreur lors de la modification";    
                $result=false;
            }

        } catch (PDOException $e) {
           echo $message .= $e->getMessage() . '<br>';

        return $result;
         }
    
    DBLink::disconnect($con);
    return $result;

    }


    public function getVersementToAccept($gid,$uid,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT v.gid,v.uid as uidD,v.uid_1 as uidR,v.dateHeure,v.montant,v.estConfirme as estConfirmer FROM '.self::TABLE_NAME.' v  WHERE v.uid_1=:uid AND v.gid=:gid AND v.estConfirme=0 ORDER BY v.dateHeure DESC');
            $stmt->bindValue(':gid', $gid);
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


    public function getAllVersementOfAGroupe($gid,&$message){
        $result=null;
        $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT v.gid,v.uid as uidD,v.uid_1 as uidR,v.dateHeure,v.montant,v.estConfirme as estConfirmer FROM '.self::TABLE_NAME.' v  WHERE  v.gid=:gid   ORDER BY v.dateHeure DESC');
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
    }

    public function displayVersmentToConfirm($gid,$uid,&$message){
        $allVersement = VersementRepository::getAllVersementOfAGroupe($gid,$message);
        $repoUser=new UserRepository();
    $display='<ul class="TableDate">';
        if(sizeof($allVersement)>0){
            $form="";
                foreach($allVersement as $versement){
                    $user=$repoUser->getUserById($versement->uidD);
                    if($versement->estConfirmer==0){
                    $form.='<li>
                    <form method="post">
                        <label>Versement de '. $user->prenom.'.'.$user->nom[0].' | Montant: '.$versement->montant.'</label>
                        <input type="hidden" name="versementId" value="'.$versement->gid.','.$versement->uidD.','.$versement->uidR.'">';
                    if($versement->uidR==$uid){
                    $form.= '<input type ="submit" name="versementAccepter" value="Accepter">
                        <input  type ="submit" class="Supprimer" name="versementRefuser" value="Refuser">';
                    }
                    $form.='</form></li>';
                }else{
                    $form.='<li> Versement confirmé : '. $user->prenom.'.'.$user->nom[0].' | Montant: '.$versement->montant.'</li>';
                }
                }

                return $display.=$form.'</ul>';
            }else{
            return "Vous n'avez pas de versement";
        }



    }
}