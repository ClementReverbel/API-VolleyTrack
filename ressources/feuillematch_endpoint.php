<?php

include '../functions/gestionFeuilleMatch.php';
include 'deliver_response.php';
include '../functions/connexion_db.php';
include '../functions/verificationJWT.php';

if (getJwtValid()) {
    $linkpdo = connexion_db();
    $http_method = $_SERVER['REQUEST_METHOD'];
    switch ($http_method) {
        case "GET":
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                //Vérifie si l'ID est numérique (pas de texte)
                if (!is_numeric($id)) {
                    deliver_response(422, "L'ID doit être numérique");
                } else {
                    if (!empty(idMatchExiste($linkpdo, $id))) {
                    $joueurs = getJoueursSelectionnesAUnMatch($linkpdo, $id);
                    //Vérifie si la requête a bel et bien renvoyé des données
                        if (empty($joueurs)) {
                                deliver_response(404, "Le match choisi n'a pas de feuille de match");
                        } else {
                                deliver_response(200, "Joueurs du match selectionné récupérés avec succès", $joueurs);
                        }
                    } else {
                        deliver_response(404, "Aucun match trouvé avec cet ID");
                    }
        }
            } else {
                deliver_response(400, "Le requête GET a besoin de l'ID d'un match");
        }
            break;
        case "POST":
            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true);
            if (isset($data['id']) && isset($data['liste_joueurs']) && isset($data['liste_roles'])) {
                $response = ajouterFeuilleMatch($linkpdo, $data['id'], $data['liste_joueurs'], $data['liste_roles']);
                //Vérifie si la fonction renvoie un message d'erreur ou non
                if (str_contains($response, "Erreur")) {
                    if (str_contains($response, "enregistrement")) {
                        deliver_response(500, $response);
                    } else {
                        deliver_response(400, $response);
                    }
                } else {
                    deliver_response(201, $response);
                }
            } else {
                deliver_response(400, "L'id du match, la liste des id des jouers, la liste des roles sont requis");
            }
            break;
        case "PUT":
            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true);
            if (isset($data['id']) && isset($data['liste_joueurs']) && isset($data['liste_roles']) && isset($data['liste_notes'])) {
                $response = modifierFeuilleMatch($linkpdo, $data['id'], $data['liste_joueurs'], $data['liste_roles'], $data['liste_notes']);
                //Vérifie si la fonction renvoie un message d'erreur ou non
                if (str_contains($response, "Erreur")) {
                    if (str_contains($response, "enregistrement")) {
                        deliver_response(500, $response);
                    } else {
                        deliver_response(400, $response);
                    }
                } else {
                    deliver_response(200, $response);
                }
            } else {
                deliver_response(400, "L'id du match, la liste des id des jouers, la liste des roles et la liste des notes sont requis");
            }
            break;
        //Renvoie un code d'erreur correct pour les mauvaises requêtes et pas seulement une erreur serveur
        case "DELETE":
            deliver_response(405, "Méthode non implémentée, il est impossible de supprimer une feuille de match");
            break;
        //Methode a implémenter pour les CORS
        case "OPTIONS":
            deliver_response(200,"Prerequest validée");
            break;
    }
} else {
    deliver_response(401, "Veuillez vous connecter pour accéder à l'application");
}
$linkpdo = null;
?>