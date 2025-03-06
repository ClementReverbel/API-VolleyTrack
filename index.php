<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Gestion volley : connexion</title>
        <link href="style/style.css" rel="stylesheet">
    </head>
    <body>
        <!-- Affichage du fromulaire si l'utilisateur n'a pas de session en cours -->
        <?php
            if(!isset($_SESSION["login"])) {
        ?>
            <div id="connex">
                <h3>Connectez vous pour acceder à l'application</h3>
                <form method="POST" action="">
                    <li>
                        <label>Utilisateur : </label>
                        <input type="text" name="nomUtil" required>
                    </li>
                    <li>
                        <label>Mot de pase : </label>
                        <input type="password" name="mdp" required>
                    </li>
                    <input type="submit" name="submdp" value="Entrer"\>
                </form>
        <!-- Quand l'utilisateur en envoyé le formulaire : connexion a la base de données -->
        <?php
            if(isset($_POST['nomUtil']) && isset($_POST['mdp'])){
                try{
                    $linkpdo = new PDO("mysql:host=mysql-volleytrack.alwaysdata.net;dbname=volleytrack_bd","385425","\$iutinfo");
                }
                catch(Exception $e){
                    die("Erreur: ".$e->getMessage());
                }
                //Recherche des données entrées dans la base de données
                $requete = $linkpdo->prepare('SELECT mdp FROM utilisateur WHERE login = :login');
                $requete->execute(array('login'=>$_POST['nomUtil']));
                $resultat = $requete->fetch(PDO::FETCH_ASSOC);

                if($resultat){
                    //Si le mot de passe correspond au hachage : ouverture de la page d'accueil
                    if(password_verify($_POST['mdp'],$resultat['mdp'])){
                        $_SESSION["login"]=$_POST['nomUtil'];
                        header('Location:pages/accueil_stat.php');
                        exit();
                    } else {
                    //Si l'utilisateur n'est pas trouvé dans la base de données ou que le mot de passe est incorrect :
                    //Affichage d'une erreur adéquat
                        echo("<div class='message'> Mot de passe erroné </div>");
                    }
                }else{
                    echo("<div class='message'> Utilisateur inconnu </div>");
                }
            }
        } else {
            header("Location:pages/accueil_stat.php");
        }
        ?>
        </div>
    </body>
</html>