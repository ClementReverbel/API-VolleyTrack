<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
        <meta charset="utf-8">
        <title>Gestion volley : Gestion joueurs/matchs</title>
        <link href="../style/style.css" rel="stylesheet">
    </head>
    <?php if (!isset($_SESSION["login"])){ ?>
        <p>Vous devez vous connecter d'abord</p>
        <a href="../index.php">Lien vers la page de connexion</a>
    <?php 
        } else { ?>
            <header id="myHeader">  
                <div id="menunav">
                    <ul class="menu-list">
                        <img class="headerlogo" src="photo/Headerlogo.png">
                        <li><a href="accueil_stat.php">Statistiques</a></li>
                        <li><a  href="Gestion_joueurs_matchs.php">Joueurs</a></li>
                        <li><a class='appui' href="#">Matchs</a></li>
                    </ul>
                </div>
            </header>
            <body>
                <!-- formulaire de création d'un joueur -->
                <div id ="creation_match">
                    <h3>Créer un match</h2>
                    <form method="POST" action="">
                        <li>
                            <label>Nom de l'équipe adverse : </label>
                            <input type="text" name="equipeadv" required>
                        </li>
                        <li>
                            <label>Date du match :</label>
                            <input type="date" name="date" required>
                        </li>
                        <li>
                            <label>heure prévue de la rencontre : </label>
                            <input type="time" name="heure"  required>
                        </li>
                        <li>
                            <label>Match à domicile : </label>
                            <input type="checkbox" name="domicile" >
                        </li>
                        </li>
                        <input type="submit" name="submdp" value="Créer"\>
                    </form>
                </div>
                <div>
                    <form method="POST" action="">
                        <input type="submit" name="feuille" value="Saisir une feuille de match">
                    </form>
                </div>
                <?php
                    if(isset($_POST['feuille'])){
                        header("Location:saisie_feuille_match.php");
                    }
                try{
                    $linkpdo = new PDO("mysql:host=mysql-volleytrack.alwaysdata.net;dbname=volleytrack_bd", "385425", "\$iutinfo");
                    //si le bouton à d'envoi à été préssé et que le formulaire est remplit
                    if(isset($_POST['equipeadv']) && 
                    isset($_POST['date']) && isset($_POST['heure'])){
                    //Connexion à la bs
                    //Insertion du nouveau match
                    $requete = $linkpdo->prepare('INSERT INTO matchs(Date_heure_match,Nom_equipe_adverse,Rencontre_domicile)
                    VALUES (:date_time,:equipeadv,:domicile)');

                    //Transformation de la date est de l'heure rentrée en type Datetime
                    $heure =  $_POST['heure'];
                    $date = $_POST['date'];
                    $date_time = ($date.' '.$heure.':00');
                    
                    if(isset($_POST['domicile'])){
                        $domicile = 1;
                    } else {
                        $domicile = 0;
                    }
                   //liaison du formulaire à la requete SQL
                    $requete->execute(array('date_time'=>$date_time,'equipeadv'=>$_POST['equipeadv'],
                    'domicile'=>$domicile));
                    }

                     //Création du tableau rempli avec les matchs
                    echo "<table>
                        <tr>
                            <th>Date et heure du match</th>
                            <th>Équipe adverse</th>
                            <th>Rencontre à domicile</th>
                            <th>Score</th>
                            <th>Résultat</th>
                        </tr>";
                    //remplissage du tableau
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
                        
                        //Date actuelle sous forme de tableau
                        $date_array = getdate();

                        //Récupération des bonnes informations de la date actuelle
                        $date_actu = $date_array['mday']."-".$date_array['mon']."-".$date_array['year'];

                        //On vérifie si le match à déjà été joué pour savoir si on peu supprimer le match ou pas
                        if (strtotime($date_actu) > strtotime($dateFormatted)){
                            echo "
                                <tr>
                                    <td>{$date_heure}</td>
                                    <td>{$match['Nom_equipe_adverse']}</td>
                                    <td>{$domicile}</td>
                                    <td>{$match['Score']}</td>
                                    <td>{$gagne}</td>
                                </tr>";
                            //Si le bouton modifié a été pressé sur cette ligne
                        } elseif(isset($_POST['modifier']) && $_POST['id_match_modif'] == $match['id_match']){
                            //Si la rencotre est à domicile préremplie la checkbox avec la bonne valeur
                            if($domicile ==="OUI"){
                                $checked = 'checked';
                            } else {
                                $checked = '';
                            }

                            echo " <tr>
                                        <form method='POST' action=''>
                                            <td>
                                                
                                                    <input type='date' name='new_date' value='$date_return'>
                                                    <input type='time' name='new_time' value='$timeFormatted'>
                                            <td>
                                                    <input type='text' name='new_equipeadv' value='{$match['Nom_equipe_adverse']}'>
                                            </td>
                                            <td>
                                                    <input type='checkbox' name='new_domicile'".$checked.">
                                            </td>
                                            <td>
                                                    <input type='hidden' name='id_match_modif_ok' value='{$match['id_match']}'>
                                                    <input type='submit' name='ok_modifier' value='Ok'>
                                            </td>
                                        </form>
                                    </tr>";
                        } else {
                            echo "<tr>
                                    <td>{$date_heure}</td>
                                    <td>{$match['Nom_equipe_adverse']}</td>
                                    <td>{$domicile}</td>
                                    <td>{$match['Score']}</td>
                                    <td>{$gagne}</td>
                                    <td>
                                        <form method='POST' action=''>
                                            <input type='hidden' name='id_match_modif' value='{$match['id_match']}'>
                                            <input type='submit' name='modifier' value='Modifier'>
                                        </form>
                                    </td>
                                    <td>
                                        <form method='POST' action=''>
                                            <input type='hidden' name='id_match' value='{$match['id_match']}'>
                                            <input type='submit' name='supprimer' value='Supprimer'>
                                        </form>
                                    </td>
                                </tr>";
                        }
                    }
                    echo "</table>";
                    //Si le bouton supprimé a été pressé
                    if (isset($_POST['supprimer']) && isset($_POST['id_match'])) {
                        $id_match = $_POST['id_match'];
                    
                        // Préparer la requête de suppression
                        $requete = $linkpdo->prepare('DELETE FROM matchs WHERE id_match = ?');
                    
                        // Exécuter la requête
                        if ($requete->execute(array($id_match))) {
                            echo '<div class="messagereussi">Le match à été supprimé avec succès.</div>';
                        } else {
                            echo ' <div class="message">Erreur lors de la suppression du match.</div>';
                        }
                    }
                    //Si le bouton modifié a été pressé
                    if (isset($_POST['ok_modifier'])) {
                        //On récuprère l'id du match à modifié
                        $id_match = $_POST['id_match_modif_ok'];
                        if(isset($_POST['new_equipeadv']) && isset($_POST['new_date']) && isset($_POST['new_time'])){
                                //Update du match
                                $requete = $linkpdo->prepare('UPDATE matchs SET Date_heure_match = :date_time ,Nom_equipe_adverse = :equipeadv, Rencontre_domicile = :domicile WHERE id_match = :id_match');

                                //Transformation de la date est de l'heure rentrée en type Datetime
                                $heure =  $_POST['new_time'];
                                $date = $_POST['new_date'];
                                $date_time = ($date.' '.$heure.':00');
                            
                                if(isset($_POST['new_domicile'])){
                                    $domicile = 1;
                                } else {
                                    $domicile = 0;
                                }
                                //liaison du formulaire à la requete SQL
                                if($requete->execute(array('date_time'=>$date_time,'equipeadv'=>$_POST['new_equipeadv'],
                                   'domicile'=>$domicile, 'id_match'=>$id_match))){
                                   echo '<div class="messagereussi">Le match à été modifié avec succès.</div>';
                                } else {
                                    echo '<div class="message">problème lors de la modification</div>';
                                }
                                
                    }

                }

            } catch(Exception $e){
                    echo "Erreur: ".$e->getMessage();
                }
        }
                ?>

</html>