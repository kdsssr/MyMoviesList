<?php
// Auteur       : De Sousa Kevin
// Nom          : MyMoviesList
// Date         : 7 Juin 2017
// Fonctions d'affichage

/**
 * Affiche le nav si c'est un utilisateur connecté ou non
 * @param bool $etatUtilisateur Vrai si l'utilisateur est connecté, faux si non
 */
function AfficherNav($etatUtilisateur)
{
    ?>
    
    <nav class="navbar navbar-toggleable-md navbar-inverse bg-inverse">
        <button class="navbar-toggler navbar-toggler-right collapsed" type="button" data-toggle="collapse" data-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="index.php"><img src="vue/img/iconeB.png" style="width: 40px"></a>
        <div class="navbar-collapse collapse" id="navbarColor01" aria-expanded="false">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Accueil <span class="sr-only"></span></a>
                </li>
                <?php
                
                if ($etatUtilisateur) 
                {
                    echo '<li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" id="listes" href="index.php?type=Avoir" role="button" aria-haspopup="true" aria-expanded="false">
                    Mes listes
                    </a>
                    <div class="dropdown-menu" aria-labelledby="Preview">
                    <a class="dropdown-item" href="index.php?type=aVoir">A voir</a>
                    <a class="dropdown-item" href="index.php?type=vu">Vu</a>
                    </div></li>';
                }
                ?>
            </ul>
            <form action="index.php" method="post" class="form-inline">
                <select class="custom-select mb-2 mr-sm-2 mb-sm-0 input-group" id="inlineFormCustomSelect" name="categorie">
                        <option value="film">Film</option>
                        <option value="profil">Profil</option>
                </select>
                <div class="input-group">
                    <input class="form-control" type="text"  name="rechercheTitre" placeholder="Rechercher">
                    <span class="input-group-btn">
                        <button class="btn btn-outline-secondary mr-sm-4" type="submit"><img src="vue/img/loupe.png" style="width: 20px"></button>
                    </span>
                </div>
            </form>
            <form action="index.php" method="post">
                <div class="btn-group" role="group">
                    <?php 
                    if ($etatUtilisateur)
                    {
                        echo '<button class="btn  btn-outline-danger my-2 my-sm-0" type="submit" name="deconnecter">Déconnecter</button>';
                    }
                    else
                    {
                        echo '<button class="btn  btn-outline-primary my-2 my-sm-0" type="submit" name="inscrire">S\'inscrire</button>';
                        echo '<button class="btn  btn-outline-primary my-2 my-sm-0" type="submit" name="connecter">Se connecter</button>';
                    }
                    ?>
                </div>
            </form>
        </div>
    </nav>

    <?php
}

/**
 * La fonction gère l'affichage des informations du film dans une table
 * @param object $film L'objet comportant les informations du film
 */
function AfficherFilm($film,$listeDejaActive)
{
    if ($film->Response == "True")
    {
        if ($listeDejaActive == "vu")
        {
            $vu = " disabled";
            $aVoir = "";
        }
        else if ($listeDejaActive == "aVoir")
        {
            $vu = "";
            $aVoir = " disabled";
        }
        else
        {
            $vu = "";
            $aVoir = "";
        }
        
        $nbDansListes = compterFilmDansListe($film->imdbID);
        
        echo '<table class="table table-striped" ><tr><th colspan="3" class="display-4 text-center" >' . $film->Title . '</th></tr>'
                . '<tr><td rowspan="7"><img class="mx-auto d-block" src="' . $film->Poster . '" alt="Poster" style="width: 240px;"></td>'
                . '<th>Date de sortie : </th><td>' . $film->Released . '</td></tr>'
                . '<tr><th>Genre : </th><td>' . $film->Genre . '</td></tr>'
                . '<tr><th>Réalisateur : </th><td>' . $film->Director . '</td></tr>'
                . '<tr><th>Acteurs : </th><td>' . $film->Actors . '</td></tr>'
                . '<tr><th>Notes moyennes : </th><td>' . $film->imdbRating . '</td></tr>'
                . '<tr><td>Utilisateurs voulant voir ce film : ' . $nbDansListes[0]["nbFilmsAvoir"] .'</td><td>Utilisateurs ayant vu ce film : ' . $nbDansListes[0]["nbFilmsVu"] .'</td></tr>'
                . '<tr><td><button' . $aVoir . ' type="submit" name="typeListe" value="aVoir" class="btn btn-warning btn-sm">A voir</button></td>'
                . '<td><button' . $vu .' type="submit" name="typeListe" value="vu" class="btn btn-success btn-sm">Vu</button></td></tr>'
                . '<tr><th  colspan="3">Synopsis : </th></tr><tr><td colspan="3">' . $film->Plot . '</td></tr></table>'
                . '<input type="hidden" value="' . $film->imdbID .'" name="filmID">';
    }
    else
    {
        echo '<h1 >Aucun résultat.</h1>';
    }
    
}

function AfficherCommentaires($commentaires)
{
    if (!(is_null($commentaires)))
    {
        echo '<div class="form-group" ><textarea class="form-control" placeholder="Ajoutez un commentaire" rows="3" ></textarea>'
        . '<input type="submit" class="btn btn-primary mt-3" name="commenter" value="Envoyer">'
        . '<h1 class="display-4">Commentaires</h1>';
        
        foreach ($commentaires as $key => $value)
        {
            echo '<div class="card"><div class="card-block"><h4 class="card-title" >' . $value["pseudo"] . '</h4>'
            . '<p class="card-text" >' . $value["commentaire"] . '</p>';
        }
        
    }
}

function AfficherListe($liste, $proprietaire, $type,$utilisateur)
{
    if ($proprietaire)
    {
      AfficherListePerso($liste, $type);
    }
    else
    {
      AfficherListeAutre($liste, $type,$utilisateur);  
    }
}

function AfficherListePerso($laListe,$type)
{
    echo '<h1 class="mb-5 mt-5 display-4">Voici votre liste de film ' . $type . '</h1>';
    echo '<table class="table table-striped table-inverse"><tr><th>Nom</th><th>Changer de liste</th><th>Supprimer de la liste</th></tr>'
    . '<form action="index.php" method="post">';
    
    foreach ($laListe as $key => $value)
    {
        echo '<tr><td class="align-middle" ><a href="index.php?f=' . $value["imdbID"] .'">' . $value["nomFilm"] .'</a></td><td>'
                . '<button class="btn btn-outline-info" type="submit" style="border: none;" name="filmMaJ" value="' . $value["imdbID"] .'" ><img src="./vue/img/changer.png" style="width: 25px;" alt="Supprimer"></button></td><td>'
                . '<button class="btn  btn-outline-danger" type="submit" style="border: none;" name="suppFilm" value="' . $value["imdbID"] .'">'
                . '<img src="./vue/img/croix.png" style="width: 25px;" alt="Supprimer"></button></tr>';
    }
    
    echo '</form></table>';
}

function AfficherListeAutre($laListe,$type,$nomUtilisateur)
{
    echo '<h1 class="mb-5 mt-5 display-4">Voici la liste de film ' . $type . ' de <a href="">' . $nomUtilisateur .'</a></h1>';
    echo '<table class="table table-striped table-inverse"><tr><th>Nom</th></tr>';
    
    foreach ($laListe as $key => $value)
    {
        echo '<tr><td  class="align-middle" ><a href="index.php?f=' . $value["imdbID"] .'">' . $value["nomFilm"] .'</a></td></tr>';
    }
    
    echo '</table>';
}

function AfficherAccueil($films,$triNom,$triNombre)
{
    echo '<table class="table table-striped table-inverse"><tr><th>Nom <a href="index.php?tri=a' . $triNom .'"><img src="./vue/img/tri.png" style="width: 20px;" alt="Tri"></a></th>'
    . '<th>Nombre d\'apparition <a href="index.php?tri=na' . $triNombre .'"><img src="./vue/img/tri.png" style="width: 20px;" alt="Tri"></a></th></tr>';
    
    foreach ($films as $key => $value)
    {
        echo '<tr><td  class="align-middle" ><a href="index.php?f=' . $value["imdbID"] .'">' . $value["nomFilm"] .'</a></td>'
                . '<td>' . $value["nbfilms"] .'</td></tr>';
    }
    
    echo '</table>';
}

function AfficherBtnPages($limite,$pActuelle)
{
    $nbFilms = compterFilms();
    
    $nbPage = ceil($nbFilms[0]["nbFilms"] /$limite);
    
    echo '<ul class="pagination justify-content-center">';
    
    for ($i = 1; $i <= $nbPage; $i++)
    {
        if ($i == $pActuelle)
        {
            echo '<li class="page-item active"><a class="page-link" href="index.php?page=' . $i .'">' . $i .'</a></li>';
        }  
        else
        {
            echo '<li class="page-item"><a class="page-link" href="index.php?page=' . $i .'">' . $i .'</a></li>';
        }
        
    }
    
    echo '</ul>';
    
}