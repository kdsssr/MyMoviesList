<?php
// Auteur       : De Sousa Kevin
// Nom          : MyMoviesList
// Date         : 15 Juin 2017
// Fonctions lié à la base de donnée

DEFINE('DB_HOST', "127.0.0.1");
DEFINE('DB_NAME', "apifilm");
DEFINE('DB_USER', "adminFilm");
DEFINE('DB_PASS', "cinema");

/**
 * Se connecte à la base de donnée
 * @return \PDO retourne la connexion à la base de donnée
 */
function GetConnexion()
{
    static $dbb = null;
    
    if ($dbb === null)
    {
        try
        {
            $conexionString = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . '';
            $dbb = new PDO($conexionString, DB_USER, DB_PASS);
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
    $connexion = GetConnexion();

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
 * @return {tableau associatif} retourne des tableaux de commentaires avec pour chaque commentaires un tableau avec leurs informations
 */
function GetCommentaire($idFilm)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("select commentaire,pseudo FROM avis natural join utilisateurs WHERE imdbID = :id ORDER BY idAvis desc");
    
    $requete->bindParam(":id", $idFilm, PDO::PARAM_STR);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * Ajoute un commentaire a un film
 * @param int $idUtilisateur L'id dde l'utilisateur qui commente
 * @param string $idFilm L'id du film à commenté
 * @param string $commentaire Le commentaire
 */
function AjouterCommentaire($idUtilisateur,$idFilm,$commentaire)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("insert into avis (idUtilisateur, imdbID, commentaire) VALUES (:idUtilisateur,:idFilm,:commentaire)");
    
    $requete->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
    $requete->bindParam(":idFilm", $idFilm, PDO::PARAM_STR);
    $requete->bindParam(":commentaire", $commentaire, PDO::PARAM_STR);
    
    $requete->execute();
}

/**
 * Rajoute le film voulu par l'utilisateur dans la liste voulu
 * @param int $idUtilisateur L'id de l'utilisateur qui a ajouté le film
 * @param string $idFilm L'id du film à rajouter dans la liste
 * @param string $typeListe Le nom de la liste où l'on va rajouter le film
 */
function AjouterFilmListe($idUtilisateur,$idFilm,$typeListe)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("INSERT INTO listes (typeListe, idUtilisateur, imdbID) VALUES (:type,:idUtilisateur,:idFilm)");
    
    $requete->bindParam(":type", $typeListe, PDO::PARAM_STR);
    $requete->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
    $requete->bindParam(":idFilm", $idFilm, PDO::PARAM_STR);
    
    $requete->execute();
}

/**
 * Ajoute l'id omdbapi dans la base de donnée et le nom
 * @param string $idFilm L'id du film
 * @param string $nom Le nom du film
 */
function AjouterFilmBDD($idFilm,$nom)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("INSERT INTO api (imdbID,nomFilm) VALUES (:idFilm,:nom)");
    
    $requete->bindParam(":idFilm", $idFilm, PDO::PARAM_STR);
    $requete->bindParam(":nom", $nom, PDO::PARAM_STR);
    
    $requete->execute();
}

/**
 * Vérifie si l'id du film est déjà dans la base de donnée
 * @param string $id L'id du film
 * @return {tableau associatif} retourne un tableau de tableau si le film existe dans la bdd et faux si le film n'est pas dans la bdd
 */
function VerifierFilmExiste($id)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("select * FROM api WHERE imdbID = :id");
    
    $requete->bindParam(":id", $id, PDO::PARAM_STR);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * Récupère une des deux listes d'un utilisateur
 * @param int $idUtilisateur L'id de l'utiisateur à qui appartient la liste
 * @param string $type Le type de la liste (vu ou à voir)
 * @return {tableau associatif} retourne des tableaux avec les films qui font partie de cette liste et un tableau par film qui ont les informations sur le film
 */
function GetFilmListe($idUtilisateur,$type)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("select * FROM listes natural join api WHERE idUtilisateur = :id and typeListe = :type order by nomFilm");
    
    $requete->bindParam(":id", $idUtilisateur, PDO::PARAM_INT);
    $requete->bindParam(":type", $type, PDO::PARAM_STR);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * Supprime un film de la liste d'un utilisateur
 * @param int $idUtilisateur L'id de l'utilisateur qui veut le film de sa liste
 * @param string $idFilm L'id du film à supprimer
 */
function DeleteFilmListe($idUtilisateur,$idFilm)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("delete FROM listes WHERE idUtilisateur = :idUtilisateur and imdbID = :idFilm");
    
    $requete->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
    $requete->bindParam(":idFilm", $idFilm, PDO::PARAM_STR);
    
    $requete->execute();
}

/**
 * Prends le type de la liste où se trouve un film ajouté par un utilisateur
 * @param int $idUtilisateur L'id de l'utilisateur à qui appartient la liste
 * @param string $idFilm L'id du film
 * @return {tableau associatif} retourne un tableau de tableau avec le type de la liste
 */
function GetTypeListe($idUtilisateur,$idFilm)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("select typeListe FROM listes WHERE idUtilisateur = :idUtilisateur and imdbID = :idFilm");
    
    $requete->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
    $requete->bindParam(":idFilm", $idFilm, PDO::PARAM_STR);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * Met à jour le type de la liste d'un film d'un utilisateur
 * @param int $idUtilisateur L'id de l'utilisateur qui veut mettre à jour un film de sa liste
 * @param string $idFilm L'id du film qui va être mit à jour
 * @param string $type La liste dans laquelle le film va être changé
 */
function UpdateListe($idUtilisateur,$idFilm,$type)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("UPDATE listes set typeListe =:NouvType WHERE idUtilisateur = :idUtilisateur and imdbID = :idFilm");
    
    $requete->bindParam(":NouvType", $type, PDO::PARAM_STR);
    $requete->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
    $requete->bindParam(":idFilm", $idFilm, PDO::PARAM_STR);
    
    $requete->execute();
}

/**
 * Récupère le nom d'un utilisateur avec son id
 * @param int $idUtilisateur L'id de l'utilisateur que l'on cherche à obtenir le pseudo
 * @return {tableau associatif} retourne un tableau de tableau avec le nom de l'utilisateur
 */
function GetNomUtilisateur($idUtilisateur)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("select pseudo FROM utilisateurs WHERE idUtilisateur = :id");
    
    $requete->bindParam(":id", $idUtilisateur, PDO::PARAM_INT);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * Récupère tous les films qui ont été mis dans une liste, une seule fois, trier et par page d'une certaine limite de films
 * @param int $page Le numéro de la page éffective
 * @param int $limite La limite de film par page
 * @param string $tri Le tri qui est effectif (nomFilm ou nbFilms)
 * @param string $ordre L'ordre du tri (asc ou desc))
 * @return {tableau associatif} retourne des tableaux avec les films qui font partie d'au moins une liste et un tableau par film qui ont les informations sur le film
 */
function GetFilm($page,$limite,$tri,$ordre)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("SELECT nomFilm,imdbID,count(imdbID) as nbfilms FROM `listes` natural join api group by imdbID order by " . $tri . " " . $ordre
            . " LIMIT " . (($page-1) * $limite) . "," . $limite );
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * Compte le nombre de films différents dans toutes les listes 
 * @return {tableau associatif} retourne un tableau de tableau avec le nombre de films
 */
function CompterFilms()
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("SELECT count(distinct imdbID) as nbFilms FROM listes");
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * Compte le nombre de fois qu'un film apparait dans chacune des listes
 * @param string $id L'id du film
 * @return {tableau associatif} retourne un tableau de tableau avec le nombre de fois qu'un film apparait dans la liste vu et à voir
 */
function CompterFilmDansListe($id)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare('SELECT (select count(typeListe) FROM listes WHERE typeListe = "aVoir" and imdbID = :id) as nbFilmsAvoir,'
            . '(select count(typeListe)FROM listes WHERE typeListe = "vu" and imdbID = :id) as nbFilmsVu');
    
    $requete->bindParam(":id", $id, PDO::PARAM_STR);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * Vérifie si un pseudo est déjà prit
 * @param string $nom Le pseudo à vérifier
 * @return {tableau associatif} retourne un tableau de tableau avec le pseudo s'il est déjà prit
 */
function VerifierNomUtilisateur($nom)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("select pseudo,idUtilisateur FROM utilisateurs WHERE pseudo = :nom");
    
    $requete->bindParam(":nom", $nom, PDO::PARAM_STR);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}

/**
 * Ajoute un utilisateur avec son nom et mot de passe dans la base de données
 * @param string $nom Le pseudo de l'utilisateur
 * @param string $mdp Le mot de passe crypté en SHA1 de l'utilisateur.
 * @return {tableau associatif} retourne un tableau de tableau avec l'id de l'utilisateur qui vient d'être inséré.
 */
function AjouterUtilisateur($nom,$mdp)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare("INSERT INTO utilisateurs (pseudo,mdp) VALUES (:nom,:mdp)");
    
    $requete->bindParam(":nom", $nom, PDO::PARAM_STR);
    $requete->bindParam(":mdp", $mdp, PDO::PARAM_STR);
    
    $requete->execute();
    
    return ($connexion->lastInsertId());
}

/**
 * Compte le nombre de films qu'un utilisateur a dans chaque liste
 * @param int $idUtilisateur L'id de l'utilisateur.
 * @return {tableau associatif} retourne un tableau de tableau avec le nombre de films qu'un utilisateur a dans chaque liste
 */
function CompterFilmsParListe($idUtilisateur)
{
    $connexion = GetConnexion();
    
    $requete = $connexion->prepare('SELECT (select count(imdbID) FROM listes WHERE idUtilisateur = :id and typeListe = "vu")as nbVu,'
            . '(select count(imdbID) FROM listes WHERE idUtilisateur = :id and typeListe = "aVoir")as nbAVoir');
    
    $requete->bindParam(":id", $idUtilisateur, PDO::PARAM_INT);
    
    $requete->execute();
    
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultat;
}