<?php

    //Fonction permettant de créer une unique connexion à la BD pour toutes les utilisations    
    function connexion_db(){
        try {
            $linkpdo = new PDO("mysql:host=mysql-volleyapi.alwaysdata.net;dbname=volleyapi_bd", "volleyapi", "VolleyApi!158");
            return $linkpdo;
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

?>