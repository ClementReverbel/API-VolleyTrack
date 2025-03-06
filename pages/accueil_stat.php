<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Gestion volley : accueil</title>
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
                            <li><a class="appui" href="#">Statistiques</a></li>
                            <li><a href="Gestion_joueurs_matchs.php">Joueurs</a></li>
                            <li><a href="matchs.php">Matchs</a></li>
                        </ul>
                    </div>
                </header>
                <body>
                <p>Bonjour, <?php echo $_SESSION["login"]; ?></p>
                <h1>Statistiques des matchs</h1>
                <?php
                try {
                    // Connexion à la base de données
                    $linkpdo = new PDO("mysql:host=mysql-volleytrack.alwaysdata.net;dbname=volleytrack_bd", "385425", "\$iutinfo");

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

                    // Affichage des résultats
                    echo "
                    <table>
                        <tr>
                            <th>Statistique</th>
                            <th>Nombre</th>
                            <th>Pourcentage</th>
                        </tr>
                        <tr>
                            <td>Matchs Gagnés</td>
                            <td>$gagnes</td>
                            <td>$gagnesPourcentage%</td>
                        </tr>
                        <tr>
                            <td>Matchs Perdus</td>
                            <td>$perdus</td>
                            <td>$perdusPourcentage%</td>
                        </tr>
                    </table>";
                
                    $joueurs = $linkpdo->query("
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
                
                echo "
                <table>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Statut</th>
                        <th>Poste Préféré</th>
                        <th>Total Sélections (Titulaire)</th>
                        <th>Total Sélections (Remplaçant)</th>
                        <th>Moyenne des Évaluations</th>
                        <th>% Matchs Gagnés</th>
                        <th>Sélections Consécutives</th>
                    </tr>";

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
                
                    // Affichage
                    echo "
                        <tr>
                            <td>{$joueur['Nom']}</td>
                            <td>{$joueur['Prenom']}</td>
                            <td>{$joueur['Statut']}</td>
                            <td>{$joueur['Poste_prefere']}</td>
                            <td>{$joueur['Total_titulaire']}</td>
                            <td>{$joueur['Total_remplacant']}</td>
                            <td>{$joueur['Moyenne_note']}</td>
                            <td>{$joueur['Pourcentage_gagne']}%</td>
                            <td>{$selectionsConsecutives[$idJoueur]}</td>
                        </tr>";
                }

                    echo "</table>";
                } catch (Exception $e) {
                    echo "Erreur de connexion à la base de données : " . $e->getMessage();
                }   
                ?>
            </body>
        <?php
        }
        ?>
</html>