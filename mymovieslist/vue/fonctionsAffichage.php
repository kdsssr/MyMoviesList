<?php
// Auteur       : De Sousa Kevin
// Nom          : MyMoviesList
// Fonctions d'affichage

/**
 * La fonction gère l'affichage des informations du film dans une table
 * @param object $film L'objet comportant les informations du film
 */
function AfficherFilm($film)
{
    if (!(is_null($film))&& $film->Response == "True")
    {
        echo '<table class="table table-striped" ><tr><th colspan="3" class="display-4 text-center" >' . $film->Title . '</th></tr><tr><td rowspan="5"><img colspan="2" class="mx-auto d-block" src="' . $film->Poster . '" alt="Poster" style="width: 200px;"></td>';
        echo '<th>Date de sortie : </th><td>' . $film->Released . '</td></tr>';
        echo '<tr><th>Genre : </th><td>' . $film->Genre . '</td></tr>';
        echo '<tr><th>Réalisateur : </th><td>' . $film->Director . '</td></tr>';
        echo '<tr><th>Acteurs : </th><td>' . $film->Actors . '</td></tr>';
        echo '<tr><td><button type="submit" name="typeListe" value="aVoir" class="btn btn-warning btn-sm">A voir</button></td>'
        . '<td><button type="submit" name="typeListe" value="vu" class="btn btn-success btn-sm">Vu</button></td></tr>';
        echo '<tr><th  colspan="3">Synopsis : </th></tr><tr><td colspan="3">' . $film->Plot . '</td></tr></table>';
        echo '<input type="hidden" value="' . $film->imdbID .'" name="filmID">';
    }
    else
    {
        echo '<h1 >Aucun résultat.</h1>';
    }
    
}

/**
 * Affiche le nav si c'est un utilisateur connecté ou non
 * @param bool $etatUtilisateur Vrai si l'utilisateur est connecté, faux si non
 */
function AfficherNav($etatUtilisateur,$nom)
{
    ?>
    
    <nav class="navbar navbar-toggleable-md navbar-inverse bg-inverse">
        <button class="navbar-toggler navbar-toggler-right collapsed" type="button" data-toggle="collapse" data-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="index.php">My Movies List</a>
        <div class="navbar-collapse collapse" id="navbarColor01" aria-expanded="false">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Accueil <span class="sr-only"></span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" id="listes" href="index.php?type=Avoir" role="button" aria-haspopup="true" aria-expanded="false">
                    Mes listes
                    </a>
                    <div class="dropdown-menu" aria-labelledby="Preview">
                    <a class="dropdown-item" href="index.php?type=AVoir">A voir</a>
                    <a class="dropdown-item" href="index.php?type=Vu">Vu</a>
                    </div>
                </li>
            </ul>
            <?php 
            if ($etatUtilisateur) 
            {
                echo '<span class="navbar-text mr-sm-4">Bienvenue, ' . $nom .'</span>';
            }
            ?>
            <form action="index.php" method="post" class="form-inline">
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