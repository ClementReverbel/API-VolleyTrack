<?php

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

    //Récupère un match en fonction de son id
    function isMatchJouer($linkpdo, $idMatch){
              //Date actuelle sous forme de tableau
              $date_array = getdate();

              //Récupération des bonnes informations de la date actuelle
              $date_actu = $date_array['mday']."-".$date_array['mon']."-".$date_array['year'];

              //Récupération de la date et de l'heure du match
              $date_heure_match = getDateMatch($linkpdo, $idMatch);

              //Comparaison des dates
              return $date_actu > $date_heure_match;
    }
    
    function getOneMatch($linkpdo, $idMatch){
        $requete = $linkpdo->prepare('SELECT * FROM matchs WHERE id_match = :id');
        $requete->execute(array('id'=>$idMatch));
        $match = $requete->fetch(PDO::FETCH_ASSOC);
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


    //###########################################################################################################################
    //                      Méthode PUT - Modification d'un match
    //###########################################################################################################################

    //Modifie un match avec sont id et tous les paramètres necessaires (date, heure, equipe adverse, domicile, resultat)
    function updateMatch($linkpdo, $idMatch, $date, $heure, $equipeadv, $domicile, $resultat){
        $requete = $linkpdo->prepare('UPDATE matchs SET Date_heure_match = :date_time, Nom_equipe_adverse = :equipeadv, Rencontre_domicile = :domicile , Resultat = :resultat WHERE id_match = :id');
        $date_time = ($date.' '.$heure.':00');
        $requete->execute(array('date_time'=>$date_time,'equipeadv'=>$equipeadv,
        'domicile'=>$domicile, 'id'=>$idMatch, 'resultat'=>$resultat));
    }

    //###########################################################################################################################
    //                      Méthode POST - Création d'un match
    //###########################################################################################################################

      //Ajoute un match à la base de données
      function ajouterMatch($linkpdo, $date, $heure, $equipeadv, $domicile){
        //Insertion du nouveau match
        $requete = $linkpdo->prepare('INSERT INTO matchs(Date_heure_match,Nom_equipe_adverse,Rencontre_domicile)
        VALUES (:date_time,:equipeadv,:domicile)');
        //Transformation de la date est de l'heure rentrée en type Datetime
        $date_time = ($date.' '.$heure.':00');
        //liaison du formulaire à la requete SQL
        $requete->execute(array('date_time'=>$date_time,'equipeadv'=>$equipeadv,
        'domicile'=>$domicile));
    }

    function updateScore($linkpdo, $idMatch, $score){
        $requete = $linkpdo->prepare('UPDATE matchs SET Score = :score , Resultat = :resultat WHERE id_match = :id');
        
        $sets = explode(',', $score);
        $sets_gagnes = 0;
        
        // Compter les sets gagnés
        foreach ($sets as $set) {
            $scores_set = explode('-', trim($set));
            if (count($scores_set) === 2) {
                if (intval($scores_set[0]) > intval($scores_set[1])) {
                    $sets_gagnes++;
                }
            }
        }
        
        // Déterminer si le match est gagné (3 sets ou plus)
        $resultat = ($sets_gagnes >= 3) ? 1 : 0;
        
        $requete->execute(array('score'=>$score, 'id'=>$idMatch, 'resultat'=>$resultat));
    }

?>