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
    
    function getAllJoueur($linkpdo){
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
        ");
        $requeteJoueurs->execute();
        return $requeteJoueurs->fetchAll(PDO::FETCH_ASSOC);
    }

    //Récupère un joueur avec son numéro de licence
    function getJoueur($linkpdo, $numLic){
        $requete = $linkpdo->prepare('SELECT * FROM joueurs WHERE Numéro_de_licence = :num');
        $requete->execute(array('num' => $numLic));
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    //###########################################################################################################################
    //                      Méthode PUT - Modification d'un joueur
    //###########################################################################################################################

    //Modifie un joueur avec son numéro de licence
    function updateJoueur($linkpdo, $numLic, $nom, $prenom, $date_de_naissance, $taille, $poids, $commentaire, $statut){
        $requete = $linkpdo->prepare('UPDATE joueurs SET Nom = :Nom, Prenom = :Prenom,
            Date_de_naissance = :Date_de_naissance, Taille = :Taille, 
            Poids = :Poids, Commentaire = :Commentaire, Statut = :statut 
            WHERE Numéro_de_licence = :num');
        $requete->execute(array('Nom' => $nom, 'Prenom' => $prenom,
            'Date_de_naissance' => $date_de_naissance, 'Taille' => $taille,
            'Poids' => $poids, 'Commentaire' => $commentaire, 'statut' => $statut,
            'num' => $numLic));
    }

    //###########################################################################################################################
    //                      Méthode DELETE - Suppression d'un joueur
    //###########################################################################################################################

    //Supprime un joueur si il n'a pas participer à un match
    function deleteJoueur($linkpdo, $numLic){
            $nbMatchRequete = $linkpdo->prepare('SELECT count(*) FROM participer p
            WHERE p.idJoueur = (SELECT idJoueur FROM joueurs WHERE Numéro_de_licence = :num)');
            $nbMatchRequete->execute([
                'num' => $numLic
            ]);
            $nombreDeMatchs = $nbMatchRequete->fetchColumn();
            //Si le joueur à participer à un match, on ne peu pas le supprimer
            if ($nombreDeMatchs >= 1){
                return false;
            //Sinon on le supprime
            } else {
                $supprimerRequete = $linkpdo->prepare('DELETE FROM joueurs WHERE Numéro_de_licence = :num');
                $supprimerRequete->execute([
                    'num' => $numLic
                ]);
                return true;
            }
    }

    //###########################################################################################################################
    //                      Méthode POST - Création d'un joueur
    //###########################################################################################################################

    //Crée un joueur
    function createJoueur($linkpdo, $numLic, $nom, $prenom, $date_de_naissance, $taille, $poids, $commentaire,){
        $date_explode = explode('-', $date_de_naissance);
        if(checkdate($date_explode[1], $date_explode[0] , $date_explode[2])){
            $date_dateTime= $date_explode[2] . '-' . $date_explode[1] . '-' . $date_explode[0];

            $requete = $linkpdo->prepare('INSERT INTO joueurs(Numéro_de_licence,Nom,Prenom,Date_de_naissance ,Taille,Poids,Commentaire,Statut)
                    VALUES (:num,:Nom,:Prenom,:Date_de_naissance ,:Taille, :Poids,:Commentaire,:Statut)');
            $requete->execute(array('num' => $numLic, 'Nom' => $nom, 'Prenom' => $prenom, 'Date_de_naissance' => $date_dateTime, 'Taille' => $taille, 'Poids' => $poids, 'Commentaire' => $commentaire, 'Statut' => 'Actif'));
            return true;
        } else {        
            return false;
        }
    }
?>