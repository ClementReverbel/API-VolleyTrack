<?php

    //###########################################################################################################################
    //                     Fonctions utiles
    //###########################################################################################################################

    //Vérifie la validité du nombre de set rentré dans un score au format :
    //25-20, 25-20, 25-20...
    function getNbSetValide($score){
        //Explose la chaine de caractère pour obtenir chaque set séparément
        $score_explode = explode(',', $score);
        //Vérifie si le nombre de set est supérieur à 3 et inférieur à 5
        return sizeof($score_explode)>=3 && sizeof($score_explode)<=5 ;
    }

    //Vérifie la validité des scores de chaque set d'un score au format :
    //25-20, 25-20, 25-20...
    function getScoreValide($score){
        //Valide automatiquement le score, il sera changé en cas de problème
        $validite="Ok";
        //Explose la chaine de caractère pour obtenir chaque set séparément
        $score_explode = explode(',', $score);
        //Incrémenteur qui vérifie si nous sommes au dernier set
        $i=1;
        //On parcourt chaque set
        foreach($score_explode as $set){
            //On récupère les scores de chaque équipe (0=nous, 1=adversaire)
            $set_explode=explode('-',$set);

            //Vérifie si le score des sets est au moins à 25 pour les 4 premiers set
            if($set_explode[0]<25 && $set_explode[1]<25 && $i<5){
                $validite="Un score n'a pas les 25 points requis de validité";
                break;
            //Vérifie si le score du dernier set est au moins à 15
            } elseif ($set_explode[0]<15 && $set_explode[1]<15 && $i==5){
                $validite="Le dernier set n'a pas les 15 points requis de validité";
                break;
            }

            $ecart=$set_explode[0]-$set_explode[1];
            //Si le score du set nécessite la vérification de l'écart
            if((($set_explode[0]>=25 || $set_explode[1]>=25) && $i<5) || (($set_explode[0]>=15 || $set_explode[1]>=15) && $i==5)){
                //Vérifie si l'écart entre les deux scores est au moins de 2 
                if($ecart<2 && $ecart>-2){
                    $validite="L'écart entre deux scores est inférieur à 2";
                    break;
                }
            }

            //On passe au set suivant
            $i++;
        }
        return $validite;
    }


    //###########################################################################################################################
    //                      Méthode GET - Récupération des données des matchs
    //###########################################################################################################################

    //Récupère la date et l'heure d'un match
    function getDateMatch($linkpdo,$idMatch){
        $requeteDateMatch = $linkpdo->prepare("
            SELECT Date_heure_match
            FROM matchs
            WHERE id_match = :id;
        ");
        $requeteDateMatch -> execute(array("id" => $idMatch));
        $dateHeureMatch = $requeteDateMatch->fetch();
        return $dateHeureMatch;
    }


    //Récupère tous les matchs 
    function getAllMatchs($linkpdo){
        $matchs = $linkpdo->query("SELECT * FROM matchs");
        $tableau_res=[];
        $i=0;
        while ($match = $matchs->fetch(PDO::FETCH_ASSOC)) {
                $domicile = "";
                //Changement du type boolean en oui ou non
                if($match['Rencontre_domicile'] === 1){
                    $domicile = "OUI";
                } else {
                    $domicile = "NON";
                }

                $gagne = "";
                //Chagement du type boolean en Gagné ou perdu
                if($match['Resultat'] === 1){
                    $gagne = "GAGNÉ";
                } else if($match['Resultat'] === 0){
                    $gagne = "PERDU";
                }
                
                //Pour mieux afficher les données dans le tableau je transforme mon type Datetime de SQL 
                //en quelque chose de plus lisible
                $date_heure_return = $match['Date_heure_match'];
                list($date_return, $time_return) = explode(' ', $date_heure_return);

                // Change la date en dd-mm-yyyy
                $dateFormatted = date('d-m-Y', strtotime($date_return));

                // Garder uniquement l'heure hh:mm
                $timeFormatted = substr($time_return, 0, 5);
                
                //Concaténation pour l'affichage
                $date_heure = $dateFormatted." ".$timeFormatted;

                $tableau_res[$i]=[
                    'id' => $match['id_match'],
                    'date_heure' => $date_heure,
                    'equipeadv' => $match['Nom_equipe_adverse'],
                    'domicile' => $domicile,
                    'score' => $match['Score'],
                    'gagne' => $gagne
                ];
                $i++;
        }
        return $tableau_res;
    }
    
    //Récupère le match d'un ID donné, si l'id n'a pas de match, renvoie null
    function getOneMatch($linkpdo, $idMatch){
        $requete = $linkpdo->prepare('SELECT * FROM matchs WHERE id_match = :id');
        $requete->execute(array('id'=>$idMatch));
        $match = $requete->fetch(PDO::FETCH_ASSOC);
        if (!empty($match)){
            $domicile = "";
            //Changement du type boolean en oui ou non
            if($match['Rencontre_domicile'] === 1){
                $domicile = "OUI";
            } else {
                $domicile = "NON";
            }

            $gagne = "";
            //Chagement du type boolean en Gagné ou perdu
            if($match['Resultat'] === 1){
                $gagne = "GAGNÉ";
            } else if($match['Resultat'] === 0){
                $gagne = "PERDU";
            }
            
            //Pour mieux afficher les données dans le tableau je transforme mon type Datetime de SQL 
            //en quelque chose de plus lisible
            $date_heure_return = $match['Date_heure_match'];
            list($date_return, $time_return) = explode(' ', $date_heure_return);

            // Change la date en dd-mm-yyyy
            $dateFormatted = date('d-m-Y', strtotime($date_return));

            // Garder uniquement l'heure hh:mm
            $timeFormatted = substr($time_return, 0, 5);
            
            //Concaténation pour l'affichage
            $date_heure = $dateFormatted." ".$timeFormatted;

            return [
                'id' => $match['id_match'],
                'date_heure' => $date_heure,
                'equipeadv' => $match['Nom_equipe_adverse'],
                'domicile' => $domicile,
                'score' => $match['Score'],
                'gagne' => $gagne
            ];
        }
    }


    //###########################################################################################################################
    //                      Méthode PUT - Modification d'un match
    //###########################################################################################################################

    //Modifie un match avec sont id et tous les paramètres necessaires (date, heure, equipe adverse, domicile, resultat)
    function updateMatch($linkpdo, $idMatch, $date, $heure, $equipeadv, $domicile, $score){
        $requete = $linkpdo->prepare('UPDATE matchs SET Date_heure_match = :date_time, Nom_equipe_adverse = :equipeadv, Rencontre_domicile = :domicile , Resultat = :resultat WHERE id_match = :id');
        
        //Transformation de dd-mm-yyyy en yyyy-mm-dd
        $date_explode = explode('-', $date);
        $date_dateTime= $date_explode[2] . '-' . $date_explode[1] . '-' . $date_explode[0];
        $date_time = ($date_dateTime.' '.$heure.':00');

        $requete->execute(array('date_time'=>$date_time,'equipeadv'=>$equipeadv,
        'domicile'=>$domicile, 'id'=>$idMatch, 'resultat'=>$score));
        updateScore($linkpdo,$idMatch,$score);
    }

    //Fonction qui met automatiquement le résultat à 1 ou 0 en fonction de notre score
    function updateScore($linkpdo, $idMatch, $score){
        $requete = $linkpdo->prepare('UPDATE matchs SET Score = :score , Resultat = :resultat WHERE id_match = :id');
        
        $sets = explode(',', $score);
        $sets_gagnes = 0;
        
        // Compter les sets gagnés
        foreach ($sets as $set) {
            $scores_set = explode('-', trim($set));
            if (count($scores_set) == 2) {
                //Etant toujours à droite dans le score, nous pouvons validé comme ceci
                if ($scores_set[0] > $scores_set[1]) {
                    $sets_gagnes++;
                }
            }
        }
        
        // Déterminer si le match est gagné (3 sets ou plus)
        $resultat = ($sets_gagnes >= 3) ? 1 : 0;
        
        $requete->execute(array('score'=>$score, 'id'=>$idMatch, 'resultat'=>$resultat));
    }

    //###########################################################################################################################
    //                      Méthode POST - Création d'un match
    //###########################################################################################################################

    //Ajoute un match à la base de données
    function ajouterMatch($linkpdo, $date, $heure, $equipeadv, $domicile){
        //Insertion du nouveau match
        $requete = $linkpdo->prepare('INSERT INTO matchs(Date_heure_match,Nom_equipe_adverse,Rencontre_domicile)
        VALUES (:date_time,:equipeadv,:domicile)');
        
        //Transformation de dd-mm-yyyy en yyyy-mm-dd
        $date_explode = explode('-', $date);
        $date_dateTime= $date_explode[2] . '-' . $date_explode[1] . '-' . $date_explode[0];
        $date_time = ($date_dateTime.' '.$heure.':00');

        //liaison du formulaire à la requete SQL
        $requete->execute(array('date_time'=>$date_time,'equipeadv'=>$equipeadv,
        'domicile'=>$domicile));
    }

    //###########################################################################################################################
    //                      Méthode DELETE - Suppresion d'un match
    //###########################################################################################################################

    //Renvoie true si le match a une feuille de match associée
    //Renvoie false sinon
    function aUneFeuilleDeMatch($linkpdo,$idMatch){
        $feuilleMatchRequete = $linkpdo->prepare('SELECT count(*) as nb FROM participer WHERE idMatch=:idMatch');
        $feuilleMatchRequete->execute([
            'idMatch' => $idMatch
        ]);
        $resultat = $feuilleMatchRequete->fetch(PDO::FETCH_ASSOC);
        return $resultat['nb']>0;
    }
    
    //Permet de supprimer un match ne disposant pas de feuille de match et n'ayant pas dépassé sa date
    function supprimerUnMatch($linkpdo, $idMatch){
        $supprimerRequete = $linkpdo->prepare('DELETE FROM matchs WHERE id_match = :idMatch');
        $supprimerRequete->execute([
            'idMatch' => $idMatch
        ]);
    }

?>