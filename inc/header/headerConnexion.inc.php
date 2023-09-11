<!DOCTYPE html>
<html lang="fr">
<head id="head">
    <title>noDebt</title>
    <link rel="stylesheet" type="text/css"  href="css/style.css" />
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
<header>
    <a href="index.php"><div>no<span>Debt</span></div></a>
    <nav>
        <ul class ="Navigation">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="contacter.php">Contact</a></li>
            <li><a href="deconnexion.php">Déconnexion</a></li>
        </ul>
        <ul class ="Navigation">
            <li><span><?php echo $user->nom." ".$user->prenom;?></span></li>
            <li><a href="editionProfil.php">Édition-Profil</a></li>
            <li><a href="editionGroupe.php">Création/Édition-Groupe</a></li>
        </ul>
    </nav>
</header>
