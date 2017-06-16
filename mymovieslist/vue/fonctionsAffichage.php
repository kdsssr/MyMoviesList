<?php
// Auteur       : De Sousa Kevin
// Nom          : MyMoviesList
// Date         : 15 Juin 2017
// Fonctions d'affichage

/**
 * Affiche le nav si c'est un utilisateur connecté ou non
 * @param bool $etatUtilisateur Vrai si l'utilisateur est connecté, faux si non
 */
function AfficherNav($etatUtilisateur,$rechercheEffectue  = "")
{
    ?>
    
    <nav class="navbar navbar-toggleable-md navbar-inverse bg-inverse fixed-top">
        <button class="navbar-toggler navbar-toggler-right collapsed" type="button" data-toggle="collapse" data-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="index.php"><img src="vue/img/iconeB.png" style="width: 40px"></a>
        <div class="navbar-collapse collapse" id="navbarColor01" aria-expanded="false">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Accueil</a>
                </li>
                <?php
                
                if ($etatUtilisateur) 
                {
                    echo '<li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" id="listes" href="index.php?type=Avoir" role="button" aria-haspopup="true" aria-expanded="false">
                    Mes listes
                    </a>
                    <div class="dropdown-menu">
                    <a class="dropdown-item" href="index.php?type=aVoir">A voir</a>
                    <a class="dropdown-item" href="index.php?type=vu">Vu</a>
                    </div></li>
                    <li><a href="index.php?profil=p"><img alt="profil" src="vue/img/profil.png" style="width: 40px;"></a></li>';
                }
                ?>
            </ul>
            <form action="index.php" method="post" class="form-inline">
                <select class="custom-select mb-2 mr-sm-2 mb-sm-0 input-group" id="inlineFormCustomSelect" name="categorie">
                        <option value="film">Film</option>
                        <option value="profil">Profil</option>
                </select>
                <div class="input-group">
                    <input class="form-control" type="text"  name="recherche" placeholder="Rechercher" value="<?php echo $rechercheEffectue ?>">
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
    if (!(is_null($film)) && $film->Response == "True")
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
        
        $nbDansListes = CompterFilmDansListe($film->imdbID);
        
        echo '<table class="table table-striped titre" ><tr><th colspan="3" class="display-4 text-center" >' . $film->Title . '</th></tr>'
                . '<tr><td rowspan="7"><img class="mx-auto d-block" src="' . $film->Poster . '" alt="Poster" style="width: 240px;"></td>'
                . '<th>Date de sortie : </th><td>' . $film->Released . '</td></tr>'
                . '<tr><th>Genre : </th><td>' . $film->Genre . '</td></tr>'
                . '<tr><th>Réalisateur : </th><td>' . $film->Director . '</td></tr>'
                . '<tr><th>Acteurs : </th><td>' . $film->Actors . '</td></tr>'
                . '<tr><th>Notes moyennes : </th><td>' . $film->imdbRating . ' / 10</td></tr>'
                . '<tr><td>Utilisateurs voulant voir ce film : ' . $nbDansListes[0]["nbFilmsAvoir"] .'</td><td>Utilisateurs ayant vu ce film : ' . $nbDansListes[0]["nbFilmsVu"] .'</td></tr>'
                . '<tr><td><button' . $aVoir . ' type="submit" name="typeListe" value="aVoir" class="btn btn-warning btn-sm">A voir</button></td>'
                . '<td><button' . $vu .' type="submit" name="typeListe" value="vu" class="btn btn-success btn-sm">Vu</button></td></tr>'
                . '<tr><th  colspan="3">Synopsis : </th></tr><tr><td colspan="3">' . $film->Plot . '</td></tr></table>'
                . '<input type="hidden" value="' . $film->imdbID .'" name="filmID">';
    }
    else
    {
        AfficherAucunRsultat();
    }
    
}

/**
 * Affiche les commentaires liés à un film 
 * @param {tableau associatif} $commentaires Tableau contenant les commentaires
 */
function AfficherCommentaires($commentaires)
{
    if (!(is_null($commentaires)))
    {
        echo '<div class="form-group" ><textarea name="commentaire" class="form-control" placeholder="Ajoutez un commentaire" rows="3" ></textarea>'
        . '<input type="submit" class="btn btn-primary mt-3 float-right" name="commenter" value="Envoyer"></div>'
        . '<h1 class="gauche mt-5" >Commentaires :</h1>';
        
        foreach ($commentaires as $key => $value)
        {
            echo '<div class="card"><div class="card-block"><h4 class="card-title" ><a href="index.php?profil=' . $value["pseudo"] . '">' . $value["pseudo"] . '</a></h4>'
            . '<p class="card-text" >' . $value["commentaire"] . '</p></div>';
        }
        
    }
}

/**
 * 
 * @param type $liste
 * @param type $proprietaire
 * @param type $type
 * @param type $utilisateur
 */
function AfficherListe($liste, $proprietaire, $type,$id)
{
    if ($proprietaire)
    {
      AfficherListePerso($liste, $type);
    }
    else
    {
      $utilisateur = GetNomUtilisateur($id);
      AfficherListeAutre($liste, $type,$utilisateur[0]["pseudo"]);  
    }
}

/**
 * Affiche sa liste personelle
 * @param {tableau associatif} $laListe Tableau contenant les films faisant partie de cette liste
 * @param string $type Le type de la liste (vu ou à voir)
 */
function AfficherListePerso($laListe,$type)
{
    echo '<h1 class="mb-5 display-4">Voici votre liste de film ' . $type . '</h1>';
    echo '<table class="table table-striped table-inverse"><tr><th>Nom</th><th>Changer de liste</th><th>Supprimer de la liste</th></tr>'
    . '<form action="index.php" method="post">';
    
    foreach ($laListe as $key => $value)
    {
        echo '<tr><td class="align-middle" ><a href="index.php?f=' . $value["imdbID"] .'">' . $value["nomFilm"] .'</a></td><td>'
                . '<button class="btn btn-outline-info pas-bord" type="submit" name="filmMaJ" value="' . $value["imdbID"] .'" ><img src="./vue/img/changer.png" style="width: 25px;" alt="Modifier"></button></td><td>'
                . '<button class="btn  btn-outline-danger pas-bord" type="submit" name="suppFilm" value="' . $value["imdbID"] .'">'
                . '<img src="./vue/img/croix.png" style="width: 25px;" alt="Supprimer"></button></tr>';
    }
    
    echo '</form></table>';
}

function AfficherListeAutre($laListe,$type,$nomUtilisateur)
{
    echo '<h1 class="mb-5 display-4">Voici la liste de film ' . $type . ' de <a href="index.php?profil=' . $nomUtilisateur .'">' . $nomUtilisateur .'</a></h1>';
    echo '<table class="table table-striped table-inverse"><tr><th>Nom</th></tr>';
    
    foreach ($laListe as $key => $value)
    {
        echo '<tr><td  class="align-middle" ><a href="index.php?f=' . $value["imdbID"] .'">' . $value["nomFilm"] .'</a></td></tr>';
    }
    
    echo '</table>';
}

/**
 * Affiche l'accueil avec les films ajoutés par les utilisateurs et le nombre de fois que le film a été ajouté
 * @param {tableau associatif} $films Tableau contenant les films avec leur nom et le nombre de fois que le film a été ajouté
 * @param string $triNom Le tri, pour le nom, qui serra éffectif si l'utilisateur clique sur le lien de tri
 * @param string $triNombre Le tri, pour le nombre d'apparition, qui serra éffectif si l'utilisateur clique sur le lien de tri
 */
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

/**
 * Affiche les boutons pour la pagination par rapport aux nombre de films
 * @param int $limite La limite de film par page, qui sert à calculer le nombre de page
 * @param int $pActuelle La page où se trouve actuellement l'utilisateur
 */
function AfficherBtnPages($limite,$pActuelle)
{
    $nbFilms = CompterFilms();
    
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

/**
 * Affiche les erreurs ou la confiramtion d'une action de l'utilisateur.
 * @param string $notif Le texte à afficher
 */
function AfficherNotif($notif)
{
    if ($notif != "")
    {
        echo '<div class="alert alert-info alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>' . $notif . '</div>';
    }
}

function AfficherProfil($id, $proprietaire)
{
    $nbListes = CompterFilmsParListe($id);
    $nom = GetNomUtilisateur($id);
    
    if ($proprietaire == 0)
    {
      AfficherSonProfil($nbListes[0]["nbVu"], $nbListes[0]["nbAVoir"]);
    }
    else if ($proprietaire == 1)
    {
      AfficherProfilRecherche($nbListes[0]["nbVu"], $nbListes[0]["nbAVoir"],$nom[0]["pseudo"]);  
    }
    else
    {
        AfficherAucunRsultat();
    }
}

function AfficherSonProfil($nbVu,$nbAvoir)
{
    echo '<h1 class="mb-4 display-3" >Votre profil</h1>'
            . '<form method="post" action="index.php" ><table class="table">'
            . '<tr><td>Nombre de films vus : ' . $nbVu . '</td>'
            . '<td><button class="btn btn-info btn-sm" type="submit" name="type" value="vu">Films vu</button></td></tr>'
            . '<tr><td>Nombre de films à voir : ' . $nbAvoir . '</td>'
            . '<td><button class="btn btn-info btn-sm" type="submit" name="type" value="aVoir">Films à voir</button></td></tr></table>';
}

function AfficherProfilRecherche($nbVu,$nbAvoir,$nom)
{
    echo '<h1 class="mb-4 display-3" >Profil de ' . $nom . '</h1>'
            . '<form method="post" action="index.php" ><table class="table">'
            . '<tr><td>Nombre de films vus : ' . $nbVu . '</td>'
            . '<td><button class="btn btn-info btn-sm" type="submit" name="type" value="vu">Films vu</button></td></tr>'
            . '<tr><td>Nombre de films à voir : ' . $nbAvoir . '</td>'
            . '<td><button class="btn btn-info btn-sm" type="submit" name="type" value="aVoir">Films à voir</button></td></tr></table>';
    
}

function AfficherAucunRsultat()
{
    echo '<h1 class="display-4">Aucun résultat.</h1>';
}