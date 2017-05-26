<?php
// Auteur       : De Sousa Kevin
// Nom          : MyMoviesList
// Fonctions lié à la base de donnée

DEFINE('DB_HOST', "127.0.0.1");
DEFINE('DB_NAME', "apifilm");
DEFINE('DB_USER', "adminFilm");
DEFINE('DB_PASS', "cinema");

/**
 * Se connecte à la base de donnée
 * @return \PDO retourne la connexion à la base de donnée
 */
function getConnexion()
{
    static $dbb = null;
    
    if ($dbb === null)
    {
        try
        {
            $conectionString = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . '';
            $dbb = new PDO($conectionString, DB_USER, DB_PASS);
            $dbb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e) 
        {
            die('Erreur : ' . $e->getMessage());
        }
    }
    return $dbb;
}

/**
 * Vérifie si le login correspond au mot de passe
 * @param string $login Le pseudo de l'utilisateur
 * @param string $mdp Le mot de passe en sha1 de l'utilisateur
 * @return {tableau associatif} retourne un tableau de tableau avec les informations de l'utilisateur s'il existe et si le mot de passe correspond
 */
function VerifierLogin($login,$mdp) 
{
    $connexion = getConnexion();
    
    $requete = $connexion->prepare("select pseudo,idUtilisateur from utilisateurs where pseudo = :pseudo and mdp = :mdp");
    
    $requete->bindParam(":pseudo", $login, PDO::PARAM_STR);
    $requete->bindParam(":mdp", $mdp, PDO::PARAM_STR);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * On récupère les commentaires liés à un film
 * @param string $idFilm L'id du film dont on souhaite avoir les commentaires
 * @return {tableau associatif} retourne un tableau de tableau de commentaires
 */
function getCommentaire($idFilm)
{
    $connexion = getConnexion();
    
    $requete = $connexion->prepare("select * FROM avis WHERE imdbID = :id");
    
    $requete->bindParam(":id", $idFilm, PDO::PARAM_STR);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * Rajoute le film voulu par l'utilisateur dans la liste voulu
 * @param int $idUtilisateur
 * @param string $idFilm L'id du film à rajouter dans la liste
 * @param string $typeListe Le nom de la liste où l'on va rajouter le film
 */
function AjouterFilmListe($idUtilisateur,$idFilm,$typeListe)
{
    $connexion = getConnexion();
    
    $requete = $connexion->prepare("INSERT INTO listes (typeListe, idUtilisateur, imdbID) VALUES (:type,:idUtilisateur,:idFilm)");
    
    $requete->bindParam(":type", $typeListe, PDO::PARAM_STR);
    $requete->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
    $requete->bindParam(":idFilm", $idFilm, PDO::PARAM_STR);
    
    $requete->execute();
}

/**
 * Ajoute l'id omdbapi dans la base de donnée
 * @param string $idFilm L'id du film
 */
function AjouterFilmBDD($idFilm)
{
    $connexion = getConnexion();
    
    $requete = $connexion->prepare("INSERT INTO api (imdbID) VALUES (:idFilm)");
    
    $requete->bindParam(":idFilm", $idFilm, PDO::PARAM_STR);
    
    $requete->execute();
}

/**
 * Vérifie si l'id du film est déjà dans la base de donnée
 * @param string $id L'id du film
 * @return {tableau associatif} retourne un tableau de tableau si le film existe dans la bdd et faux si le film n'est pas dans la bdd
 */
function VerifierFilmExiste($id)
{
    $connexion = getConnexion();
    
    $requete = $connexion->prepare("select * FROM api WHERE imdbID = :id");
    
    $requete->bindParam(":id", $id, PDO::PARAM_STR);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}