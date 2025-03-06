<?php
    include "gestionJoueurs.php";
    include "gestionMatchs.php";
    
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
?>
