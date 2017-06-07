<?php
// Auteur       : De Sousa Kevin
// Nom          : MyMoviesList
// Date         : 7 Juin 2017
// Fonctions liés à omdbapi
// API key = fc17a632

/**
 * Fait une recherche avec le titre du film avec omdbapi
 * @param string $titre Le titre à chercher
 * @return object $objet Retourne un objet php avec les informations sur le film
 */
function RechercheFilmParTitre($titre)
{
    $url = "http://www.omdbapi.com/?t="  . urlencode($titre) ."&r=json&type=movie&apikey=fc17a632";
    
    $jsonReponse = file_get_contents($url);
    $objet = json_decode($jsonReponse);
    
    return $objet;
}

/**
 * Fait une recherche avec l'id du film avec omdbapi
 * @param string $id L'id du film à chercher
 * @return object $objet Retourne un objet php avec les informations sur le film
 */
function RechercheFilmParId($id)
{
    $url = "http://www.omdbapi.com/?i="  . urlencode($id) ."&r=json&type=movie&apikey=fc17a632";
    
    $jsonReponse = file_get_contents($url);
    $objet = json_decode($jsonReponse);
    
    return $objet;
}