<?php
    include '../functions/gestionJoueurs.php';
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
                // Récupération d'un joueur spécifique si le numéro de licence est présent
                if(isset($_GET['numLic'])){
                    //Si le numéro de licence est présent, on vérifie si il est valide
                    if(numLicValide($_GET['numLic'])){
                        $joueur = getJoueur($linkpdo, $_GET['numLic']);
                        deliver_response(200, "Joueur récupéré avec succès", $joueur);
                    } else {
                        //Si le numéro de licence ne fait pas 10 caractères, on renvoie une erreur
                        deliver_response(400, "Le numéro de licence est doit contenir 10 caractères");
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
                        //Vérifie si la taille et le poids sont des chiffres
                        if(is_numeric($data['taille'])&&is_numeric($data['poids'])){
                            $response = createJoueur($linkpdo, $data['numLic'], $data['nom'], 
                            $data['prenom'], $data['date_de_naissance'], $data['taille'], 
                            $data['poids'], $data['commentaire']);
                            if($response){
                                deliver_response(201, "Joueur créé avec succès");
                            } else {
                                deliver_response(201, "Mauvaise date");
                            }
                        } else {
                            deliver_response(400, "La taille et le poids doivent être numérique");
                        }
                    } else {
                        //Si le numéro de licence ne fait pas 10 caractères, on renvoie une erreur
                        deliver_response(400, "Le numéro de licence est doit contenir 10 caractères");
                    }
                } else {
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
                        $joueur = updateJoueur($linkpdo, $data['numLic'], $data['nom'], $data['prenom'], 
                        $data['date_de_naissance'], $data['taille'], $data['poids'], $data['commentaire'], $data['statut']);
                        deliver_response(200, "Joueur modifié avec succès", $joueur);
                    } else {
                        //Si le numéro de licence ne fait pas 10 caractères, on renvoie une erreur
                        deliver_response(400, "Le numéro de licence est doit contenir 10 caractères");
                    }
                } else {
                    deliver_response(400, "Le numéro de licence, le nom, le prénom, la date de naissance, la taille, le poids, le commentaire et le statut sont requis pour modifier un joueur");
                }
                break;
            
            case "DELETE" :
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData,true);
                // Suppression d'un joueur existant
                if(isset($data['numLic'])){
                    //Si le numéro de licence est présent, on vérifie si le joueur a participé à un match
                    $joueur = deleteJoueur($linkpdo, $data['numLic']);
                    if($joueur){
                        //Si le joueur n'a participé à aucun match, on le supprime
                        deliver_response(200, "Joueur supprimé avec succès");
                    } else {
                        //Sinon la suppression n'est pas possible
                        deliver_response(405, "Le joueur a participé à un match et ne peut pas être supprimé");
                    }
                } else {
                    //Si le numéro de licence n'est pas présent, on renvoie une erreur
                    deliver_response(400, "Le numéro de licence est requis pour supprimer un joueur");
                }
                break;
        }
    } else {
         // Réponse en cas d'échec de l'authentification
        deliver_response(401, "Veuillez vous connecter pour accéder à la ressource");
    }
    // Fermeture de la connexion à la base de données
    $linkpdo = null;

    function numLicValide($numLic){
        return strlen($numLic) == 10;
    }
?>