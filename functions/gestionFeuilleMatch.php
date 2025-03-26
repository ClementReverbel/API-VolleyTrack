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
            return "Erreur, veuillez sélectionner au moins 6 joueurs.";
        }
        if (count($idJoueurs) > 12) {
            return "Erreur, vous ne pouvez pas sélectionner plus de 12 joueurs.";
        }
        //Vérification de la non-sélection de joueurs identiques
        if (count($idJoueurs) !== count(array_unique($idJoueurs))) {
            return "Erreur, un joueur ne peut pas être sélectionné deux fois.";
        }
        //Vérification que le nombre de rôles correspond au nombre de joueurs
        if (sizeof($roles) != sizeof($idJoueurs)) {
            return "Erreur, il faut attribuer un rôle à chaque joueur (ni plus ni moins)";
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
                return "Erreur, les joueurs sélectionnés doivent existés et être actifs";
            }
        }
        return '';
    }

    function idMatchExiste($linkpdo, $idMatch) {
        $requeteMatch = $linkpdo->prepare("SELECT * FROM matchs WHERE id_match = :idMatch");
        $requeteMatch->execute([':idMatch' => $idMatch]);
        return $requeteMatch->fetch(PDO::FETCH_ASSOC);
    }
    //###########################################################################################################################
    //                      Méthode GET - Récupérer les joueurs d'un match donné
    //###########################################################################################################################

    function getJoueursSelectionnesAUnMatch($linkpdo,$idMatch){
        // Récupérer les joueurs déjà sélectionnés pour ce match
        $requeteJoueursSelectionnes = $linkpdo->prepare("
            SELECT idJoueur
            FROM participer
            WHERE idMatch = :idMatch
        ");
        $requeteJoueursSelectionnes->execute([':idMatch' => $idMatch]);
        return $requeteJoueursSelectionnes->fetchAll();
    }

    //###########################################################################################################################
    //                      Méthode POST - Insertion d'une feuille de match
    //###########################################################################################################################

    //Script SQL - Insertion dans la BD de la feuille de match
    function insererFeuilleMatch($linkpdo, $idJoueurs, $roles, $idMatch) {
            $checkEmpty = $linkpdo->prepare("SELECT idMatch FROM participer WHERE idMatch = :idMatch");
            $checkEmpty->execute([':idMatch' => $idMatch]);
            $empty = $checkEmpty->fetchAll(PDO::FETCH_ASSOC);
            //Si la feuille de match existe déjà, on ne peut pas la créer
            if(empty($empty)){
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
            } else {
                return "Erreur, une feuille de match existe déjà pour ce match";
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
                $message = $resultat;
            }
        }

        return $message;
    }

    //###########################################################################################################################
    //                  Méthode PUT - Modification d'une feuille de match
    //###########################################################################################################################

    function deleteAncienJoueur($linkpdo,$idMatch,$joueurs,$joueursAvantModif){
        //Partie du code qui enlève les joueurs retirés de la feuille de match
        //D'abord on vérifie si c'est le cas avec la taille des listes
        if (sizeof($joueurs)<=sizeof($joueursAvantModif)){
            $requeteDelete = $linkpdo->prepare("
            DELETE FROM participer WHERE idMatch=:idMatch AND idJoueur=:idJoueur");
            //Ensuite on parcours chaque ancien joueur
            foreach($joueursAvantModif as $joueurSelected){
                //Et on vérifie s'ils sont dans la nouvelle liste
                $idJoueurSelected = $joueurSelected['idJoueur'];
                if(!in_array($idJoueurSelected,$joueurs)){
                    //Si ce n'est pas le cas, on le delete
                    $requeteDelete->execute([
                        ':idJoueur' => $idJoueurSelected,
                        ':idMatch' => $idMatch
                    ]);
                }
            }
        }
    }

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

            //Retirons les anciens joueurs non présents dans la modification
            deleteAncienJoueur($linkpdo,$idMatch,$joueurs,$joueursAvantModif);

            //Ensuite on parcourt la liste d'id des nouveaux joueurs
            for($i = 0; $i < sizeof($joueurs); $i++) {
                $idJoueur = $joueurs[$i];
                $role = $roles[$i];
                $roleTitulaire = ($role != 5);

                //S'ils étaient déjà dans la table, on les update
                if(in_array($joueurs[$i],$joueursAvantModif)){
                    $requeteUpdate->execute([
                        ':idJoueur' => $idJoueur,
                        ':idMatch' => $idMatch,
                        ':Role_titulaire' => $roleTitulaire,
                        ':Poste' => $role,
                        ':Note' => $notes[$i]
                    ]);
                } else {
                    //Sinon on les rajoute dans la table
                    $requeteInsert->execute([
                        ':idJoueur' => $idJoueur,
                        ':idMatch' => $idMatch,
                        ':Role_titulaire' => $roleTitulaire,
                        ':Poste' => $role,
                        ':Note' => $notes[$i]
                    ]);
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

        //Nous vérifions que les données fournies ne sont pas vides
        $joueurs = !empty($listeidjoueur) ? array_filter($listeidjoueur) : [];
        $roles = !empty($listerole) ? $listerole : [];
        $notes = !empty($listenote) ? $listenote : [];

        // Vérifications des données
        $message = validerDonneesFeuilleMatch($joueurs, $roles);

        if (!(sizeof($joueurs)==sizeof($roles)&&sizeof($roles)==sizeof($notes)&&sizeof($joueurs)==sizeof($notes))){
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
                $message = "Erreur lors de l'enregistrement";
            }
        }
        return $message;
    }
?>
