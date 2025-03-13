<?php
    include '../functions/gestionMatchs.php';
    include 'deliver_response.php';
    include '../functions/connexion_db.php';
    include '../functions/verificationJWT.php';

    // Vérification de l'authentification
    if(getJwtValid()){
        // Connexion à la base de données
        $linkpdo = connexion_db();
        $http_method = $_SERVER['REQUEST_METHOD'];

        switch ($http_method){
            case "GET" :
                // Récupération d'un match spécifique si l'id est présent
                if(isset($_GET['id'])){
                    $id = $_GET['id'];
                    $match = getOneMatch($linkpdo, $id);
                    deliver_response(200, "Match récupéré avec succès", $match);
                } else {
                    // Sinon on récupère tous les matchs
                    $matchs = getAllMatchs($linkpdo);
                    deliver_response(200, "Matchs récupérés avec succès", $matchs);
                }
                break;

            case "POST" :
                // Création d'un nouveau match
                if(isset($_POST['date']) && isset($_POST['heure']) && isset($_POST['equipeadv']) && isset($_POST['domicile'])){
                    ajouterMatch($linkpdo, $_POST['date'], $_POST['heure'], $_POST['equipeadv'], $_POST['domicile']);
                    deliver_response(201, "Match ajouté avec succès");
                } else {
                    deliver_response(400, " la date, l'heure, l'équipe adverse et le lieu du match sont requis");
                }
                break;

            case "PUT" :
                // Modification d'un match existant
                if(isset($_POST['id']) && isset($_POST['date']) && isset($_POST['heure']) && isset($_POST['equipeadv']) && isset($_POST['domicile']) && isset($_POST['score'])){
                    updateMatch($linkpdo, $_POST['id'], $_POST['date'], $_POST['heure'], $_POST['equipeadv'], $_POST['domicile'], $_POST['score']);
                    deliver_response(200, "Match modifié avec succès");
                } else {
                    deliver_response(400, "L'id, la date, l'heure, l'équipe adverse, le lieu du match et le score sont requis");
                }
                break;
        }
    } else {
        // Réponse en cas d'échec de l'authentification
        deliver_response(401, "Veuillez vous connecter pour accéder à la ressource");
    }
    // Fermeture de la connexion à la base de données
    $linkpdo = null;
?>