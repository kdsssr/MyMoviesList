<?php
// Auteur       : De Sousa Kevin
// Nom          : MyMoviesList
// Date         : 14 Juin 2017
// Page d'insciption

if (!isset($mvc) && !($_SESSION["log"]))
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
        <h1 class="text-center display-3 mb-5 mt-5" >Créer un compte</h1>
        <div class="form-login container col-4">
            <form action="index.php" method="post" class="form-signin">
                <input type="text" id="inputName" name="pseudo" class="form-control mb-2" placeholder="Pseudo" required autofocus value="<?php echo $pseudo; ?>">
                <input type="password" name="mdp" id="inputPassword" class="form-control mb-2" placeholder="Mot de passe" required>
                <input type="password" name="mdpVerif" id="inputPassword" class="form-control mb-4" placeholder="Confirmation du mot de passe" required>
                <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit" name="inscrit">S'inscrire</button>
            </form>
        </div>
    </body>
</html>