<?php
    include "gestionJoueurs.php";
    include "gestionMatchs.php";

    //###########################################################################################################################
    //                       REFACTORISATION - Fonctions factorisées
    //###########################################################################################################################
    
    //Valide la liste de joueurs et roles pour une feuille de match
    function validerDonneesFeuilleMatch($idJoueurs, $roles) {
        //Vérification du nombre de joueurs
        if (count($idJoueurs) < 6) {
            return "Veuillez sélectionner au moins 6 joueurs.";
        }
        if (count($idJoueurs) > 12) {
            return "Vous ne pouvez pas sélectionner plus de 12 joueurs.";
        }
        //Vérification de la non-sélection de joueurs identiques
        if (count($idJoueurs) !== count(array_unique($idJoueurs))) {
            return "Un joueur ne peut pas être sélectionné deux fois.";
        }
        //Vérification que le nombre de rôles correspond au nombre de joueurs
        if (sizeof($roles) != sizeof($idJoueurs)) {
            return "Il faut attribuer un rôle à chaque joueur (ni plus ni moins)";
        }
        return '';
    }


    //Vérifie si les joueurs sélectionnés sont actifs
    function verifierJoueursActifs($idJoueurs, $joueursActif) {
        //Créer la liste des id des joueurs actifs
        $listeidactif = array();
        foreach($joueursActif as $joueur) {
            array_push($listeidactif, $joueur['idJoueur']);
        }
        //Vérification que les joueurs sélectionnés sont actifs
        foreach($idJoueurs as $idJoueur) {
            if(!in_array($idJoueur, $listeidactif)) {
                return "Les joueurs sélectionnés doivent existés et être actifs";
            }
        }
        return '';
    }

    //###########################################################################################################################
    //                      Méthode Get - Récupérer les joueurs d'un match donné
    //###########################################################################################################################

    function getJoueursSelectionnesAUnMatch($linkpdo,$idMatch){
        // Récupérer les joueurs déjà sélectionnés pour ce match
        $requeteJoueursSelectionnes = $linkpdo->prepare("
            SELECT p.idJoueur, CONCAT(j.Nom, ' ', j.Prenom) AS NomComplet, 
                j.Taille, 
                j.Poids, 
                (SELECT ROUND(SUM(Note)/COUNT(*), 1)
                FROM participer 
                WHERE participer.idJoueur = j.idJoueur
                ) AS Moyenne_note, 
                j.Commentaire,
                p.Poste, 
                p.Role_titulaire
            FROM participer p, joueurs j
            WHERE p.idJoueur = j.idJoueur
            AND p.idMatch = :idMatch
        ");
        $requeteJoueursSelectionnes->execute([':idMatch' => $idMatch]);
        return $requeteJoueursSelectionnes->fetchAll(PDO::FETCH_ASSOC);
    }

    //###########################################################################################################################
    //                      Méthode POST - Insertion d'une feuille de match
    //###########################################################################################################################

    //Script SQL - Insertion dans la BD de la feuille de match
    function insererFeuilleMatch($linkpdo, $idJoueurs, $roles, $idMatch) {
        try {
            $requete = $linkpdo->prepare("
                INSERT INTO participer (idJoueur, idMatch, Role_titulaire, Poste)
                VALUES (:idJoueur, :idMatch, :Role_titulaire, :Poste)
            ");
            //Pour chaque joueur, je vérifie si il est titulaire
            for($i = 0; $i < sizeof($idJoueurs); $i++) {
                $idJoueur = $idJoueurs[$i];
                $role = $roles[$i];
                $roleTitulaire = ($role != 5);

                $requete->execute([
                    ':idJoueur' => $idJoueur,
                    ':idMatch' => $idMatch,
                    ':Role_titulaire' => $roleTitulaire,
                    ':Poste' => $role
                ]);
            }
            return true;
        } catch (Exception $e) {
            return "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }

    //Fonction a appeler pour ajouter une feuille de matchs avec des joueurs et roles donnés
    function ajouterFeuilleMatch($linkpdo, $idMatch, $listeidjoueur, $listerole) {
        $joueursActif = getJoueurActif($linkpdo);
        $message = '';
        $dateHeureMatch = getDateMatch($linkpdo, $idMatch);

        $joueurs = !empty($listeidjoueur) ? array_filter($listeidjoueur) : [];
        $roles = !empty($listerole) ? $listerole : [];

        // Vérifications des données
        $message = validerDonneesFeuilleMatch($joueurs, $roles);
        
        if (empty($message)) {
            $message = verifierJoueursActifs($joueurs, $joueursActif);
        }

        // Si tout est valide, insérer dans la base de données
        if (empty($message)) {
            $resultat = insererFeuilleMatch($linkpdo, $joueurs, $roles, $idMatch);
            if ($resultat === true) {
                $message = "Sélection des joueurs enregistrée avec succès pour le match du ". $dateHeureMatch['Date_heure_match'].".";
            } else {
                $message = "Erreur lors de l'enregistrement : " + $message;
            }
        }

        return $message;
    }

    //###########################################################################################################################
    //                  Méthode PUT - Modification d'une feuille de match
    //###########################################################################################################################


    //Modifie la place d'un joueur donné dans un feuille de match 
    function miseAJourFeuilleMatch($linkpdo, $idMatch, $joueurs, $roles, $notes,$joueursAvantModif){
        try {
            $requeteUpdate = $linkpdo->prepare("
                UPDATE participer set idJoueur=:idJoueur, Role_titulaire=:Role_titulaire, Poste=:Poste, Note=:Note)
                WHERE idMatch=:idMatch
            ");

            $requeteInsert = $linkpdo->prepare("
                INSERT INTO participer (idJoueur, idMatch, Role_titulaire, Poste, Note)
                VALUES (:idJoueur, :idMatch, :Role_titulaire, :Poste, :Note)
            ");

            for($i = 0; $i < sizeof($joueurs); $i++) {
                $idJoueur = $joueurs[$i];
                $role = $roles[$i];
                $roleTitulaire = ($role != 5);

                if(in_array($joueurs[$i],$joueursAvantModif)){
                    $requeteUpdate->execute([
                        ':idJoueur' => $idJoueur,
                        ':idMatch' => $idMatch,
                        ':Role_titulaire' => $roleTitulaire,
                        ':Poste' => $role,
                        ':Note' => $notes[$i]
                    ]);
                } else {
                    if (sizeof($joueurs)<=sizeof($joueursAvantModif)){

                    } else {
                        $requeteInsert->execute([
                            ':idJoueur' => $idJoueur,
                            ':idMatch' => $idMatch,
                            ':Role_titulaire' => $roleTitulaire,
                            ':Poste' => $role,
                            ':Note' => $notes[$i]
                        ]);
                    }
                }
            }        
            return true; 
        }catch (Exception $e) {
            return "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }

    //Fonction a appeler pour modifier une feuille de matchs avec des joueurs et roles donnés
    function modifierFeuilleMatch($linkpdo, $idMatch, $listeidjoueur, $listerole, $listenote){
        $message = '';
        $joueursActif = getJoueurActif($linkpdo);
        $dateHeureMatch = getDateMatch($linkpdo, $idMatch);

        $joueurs = !empty($listeidjoueur) ? array_filter($listeidjoueur) : [];
        $roles = !empty($listerole) ? $listerole : [];
        $notes = !empty($listenote) ? $listenote : [];

        // Vérifications des données
        $message = validerDonneesFeuilleMatch($joueurs, $roles);

        if (!(sizeof($joueurs)==sizeof($roles)==sizeof($notes))){
            $message= "Erreur, le nombre de joueurs/roles/notes ne correspond pas";
        }
        
        if (empty($message)) {
            $message = verifierJoueursActifs($joueurs, $joueursActif);
        }

       $joueursAvantModif= getJoueursSelectionnesAUnMatch($linkpdo,$idMatch);  

        // Si tout est valide, mettre à jour la base de données
        if (empty($message)) {
            $res=miseAJourFeuilleMatch($linkpdo,$idMatch,$joueurs,$roles,$notes,$joueursAvantModif);
            if ($res){
                $message = "Modification des joueurs enregistrée avec succès pour le match du ". $dateHeureMatch['Date_heure_match'].".";
            } else {
                $message = "Erreur lors de l'enregistrement : " + $message;
            }
        }
    }
?>
