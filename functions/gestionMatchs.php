<?php
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
?>