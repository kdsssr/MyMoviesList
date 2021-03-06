<?php
// Auteur       : De Sousa Kevin
// Nom          : MyMoviesList
// Date         : 16 Juin 2017
// Contrôleur

session_start();

require './vue/fonctionsAffichage.php';
require './modele/fonctionsAPI.php';
require './modele/fonctionsDB.php';

$etat = "";         // Notifications
$mvc = true;        // Variable qui indique qu'on passe par le contrôleur
$limite = 8;       // Limite de films par page à l'accueil
$recherche = "";    // La dernière recherche effectué

if (isset($_SESSION["idUtilisateur"]))
{
    $_SESSION["log"] = true;
}
else
{
    $_SESSION["log"] = false;
}

// Vérifie si l'utilisateur a cliqué sur le bouton connecter et qu'il ne soit pas connecté, si c'est bon il est dirigé vers cette page
if (isset($_REQUEST["connecter"]) && !($_SESSION["log"]))
{
    $pseudo = "";
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
}

// Vérifie si l'utilisateur a envoyé le formulaire d'inscription
if (isset($_REQUEST["inscrit"]))
{
    if (isset($_REQUEST["mdp"]) && isset($_REQUEST["mdpVerif"]) && isset($_REQUEST["pseudo"]))
    {
        // J'utilise la fonction VerifierNomUtilisateur pour voir si l'utilisateur a choisit un pseudo déjà utilisé
        if (!(VerifierNomUtilisateur($_REQUEST["pseudo"])))
        {
            // Le pseudo ne doit être composé que de lettres ou de chiffres
            if (preg_match('`^([a-zA-Z0-9]{2,20})$`', $_REQUEST["pseudo"]) && !(empty($_REQUEST["mdp"])))
            {
                // Vérifie si l'utilisateur a bien confirmé le mot de passe
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
                $etat = "Le pseudo est incorrecte ( 2 à 20 caractères avec lettres et chiffres uniquement) ou mot de passe vide.";
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
    else
    {
        $pseudo = $_REQUEST["pseudo"];
        $etat = "Pseudo ou mot de passe incorrecte.";
        include_once './vue/connexion.php';
        exit();
    }
}

// Vérifie si l'utilisateur a cliqué sur le profil d'un utilisateur ou qu'on veuille aller sur son profil
if (isset($_GET["profil"]))
{
    if ($_SESSION["log"] && $_GET["profil"] == "p")
    {
        $_SESSION["utilisateurListe"] = $_SESSION["idUtilisateur"];
        $perso = 0;
        include_once './vue/profil.php';
        exit();
    }
    else
    {
        // Filtre la recherche qu'a recherché l'utilisateur pour éviter des erreurs
        $infosUtilisateur = VerifierNomUtilisateur(filter_var($_GET["profil"], FILTER_SANITIZE_STRING));
        
        if ($infosUtilisateur)
        {
            $_SESSION["utilisateurListe"] = $infosUtilisateur[0]["idUtilisateur"];
            $pseudo = $infosUtilisateur[0]["pseudo"];
            if ($_SESSION["log"] && $_SESSION["utilisateurListe"] == $_SESSION["idUtilisateur"])
            {
                $perso = 0;
            }
            else
            {
                $perso = 1;
            }
            
        }
        else 
        {
            $perso = 2;
        }
        
        include_once './vue/profil.php';
        exit();
    }
}

// Vérifie si l'utilisateur a tapé quelque chose dans la barre de recherche
if (isset($_REQUEST["recherche"])&& isset($_REQUEST["categorie"]))
{
    if ($_REQUEST["categorie"] == "film")
    {
        // Filtre la recherche qu'a recherché l'utilisateur pour éviter des erreurs
        $infosFilm = RechercheFilmParTitre(filter_var($_REQUEST["recherche"], FILTER_SANITIZE_STRING));

        $typeDejaListe = "pas";
        
        if (!(is_null($infosFilm)) && $infosFilm->Response == "True")
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
        
        // Filtre la recherche qu'a recherché l'utilisateur pour éviter des erreurs
        $recherche = filter_var($_REQUEST["recherche"], FILTER_SANITIZE_STRING);
        include_once './vue/pagefilm.php';
        exit();
    }
    else if ($_REQUEST["categorie"] == "profil")
    {
        // Filtre la recherche qu'a recherché l'utilisateur pour éviter des erreurs
        $infosUtilisateur = VerifierNomUtilisateur(filter_var($_REQUEST["recherche"], FILTER_SANITIZE_STRING));
        
        if ($infosUtilisateur)
        {
            
            $_SESSION["utilisateurListe"] = $infosUtilisateur[0]["idUtilisateur"];
            $pseudo = $infosUtilisateur[0]["pseudo"];
            if ($_SESSION["log"] && $_SESSION["utilisateurListe"] == $_SESSION["idUtilisateur"])
            {
                $perso = 0;
            }
            else
            {
                $perso = 1;
            }
            
        }
        else 
        {
            $perso = 2;
        }
        
        // Filtre la recherche qu'a recherché l'utilisateur pour éviter des erreurs
        $recherche = filter_var($_REQUEST["recherche"], FILTER_SANITIZE_STRING); 
        include_once './vue/profil.php';
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
    $pseudo = "";
    $etat = "Vous devez vous connecter pour pouvoir ajouter un film dans une liste.";
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
        $_SESSION["utilisateurListe"] = $_SESSION["idUtilisateur"];
        $perso = true;
        include_once './vue/liste.php';
        exit();
    }
}

// Vérifie si l'utilisateur veut voir ses listes ou une liste d'un autre utilisateur
if (isset($_POST["type"]))
{
    $listeFilms = GetFilmListe($_SESSION["utilisateurListe"], $_POST["type"]);
    $typeListe = $_POST["type"];
    
    if ($_SESSION["log"])
    {
        if ($_SESSION["idUtilisateur"] == $_SESSION["utilisateurListe"])
        {
            $perso = true;
            $nom = $_SESSION["pseudo"];
        }
        else
        {
            $perso = false;
            $nom = GetNomUtilisateur($_SESSION["utilisateurListe"])[0]["pseudo"];
        }
    }
    else
    {
        $perso = false;
        $nom = GetNomUtilisateur($_SESSION["utilisateurListe"])[0]["pseudo"];
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
        $etat = "Le film a été déplacé dans la liste à voir.";
        $_SESSION["utilisateurListe"] = $_SESSION["idUtilisateur"];
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
        $etat = "Le film a été déplacé dans la liste vu.";
        $_SESSION["utilisateurListe"] = $_SESSION["idUtilisateur"];
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
        $etat = "Le film à été supprimé.";
        $_SESSION["utilisateurListe"] = $_SESSION["idUtilisateur"];
        include_once './vue/liste.php';
        exit();
    }
}

// Vérifie si l'utilisateur veut commenter un film
if (isset($_REQUEST["commenter"]))
{
    $filmCommente = RechercheFilmParId($_POST["filmID"]);
    
    if ($filmCommente->Response == "True")
    {
        if ($_SESSION["log"])
        {
            $commentaire = filter_var($_REQUEST["commentaire"], FILTER_SANITIZE_STRING);
        
            if (!(empty($commentaire)))
            {
                AjouterCommentaire($_SESSION["idUtilisateur"], $_POST["filmID"], $commentaire);
            }
            else 
            {
                $etat = "Aucun commentaire envoyé ou caractère interdit utilisé.";
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
        else
        {
            $etat = "Vous devez vous connecter pour commenter.";
            $pseudo = "";
            include_once './vue/connexion.php';
            exit();
        }
        
    }
    else
    {
        $etat = "Ce film n'existe pas.";
    }
}
// Vérifie si l'utilisateur à changer de page
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
        // ac -> alphabétique croissant
        case "ac":
            $_SESSION["tri"] = "ac";
            break;
        // anc -> alphabétique non croissant
        case "anc":
            $_SESSION["tri"] = "anc";
            break;
        // nac -> nombre d'apparition croissant
        case "nac":
            $_SESSION["tri"] = "nac";
            break;
        // nanc -> nombre d'apparition non croissant
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
    // ac -> alphabétique croissant
    case "ac":
        $filmsAccueil = GetFilm($page,$limite,"nomFilm","asc");
        $triA = "nc";
        $triNA = "nc";
        break;
    // anc -> alphabétique non croissant
    case "anc":
        $filmsAccueil = GetFilm($page,$limite,"nomFilm","desc");
        $triA = "c";
        $triNA = "nc";
        break;
    // nac -> nombre d'apparition croissant
    case "nac":
        $filmsAccueil = GetFilm($page,$limite,"nbFilms","asc");
        $triA = "nc";
        $triNA = "nc";
        break;
    // nanc -> nombre d'apparition non croissant
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