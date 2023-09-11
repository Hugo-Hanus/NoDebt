<?php
include ("inc/header/header.inc.php");
require_once('php/dataSecurity.php');
use function dataSecurity\secureString;
    $message='';
    if(isset($_POST['inscription'])){
        if((!empty(trim($_POST['nom']))) && (!empty(trim($_POST['prenom']))) && (!empty(trim($_POST['mail']))) && (!empty(trim($_POST['mdp']))) && (!empty(trim($_POST['vmdp']))) && empty($_POST['spam'])){
          $nom = secureString($_POST['nom']);
          $prenom = secureString($_POST['prenom']);
          $email = secureString($_POST['mail']);
          $mdp = secureString($_POST['mdp']);
          $vmdp = secureString($_POST['vmdp']);
          if($mdp == $vmdp){
              if($repoUser->existsInDB($email,$message)){
                  $message = "Cette email existe déjà";
              }else{
            if($repoUser->insertUserInDB($nom,$prenom,$email,$mdp,$message)){
              $message = 'Votre compte a bien été créé';
              $_SESSION['user']= ($repoUser->getUserByMail($email));
            }else{
              $message = 'Compte non créé';
            }}
          }else{
            $message = 'Pas le même mot de passe';
          }
          }
      }
?>
<main>
    <section>
        <h1>Formulaire d'Inscription :</h1>
        <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
        <article>
            <form  method="post" enctype="multipart/form-data">
                    <label class="Cache"><input type="text" name="spam" value=""/></label>
                    <label for="nom">Nom : *</label><input id="nom" name="nom" type="text"  required>
                    <label for="prenom">Prénom: *</label><input id="prenom" name="prenom" type="text" required>
                    <label for="mail">Courriel: *</label><input id="mail" name="mail" type="email" required>
                    <label for="mdp">Mot de Passe : *</label><input id="mdp" name="mdp" type="password" required>
                    <label for="vmdp">Confirmation Mot de Passe : *</label><input id="vmdp" name="vmdp" type="password" required>
                    <input type="submit" name="inscription" value="Inscription"  />
        </article>
    </section>
</main>
<?php
include ("inc/footer/footer.inc.php");
?>