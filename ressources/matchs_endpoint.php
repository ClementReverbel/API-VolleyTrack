<?php
    include '../functions/gestionMatchs.php';
    include '../functions/date_verifier.php';
    include 'deliver_response.php';
    include '../functions/connexion_db.php';
    include '../functions/verificationJWT.php';

    //Méthode à implémenter  pour le CORS ET avant la vérification JWT qui bloque tout
    if($_SERVER['REQUEST_METHOD']=="OPTIONS"){
        deliver_response(200,"Prerequest validée");
        exit();
    }

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
                        deliver_response(403, "L'ID doit être numérique");
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
                //Récupère les données envoyées par le client
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData,true);
                //Vérifie si les données requises sont présentes
                if(isset($data['date']) && isset($data['heure']) && isset($data['equipeadv']) && isset($data['domicile'])){
                    if ($data['domicile']==1 || $data['domicile']==0){
                        //Vérifie si la date et l'heure sont valides
                        if(date_valide($data['date']) && heure_valide($data['heure'])){
                            ajouterMatch($linkpdo, $data['date'], $data['heure'], $data['equipeadv'], $data['domicile']);
                            deliver_response(201, "Match ajouté avec succès");
                        } else {
                            //Si la date ou l'heure ne sont pas valides, on renvoie un message d'erreur
                            deliver_response(400,"La date et l'heure doivent avoir respectivement le format jj/mm/yyyy et hh:mm");
                        }
                    } else {
                        //Si le lieu du match n'est pas 1 ou 0, on renvoie un message d'erreur
                        deliver_response(400, "Le domicile doit avoir une valeur de 1 ou 0, (1=A domicile, 0=Chez l'adversaire)");
                    }
                } else {
                    //Si les toutes données requises ne sont pas présentes, on renvoie un message d'erreur
                    deliver_response(400, "La date, l'heure, l'équipe adverse et le lieu du match sont requis");
                }
                break;
            case "PUT" :
                //Récupère les données envoyées par le client
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData,true);
                //Vérifie si les données requises sont présentes
                if(isset($data['id']) && isset($data['date']) && isset($data['heure']) && isset($data['equipeadv']) && isset($data['domicile']) && isset($data['score'])){
                    //Vérifie si l'ID envoyée correspond à un match existant
                    if(!empty(getOneMatch($linkpdo,$data['id']))){
                        //Vérifie si le lieu du match est valide
                        if ($data['domicile']==1 || $data['domicile']==0){
                            //Vérifie si la date et l'heure sont valides
                            if(date_valide($data['date']) && heure_valide($data['heure'])){
                                //Vérifie que le nombre de set est entre 3 et 5
                                if(getNbSetValide($data['score'])){
                                    //Vérifie si le score est valide en fonction des règles officielles du volley-ball
                                    $message=getScoreValide($data['score']);
                                    if($message=="Ok"){
                                        updateMatch($linkpdo, $data['id'], $data['date'], $data['heure'], $data['equipeadv'], $data['domicile'], $data['score']);
                                        deliver_response(200, "Match modifié avec succès");
                                    } else { 
                                        //Si le score n'est pas valide, on renvoie un message d'erreur adapté
                                        deliver_response(400,$message);
                                    }
                                } else {
                                    //Si le nombre de set n'est pas valide, on renvoie un message d'erreur
                                    deliver_response(400,"Le nombre de set doit être compris entre 3 et 5");
                                }
                            } else {
                                //Si la date ou l'heure ne sont pas valides, on renvoie un message d'erreur
                                deliver_response(400,"La date et l'heure doivent avoir respectivement le format jj/mm/yyyy et hh:mm");
                            }
                        } else {
                            //Si le lieu du match n'est pas 1 ou 0, on renvoie un message d'erreur
                            deliver_response(400, "Le domicile doit avoir une valeur de 1 ou 0, (1=A domicile, 0=Chez l'adversaire)");
                        }
                    } else {
                        //Si l'ID ne correspond à aucun match, on renvoie un message d'erreur
                        deliver_response(404,"L'id rentré ne correspond à aucun match");
                    }
                } else {
                    //Si les toutes données requises ne sont pas présentes, on renvoie un message d'erreur
                    deliver_response(400, "L'id, la date, l'heure, l'équipe adverse, le lieu du match et le score sont requis");
                }
                break;

            case "DELETE":
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData,true);
                //Vérifie si l'id est spécifié dans le body ou non
                if ($data['id']){   
                    //Vérifie si le match existe
                    if (!empty(getOneMatch($linkpdo, $data['id']))){
                        //Vérifie si le match a déjà été joué (possède une feuille de match)
                        if (!aUneFeuilleDeMatch($linkpdo,$data["id"])){
                            //Si tout est vérifier, alors on supprime le match
                            supprimerUnMatch($linkpdo,$data["id"]);
                            deliver_response(200,"Le match a bien été supprimé");
                        } else {
                            //Si le match est déjà joué, on revoit l'erreur 403
                            //Cela permet de dire que le serveur comprend mais n'éxecute pas le code pour certaines raisons
                            deliver_response(403,"Le match a une feuille de match, il ne peut pas être supprimé");
                        }
                    } else {
                        //Si le match n'est pas trouvé, on renvoit l'erreur 404
                        deliver_response(404,"L'id doit correspondre à un match existant");
                    }
                } else {
                    //Si l'id n'est pas fourni c'est une "Bad request" de l'erreur 400
                    deliver_response(400,"Un id doit être fourni");
                }
                break;
            //Methode a implémenter pour les CORS
        }
    } else {
        // Réponse en cas d'échec de l'authentification
        deliver_response(401, "Veuillez vous connecter pour accéder à la ressource");
    }
    // Fermeture de la connexion à la base de données
    $linkpdo = null;
?>