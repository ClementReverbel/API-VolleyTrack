<?php

    function getJoueurActif($linkpdo){
        // Récupération de la liste des joueurs actifs
        $requeteJoueurs = $linkpdo->query("
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
       return $requeteJoueurs->fetchAll(PDO::FETCH_ASSOC);
    }


?>