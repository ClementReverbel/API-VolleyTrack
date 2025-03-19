<?php
    include '../functions/gestionStats.php';
    include 'deliver_response.php';
    include '../functions/connexion_db.php';
    include '../functions/verificationJWT.php';

    // Vérification de l'authentification
    if(getJwtValid()){
        $linkpdo = connexion_db();
        $http_method = $_SERVER['REQUEST_METHOD'];
        switch ($http_method){
            case "GET" :
                //Vérifie si la statistique demandée est celle du joueur
                if(isset($_GET['stats_joueur'])){
                    $data = getAllStatsJoueur($linkpdo);
                    deliver_response(200, "Statistiques du joueur", $data);
                //Vérifie si la statistique demandée est celle du match
                } else if(isset($_GET['stats_match'])){
                    $data = getStatMatch($linkpdo);
                    deliver_response(200, "Récupération des statistiques des matchs", $data);
                } else {
                    //Si aucune statistique n'est demandée, on renvoie une erreur
                    deliver_response(400, "Veuillez spécifier le type de statistique à récupérer (stats_joueur ou stats_match)");
                }
                break;
            //Renvoie un code d'erreur correct pour les mauvaises requêtes et pas seulement une erreur serveur
            default:
                deliver_response(405, "Méthode non implémentée, seul la méthode GET est disponible pour les statistiques");
                break;
        }
    } else {
        // Réponse en cas d'échec de l'authentification
        deliver_response(401, "Veuillez vous connecter pour accéder à la ressource");
    }
    // Fermeture de la connexion à la base de données
    $linkpdo = null;
?>