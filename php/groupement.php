<?php
namespace Groupement;

require_once 'inc/db/db_link.inc.php';
require_once 'inc/db/db_user.inc.php';


use DB\DBLink;
use PDO;
use PDOException;
use User\User;
use User\UserRepository;
use Groupe\Groupe;
use Groupe\GroupeRepository;
use Depense\Depense;
use Depense\DepenseRepository;

class Groupement{

    public $ggid;
    public $libelle;
    public $uid;
   

    public function __construct($ggid,$libelle,$uid,$gid) {
        $this->ggid = $ggid;
        $this->libelle = $libelle;
        $this->uid = $uid;
        $this->gid = $gid;
    }

    public function __get($prop){
        return $this->$prop;
    }
}


class GroupementRepository{
    const TABLE_NAME='groupement';

   public function storeGroupement($libelle,$uid,$gid,&$message){
        $result = false;
        $bdd = null;
        try {

            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME . " VALUES (0,:libelle,:uid,:gid)");
            $stmt->bindValue(':libelle', $libelle);
            $stmt->bindValue(':uid', $uid);
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){ 
                return $result = true;
                $message="Groupement crée";
            }else{
                $message="Groupement non crée";
                return $result=false;
                }
        } catch (PDOException $e) {
           echo $message .= $e->getMessage() . '<br>';

        return $result;
    }
    
    DBLink::disconnect($con);
    return $result;
   }

   public function addUserIntoGroupement($uid,$ggid,&$message){
        $result = false;
        $bdd = null;
        try {

            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO  grouper VALUES (:ggid,:uid)");
            $stmt->bindValue(':uid', $uid);
            $stmt->bindValue(':ggid', $ggid);
            if($stmt->execute()){ 
                return $result = true;
            }else{
                $message="Particpant(e) non ajouté(e)";
                return $result=false;
                }
        } catch (PDOException $e) {
        echo $message .= $e->getMessage() . '<br>';

            return $result;
        }

        DBLink::disconnect($con);
        return $result;
   }

   public function getGroupementById($ggid) {
    $result=null;
    $bdd    = null;
    $message = '';
    try {
        $bdd  = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT gr.* FROM ".self::TABLE_NAME. " gr WHERE gr.ggid=:ggid");
        $stmt->bindValue(':ggid', $ggid);
        if ($stmt->execute()){
            $stmt->setFetchMode(PDO::FETCH_CLASS| PDO::FETCH_PROPS_LATE,'Groupement');
             $result= $stmt->fetchObject();
        }
    } catch (PDOException $e) {
        $message .= $e->getMessage().'<br>';
    }
    DBLink::disconnect($bdd);
    return $result;
}
public function getGroupementByGroupe($gid) {
    $result=null;
    $bdd    = null;
    $message = '';
    try {
        $bdd  = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare("SELECT gr.* FROM ".self::TABLE_NAME. " gr WHERE gr.gid=:gid");
        $stmt->bindValue(':gid', $gid);
        if ($stmt->execute()){
             $result= $stmt->fetchALL(PDO::FETCH_OBJ);
        }
    } catch (PDOException $e) {
        $message .= $e->getMessage().'<br>';
    }
    DBLink::disconnect($bdd);
    return $result;
}
    public function getGroupementByUser($uid,$gid) {
        $result=null;
        $bdd    = null;
        $message = '';
        try {
            $bdd  = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT gr.* FROM ".self::TABLE_NAME. " gr JOIN grouper grr on grr.ggid  = gr.ggid WHERE gr.gid=:gid and grr.uid=:uid");
            $stmt->bindValue(':gid', $gid);
            $stmt->bindValue(':uid', $uid);
            if ($stmt->execute()){
                $stmt->setFetchMode(PDO::FETCH_CLASS| PDO::FETCH_PROPS_LATE,'Groupement');
                $result= $stmt->fetchObject();
            }
        } catch (PDOException $e) {
            $message .= $e->getMessage().'<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }



   public function getGroupementOfGestionnaire($uid,$gid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT gr.* FROM groupement gr  WHERE  gr.gid=:gid AND gr.uid=:id ORDER BY gr.ggid DESC');
        $stmt->bindValue(':id', $uid);
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
            $stmt->setFetchMode(PDO::FETCH_CLASS| PDO::FETCH_PROPS_LATE,'Groupement');
                $result= $stmt->fetchObject();
        }else{
            $message="Pas de groupement";
        }
       
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    return $result;
    DBLink::disconnect($bdd);
    }



   public function userNotInAGroupementInAGroupe($gid,$uid,&$message){
    $result=null;
    $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT u.uid FROM grouper gr JOIN utilisateur u on gr.uid = u.uid JOIN groupement g on gr.ggid = g.ggid WHERE  g.gid=:gid AND gr.uid=:uid;');
            $stmt->bindValue(':gid', $gid);
            $stmt->bindValue(':uid', $uid);
            if($stmt->execute()){
                $result=$stmt->fetchAll();
                if (count($result) <=0) {
                    DBLink::disconnect($bdd);
                    return true;
                } else {
                    DBLink::disconnect($bdd);
                    return false;
                }
            }else{
                $message="Erreur lors de l'exécution";
            }
        
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }

    return $result;
    DBLink::disconnect($bdd);
    }

   public function existsGroupementInGroupe($gid,$message){
    $result=null;
    $bdd=null;
        try{
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare('SELECT gr.ggid FROM groupe g JOIN groupement gr on gr.gid = g.gid WHERE  gr.gid=:gid ');
            $stmt->bindValue(':gid', $gid);
            if($stmt->execute()){
                $result=$stmt->fetchAll();
                if (count($result) <=1) {
                    DBLink::disconnect($bdd);
                    return true;
                } else {
                    DBLink::disconnect($bdd);
                    return false;
                }
            }else{
                $message="Erreur lors de l'exécution";
            }
        
        }catch(PDOException $e){
            $message = "Erreur connexion à la base de donnée";
        DBLink::disconnect($bdd);
        return $result;
        }
   }

   public function noDepenseInGroupement($ggid,&$message){
    $result=false;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT d.* from depense d JOIN utilisateur u on d.uid = u.uid JOIN grouper g on u.uid = g.uid WHERE g.ggid=:ggid;');
        $stmt->bindValue(':ggid', $ggid);
        if($stmt->execute()){
            $result=$stmt->fetchAll();
            if (count($result) <=0) {
                DBLink::disconnect($bdd);
                return true;
            } else {
                DBLink::disconnect($bdd);
                return false;
            }
        }else{
            $message="Pas de groupement";
        }
       
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    return $result;
    DBLink::disconnect($bdd);
    }
   


   public function getParticipantOfGroupement($ggid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT u.* FROM grouper gr JOIN utilisateur u on gr.uid = u.uid WHERE  gr.ggid=:ggid AND u.estActif=1;');
        $stmt->bindValue(':ggid', $ggid);
        if($stmt->execute()){
            $result=$stmt->fetchAll(PDO::FETCH_OBJ);
        }else{
            $message="Pas de groupement";
        }
       
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    return $result;
    DBLink::disconnect($bdd);
    }

   public function changeGestionnaireGroupement($ggid,$uid,&$message){
    $result=null;
    $bdd=null;
    try{
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('UPDATE groupement SET uid=:uid WHERE  ggid=:ggid');
        $stmt->bindValue(':uid', $uid);
        $stmt->bindValue(':ggid', $ggid);
        if($stmt->execute()){
            $result=true;
            DBLink::disconnect($bdd);
        } else {
            return false;
            $message .= "Une erreur systeme est survenue";
        }
        $stmt = null;
    }catch(PDOException $e){
        $message = "Erreur connexion à la base de donnée";
    DBLink::disconnect($bdd);
    return $result;
    }
    DBLink::disconnect($bdd);
    return $result;
   }

   public function deleteParticipantGroupement($ggid,$uid,&$message){
    $result=null;
    $bdd= null;
    try {
        $bdd = DBLink::connect2db(MYDB, $message);

        $requete = $bdd->prepare("DELETE  FROM grouper WHERE ggid=:ggid AND uid=:uid");
        $requete->bindValue(":uid", $uid);
        $requete->bindValue(":ggid", $ggid);
    if( $requete->execute()){
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

    public function deleteGroupement($ggid,&$message){
        $result=null;
        $bdd= null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);

            $requete = $bdd->prepare("DELETE  FROM grouper WHERE ggid=:ggid;DELETE  FROM groupement WHERE ggid=:ggid;");
            $requete->bindValue(":ggid", $ggid);
        if( $requete->execute()){
            $message="Groupement supprimé avec succès";
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

    public function deleteAllGroupementOfGroupe($gid,&$message){
        $result=null;
        $bdd= null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);

            $requete = $bdd->prepare("DELETE  FROM grouper WHERE ggid=:ggid;DELETE  FROM groupement WHERE ggid=:ggid;");
            $requete->bindValue(":ggid", $ggid);
        if( $requete->execute()){
            $message="Groupement supprimé avec succès";
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

    public function diplayAllUserGroupement($groupement){
        $display='<h2>Composition du Groupement: '.$groupement->libelle.'</h2><article class="Table"><ul class="TableDate">';
        $message="";
        $alluser=GroupementRepository::getParticipantOfGroupement($groupement->ggid,$message);
        foreach($alluser as $user){
            $display.="<li>".$user->prenom.".".$user->nom[0]."</li>";
        }
       return $display.="</ul></article>";
    }


    public function displayFormAddUserToGroupement($tabUser,$userCurrent,$gid,&$message){
        $form=' <article><h2>Création</h2><form  method="post" enctype="multipart/form-data">
        <label for="nomGroupement">Nom du groupement: *</label><input id="nomGroupement" name="nomGroupement" type="text" >
        <label for="participantAdd">Participant du groupe: *</label>
        <ul class="Checkboxing">';
        foreach($tabUser as $user){
            if(!($user->uid== $userCurrent->uid)){
                if(GroupementRepository::userNotInAGroupementInAGroupe($gid,$user->uid,$message)){
                    $form.='<li><label><input type="radio" name="participantAdd" value="'.$user->uid.'">'.$user->prenom.'.'.$user->nom[0].'</label></li>';
                }
            }
        }
        return $form.='</ul>
        <input type="submit" name="creerGroupement" value="Créer un groupement" />
        <input class="Annuler" type="reset" name="annuler" value="Annuler" /></form> </article>';
    }


    public function displayAddUser($tabUserNotGroupement,$ggid,&$message){
        if(!empty($tabUserNotGroupement)){
        $form='<article><h2>Ajouter un participant du groupement</h2>
        <form  method="post" enctype="multipart/form-data">
        <label for="participantAddBis">Participant a ajouté au groupement: *</label>
        <ul class="Checkboxing">';
        foreach ($tabUserNotGroupement as $user){
                $form.= '<li><input type="radio" id="user" name="userAdd" value="'.$user->uid.'"><label for="userAdd">'.$user->prenom.'.'.$user->nom[0].'</label></li>';
        }
        return $form.='</ul><input  type="submit" name="AddParticipantGroupement" value="Ajout du particpant" /><input class="Annuler" type="reset"  value="Annuler" /></form> </article>';
        }else{
            return '<article><h2>Ajouter un participant du groupement</h2><p> Pas d\'utilisateur sans groupement dans le groupe</p></article>';
        }
    }

    public function displayFormForDelegation($tabUser,$userCurrent,&$message){
        $form='<article><h2>Délégation</h2><form  method="post" enctype="multipart/form-data">
        <label for="participantDelegate">Participant du groupe devenant gestionnaire: *</label>
        <ul class="Checkboxing">';
        foreach ($tabUser as $user){
            if(!($user->uid== $userCurrent->uid)){
                $form.= '<li><input type="radio" id="user" name="userDelegate" value="'.$user->uid.'"><label for="userDelegate">'.$user->prenom.'.'.$user->nom[0].'</label></li>';
            }
        }
        return $form.='</ul><input type="submit" name="changeGroupement" value="Changer le gestionnaire" /><input class="Annuler" type="reset" name="annuler" value="Annuler" /></form></article>';

    }

    public function displayFormDeleteParticipant($tabUser,$userCurrent,&$message){
        $form='<article><h2>Supprimer un participant du groupement</h2>
        <form  method="post" enctype="multipart/form-data">
        <label for="participantSupp">Participant du groupe à supprimer: *</label>
        <ul class="Checkboxing">';
        foreach ($tabUser as $user){
            if(!($user->uid== $userCurrent->uid)){
                $form.= '<li><input type="radio" id="user" name="userSupp" value="'.$user->uid.'"><label for="userSupp">'.$user->prenom.'.'.$user->nom[0].'</label></li>';
            }
        }
        return $form.='</ul><input class="Annuler" type="reset"  value="Annuler" /><input class="Supprimer" type="submit" name="suppParticipantGroupement" value="Suppression du particpant" />
        </form> </article>';

    }
    public function displayFormDeleteGroup($ggid,&$message){
        $form='<article><h2>Suppression du groupement</h2>
        <form  method="post" enctype="multipart/form-data">
            <input  type="hidden" name="groupementSupp" value="'.$ggid.'" />
            <input class="Supprimer" type="submit" name="suppGroupement" value="Suppression du Groupement" />
            </form> </article>';
        
        return $form;

    }

}