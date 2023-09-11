<?php
session_start();
require_once 'inc/db/db_user.inc.php';
require_once 'php/groupe.php';
require_once 'php/depense.php';
require_once 'php/dataSecurity.php';
use User\User;
use User\UserRepository;
use Groupe\Groupe;
use Groupe\GroupeRepository;
use Depense\Depense;
use Depense\DepenseRepository;
use function dataSecurity\secureString;
date_default_timezone_set('Europe/Brussels');
$repoUser = new UserRepository();
$repoGroupe = new GroupeRepository();
$repoDepense = new DepenseRepository();
$message='';
if(isset($_SESSION['user'])&&(!empty($_SESSION))){
    $user=$_SESSION['user'];
    include('headerConnexion.inc.php');
}else{
    include('headerInscription.inc.php');
}

?>