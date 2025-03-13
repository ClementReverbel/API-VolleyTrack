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
                if(isset($_POST['id']) && isset($_POST['liste_joueurs']) && isset($_POST['liste_roles'])){
                    $response=ajouterFeuilleMatch($linkpdo,$_POST['id'],$_POST['liste_joueurs'],$_POST['liste_roles']);
                    //Vérifie si la fonction renvoie un message d'erreur ou non
                    if (str_contains($response,"Erreur")){
                        if(str_contains($response,"enregistrement")){
                            deliver_response(500,$response);
                        } else {
                            deliver_response(400,$response);
                        }
                    }else{
                        deliver_response(200,$response);
                    }
                } else {
                    deliver_response(400, "L'id du match, la liste des id des jouers, la liste des roles sont requis");
                }
                break;
            case "PUT" :
                if(isset($_POST['id']) && isset($_POST['liste_joueurs']) && isset($_POST['liste_roles']) && isset($_POST['liste_notes'])){
                    $response=modifierFeuilleMatch($linkpdo,$_POST['id'],$_POST['liste_joueurs'],$_POST['liste_roles'],$_POST['liste_notes']);
                    //Vérifie si la fonction renvoie un message d'erreur ou non
                    if (str_contains($response,"Erreur")){
                        if(str_contains($response,"enregistrement")){
                            deliver_response(500,$response);
                        } else {
                            deliver_response(400,$response);
                        }
                    }else{
                        deliver_response(200,$response);
                    }
                } else {
                    deliver_response(400, "L'id du match, la liste des id des jouers, la liste des roles et la liste des notes sont requis");
                }
                break;
        }
    } else {
        deliver_response(401, "Veuillez vous connecter pour accéder à l'application");
    }
    $linkpdo = null;
?>