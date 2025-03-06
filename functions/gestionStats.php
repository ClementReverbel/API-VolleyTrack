<?php

    function getStatMatch($linkpdo){
        //Total match
        $requete = $linkpdo->query("SELECT COUNT(*) AS total FROM matchs");
        $totalMatchs = $requete->fetch(PDO::FETCH_ASSOC)['total'];

        $requete = $linkpdo->query("SELECT COUNT(*) AS total FROM matchs");
        $totalMatchs = $requete->fetch(PDO::FETCH_ASSOC)['total'];

        // Requête pour compter les matchs gagnés
        $requete = $linkpdo->query("SELECT COUNT(*) AS gagnes FROM matchs WHERE Resultat = 1");
        $gagnes = $requete->fetch(PDO::FETCH_ASSOC)['gagnes'];

        // Requête pour compter les matchs perdus
        $requete = $linkpdo->query("SELECT COUNT(*) AS perdus FROM matchs WHERE Resultat = 0");
        $perdus = $requete->fetch(PDO::FETCH_ASSOC)['perdus'];

        // Calcul des pourcentages
        $gagnesPourcentage = $totalMatchs > 0 ? round(($gagnes / $totalMatchs) * 100, 2) : 0;
        $perdusPourcentage = $totalMatchs > 0 ? round(($perdus / $totalMatchs) * 100, 2) : 0;

        return ['nbgagne'=>$gagnes, 'nbperdu'=>$perdus, 'pourcentgagne'=>$gagnesPourcentage, 'pourcentperdu'=>$perdusPourcentage];
    }

    function getStatJoueur($linkpdo){
        return $linkpdo->query("
                        SELECT 
                            j.idJoueur,
                            j.Nom,
                            j.Prenom,
                            j.Statut,
                            (SELECT Poste
                                FROM participer
                                WHERE participer.idJoueur = j.idJoueur AND Poste != 'Remplaçant'
                                GROUP BY Poste
                                ORDER BY COUNT(*) DESC
                            LIMIT 1) AS Poste_prefere,
                            (SELECT COUNT(*) 
                                FROM participer 
                                WHERE participer.idJoueur = j.idJoueur AND participer.Role_titulaire = 1
                            ) AS Total_titulaire,
                            (SELECT COUNT(*) 
                                FROM participer 
                                WHERE participer.idJoueur = j.idJoueur AND participer.Role_titulaire = 0
                            ) AS Total_remplacant,
                            (SELECT ROUND(SUM(Note)/COUNT(*),1)
                                FROM participer 
                                WHERE participer.idJoueur = j.idJoueur
                            ) AS Moyenne_note,
                            (ROUND(( 
                                SELECT COUNT(*)
                                FROM participer p, matchs m
                                WHERE p.idJoueur = j.idJoueur 
                                AND m.id_match=p.idMatch
                                AND m.Resultat = 1
                            ) / (
                                SELECT COUNT(*)
                                FROM participer p
                                WHERE p.idJoueur = j.idJoueur
                            ) * 100, 0)) AS Pourcentage_gagne
                        FROM joueurs AS j
                        GROUP BY j.idJoueur
                    ");
    }

    function getSelectionsConsecutives($linkpdo){
        $joueurs=getStatJoueur($linkpdo);
        // Calcul des sélections consécutives
        $selectionsConsecutives = [];
        while ($joueur = $joueurs->fetch(PDO::FETCH_ASSOC)) {
            $idJoueur = $joueur['idJoueur'];
        
            // Récupérer les dates des matchs triées pour ce joueur
            $datesjoueurs = $linkpdo->query("
                SELECT idMatch
                FROM participer
                WHERE idJoueur = $idJoueur 
                ORDER BY idMatch DESC
            ")->fetchAll(PDO::FETCH_COLUMN);

            // Récupère toutes les dates des matchs joués
            $datesmatch = $linkpdo->query("
                SELECT DISTINCT idMatch
                FROM participer
                ORDER BY idMatch DESC
            ")->fetchAll(PDO::FETCH_COLUMN);
        
            // Calculer les sélections consécutives
            $currentConsecutives = 0;
            $i=0;
            //Vérifie si un joueur a participé à un match
            if (count($datesjoueurs)-1!=-1) {
                $currentConsecutives = 1;
                //Tant que la dernière date du joueur est celle du dernier match joué, on continue
                while($datesjoueurs[$i]==$datesmatch[$i] && $i<count($datesjoueurs)-1){
                    $currentConsecutives++;
                    $i++;
                }
            }
            $selectionsConsecutives[$idJoueur] = $currentConsecutives;
        
        }
        return $selectionsConsecutives;
    }

?>