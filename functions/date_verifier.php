<?php
    //Vérifie si la date est valide
    function date_valide($date){
        //décompose la date en jour, mois et année
        $date_explode = explode('-', $date);
        if(checkdate($date_explode[1], $date_explode[0] , $date_explode[2])){
            return true;
        } else {
            return false;
        }
    }
?>