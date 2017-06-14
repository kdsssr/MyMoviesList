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

// Vérifie si l'utilisateur a cliqué sur le bouton connecter et qu'il ne soit pas connecté, si c'est bon il est dirigé vers cette page
if (isset($_REQUEST["connecter"]) && !($_SESSION["log"]))
{
    include_once './vue/connexion.php';
    exit();
}

// Vérifie si l'utilisateur a cliqué sur le bouton s'inscrire et qu'il ne soit pas connecté, si c'est bon il est dirigé vers cette page
if (isset($_REQUEST["inscrire"]) && !($_SESSION["log"]))
{
    $pseudo = "";
    include_once './vue/inscription.php';
    exit();
}

// Vérifie si l'utilisateur a cliqué sur le bouton déconnecter et qu'il ne soit pas déconnecté, si c'est bon il est déconnecté
if (isset($_REQUEST["deconnecter"]) && $_SESSION["log"])
{
    session_destroy();
    $_SESSION = array();
    $_SESSION["log"] = false;
    $_SESSION["pseudo"] = "";
}

// Vérifie si l'utilisateur a envoyé le formulaire d'inscription
if (isset($_REQUEST["inscrit"]))
{
    if (isset($_REQUEST["mdp"]) && isset($_REQUEST["mdpVerif"]) && isset($_REQUEST["pseudo"]))
    {
        if (!(VerifierNomUtilisateur($_REQUEST["pseudo"])))
        {
            if (preg_match('`^([a-zA-Z0-9]{2,20})$`', $_REQUEST["pseudo"]))
            {
                if ($_REQUEST["mdp"] == $_REQUEST["mdpVerif"])
                {
                    $_SESSION["idUtilisateur"] = AjouterUtilisateur($_REQUEST["pseudo"], sha1($_REQUEST["mdp"]));
                    $_SESSION["pseudo"] = $_REQUEST["pseudo"];
                    $_SESSION["log"] = true;
                }
                else 
                {
                    $pseudo = $_REQUEST["pseudo"];
                    $etat = "Les mots de passes ne correspondent pas.";
                    include_once './vue/inscription.php';
                    exit();
                }
            }
            else 
            {
                $etat = "Le pseudo est incorrecte. ( 2 à 20 caractères avec lettres et chiffres uniquement) ";
                $pseudo = "";
                include_once './vue/inscription.php';
                exit();
            }
        }
        else 
        {
            $pseudo = "";
            $etat = "Ce pseudo est déjà utilisé.";
            include_once './vue/inscription.php';
            exit();
        }
    }
    else
    {
        $pseudo = "";
        $etat = "Vous avez oublié de remplir certains champs.";
        include_once './vue/inscription.php';
        exit();
    }
}

// Vérifie si l'utilisateur a envoyé le formulaire de connexion
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

// Vérifie si l'utilisateur a tapé quelque chose dans la barre de recherche
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

// Vérifie si l'utilisateur à cliqué sur un lien menant vers un film
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

// Vérifie si l'utilisateur a voulu ajouter un film dans une de ses listes
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

// Vérifie si l'utilisateur veut accéder à ses listes, il faut donc qu'il soit connecté
if (isset($_GET["type"])  && $_SESSION["log"])
{
    if ($_GET["type"] == "vu" || $_GET["type"] == "aVoir")
    {
        $listeFilms = GetFilmListe($_SESSION["idUtilisateur"], $_GET["type"]);
        $typeListe = $_GET["type"];
        $perso = true;
        $nom = $_SESSION["pseudo"];
        include_once './vue/liste.php';
        exit();
    }
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

// Vérifie si l'utilisateur veut mettre à jour un film de la liste
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

// Vérifie si l'utilisateur veut supprimer un film de la liste
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

// Vérifie si l'utilisateur veut commenter un film
if (isset($_REQUEST["commenter"]) && $_SESSION["log"])
{
    $filmCommente = RechercheFilmParId($_POST["filmID"]);
    
    if ($filmCommente->Response == "True")
    {
        $commentaire = filter_var($_REQUEST["commentaire"], FILTER_SANITIZE_STRING);
        
        if ($commentaire != "")
        {
            AjouterCommentaire($_SESSION["idUtilisateur"], $_POST["filmID"], $commentaire);
            $_REQUEST = null;
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
// Vérifie si l'u
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

// Vérifie si l'utilisateur à cliqué sur l'un des deux boutons de tri
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

// Sélectionne le bon tri et récupère les films avec les bons tris en fonction de la variable tri dans la session
switch ($_SESSION["tri"])
{
    case "ac":
        $filmsAccueil = GetFilm($page,$limite,"nomFilm","asc");
        $triA = "nc";
        $triNA = "nc";
        include_once './vue/accueil.php';
        exit();
        break;
    case "anc":
        $filmsAccueil = GetFilm($page,$limite,"nomFilm","desc");
        $triA = "c";
        $triNA = "nc";
        include_once './vue/accueil.php';
        exit();
        break;
    case "nac":
        $filmsAccueil = GetFilm($page,$limite,"nbFilms","asc");
        $triA = "nc";
        $triNA = "nc";
        include_once './vue/accueil.php';
        exit();
        break;
    case "nanc":
        $filmsAccueil = GetFilm($page,$limite,"nbFilms","desc");
        $triA = "nc";
        $triNA = "c";
        include_once './vue/accueil.php';
        exit();
        break;
    default:
        $filmsAccueil = GetFilm($page,$limite,"nomFilm","asc");
        $triA = "nc";
        $triNA = "nc";
        include_once './vue/accueil.php';
        exit();
        break;
}


?>
