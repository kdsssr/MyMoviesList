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
$limite = 10;

if (isset($_SESSION["idUtilisateur"]))
{
    $_SESSION["log"] = true;
}
else
{
    $_SESSION["log"] = false;
    $_SESSION["pseudo"] = "";
}

// Vérifie si l'utilisateur à cliquer sur le bouton connecter et qu'il ne soit pas connecté et le dirige si c'est le cas
if (isset($_POST["connecter"]) && !($_SESSION["log"]))
{
    include_once './vue/connexion.php';
    exit();
}

// Vérifie si l'utilisateur à cliquer sur le bouton déconnecter et qu'il ne soit pas déconnecté et le déconnecte si c'est le cas
if (isset($_POST["deconnecter"]) && $_SESSION["log"])
{
    session_destroy();
    $_SESSION = array();
    $_SESSION["log"] = false;
    $_SESSION["pseudo"] = "";
}

if (isset($_REQUEST["logger"]) && isset($_REQUEST["mdp"]))
{
    $Utilisateur = VerifierLogin($_REQUEST["pseudo"], sha1($_REQUEST["mdp"]));
    
    if ($Utilisateur)
    {
        $_SESSION["idUtilisateur"] = $Utilisateur[0]["idUtilisateur"];
        $_SESSION["pseudo"] = $Utilisateur[0]["pseudo"];
        $_SESSION["log"] = true;
    } 
}

if (isset($_REQUEST["rechercheTitre"])&& isset($_REQUEST["categorie"]))
{
    if ($_REQUEST["categorie"] == "film")
    {
        $infosFilm = RechercheFilmParTitre($_REQUEST["rechercheTitre"]);

        $typeDejaListe = "pas";

        if ($infosFilm->Response == "True")
        {
            $commentairesFilm = GetCommentaire($infosFilm->imdbID);
            
            if ($_SESSION["log"])
            {
                $infoListe = GetTypeListe($_SESSION["idUtilisateur"], $infosFilm->imdbID);

                if ($infoListe)
                {
                    $typeDejaListe = $infoListe[0]["typeListe"];
                }
            }
        }
        else
        {
            $commentairesFilm = null;
        }

        include_once './vue/pagefilm.php';
        exit();
    }
}
else
{
     $infosFilm = null;
     $commentairesFilm = null;
}

if (isset($_GET["f"]))
{
    $infosFilm = RechercheFilmParId($_GET["f"]);
    
    $typeDejaListe = "pas";
    
    if ($infosFilm->Response == "True")
    {
        $commentairesFilm = GetCommentaire($infosFilm->imdbID);

        if ($_SESSION["log"])
        {
            $infoListe = GetTypeListe($_SESSION["idUtilisateur"], $infosFilm->imdbID);

            if ($infoListe)
            {
                $typeDejaListe = $infoListe[0]["typeListe"];
            }
        }
    }
    else
    {
        $commentairesFilm = null;
    }
    
    include_once './vue/pagefilm.php';
    exit();
}
else
{
     $infosFilm = null;
     $commentairesFilm = null;
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
        
        $listeActuelle = GetTypeListe($_SESSION["idUtilisateur"], $filmAjoute->imdbID);
        
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
            UpdateListe($_SESSION["idUtilisateur"], $filmAjoute->imdbID, $_POST["typeListe"]);
        }
        
        $infosFilm = RechercheFilmParTitre($filmAjoute->Title);

        $typeDejaListe = "pas";

        if ($infosFilm->Response == "True")
        {
            $commentairesFilm = GetCommentaire($infosFilm->imdbID);

            if ($_SESSION["log"])
            {
                $infoListe = GetTypeListe($_SESSION["idUtilisateur"], $infosFilm->imdbID);

                if ($infoListe)
                {
                    $typeDejaListe = $infoListe[0]["typeListe"];
                }
            }
        }
        else
        {
            $commentairesFilm = null;
        }

        include_once './vue/pagefilm.php';
        exit();
        
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
    $listeFilms = GetFilmListe($_SESSION["idUtilisateur"], $_GET["type"]);
    $typeListe = $_GET["type"];
    $perso = true;
    $nom = $_SESSION["pseudo"];
    include_once './vue/liste.php';
    exit();
}

if (isset($_POST["type"])&& $_SESSION["log"])
{
    $listeFilms = GetFilmListe($_POST["utilisateurListe"], $_POST["type"]);
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
            $nom = GetNomUtilisateur($_POST["utilisateurListe"])[0]["pseudo"];
        }
    }
    else
    {
        $perso = false;
        $nom = GetNomUtilisateur($_POST["utilisateurListe"])[0]["pseudo"];
    }
    
    include_once './vue/liste.php';
    exit();
}

if (isset($_POST["filmMaJ"]))
{
    $typeListeAvant = GetTypeListe($_SESSION["idUtilisateur"], $_POST["filmMaJ"]);
    
    if ($typeListeAvant[0]["typeListe"] == "vu")
    {
        UpdateListe($_SESSION["idUtilisateur"], $_POST["filmMaJ"], "aVoir");
        
        $listeFilms = GetFilmListe($_SESSION["idUtilisateur"], $typeListeAvant[0]["typeListe"]);
        $typeListe = $typeListeAvant[0]["typeListe"];
        $perso = true;
        $nom = $_SESSION["pseudo"];
        include_once './vue/liste.php';
        exit();
    }
    else if ($typeListeAvant[0]["typeListe"] == "aVoir")
    {
        UpdateListe($_SESSION["idUtilisateur"], $_POST["filmMaJ"], "vu");
        
        $listeFilms = GetFilmListe($_SESSION["idUtilisateur"], $typeListeAvant[0]["typeListe"]);
        $typeListe = $typeListeAvant[0]["typeListe"];
        $perso = true;
        $nom = $_SESSION["pseudo"];
        include_once './vue/liste.php';
        exit();
    }
    
}

if (isset($_POST["suppFilm"]))
{
    $typeListeAvant = GetTypeListe($_SESSION["idUtilisateur"], $_POST["suppFilm"]);
    
    if ($typeListeAvant)
    {
        DeleteFilmListe($_SESSION["idUtilisateur"], $_POST["suppFilm"]);

        $listeFilms = GetFilmListe($_SESSION["idUtilisateur"], $typeListeAvant[0]["typeListe"]);
        $typeListe = $typeListeAvant[0]["typeListe"];
        $perso = true;
        $nom = $_SESSION["pseudo"];
        include_once './vue/liste.php';
        exit();
    }
}

if (isset($_REQUEST["commenter"]) && $_SESSION["log"])
{
    $filmCommente = RechercheFilmParId($_POST["filmID"]);
    
    if ($filmCommente->Response == "True")
    {
        if ($_REQUEST["commentaire"] != "")
        {
            AjouterCommentaire($_SESSION["idUtilisateur"], $_POST["filmID"], $_REQUEST["commentaire"]);
        }
        
        $infosFilm = RechercheFilmParTitre($filmCommente->Title);
    
        $typeDejaListe = "pas";

        if ($infosFilm->Response == "True")
        {
            $commentairesFilm = GetCommentaire($infosFilm->imdbID);

            if ($_SESSION["log"])
            {
                $infoListe = GetTypeListe($_SESSION["idUtilisateur"], $infosFilm->imdbID);

                if ($infoListe)
                {
                    $typeDejaListe = $infoListe[0]["typeListe"];
                }
            }
        }
        else
        {
            $commentairesFilm = null;
        }

        include_once './vue/pagefilm.php';
        exit();
        
    }
}

if (isset($_GET["page"]))
{
    $page = $_GET["page"];
}
else
{
    $page = 1;
}

if (!(isset($_SESSION["tri"])))
{
    $_SESSION["tri"] = "anc";
}

if (isset($_GET["tri"]))
{
    switch ($_GET["tri"])
    {
        case "ac":
            $_SESSION["tri"] = "ac";
            break;
        case "anc":
            $_SESSION["tri"] = "anc";
            break;
        case "nac":
            $_SESSION["tri"] = "nac";
            break;
        case "nanc":
            $_SESSION["tri"] = "nanc";
            break;
            break;
        default:
            $_SESSION["tri"] = "ac";
            break;
    }
}

switch ($_SESSION["tri"])
{
    case "ac":
        $filmsAccueil = GetFilm($page,$limite,"nomFilm","asc");
        $triA = "nc";
        $triNA = "nc";
        break;
    case "anc":
        $filmsAccueil = GetFilm($page,$limite,"nomFilm","desc");
        $triA = "c";
        $triNA = "nc";
        break;
    case "nac":
        $filmsAccueil = GetFilm($page,$limite,"nbFilms","asc");
        $triA = "nc";
        $triNA = "nc";
        break;
    case "nanc":
        $filmsAccueil = GetFilm($page,$limite,"nbFilms","desc");
        $triA = "nc";
        $triNA = "c";
        break;
    default:
        $filmsAccueil = GetFilm($page,$limite,"nomFilm","asc");
        $triA = "nc";
        $triNA = "nc";
        break;
}

include_once './vue/accueil.php';
exit();

?>
