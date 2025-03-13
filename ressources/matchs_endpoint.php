<?php
    include '../functions/gestionMatchs.php';
    include 'deliver_response.php';
    include '../functions/connexion_db.php';
    include '../functions/verificationJWT.php';

    // Vérification de l'authentification
    if(getJwtValid()){
        $linkpdo = connexion_db();
        $http_method = $_SERVER['REQUEST_METHOD'];
        switch ($http_method){
            case "GET" :
                if(isset($_GET['id'])){
                    $id = $_GET['id'];
                    //Vérifie si l'ID est numérique (pas de texte)
                    if (!is_numeric($id)){
                        deliver_response(422, "L'ID doit être numérique");
                    } else {
                        $match = getOneMatch($linkpdo, $id);
                        //On vérifie s'il y a des données dans la réponse de la fonction
                        if (empty($match)){
                            deliver_response(404, "Aucun match n'est associé à l'ID");
                        } else {
                            deliver_response(200, "Match récupéré avec succès", $match);
                        }
                    }
                } else {
                    $matchs = getAllMatchs($linkpdo);
                    //On vérifie s'il y a des données dans la réponse de la fonction
                    if (empty($matchs)){
                        deliver_response(404, "Aucun match existant");
                    } else {
                        deliver_response(200, "Matchs récupérés avec succès", $matchs);
                    }
                }
                break;
            case "POST" :
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData,true);
                if(isset($data['date']) && isset($data['heure']) && isset($data['equipeadv']) && isset($data['domicile'])){
                    ajouterMatch($linkpdo, $data['date'], $data['heure'], $data['equipeadv'], $data['domicile']);
                    deliver_response(201, "Match ajouté avec succès");
                } else {
                    deliver_response(400, "La date, l'heure, l'équipe adverse et le lieu du match sont requis");
                }
                break;
            case "PUT" :
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData,true);
                if(isset($data['id']) && isset($data['date']) && isset($data['heure']) && isset($data['equipeadv']) && isset($data['domicile']) && isset($data['score'])){
                    updateMatch($linkpdo, $data['id'], $data['date'], $data['heure'], $data['equipeadv'], $data['domicile'], $data['score']);
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