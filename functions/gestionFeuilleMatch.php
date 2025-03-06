<?php
    include "gestionJoueurs.php";

    function ajouterFeuilleMatch($linkpdo,$idMatch,$listeidjoueur,$listerole){
        $joueursActif=getJoueurActif($linkpdo);

        $message = '';

        $requeteDateMatch = $linkpdo->prepare("
            SELECT Date_heure_match
            FROM matchs
            WHERE id_match = :id;
        ");
        $requeteDateMatch -> execute(array("id" => $idMatch));
        $dateHeureMatch = $requeteDateMatch->fetch(); 

        $joueurs = !empty($listeidjoueur) ? array_filter($listeidjoueur) : [];
        $roles = !empty($listerole) ? $listerole : [];

        // Vérifications des données
        if (count($joueurs) < 6) {
            $message = "Veuillez sélectionner au moins 6 joueurs.";
        } elseif (count($joueurs) > 12) {
            $message = "Vous ne pouvez pas sélectionner plus de 12 joueurs.";
        } elseif (count($joueurs) !== count(array_unique($joueurs))) {
            $message = "Un joueur ne peut pas être sélectionné deux fois.";
        } elseif (sizeof($roles)!=sizeof($joueurs)) {
            $message = "Il faut attribuer un rôle à chaque joueur (ni plus ni moins)";
        } else {
            $listeidactif=array();
            foreach($joueursActif as $joueur){
                array_push($listeidactif,$joueur['idJoueur']);
            }
            foreach($joueurs as $joueur){
                if(!in_array($joueur,$listeidactif)){
                    $message="Les joueurs sélectionnés doivent existés et être actifs";
                    break;
                }
            }
        }

        // Si tout est valide, insérer dans la base de données
        if (empty($message)) {
            try {
                $requete = $linkpdo->prepare("
                    INSERT INTO participer (idJoueur, idMatch, Role_titulaire, Poste)
                    VALUES (:idJoueur, :idMatch, :Role_titulaire, :Poste)
                ");

                for($i=0;$i<sizeof($joueurs);$i++){
                    $idJoueur=$joueurs[$i];
                    $role=$roles[$i];

                    //Les rôles seront associés de cette manière :
                    /*
                    1 - Attaquant
                    2 - Centre
                    3 - Passeur
                    4 - Libero
                    5 - Remplaçant
                    */

                    $roleTitulaire=($role!=5);

                    $requete->execute([
                        ':idJoueur' => $idJoueur,
                        ':idMatch' => $idMatch,
                        ':Role_titulaire' => $roleTitulaire,
                        ':Poste' => $role
                    ]);
                }
                $message = "Sélection des joueurs enregistrée avec succès pour le match du ". $dateHeureMatch['Date_heure_match'].".";
            } catch (Exception $e) {
                $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
            }
        }
    }
?>
