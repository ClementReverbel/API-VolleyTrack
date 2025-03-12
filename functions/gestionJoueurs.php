<?php
    //###########################################################################################################################
    //                      Méthode GET - Récupération des joueurs
    //###########################################################################################################################
    function getJoueurActif($linkpdo){
        // Récupération de la liste des joueurs actifs
        $requeteJoueurs = $linkpdo->prepare("
            SELECT idJoueur, CONCAT(Nom, ' ', Prenom) AS NomComplet, 
                Taille, 
                Poids, 
                (SELECT ROUND(SUM(Note)/COUNT(*), 1)
                FROM participer 
                WHERE participer.idJoueur = j.idJoueur
                ) AS Moyenne_note, 
                Commentaire 
            FROM joueurs j
            WHERE Statut = 'Actif'
        ");
        $requeteJoueurs->execute();
        return $requeteJoueurs->fetchAll(PDO::FETCH_ASSOC);
    }

    //###########################################################################################################################
    //                      Méthode PUT - Modification d'un joueur
    //###########################################################################################################################

    function updateJoueur($linkpdo, $idJoueur, $nom, $prenom, $date_de_naissance, $taille, $poids, $commentaire, $statut){
        $requete = $linkpdo->prepare('UPDATE joueurs SET Nom = :Nom, Prenom = :Prenom,
            Date_de_naissance = :Date_de_naissance, Taille = :Taille, 
            Poids = :Poids, Commentaire = :Commentaire, Statut = :statut 
            WHERE Numéro_de_licence = :num');
        $requete->execute(array('Nom' => $nom, 'Prenom' => $prenom,
            'Date_de_naissance' => $date_de_naissance, 'Taille' => $taille,
            'Poids' => $poids, 'Commentaire' => $commentaire, 'statut' => $statut,
            'num' => $idJoueur));
    }

    //###########################################################################################################################
    //                      Méthode DELETE - Suppression d'un joueur
    //###########################################################################################################################

    //Supprime un joueur si il n'a pas participer à un match
    function deleteJoueur($linkpdo, $NumLic){
            $nbMatchRequete = $linkpdo->prepare('SELECT count(*) FROM participer p  WHERE p.idJoueur = (SELECT idJoueur FROM joueurs WHERE Numéro_de_licence = :num)');
            $nbMatchRequete->execute([
                'num' => $NumLic
            ]);
            $nombreDeMatchs = $nbMatchRequete->fetchColumn();
            //Si le joueur à participer à un match, on ne peu pas le supprimer
            if ($nombreDeMatchs >= 1){
                return false;
            //Sinon on le supprime
            } else {
                $supprimerRequete = $linkpdo->prepare('DELETE FROM joueurs WHERE Numéro_de_licence = :num');
                $supprimerRequete->execute([
                    'num' => $NumLic
                ]);
                return true;
            }
    }
?>