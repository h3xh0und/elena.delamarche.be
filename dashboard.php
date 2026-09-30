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
    return '<span class="vol">' . str_repeat('★', $full) . '</span>' . str_repeat('★', $empty);
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#FFF6E9">
<title>Dashboard – Oefenwebsite</title>
<link rel="stylesheet" href="<?= asset('assets/css/fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<body class="site-page">

<header class="site-header">
    <div class="header-inhoud">
        <span class="header-hoi">👋 Hoi, <strong><?= htmlspecialchars($user) ?></strong>!</span>
        <div class="header-knoppen">
            <a href="settings.php" class="btn btn-klein btn-uitlog" title="Instellingen" aria-label="Instellingen">⚙️</a>
            <a href="logout.php" class="btn btn-klein btn-uitlog">Stoppen 👋</a>
        </div>
    </div>
</header>

<main class="dashboard">

<a href="speedtest.php" class="tegel sneltest-kaart">
    <div class="oef-emoji">⚡</div>
    <div>
        <div class="oef-naam">Sneltest</div>
        <?php if ($speedtestHighscore > 0): ?>
        <div class="oef-nieuw">🏆 Record: <?= $speedtestHighscore ?></div>
        <?php else: ?>
        <div class="oef-nieuw">Hoeveel sommen in 2 minuten?</div>
        <?php endif; ?>
    </div>
</a>

<?php foreach ($CATEGORIES as $catKey => $category): ?>
<section class="vak-sectie">
    <h2 class="vak-titel"><span><?= $category['emoji'] ?></span> <?= htmlspecialchars($category['name']) ?></h2>
    <div class="oefeningen-grid">
    <?php foreach ($category['exercises'] as $exerciseKey => $exercise):
        $stats   = $progress[$exerciseKey] ?? null;
        $done    = $stats['done']    ?? 0;
        $correct = $stats['correct'] ?? 0;
    ?>
    <a href="exercise.php?cat=<?= $exerciseKey ?>" class="tegel">
        <div class="oef-emoji"><?= $exercise['emoji'] ?></div>
        <div class="oef-naam"><?= htmlspecialchars($exercise['name']) ?></div>
        <?php if ($done > 0): ?>
        <div class="oef-sterren" aria-label="<?= $correct ?> juist"><?= stars($correct) ?></div>
        <?php else: ?>
        <div class="oef-nieuw">Nieuw!</div>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>

</main>

<footer class="site-footer">
    <a href="privacy.php">Privacyverklaring</a>
    ·
    <a href="https://github.com/h3xh0und/elena.delamarche.be" target="_blank" rel="noopener noreferrer">Open source op GitHub ↗</a>
</footer>

</body>
</html>
