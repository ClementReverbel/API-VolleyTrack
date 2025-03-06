<?php
    session_start();
    if (!isset($_SESSION["login"])) {
        echo "<p>Vous devez vous connecter d'abord</p>";
        echo "<a href='../index.php'>Lien vers la page de connexion</a>";
        exit;
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Gestion volley : Gestion joueurs/matchs</title>
        <link href="../style/style.css" rel="stylesheet">
    </head>
    <header id="myHeader">
        <div id="menunav">
            <ul class="menu-list">
                <img class="headerlogo" src="photo/Headerlogo.png">
                <li><a href="accueil_stat.php">Statistiques</a></li>
                <li><a class="appui" href="#">Joueurs</a></li>
                <li><a href="matchs.php">Matchs</a></li>
            </ul>
        </div>
    </header>
    <body>
        <!-- formulaire de création d'un joueur -->
        <div id ="ajout_joueur">
            <h3>Ajouter un joueur</h2>
            <form method="POST" action="">
                <li>
                    <label>Numéro de licence : </label>
                    <input type="text" name="NumLic" required>
                </li>
                <li>
                    <label>Nom : </label>
                    <input type="text" name="nomJ" required>
                </li>
                <li>
                    <label>Prénom : </label>
                    <input type="text" name="prenomJ" required>
                </li>
                <li>
                    <label>Date de naissance : </label>
                    <input type="date" name="dateJ" required>
                </li>
                <li>
                    <label>Taille (en mètre) : </label>
                    <input type="number" id="taille" name="tailleJ" step="0.01" value="1,75" min="0" srequired>
                </li>
                <li>
                    <label>Poids (en kilo) : </label>
                    <input type="number" id="poid" name="poid" step="1" min="0" required>
                </li>
                <li>
                    <label>Commentaire (facultatif) : </label>
                    <textarea id="commentaire" name="commentaire"></textarea>
                </li>
                <li>
                    <label>Statut : </label>
                    <select id="statut" name="statut" required>
                        <option value="Actif">Actif</option>
                        <option value="Blessé">Blessé</option>
                        <option value="Suspendu">Suspendu</option>
                        <option value="Absent">Absent</option>
                    </select>
                </li>
                <input type="submit" name="submdp" value="+"\>
            </form>
        </div>
        <?php
        try{
            //si le bouton à d'envoi à été préssé et que le formulaire est remplit
            if(isset($_POST['submdp']) && 
            isset($_POST['NumLic']) && isset($_POST['nomJ']) && 
            isset($_POST['prenomJ']) && isset($_POST['dateJ']) && 
            isset($_POST['tailleJ']) && isset($_POST['poid']) && 
            isset($_POST['commentaire']) && isset($_POST['statut'])){
                //Connexion à la bs
                $linkpdo = new PDO("mysql:host=mysql-volleytrack.alwaysdata.net;dbname=volleytrack_bd", "385425", "\$iutinfo");
                //Insertion du nouveau joueur
                $requete = $linkpdo->prepare('INSERT INTO joueurs(Numéro_de_licence,Nom,Prenom,Date_de_naissance ,Taille,Poids,Commentaire,Statut)
                VALUES (:num,:Nom,:Prenom,:Date_de_naissance ,:Taille, :Poids,:Commentaire,:Statut)');
                //liaison du formulaire à la requete SQL
                $requete->execute(array('num'=>$_POST['NumLic'],'Nom'=>$_POST['nomJ'],
                'Prenom'=>$_POST['prenomJ'],'Date_de_naissance'=>$_POST['dateJ'],
                'Taille'=>$_POST['tailleJ'],'Poids'=>$_POST['poid'],
                'Commentaire'=>$_POST['commentaire'],'Statut'=>$_POST['statut']));
            }
        }
        catch(Exception $e){
            die("Erreur: ".$e->getMessage());
        }
        ?>

        <div id="recherche_joueur">
        <h3>Rechercher un joueur</h3>
        <!--Section de recherche du joueur à modifier ou supprimer -->
        <form method="POST" action="">
            <label>Numéro de licence : </label>
            <input type="text" name="rechNumLic" required>
            <input type="submit" name="rech" value="Rechercher">
        </form>
    </div>

    <?php
    try {
        //Connexion à la bd
        $linkpdo = new PDO("mysql:host=mysql-volleytrack.alwaysdata.net;dbname=volleytrack_bd", "385425", "\$iutinfo");

        //Si l'utilisateur veux recherher
        if (isset($_POST['rech']) && !empty($_POST['rechNumLic'])) {
            $rechNumLic = $_POST['rechNumLic'];
            //On recherche le joueur grace au numéro de licence indiqué
            $requete = $linkpdo->prepare('SELECT * FROM joueurs WHERE Numéro_de_licence = :num');
            $requete->execute(['num' => $rechNumLic]);
            $joueur = $requete->fetch(PDO::FETCH_ASSOC);

            if ($joueur) {
                ?>
                <!-- Section du formulaire préremplit avec les info du joueur trouvé -->
                <div id="modifier_joueur">
                    <h3>Modifier un joueur</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="NumLic" value="<?php echo $joueur['Numéro_de_licence'] ?>">
                        <li>
                            <label>Nom : </label>
                            <input type="text" name="nomJ" value="<?php echo $joueur['Nom'] ?>" required>
                        </li>
                        <li>
                            <label>Prénom : </label>
                            <input type="text" name="prenomJ" value="<?php echo $joueur['Prenom'] ?>" required>
                        </li>
                        <li>
                            <label>Date de naissance : </label>
                            <input type="date" name="dateJ" value="<?php echo $joueur['Date_de_naissance'] ?>" required>
                        </li>
                        <li>
                            <label>Taille (en mètre) : </label>
                            <input type="number" name="tailleJ" step="0.01" min="0" value="<?php echo $joueur['Taille'] ?>" required>
                        </li>
                        <li>
                            <label>Poids (en kilo) : </label>
                            <input type="number" name="poid" step="1" min="0" value="<?php echo $joueur['Poids']; ?>" required>
                        </li>
                        <li>
                            <label>Commentaire : </label>
                            <textarea name="commentaire"><?php echo $joueur['Commentaire'] ?></textarea>
                        </li>
                        <li>
                            <label>Statut : </label>
                            <select id="statut" name="statut" required>
                                <option value="Actif">Actif</option>
                                <option value="Blessé">Blessé</option>
                                <option value="Suspendu">Suspendu</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </li>
                        <!-- 2 cas possible: soit il veux modifier le joueur, soit il veux le supprimer -->
                        <input type="submit" name="update" value="Mettre à jour">
                        <input type="submit" name="supprimer" value="Supprimer">
                    </form>
                </div>
                <?php
            } else {
                //Si le joueur n'est pas trouvé, on affiche une erreur
                echo '<div class="message"> Joueur introuvable avec ce numéro de licence.</div>';
            }
        }
        //Si l'utilisateur à choisit de modifier le joueur
        if (isset($_POST['update'])) {
            //Création de la requete SQL
            $updateRequete = $linkpdo->prepare('UPDATE joueurs SET Nom = :Nom, Prenom = :Prenom,
            Date_de_naissance = :Date_de_naissance, Taille = :Taille, 
            Poids = :Poids, Commentaire = :Commentaire, Statut = :statut 
            WHERE Numéro_de_licence = :num');
            //liaison du formulaire à la requete SQL
            $updateRequete->execute([
                'Nom' => $_POST['nomJ'],
                'Prenom' => $_POST['prenomJ'],
                'Date_de_naissance' => $_POST['dateJ'],
                'Taille' => $_POST['tailleJ'],
                'Poids' => $_POST['poid'],
                'Commentaire' => $_POST['commentaire'],
                'num' => $_POST['NumLic'],
                'statut' => $_POST['statut']
            ]);

            echo '<div class="message">Joueur mis à jour avec succès !</div>';
        }
        //Si l'utilisateur à choisit de supprimer le joueur
        if (isset($_POST['supprimer'])) {
            //Création de la requete pour avoir le nombre de match auquel à participer le joueur
            $nbMatchRequete = $linkpdo->prepare('SELECT count(*) FROM participer p  WHERE p.idJoueur = (SELECT idJoueur FROM joueurs WHERE Numéro_de_licence = :num)');
            $nbMatchRequete->execute([
                'num' => $_POST['NumLic']
            ]);
            $nombreDeMatchs = $nbMatchRequete->fetchColumn();
            //Si le joueur à participer à un match, on ne peu pas le supprimer
            if ($nombreDeMatchs >= 1){
                echo "<p>Impossible de supprimer le joueur car il a déjà participer à un match</p>";
            //Sinon on le supprime
            } else {
            $supprimerRequete = $linkpdo->prepare('DELETE FROM joueurs WHERE Numéro_de_licence = :num');
            $supprimerRequete->execute([
                'num' => $_POST['NumLic']
            ]);
            echo '<div class="message">Le joueur a été supprimé</div>';
            }
        }
        echo "<table>
                <tr>
                    <th>Numéro de licence</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Date de naissance</th>
                    <th>Taille</th>
                    <th>Poids</th>
                    <th>Commentaire</th>
                    <th>Statut</th>
                </tr>";
        $joueurs = $linkpdo->query("SELECT Numéro_de_licence, Nom, 
                                    Prenom, Date_de_naissance, 
                                    Taille, Poids, Commentaire, Statut 
                                    FROM joueurs");

        while ($joueur = $joueurs->fetch(PDO::FETCH_ASSOC)) {
                echo "
                    <tr>
                        <td>{$joueur['Numéro_de_licence']}</td>
                        <td>{$joueur['Nom']}</td>
                        <td>{$joueur['Prenom']}</td>
                        <td>{$joueur['Date_de_naissance']}</td>
                        <td>{$joueur['Taille']}</td>
                        <td>{$joueur['Poids']}</td>
                        <td>{$joueur['Commentaire']}</td>
                        <td>{$joueur['Statut']}</td>
                    </tr>";
            }

                echo "</table>";
            } catch (Exception $e) {
                die("Erreur : " . $e->getMessage());
            }
        ?>
    </body>
</html>