<?php


// Récupération de la liste des joueurs actifs
$requeteJoueurs = $linkpdo->query("
    SELECT idJoueur, CONCAT(Nom, ' ', Prenom) AS NomComplet, 
        Taille, 
        Poids, 
        (SELECT ROUND(SUM(Note)/COUNT(*), 1)
         FROM participer 
         WHERE participer.idJoueur = j.idJoueur
        ) AS Moyenne_note, 
        Commentaire 
    FROM joueurs j
    WHERE Statut = 'Actif'
");
$listeJoueurs = $requeteJoueurs->fetchAll(PDO::FETCH_ASSOC);

$idMatch = $_GET['id_match'];
$message = '';

$requeteDateMatch = $linkpdo->prepare("
    SELECT Date_heure_match
    FROM matchs
    WHERE id_match = :id;
");
$requeteDateMatch -> execute(array("id" => $idMatch));
$dateHeureMatch = $requeteDateMatch->fetch(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $joueurs = isset($_POST['joueurs']) ? array_filter($_POST['joueurs']) : [];
    $roles = isset($_POST['roles']) ? $_POST['roles'] : [];

    // Vérifications des données
    if (count($joueurs) < 6) {
        $message = "Veuillez sélectionner au moins 6 joueurs.";
    } elseif (count($joueurs) > 12) {
        $message = "Vous ne pouvez pas sélectionner plus de 12 joueurs.";
    } elseif (count($joueurs) !== count(array_unique($joueurs))) {
        $message = "Un joueur ne peut pas être sélectionné deux fois.";
    } else {
        foreach ($joueurs as $idJoueur) {
            if (empty($roles[$idJoueur])) {
                $message = "Veuillez attribuer un rôle à chaque joueur sélectionné.";
                break;
            }
        }
    }

    // Si tout est valide, insérer dans la base de données
    if (empty($message)) {
        try {
            $requete = $linkpdo->prepare("
                INSERT INTO participer (idJoueur, idMatch, Role_titulaire, Poste)
                VALUES (:idJoueur, :idMatch, :Role_titulaire, :Poste)
            ");

            foreach ($joueurs as $idJoueur) {
                $role = $roles[$idJoueur];
                $roleTitulaire = ($role !== 'Remplaçant') ? 1 : 0;

                $requete->execute([
                    ':idJoueur' => $idJoueur,
                    ':idMatch' => $idMatch,
                    ':Role_titulaire' => $roleTitulaire,
                    ':Poste' => $role
                ]);
            }

            $message = "Sélection des joueurs enregistrée avec succès pour le match du ". $dateHeureMatch['Date_heure_match'].".";
        } catch (Exception $e) {
            $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
    if (!empty($message)): ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="utf-8">
            <title>Modifier Joueurs pour le Match</title>
            <link href="../style/style.css" rel="stylesheet">
        </head>
        <body>
            <header id="myHeader">
                <div id="menunav">
                    <ul class="menu-list">
                        <img class="headerlogo" src="photo/Headerlogo.png">
                        <li><a href="accueil_stat.php">Statistiques</a></li>
                        <li><a href="Gestion_joueurs_matchs.php">Joueurs</a></li>
                        <li><a href="matchs.php">Matchs</a></li>
                    </ul>
                </div>
            </header>
            <div class="message"><?= $message ?></div>
            <div>
                <form method="POST" action="">
                    <input type="submit" name="feuille" value="Revenir à la saise de feuille de match"> 
                </form>
            </div>
            <?php
                if(isset($_POST['feuille'])){
                    header("Location:saisie_feuille_match.php");
                }
                ?>
        </body>
        </html>
    <?php endif;
} else {
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Ajouter Joueurs au Match</title>
    <link href="../style/style.css" rel="stylesheet">
</head>
<body>
    <header id="myHeader">
        <div id="menunav">
            <ul class="menu-list">
                <img class="headerlogo" src="photo/Headerlogo.png">
                <li><a href="accueil_stat.php">Statistiques</a></li>
                <li><a href="Gestion_joueurs_matchs.php">Joueurs</a></li>
                <li><a href="matchs.php">Matchs</a></li>
            </ul>
        </div>
    </header>
    <h1>Saisie des joueurs pour le match</h1>
    <p>Match sélectionné : <?= $dateHeureMatch['Date_heure_match'] ?></p>

    <?php if (!empty($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form action="" method="POST" class="tab">
        <input type="hidden" name="idMatch" value="<?= $idMatch ?>">
        <table>
            <tr>
                <th>Nom</th>
                <th>Taille</th>
                <th>Poids</th>
                <th>Moyenne des notes</th>
                <th>Commentaire</th>
                <th>Sélectionner</th>
                <th>Rôle</th>
            </tr>
            <?php foreach ($listeJoueurs as $joueur): ?>
                <tr>
                    <td><?= $joueur['NomComplet'] ?></td>
                    <td><?= $joueur['Taille'] ?> cm</td>
                    <td><?= $joueur['Poids'] ?> kg</td>
                    <td><?= $joueur['Moyenne_note']?></td>
                    <td><?= $joueur['Commentaire'] ?></td>
                    <td>
                        <input type="checkbox" name="joueurs[]" value="<?= $joueur['idJoueur'] ?>"
                               <?= isset($_POST['joueurs']) && in_array($joueur['idJoueur'], $_POST['joueurs']) ? 'checked' : '' ?>>
                    </td>
                    <td>
                        <select name="roles[<?= $joueur['idJoueur'] ?>]">
                            <option value="">-- Sélectionnez un rôle --</option>
                            <option value="Attaquant" <?= isset($_POST['roles'][$joueur['idJoueur']]) && $_POST['roles'][$joueur['idJoueur']] == 'Attaquant' ? 'selected' : '' ?>>Attaquant</option>
                            <option value="Passeur" <?= isset($_POST['roles'][$joueur['idJoueur']]) && $_POST['roles'][$joueur['idJoueur']] == 'Passeur' ? 'selected' : '' ?>>Passeur</option>
                            <option value="Libero" <?= isset($_POST['roles'][$joueur['idJoueur']]) && $_POST['roles'][$joueur['idJoueur']] == 'Libero' ? 'selected' : '' ?>>Libero</option>
                            <option value="Centre" <?= isset($_POST['roles'][$joueur['idJoueur']]) && $_POST['roles'][$joueur['idJoueur']] == 'Centre' ? 'selected' : '' ?>>Centre</option>
                            <option value="Remplaçant" <?= isset($_POST['roles'][$joueur['idJoueur']]) && $_POST['roles'][$joueur['idJoueur']] == 'Remplaçant' ? 'selected' : '' ?>>Remplaçant</option>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="submit">Valider la sélection</button>
        <?php } ?>
    </form>
</body>
</html>
