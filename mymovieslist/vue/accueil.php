<?php
// Auteur       : De Sousa Kevin
// Nom          : MyMoviesList
// Date         : 7 Juin 2017
// Page d'accueil

if (!isset($mvc))
{
    header("Location: ../index.php");
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
        <link rel="icon" type="image/png" href="./vue/img/icone.png" />
        <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="./vue/css/maCss.css">
        <title>MyMoviesList</title>
    </head>
    <body>
        <?php AfficherNav($_SESSION["log"],$_SESSION["pseudo"]); AfficherNotif($etat);?>
        <div class="container" align="center">
            <h1 class="mb-4 display-3">Accueil</h1>
            <h2 class="mb-4">Voici les films que les utilisateurs ont ajouté :</h2>
            <?php 
            AfficherAccueil($filmsAccueil,$triA,$triNA);
            AfficherBtnPages($limite, $page);
            ?>
        </div>
    </body>
</html>