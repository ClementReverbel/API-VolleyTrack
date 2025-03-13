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
                } else {
                    // Sinon on récupère tous les joueurs actifs
                    $joueurs = getAllJoueur($linkpdo);
                    deliver_response(200, "Liste des joueurs actifs récupérée avec succès", $joueurs);
                }
                break;
            
            case "POST" :
                // Création d'un nouveau joueur
                if(isset($_POST['numLic']) && isset($_POST['nom']) && 
                isset($_POST['prenom']) && isset($_POST['date_de_naissance']) && 
                isset($_POST['taille']) && isset($_POST['poids']) && isset($_POST['commentaire'])){
                    //Si le numéro de licence est présent, on vérifie si il est valide
                    if(numLicValide($_POST['numLic'])){
                        $joueur = createJoueur($linkpdo, $_POST['numLic'], $_POST['nom'], 
                        $_POST['prenom'], $_POST['date_de_naissance'], $_POST['taille'], 
                        $_POST['poids'], $_POST['commentaire']);
                        deliver_response(201, "Joueur créé avec succès", $joueur);
                    } else {
                        //Si le numéro de licence ne fait pas 10 caractères, on renvoie une erreur
                        deliver_response(400, "Le numéro de licence est doit contenir 10 caractères");
                    }
                } else {
                    deliver_response(400, "Le numéro de licence, le nom, le prénom, la date de naissance, 
                    la taille, le poids et le commentaire sont requis pour créer un joueur");
                }
                break;
            
            case "PUT" :
                // Modification d'un joueur existant
                if(isset($_POST['numLic']) && isset($_POST['nom']) && isset($_POST['prenom']) && 
                    isset($_POST['date_de_naissance']) && isset($_POST['taille']) && 
                    isset($_POST['poids']) && isset($_POST['commentaire']) && isset($_POST['statut'])){
                    //Si le numéro de licence est présent, on vérifie si il est valide
                    if(numLicValide($_POST['numLic'])){
                        $joueur = updateJoueur($linkpdo, $_POST['numLic'], $_POST['nom'], $_POST['prenom'], 
                        $_POST['date_de_naissance'], $_POST['taille'], $_POST['poids'], $_POST['commentaire'], $_POST['statut']);
                        deliver_response(200, "Joueur modifié avec succès", $joueur);
                    } else {
                        //Si le numéro de licence ne fait pas 10 caractères, on renvoie une erreur
                        deliver_response(400, "Le numéro de licence est doit contenir 10 caractères");
                    }
                } else {
                    deliver_response(400, "Le numéro de licence, le nom, le prénom, la date de naissance,
                                        la taille, le poids, le commentaire et le statut sont requis pour modifier un joueur");
                }
                break;
            
            case "DELETE" :
                // Suppression d'un joueur existant
                if(isset($_POST['numLic'])){
                    //Si le numéro de licence est présent, on vérifie si le joueur a participé à un match
                    $joueur = deleteJoueur($linkpdo, $_POST['numLic']);
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