<?php

    include '../functions/gestionFeuilleMatch.php';
    include 'deliver_response.php';
    include '../functions/connexion_db.php';
    include '../functions/verificationJWT.php';

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

                    $joueurs = getJoueursSelectionnesAUnMatch($linkpdo, $id);
                    //Vérifie si la requête a bel et bien renvoyé des données
                    if (empty($joueurs)){
                        deliver_response(404, "L'ID du match ne pointe vers aucun joueurs");
                    } else {
                        deliver_response(200, "Joueurs du match selectionné récupérés avec succès", $joueurs);
                    }
                } else {
                    deliver_response(400, "Le requête GET a besoin de l'ID d'un match");
                }
                break;
            case "POST" :
                //a remplir
                break;
            case "PUT" :
                //a remplir
                break;
        }
    } else {
        deliver_response(401, "Veuillez vous connecter pour accéder à l'application");
    }
    $linkpdo = null;
?>