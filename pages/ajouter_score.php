<?php
session_start();
if (!isset($_SESSION["login"])) {
    echo "<p>Vous devez vous connecter d'abord</p>";
    echo "<a href='../index.php'>Lien vers la page de connexion</a>";
    exit;
}

if (!isset($_GET['id_match'])) {
    echo "<p>Veuillez d'abord sélectionner un match.</p>";
    echo "<a href='feuille_match.php'>Retour au choix des matchs</a>";
    exit;
}

try {
    $linkpdo = new PDO("mysql:host=mysql-volleytrack.alwaysdata.net;dbname=volleytrack_bd", "385425", "\$iutinfo");
} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$idMatch = $_GET['id_match'];
$message = '';

// Récupérer la date et l'heure du match
$requeteDateMatch = $linkpdo->prepare("
    SELECT Date_heure_match
    FROM matchs
    WHERE id_match = :id;
");
$requeteDateMatch->execute(['id' => $idMatch]);
$dateHeureMatch = $requeteDateMatch->fetch();

// Récupérer les joueurs déjà sélectionnés pour ce match
$requeteJoueursSelectionnes = $linkpdo->prepare("
    SELECT p.idJoueur, CONCAT(j.Nom, ' ', j.Prenom) AS NomComplet, 
        j.Taille, 
        j.Poids, 
        (SELECT ROUND(SUM(Note)/COUNT(*), 1)
         FROM participer 
         WHERE participer.idJoueur = j.idJoueur
        ) AS Moyenne_note, 
        j.Commentaire,
        p.Poste, 
        p.Role_titulaire
    FROM participer p
    JOIN joueurs j ON p.idJoueur = j.idJoueur
    WHERE p.idMatch = :idMatch
");
$requeteJoueursSelectionnes->execute([':idMatch' => $idMatch]);
$listeJoueurs = $requeteJoueursSelectionnes->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Modifier les notes des joueurs
        $requete = $linkpdo->prepare("
            UPDATE participer SET Note = :Note
            WHERE idJoueur = :idJoueur AND idMatch = :idMatch
        ");

        foreach ($listeJoueurs as $joueur) {
            $idJoueur = $joueur['idJoueur'];
            if (isset($_POST["note_$idJoueur"])) {
                $note = $_POST["note_$idJoueur"];
                $requete->execute([
                    ':idJoueur' => $idJoueur,
                    ':Note' => $note,
                    ':idMatch' => $idMatch,
                ]);
            }
        }

        // Construire le score global et calculer le résultat
        $score = '';
        $setsEquipe = 0;
        $setsAdverse = 0;

        for ($i = 1; $i <= 5; $i++) {
            if (!empty($_POST["scoreA_$i"]) && !empty($_POST["scoreB_$i"])) {
                $scoreEquipe = (int)$_POST["scoreA_$i"];
                $scoreAdverse = (int)$_POST["scoreB_$i"];

                $score .= "$scoreEquipe-$scoreAdverse, ";

                // Déterminer qui a gagné le set
                if ($scoreEquipe > $scoreAdverse) {
                    $setsEquipe++;
                } else {
                    $setsAdverse++;
                }
            }
        }

        $score = rtrim($score, ', ');

        // Calculer le résultat : victoire ou défaite
        $resultat = $setsEquipe > $setsAdverse ? 1 : 0;

        // Mettre à jour le score et le résultat du match
        $requeteMatch = $linkpdo->prepare("
            UPDATE matchs SET Score = :Score, Resultat = :Resultat
            WHERE id_match = :idMatch
        ");
        $requeteMatch->execute([
            ':Score' => $score,
            ':Resultat' => $resultat,
            ':idMatch' => $idMatch,
        ]);

        $message = "Validation des joueurs, score et résultat enregistrés avec succès pour le match du " . $dateHeureMatch['Date_heure_match'] . ".";
    } catch (Exception $e) {
        $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
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
    <h1>Valider la feuille de match</h1>
    <p>Match sélectionné : <?= $dateHeureMatch['Date_heure_match'] ?></p>

    <?php if (!empty($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form action="" method="POST" class="tab">
        <table>
            <tr>
                <th>Nom</th>
                <th>Taille</th>
                <th>Poids</th>
                <th>Moyenne des notes</th>
                <th>Commentaire</th>
                <th>Note</th>
            </tr>
            <?php foreach ($listeJoueurs as $joueur): ?>
                <tr>
                    <td><?= $joueur['NomComplet'] ?></td>
                    <td><?= $joueur['Taille'] ?> cm</td>
                    <td><?= $joueur['Poids'] ?> kg</td>
                    <td><?= $joueur['Moyenne_note'] ?></td>
                    <td><?= $joueur['Commentaire'] ?></td>
                    <td>
                        <input name="note_<?= $joueur['idJoueur'] ?>" type="number" min="0" max="10" step="0.5" required>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <h2>Saisir le score</h2>
        <table>
            <tr>
                <th>Score de votre équipe</th>
                <th>Score de l'équipe adverse</th>
            </tr>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <tr>
                    <td>
                        <label>Set <?= $i ?></label>
                        <input name="scoreA_<?= $i ?>" type="number" min="0" step="1" <?= $i <= 3 ? 'required' : '' ?>>
                    </td>
                    <td>
                        <label>Set <?= $i ?></label>
                        <input name="scoreB_<?= $i ?>" type="number" min="0" step="1" <?= $i <= 3 ? 'required' : '' ?>>
                    </td>
                </tr>
            <?php endfor; ?>
        </table>
        <button type="submit">Valider match</button>
        <?php } ?>
    </form>
</body>
</html>
