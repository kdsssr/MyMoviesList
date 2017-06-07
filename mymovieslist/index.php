<?php
// Auteur       : De Sousa Kevin
// Nom          : MyMoviesList
// Date         : 6 Juin 2017
// Contrôleur

session_start();

require './vue/fonctionsAffichage.php';
require './modele/fonctionsAPI.php';
require './modele/fonctionsDB.php';

$etat = "";
$mvc = true;

if (isset($_SESSION["idUtilisateur"]))
{
    $etat = "Vous êtes connecté.";
    $_SESSION["log"] = true;
}
else
{
    $etat = "Vous n'êtes pas connecté.";
    $_SESSION["log"] = false;
    $_SESSION["pseudo"] = "";
}

if (isset($_POST["connecter"]) && !(isset($_SESSION["idUtilisateur"])))
{
    include_once './vue/connexion.php';
    exit();
}

if (isset($_REQUEST["logger"]) && isset($_REQUEST["mdp"]))
{
    $Utilisateur = VerifierLogin($_REQUEST["pseudo"], sha1($_REQUEST["mdp"]));
    
    if ($Utilisateur)
    {
        $etat = "Vous êtes connecté";
        $_SESSION["idUtilisateur"] = $Utilisateur[0]["idUtilisateur"];
        $_SESSION["pseudo"] = $Utilisateur[0]["pseudo"];
        $_SESSION["log"] = true;
    } 
    else
    {
        $etat = "Echec d'identification";
    }
}

include_once './vue/accueil.php';
exit();

?>
