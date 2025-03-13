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
                    }

                    $match = getOneMatch($linkpdo, $id);
                    //On vérifie s'il y a des données dans la réponse de la fonction
                    if (empty($match)){
                        deliver_response(404, "Aucun match n'est associé à l'ID");
                    } else {
                        deliver_response(200, "Match récupéré avec succès", $match);
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
                if(isset($_POST['date']) && isset($_POST['heure']) && isset($_POST['equipeadv']) && isset($_POST['domicile'])){
                    ajouterMatch($linkpdo, $_POST['date'], $_POST['heure'], $_POST['equipeadv'], $_POST['domicile']);
                    deliver_response(201, "Match ajouté avec succès");
                } else {
                    deliver_response(400, "La date, l'heure, l'équipe adverse et le lieu du match sont requis");
                }
                break;
            case "PUT" :
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