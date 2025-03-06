<?php
session_start();
if (!isset($_SESSION["login"])) {
    echo "<p>Vous devez vous connecter d'abord</p>";
    echo "<a href='../index.php'>Lien vers la page de connexion</a>";
    exit;
}

//Date actuelle sous forme de tableau
$date_array = date('Y-m-d');

$linkpdo = new PDO("mysql:host=mysql-volleytrack.alwaysdata.net;dbname=volleytrack_bd", "385425", "\$iutinfo");

// Récupérer les matchs non sélectionnés
$requeteMatchsNonSelect = $linkpdo->query("
    SELECT id_match, Date_heure_match, Nom_equipe_adverse 
    FROM matchs 
    WHERE id_match NOT IN (
        SELECT DISTINCT idMatch 
        FROM participer
    )
");
$matchsNonSelect = $requeteMatchsNonSelect->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les matchs déjà configurés
$requeteMatchsSelect = $linkpdo->query("
    SELECT DISTINCT m.id_match, m.Date_heure_match, m.Nom_equipe_adverse 
    FROM matchs m, participer p
    WHERE  m.id_match = p.idMatch
    AND m.Score IS NULL
");

$matchsSelect = $requeteMatchsSelect->fetchAll(PDO::FETCH_ASSOC);

$requeteMatchsPasses = $linkpdo->query("
    SELECT DISTINCT m.id_match, m.Date_heure_match, m.Nom_equipe_adverse
    FROM matchs m, participer p
    WHERE  m.id_match = p.idMatch
    AND m.Score IS NULL
    AND CAST(m.Date_heure_match AS DATE) < '".$date_array."'
");

$matchPasses = $requeteMatchsPasses->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Feuille de Match</title>
    <link href="../style/style.css" rel="stylesheet">
</head>
<header id="myHeader">
    <div id="menunav">
        <ul class="menu-list">
            <img class="headerlogo" src="photo/Headerlogo.png">
            <li><a href="accueil_stat.php">Statistiques</a></li>
            <li><a href="Gestion_joueurs_matchs.php">Joueurs</a></li>
            <li><a href="matchs.php" class="appui"> Matchs</a></li>
        </ul>
    </div>
</header>
<body>
    <h1>Gestion des Feuilles de Match</h1>

    <!-- Section pour les nouveaux matchs -->
    <h2>Nouvelle feuille de match</h2>
    <form action="ajouter_joueur_match.php" method="GET">
        <label for="match">Choisir un match :</label>
        <select name="id_match" id="match" required>
            <option value="">-- Sélectionnez un match --</option>
            <?php foreach ($matchsNonSelect as $match) { ?>
                <option value="<?= $match['id_match'] ?>">
                    <?= $match['Date_heure_match'] ?> - <?= $match['Nom_equipe_adverse'] ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit">Configurer</button>
    </form>

    <!-- Section pour les matchs déjà configurés -->
    <h2>Modifier une feuille de match existante</h2>
    <form action="modifier_joueur_match.php" method="GET">
        <label for="matchConfig">Choisir un match configuré :</label>
        <select name="id_match" id="matchConfig" required>
            <option value="">-- Sélectionnez un match --</option>
            <?php foreach ($matchsSelect as $match) { ?>
                <option value="<?= $match['id_match'] ?>">
                    <?= $match['Date_heure_match'] ?> - <?= $match['Nom_equipe_adverse'] ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit">Modifier</button>
    </form>

    <h2>Rentrer le score d'une feuille de match</h2>
    <form action="ajouter_score.php" method="GET">
        <label for="matchConfig">Choisir un match configuré :</label>
        <select name="id_match" id="matchConfig" required>
            <option value="">-- Sélectionnez un match --</option>
            <?php foreach ($matchPasses as $match) { ?>
                <option value="<?= $match['id_match'] ?>">
                    <?= $match['Date_heure_match'] ?> - <?= $match['Nom_equipe_adverse'] ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit">Valider</button>
    </form>
</body>
</html>
