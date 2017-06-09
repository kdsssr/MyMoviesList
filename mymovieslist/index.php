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

if (isset($_POST["connecter"]) && !($_SESSION["log"]))
{
    include_once './vue/connexion.php';
    exit();
}

if (isset($_POST["deconnecter"]) && $_SESSION["log"])
{
    session_destroy();
    $_SESSION = array();
    $etat = "Vous êtes déconnecté";
    $_SESSION["log"] = false;
    $_SESSION["pseudo"] = "";
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

if (isset($_REQUEST["rechercheTitre"]))
{
    $infosFilm = RechercheFilmParTitre($_REQUEST["rechercheTitre"]);
    
    $typeDejaListe = "pas";
    
    if ($infosFilm->Response == "True" && $_SESSION["log"])
    {
        $infoListe = getTypeListe($_SESSION["idUtilisateur"], $infosFilm->imdbID);
        
        if ($infoListe)
        {
            $typeDejaListe = $infoListe[0]["typeListe"];
        }
    }
    
    include_once './vue/pagefilm.php';
    exit();
}
else
{
     $infosFilm = null;
}

if (isset($_GET["f"]))
{
    $infosFilm = RechercheFilmParId($_GET["f"]);
    $infoListe = getTypeListe($_SESSION["idUtilisateur"], $infosFilm->imdbID);
    
    if ($infoListe)
    {
        $typeDejaListe = $infoListe[0]["typeListe"];
    }
    else 
    {
        $typeDejaListe = "pas";
    }
    include_once './vue/pagefilm.php';
    exit();
}
else
{
     $infosFilm = null;
}

if (isset($_POST["typeListe"]) && $_SESSION["log"])
{
    
    $filmAjoute = RechercheFilmParId($_POST["filmID"]);
    
    if ($filmAjoute->Response == "True" && ($_POST["typeListe"] == "vu" || $_POST["typeListe"] == "aVoir"))
    {
        
        if (!(VerifierFilmExiste($filmAjoute->imdbID)))
        {
            AjouterFilmBDD($filmAjoute->imdbID,$filmAjoute->Title);
        }
        
        $listeActuelle = getTypeListe($_SESSION["idUtilisateur"], $filmAjoute->imdbID);
        
        if (!($listeActuelle))
        {
            AjouterFilmListe($_SESSION["idUtilisateur"], $filmAjoute->imdbID, $_POST["typeListe"]);
            $etat = "Le film " . $filmAjoute->Title . " a été ajouté dans la liste " . $_POST["typeListe"];
        }
        else if ($listeActuelle[0]["typeListe"] == $_POST["typeListe"])
        {
            $etat = "Ce film est déjà dans votre liste.";
        }
        else if ($listeActuelle[0]["typeListe"] != $_POST["typeListe"])
        {
            $etat = "Ce film a été déplacé de liste.";
            updateListe($_SESSION["idUtilisateur"], $filmAjoute->imdbID, $_POST["typeListe"]);
        }
    }
    else
    {
        $etat = "Le film que vous avez essayé de rajouter n'existe pas.";
    }
}
else if (!($_SESSION["log"])&& isset($_POST["typeListe"]))
{
    include_once './vue/connexion.php';
    exit();
}

if (isset($_GET["type"])  && $_SESSION["log"])
{
    $listeFilms = getFilmListe($_SESSION["idUtilisateur"], $_GET["type"]);
    $typeListe = $_GET["type"];
    $perso = true;
    $nom = $_SESSION["pseudo"];
    include_once './vue/liste.php';
    exit();
}

if (isset($_POST["type"])&& $_SESSION["log"])
{
    $listeFilms = getFilmListe($_POST["utilisateurListe"], $_POST["type"]);
    $typeListe = $_POST["type"];
    
    if (isset($_SESSION["idUtilisateur"]))
    {
        if ($_SESSION["idUtilisateur"] == $_POST["utilisateurListe"])
        {
            $perso = true;
            $nom = $_SESSION["pseudo"];
        }
        else
        {
            $perso = false;
            $nom = getNomUtilisateur($_POST["utilisateurListe"])[0]["pseudo"];
        }
    }
    else
    {
        $perso = false;
        $nom = getNomUtilisateur($_POST["utilisateurListe"])[0]["pseudo"];
    }
    
    include_once './vue/liste.php';
    exit();
}

if (isset($_POST["filmMaJ"]))
{
    $typeListeAvant = getTypeListe($_SESSION["idUtilisateur"], $_POST["filmMaJ"]);
    
    if ($typeListeAvant[0]["typeListe"] == "vu")
    {
        updateListe($_SESSION["idUtilisateur"], $_POST["filmMaJ"], "aVoir");
    }
    else if ($typeListeAvant[0]["typeListe"] == "aVoir")
    {
        updateListe($_SESSION["idUtilisateur"], $_POST["filmMaJ"], "vu");
    }
}

if (isset($_POST["suppFilm"]))
{
    DeleteFilmListe($_SESSION["idUtilisateur"], $_POST["suppFilm"]);
}

include_once './vue/accueil.php';
exit();

?>
