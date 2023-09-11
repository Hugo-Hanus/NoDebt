<?php
include ("inc/header/header.inc.php");
require_once("php/dataSecurity.php");
use function dataSecurity\secureString;
    $mailCompletion="";
    if(isset($user)){
        $mailCompletion=$user->courriel;
    }
    if(isset($_POST['submit'])){
        if(!empty(trim($_POST['mail'])) && !empty(trim($_POST['message'])) && !empty(trim($_POST['sujet']))){
            if(filter_var(secureString($_POST['mail']), FILTER_VALIDATE_EMAIL)){
        $email = strip_tags($_POST['mail']);
         $messageMail = strip_tags($_POST['message']);
        $sujet = strip_tags($_POST['sujet']);
        $to = "h.hanus@student.helmo.be";
        $headers = 'From: Nodebt<'.$email.">\r\n";
        $headers.='Cc:<'.$email.">\r\n";
        $headers.= 'X-Mailer: PHP/' . phpversion();
         if(mail($to, $sujet, $messageMail, $headers)){
         $message ="Mail envoyé";
          }else{
        $message="Erreur lors de l'envoie de mail" ;
        }
        }
        }else{$message="Format mail invalide";}
    }  
?>
<main>
    <section class="Profil">
    <h1>Contacter un admin</h1>
    <div class=<?php if(empty($message)){echo "Cache";}else{echo "Message";}?>><span><?php echo $message?></span></div>
        <article>
            <form action="contacter.php" method="post">
                    <label for="sujet">Sujet: *</label><input id="sujet" name="sujet" type="text" required>
                    <label for="mail">Courriel: *</label><input id="mail" name="mail" type="email" value="<?php echo $mailCompletion; ?>" required>
                    <label for="message">Message: *</label><textarea id="message" name="message" rows="5" cols="100" maxlength="360" required></textarea>
                    <input type="submit" name="submit" value="Contacter">
                    <input type="reset" name="reset" value="Réinitialiser">
            </form>
        </article>
    </section>
</main>
<?php
include ("inc/footer/footer.inc.php");
?>