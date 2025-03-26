<?php
    include '../functions/gestionJoueurs.php';
    include 'deliver_response.php';
    include '../functions/connexion_db.php';
    include '../functions/verificationJWT.php';
    include '../functions/date_verifier.php';
    // Vérification de l'authentification
    if(getJwtValid()){
        // Connexion à la base de données
        $linkpdo = connexion_db();
        $http_method = $_SERVER['REQUEST_METHOD'];
        switch ($http_method){
            case "GET" :
                // Récupération d'un joueur spécifique si le numéro de licence est présent
                if(isset($_GET['numLic'])){
                    //Si le numéro de licence est présent, on vérifie si il est valide
                    if(numLicValide($_GET['numLic'])){
                        //Si le joueur existe, on le récupère
                        if(numLicExiste($linkpdo, $_GET['numLic'])){
                        $joueur = getJoueur($linkpdo, $_GET['numLic']);
                        deliver_response(200, "Joueur récupéré avec succès", $joueur);
                        } else {
                            //Si le joueur n'existe pas, on renvoie une erreur
                            deliver_response(404, "Le joueur avec ce numéro de licence n'existe pas");
                        }
                    } else {
                        //Si le numéro de licence ne fait pas 10 caractères, on renvoie une erreur
                        deliver_response(400, "Le numéro de licence est doit contenir 10 caractères et commencé par VOL");
                    }
                } elseif(isset($_GET['actif'])){
                    // Sinon on récupère tous les joueurs actifs
                    $joueurs = getJoueurActif($linkpdo);
                    deliver_response(200, "Liste des joueurs actifs récupérée avec succès", $joueurs);
                } else {
                    // Sinon on récupère tous les joueurs
                    $joueurs = getAllJoueur($linkpdo);
                    deliver_response(200, "Liste des joueurs récupérée avec succès", $joueurs);
                }
                break;
            
            case "POST" :
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData,true);
                // Création d'un nouveau joueur
                if(isset($data['numLic']) && isset($data['nom']) && 
                isset($data['prenom']) && isset($data['date_de_naissance']) && 
                isset($data['taille']) && isset($data['poids']) && isset($data['commentaire'])){
                    //Si le numéro de licence est présent, on vérifie si il est valide
                    if(numLicValide($data['numLic'])){
                        if(!numLicExiste($linkpdo, $data['numLic'])){
                            //Vérifie si la taille et le poids sont des chiffres
                            if(is_numeric($data['taille'])&&is_numeric($data['poids'])){
                                //Vérifie si la date est valide
                                if(date_valide($data["date_de_naissance"])){
                                $response = createJoueur($linkpdo, $data['numLic'], $data['nom'], 
                                $data['prenom'], $data['date_de_naissance'], $data['taille'], 
                                $data['poids'], $data['commentaire']);
                                deliver_response(201, "Joueur créé avec succès");
                                } else {
                                    //Si la date n'est pas valide, on renvoie une erreur
                                    deliver_response(400, "Mauvaise date");
                                }
                            } else {
                                //Si la taille ou le poids ne sont pas des chiffres, on renvoie une erreur
                                deliver_response(400, "La taille et le poids doivent être numérique");
                            }
                        } else {
                            //Si le joueur existe déjà, on renvoie une erreur
                            deliver_response(409, "Le joueur avec ce numéro de licence existe déjà");
                        }
                    } else {
                        //Si le numéro de licence ne fait pas 10 caractères, on renvoie une erreur
                        deliver_response(400, "Le numéro de licence est doit contenir 10 caractères et commencé par VOL");
                    }
                } else {
                    //Si une des informations requises est manquante, on renvoie une erreur
                    deliver_response(400, "Le numéro de licence, le nom, le prénom, la date de naissance, la taille, le poids et le commentaire sont requis pour créer un joueur");
                }
                break;
            
            case "PUT" :
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData,true);
                // Modification d'un joueur existant
                if(isset($data['numLic']) && isset($data['nom']) && isset($data['prenom']) && 
                    isset($data['date_de_naissance']) && isset($data['taille']) && 
                    isset($data['poids']) && isset($data['commentaire']) && isset($data['statut'])){
                    //Si le numéro de licence est présent, on vérifie si il est valide
                    if(numLicValide($data['numLic'])){
                        //Vérifi si le joueur existe
                        if(numLicExiste($linkpdo, $data['numLic'])){
                            //Vérifie si la taille et le poids sont des chiffres
                            if(is_numeric($data['taille'])&&is_numeric($data['poids'])){
                                //Vérifie si la date est valide
                                if(date_valide($data["date_de_naissance"])){
                                    //Vérifie si le statut est valide
                                    if(statutValide($data['statut'])){
                                    //Modification du joueur
                                    $joueur = updateJoueur($linkpdo, $data['numLic'], $data['nom'], $data['prenom'], 
                                    $data['date_de_naissance'], $data['taille'], $data['poids'], $data['commentaire'], $data['statut']);
                                    deliver_response(200, "Joueur modifié avec succès", $joueur);
                                    } else {
                                        //Si le statut n'est pas valide, on renvoie une erreur
                                        deliver_response(400, "Le statut doit être : Actif, Blessé, Suspendu ou Absent");
                                    }
                                } else {
                                    //Si la date n'est pas valide, on renvoie une erreur
                                    deliver_response(400, "Mauvaise date");
                                }
                            } else {
                                //Si la taille ou le poids ne sont pas des chiffres, on renvoie une erreur
                                deliver_response(400, "La taille et le poids doivent être numérique");
                            }
                        } else {
                            //Si le joueur n'existe pas, on renvoie une erreur
                            deliver_response(404, "Le joueur avec ce numéro de licence n'existe pas");
                        }
                    } else {
                        //Si le numéro de licence ne fait pas 10 caractères, on renvoie une erreur
                        deliver_response(400, "Le numéro de licence est doit contenir 10 caractères est commencer par VOL");
                    }
                } else {
                    //Si une des informations requises est manquante, on renvoie une erreur
                    deliver_response(400, "Le numéro de licence, le nom, le prénom, la date de naissance, la taille, le poids, le commentaire et le statut sont requis pour modifier un joueur");
                }
                break;
            
            case "DELETE" :
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData,true);
                // Suppression d'un joueur existant
                if(isset($data['numLic'])){
                    //Vérifie si le numéro de licence est valide
                    if(numLicValide($data['numLic'])){
                        //On vérifie si le joueur existe
                        if(numLicExiste($linkpdo, $data['numLic'])){
                        //On vérifie si le joueur a participé à un match
                        $joueur = deleteJoueur($linkpdo, $data['numLic']);
                            if($joueur){
                                //Si le joueur n'a participé à aucun match, on le supprime
                                deliver_response(200, "Joueur supprimé avec succès");
                            } else {
                                //Sinon la suppression n'est pas possible
                                deliver_response(403, "Le joueur a participé à un match et ne peut pas être supprimé");
                            }
                        } else {
                            //Si le joueur n'existe pas, on renvoie une erreur
                            deliver_response(404, "Le joueur avec ce numéro de licence n'existe pas");
                        }
                    } else {
                        //Si le numéro de licence ne fait pas 10 caractères, on renvoie une erreur
                        deliver_response(400, "Le numéro de licence est doit contenir 10 caractères et commencé par VOL");
                    }
                } else {
                    //Si le numéro de licence n'est pas présent, on renvoie une erreur
                    deliver_response(400, "Le numéro de licence est requis pour supprimer un joueur");
                }
                break;
            //Methode a implémenter pour les CORS
            case "OPTIONS":
                deliver_response(200,"Prerequest validée");
                break;
        }
    } else {
         // Réponse en cas d'échec de l'authentification
        deliver_response(401, "Veuillez vous connecter pour accéder à la ressource");
    }
    // Fermeture de la connexion à la base de données
    $linkpdo = null;
?>