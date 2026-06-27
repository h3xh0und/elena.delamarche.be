<?php
require_once 'includes/auth.php';
require_once 'includes/flatfile.php';

requireLogin();

global $CATEGORIES;
$user              = currentUser();
$progress          = readProgress($user);
$speedtestHighscore = readSpeedtestHighscore($user);

function stars(int $correct): string {
    $full  = min(5, intdiv($correct, 10));
    $empty = 5 - $full;
    return str_repeat('⭐', $full) . str_repeat('☆', $empty);
}

function progressBar(array $stats): string {
    if (!$stats || $stats['done'] === 0) return '';
    $pct = round($stats['correct'] / $stats['done'] * 100);
    return "<div class='v-balk'><div class='v-balk-vulling' style='width:{$pct}%'></div></div>";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard – Oefenwebsite</title>
<link rel="stylesheet" href="assets/css/fonts.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="site-page">

<header class="site-header">
    <div class="header-inhoud">
        <div>
            <span class="header-hoi">Hoi, <strong><?= htmlspecialchars($user) ?></strong>! 👋</span>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center">
            <a href="settings.php" class="btn btn-klein btn-uitlog" title="Instellingen" aria-label="Instellingen">⚙️</a>
            <a href="logout.php" class="btn btn-klein btn-uitlog">Uitloggen</a>
        </div>
    </div>
</header>

<main class="dashboard">

<div class="oefeningen-grid">

<a href="speedtest.php" class="oefening-kaart sneltest-kaart">
    <div class="oef-emoji">⚡</div>
    <div class="oef-naam">Sneltest</div>
    <?php if ($speedtestHighscore > 0): ?>
    <div class="oef-sterren">🏆 <?= $speedtestHighscore ?></div>
    <div class="oef-score">record</div>
    <?php else: ?>
    <div class="oef-nieuw">2 minuten</div>
    <?php endif; ?>
</a>

<?php foreach ($CATEGORIES as $catKey => $category): foreach ($category['exercises'] as $exerciseKey => $exercise):
    $stats   = $progress[$exerciseKey] ?? null;
    $done    = $stats['done']    ?? 0;
    $correct = $stats['correct'] ?? 0;
    $active  = $done > 0;
?>
<a href="exercise.php?cat=<?= $exerciseKey ?>" class="oefening-kaart <?= $active ? 'heeft-voortgang' : '' ?>">
    <div class="oef-emoji"><?= $exercise['emoji'] ?></div>
    <div class="oef-naam"><?= htmlspecialchars($exercise['name']) ?></div>
    <?php if ($active): ?>
    <div class="oef-sterren"><?= stars($correct) ?></div>
    <?php echo progressBar($stats); ?>
    <div class="oef-score"><?= $correct ?>/<?= $done ?> correct</div>
    <?php else: ?>
    <div class="oef-nieuw">Nog niet gedaan</div>
    <?php endif; ?>
</a>
<?php endforeach; endforeach; ?>
</div>

</main>

<footer class="site-footer">
    <a href="privacy.php">Privacyverklaring</a>
    ·
    <a href="https://github.com/h3xh0und/elena.delamarche.be" target="_blank" rel="noopener noreferrer">Open source op GitHub ↗</a>
</footer>

</body>
</html>
