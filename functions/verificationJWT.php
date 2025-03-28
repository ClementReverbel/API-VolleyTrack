<?php

    //Validation JWT - Appel AuthAPI
    function get_authorization_header(){
        $headers = null;
    
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
    
        return $headers;
    }
    
    function get_bearer_token() {
        $headers = get_authorization_header();
        
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                if($matches[1]=='null') //$matches[1] est de type string et peut contenir 'null'
                    return null;
                else
                    return $matches[1];
            }
        }
        return null;
    }
    
    function getJwtValid(){
        $jeton=get_bearer_token();
        if($jeton!=null){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "authapi.alwaysdata.net/authapi.php");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer ".$jeton
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Pour ne pas afficher la réponse directement
            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            return $http_code==200;
        } else {
            return false;
        }
    }

    /*function getJwtInfo(){
        $jeton=get_bearer_token();
        $tokenParts = explode('.', $jeton);
        $payload = base64_decode($tokenParts[1]);
        return json_decode($payload);
    }*/


?>