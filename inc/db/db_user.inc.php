<?php
namespace User;

require_once('db_link.inc.php');
require_once('php/dataSecurity.php');
use DB\DBLink;
use PDO;
use PDOException;
use function dataSecurity\secureString;
use function dataSecurity\generateNewMDP;

setlocale(LC_TIME, 'fr_FR.utf8','fr');

class User{
    private $uid;
    private $courriel;
    private $nom;
    private $prenom;
    private $motPasse;
    private $estActif;

    public function __get($prop){
        return $this->$prop;
    }

    public function __construct($uid,$courriel,$nom,$prenom,$motPasse,$estActif){
        $this->uid=$uid;
        $this->courriel = $courriel;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->motPasse = $motPasse;
        $this->estActif = $estActif;
    }


}

class UserRepository{
    const TABLE_NAME ='utilisateur';

    public  function userConnexion($email,$motPasse,&$message){
        $bdd = DBLink::connect2db(MYDB,$message);

        if($bdd){
            $stmt=$bdd->prepare("SELECT * FROM ".self::TABLE_NAME." WHERE courriel=:courriel");
            $stmt->bindParam(':courriel',$email);
            $result =$stmt->execute();
            if($result){
                $user=$stmt->fetchObject();
                if(password_verify($motPasse,$user->motPasse)){
                    $_SESSION['user']=$user;
                    return true;
                }else {
                    $message="Mauvais mot de passe";
                    return false;
                }
            }else{
                return null;
            }
        }else {
            $message = 'Impossible de contacter la base de données.';
            DBLink::disconnect($bdd);
            return null;
        }
    }

    public function existsInDB($courriel, &$message){
        // Connexion a la BDD
        $result="";
        $bdd  = DBLink::connect2db(MYDB, $message);
        if ($bdd) {
            $stmt = $bdd->prepare("SELECT courriel FROM ".self::TABLE_NAME." WHERE courriel LIKE :courriel");
            $stmt->bindValue(':courriel', $courriel);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) >= 1) {
                DBLink::disconnect($bdd);
                return true;
            } else {
                $message="Votre email n'existe pas";
                DBLink::disconnect($bdd);
                return false;
            }
        } else {
            $message = 'Impossible de contacter la base de données.';
            DBLink::disconnect($bdd);
            return false;
        }

    }

    public function newMDP($courriel,&$mdp,&$message){
        $message = '';
       $mdp=generateNewMDP();
        $bdd  = DBLink::connect2db(MYDB, $message);
        if ($bdd) {
            $stmt = $bdd->prepare("UPDATE ".self::TABLE_NAME. " SET motPasse=:mdp WHERE courriel=:courriel");
            $stmt->bindValue(':courriel', $courriel);
            $stmt->bindValue(':mdp',  $mdp);
            if ($stmt->execute()) {
                $message = "Votre mot de passe a été changé";
                DBLink::disconnect($bdd);
                return true;
            } else {
                $message = "Votre mot de passe n' été pas changé";
                DBLink::disconnect($bdd);
                $mdp='';
                return false;
            }
        } else {
            $message = "Impossible d'établir une connexion avec la base de données.";
            DBLink::disconnect($bdd);
            return false;
        }

    }


    public function insertUserInDB($nom,$prenom,$email,$mdp,&$message) {
        // Connexion a la BDD
        $bdd  = DBLink::connect2db(MYDB, $message);
        if ($bdd) {
            $stmt = $bdd->prepare("INSERT INTO ".self::TABLE_NAME. "(uid, prenom, nom, motPasse, courriel, estActif) VALUES (null, :prenom, :nom, :motPasse, :courriel, 1);");
            $stmt->bindValue(':prenom', $prenom);
            $stmt->bindValue(':nom', $nom);
            $stmt->bindValue(':motPasse', password_hash($mdp,PASSWORD_DEFAULT));
            $stmt->bindValue(':courriel', $email);
            if ($stmt->execute()) {
                DBLink::disconnect($bdd);
                $message= "Vous êtes enregistré";
                return true;
            } else {
                DBLink::disconnect($bdd);
                $message= "Erreur lors de l'enregistrement";
                return false;
            }
        } else {
            $message = "Impossible d'établir une connexion avec la base de données.";
            DBLink::disconnect($bdd);
            return false;
        }
    }

    public function updateUserInDB($id,$nom,$prenom,$courriel,&$message) {
        $message = '';
        $bdd  = DBLink::connect2db(MYDB, $message);
        if ($bdd) {
            $stmt = $bdd->prepare("UPDATE ".self::TABLE_NAME. " SET nom=:nom,prenom=:prenom,courriel=:courriel WHERE uid=:uid");
            $stmt->bindValue(':uid', $id);
            $stmt->bindValue(':prenom', $prenom);
            $stmt->bindValue(':nom', $nom);
            $stmt->bindValue(':courriel', $courriel);
            if ($stmt->execute()) {
                $message = "Votre Profil a été modifié";
                DBLink::disconnect($bdd);
                return true;
            } else {
                $message = "Votre Profil n'a pas été modifié";
                DBLink::disconnect($bdd);
                return false;
            }
        } else {
            $message = "Impossible d'établir une connexion avec la base de données.";
            DBLink::disconnect($bdd);
            return false;
        }
    }

        public function checkIfUserActif($courriel,&$message){
        // Connexion a la BDD
        $result="";
        $bdd  = DBLink::connect2db(MYDB, $message);
        if ($bdd) {
            $stmt = $bdd->prepare("SELECT courriel FROM ".self::TABLE_NAME." WHERE courriel LIKE :courriel AND estActif=0");
            $stmt->bindValue(':courriel', $courriel);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) >= 1) {
                DBLink::disconnect($bdd);
                $message="Un mail de récupération vous a été envoyé";
                return true;
            } else {
                $message="L'utilisateur est Actif";
                DBLink::disconnect($bdd);
                return false;
            }
        } else {
            $message = 'Impossible de contacter la base de données.';
            DBLink::disconnect($bdd);
            return false;
        }
    }

    

    public function UpdateUserActifOrNotByMail($courriel,$est_actif,&$message){
        $bdd  = DBLink::connect2db(MYDB, $message);
        if($est_actif){
            //le passe inactif
            if ($bdd) {
                $stmt = $bdd->prepare("UPDATE ".self::TABLE_NAME." SET estActif=0 WHERE courriel=:courriel");
                $stmt->bindValue(':courriel', $courriel);
                if ($stmt->execute()) {
                    $message="Profil mis Inactif";
                    return true;
                } 
                $message="Erreur lors de la modification";
                DBLink::disconnect($bdd);
                    return false;
            }else {
                $message = "Impossible d'établir une connexion avec la base de données.";
                DBLink::disconnect($bdd);
                return false;
            }
        }else{
            //le passe actif
            if ($bdd) {
                $stmtBis = $bdd->prepare("UPDATE ".self::TABLE_NAME." SET estActif=1 WHERE courriel=:courriel");
                $stmtBis->bindValue(':courriel', $courriel);
                if ($stmtBis->execute()) {
                    $message="Profil mis Actif";
                    return true;
                } 
                DBLink::disconnect($bdd);
                    return false;
            }else {
                $message = "Impossible d'établir une connexion avec la base de données.";
                DBLink::disconnect($bdd);
                return false;
            }
        }
    }
    
    public function userAccesToGroupe($gid,&$message){
    $result=false;
    $bdd  = DBLink::connect2db(MYDB, $message);
    if ($bdd) {
        $stmt = $bdd->prepare("SELECT distinct u.uid FROM utilisateur u JOIN participer p on u.uid = p.uid join depense d on u.uid = d.uid LEFT join versement v on u.uid = v.uid LEFT JOIN facture f on d.did = f.did WHERE d.gid=:gid || p.gid=:gid|| v.gid=:gid
        ");
        $stmt->bindValue(':gid', $gid);
        if($stmt->execute()){
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            DBLink::disconnect($bdd);
            return $result;
        } else {
            DBLink::disconnect($bdd);
            return false;
        }
    } else {
        $message = 'Impossible de contacter la base de données.';
        DBLink::disconnect($bdd);
        return false;
    }

    }

    public function userInNotSoldeGroupe($uid,&$message){
        $result=false;
        $bdd  = DBLink::connect2db(MYDB, $message);
        if ($bdd) {
            $stmt = $bdd->prepare("SELECT u.uid FROM ".self::TABLE_NAME." u JOIN groupe g on u.uid = g.uid JOIN participer p on g.gid = p.gid WHERE u.uid=:uid AND p.estConfirmer=1 AND g.estSolder=0");
            $stmt->bindValue(':uid', $uid);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) <= 1) {
                $message ='Vous êtes présent dans un groupe non soldé';
                DBLink::disconnect($bdd);
                return true;
            } else {
                DBLink::disconnect($bdd);
                return false;
            }
        } else {
            $message = 'Impossible de contacter la base de données.';
            DBLink::disconnect($bdd);
            return false;
        }
    }


    public function deleteUserByMail($courriel){
        $message='';
        $bdd  = DBLink::connect2db(MYDB, $message);
        if ($bdd) {
            $stmt = $bdd->prepare("DELETE FROM".self::TABLE_NAME."WHERE courriel = :courriel");
            $stmt->bindValue(':courriel', $courriel);
            if ($stmt->execute()) {
                return true;
            } 
            DBLink::disconnect($bdd);
                return false;
        }else {
            $message = "Impossible d'établir une connexion avec la base de données.";
            DBLink::disconnect($bdd);
            return false;
        }
    }


    public function getUserById($id) {
        $result=null;
        $bdd    = null;
        $message = '';
        try {
            $bdd  = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT uid,courriel,nom,prenom,motPasse,estActif FROM ".self::TABLE_NAME. " WHERE uid=:uid");
            $stmt->bindValue(':uid', $id);
            if ($stmt->execute()){
                $stmt->setFetchMode(PDO::FETCH_CLASS| PDO::FETCH_PROPS_LATE,'User');
                 $result= $stmt->fetchObject();
            }
        } catch (PDOException $e) {
            $message = "Erreur connexion à la base de donnée";
        }
        DBLink::disconnect($bdd);
        return $result;
    }
    public function getUserByMail($courriel) {
        $result=null;
        $bdd    = null;
        $message = '';
        try {
            $bdd  = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT uid,courriel,nom,prenom,motPasse,estActif FROM ".self::TABLE_NAME. " WHERE courriel=:courriel");
            $stmt->bindValue(':courriel', $courriel);
            if ($stmt->execute()){
                $stmt->setFetchMode(PDO::FETCH_CLASS| PDO::FETCH_PROPS_LATE,'User');
                return $result= $stmt->fetchObject();
            }
        } catch (PDOException $e) {
            $message = "Erreur connexion à la base de donnée";
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    public function getAllUserNames(&$message) {
        $result = array();
        $bdd    = null;

        try {
            $bdd  = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT uid, prenom, nom FROM ".self::TABLE_NAME);
            if ($stmt->execute()){
                $result = $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            $message = "Erreur connexion à la base de donnée";
        }
        DBLink::disconnect($bdd);
        return $result;
    }

}

?>