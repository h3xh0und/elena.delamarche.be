<?php
require_once 'includes/auth.php';
require_once 'includes/flatfile.php';

requireLogin();

global $CATEGORIES, $WORLDS;
$user               = currentUser();
$progress           = readProgress($user);
$speedtestHighscore = readSpeedtestHighscore($user);
$newExercises       = newExercises($CATEGORIES);

function stars(int $correct): string {
    $full  = min(5, intdiv($correct, 10));
    $empty = 5 - $full;
    return '<span class="vol">' . str_repeat('★', $full) . '</span>' . str_repeat('★', $empty);
}

function exerciseTile(string $exerciseKey, array $progress, bool $anyDone, string $colorClass = ''): void {
    $exercise = exerciseInfo($exerciseKey);
    if (!$exercise) return;
    $done    = $progress[$exerciseKey]['done']    ?? 0;
    $correct = $progress[$exerciseKey]['correct'] ?? 0;
    ?>
    <a href="exercise.php?cat=<?= $exerciseKey ?>" class="tegel <?= $colorClass ?>">
        <div class="oef-emoji" aria-hidden="true"><?= $exercise['emoji'] ?></div>
        <div class="oef-naam"><?= htmlspecialchars($exercise['name']) ?></div>
        <?php if ($done > 0): ?>
        <div class="oef-sterren" aria-label="<?= $correct ?> juist"><?= stars($correct) ?></div>
        <?php elseif ($anyDone): ?>
        <div class="oef-nieuw">Nieuw!</div>
        <?php endif; ?>
    </a>
    <?php
}

// Totals for the counters, and Oki's suggestion: an exercise that was practised least.
$totalStars = 0;
$anyDone    = false;
$suggestion = null;
$lowest     = PHP_INT_MAX;
foreach ($CATEGORIES as $category) {
    foreach ($category['exercises'] as $exerciseKey => $exercise) {
        $done        = (int)($progress[$exerciseKey]['done'] ?? 0);
        $totalStars += (int)($progress[$exerciseKey]['correct'] ?? 0);
        $anyDone     = $anyDone || $done > 0;
        $score       = $done * 100 + random_int(0, 99);   // random tie-break
        if ($score < $lowest) { $lowest = $score; $suggestion = $exerciseKey; }
    }
}

$greetings = [
    'Waar gaan we vandaag mee oefenen?',
    'Klaar voor een rekenavontuur?',
    'Kies een wereld en we beginnen!',
    'Wat wil je vandaag leren?',
];
$greeting = $greetings[array_rand($greetings)];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#FFF4E6">
<title>Rekenavontuur</title>
<link rel="stylesheet" href="<?= asset('assets/css/fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<body class="site-page">

<header class="site-header">
    <div class="header-inhoud">
        <div class="header-logo"><div class="mini-oki" data-oki="blij"></div><?= rainbow('Rekenavontuur') ?></div>
        <div class="header-knoppen">
            <a href="settings.php" class="icoon-knop" aria-label="Instellingen">⚙️</a>
            <a href="logout.php" class="btn btn-klein btn-grijs">Stoppen 👋</a>
        </div>
    </div>
</header>

<main class="dashboard">

<section class="dash-hero">
    <div class="dash-oki" data-oki="blij"></div>
    <div class="ballon">
        Hoi <strong><?= htmlspecialchars($user) ?></strong>! <?= htmlspecialchars($greeting) ?>
        <div class="teller-rij">
            <span class="teller" aria-label="<?= $totalStars ?> sommen juist">⭐ <?= $totalStars ?> juist</span>
            <?php if ($speedtestHighscore > 0): ?>
            <span class="teller paars">🏆 Record <?= $speedtestHighscore ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="snel-acties" aria-label="Snel beginnen">
    <a href="speedtest.php" class="actie-kaart sneltest">
        <span class="actie-icoon">⚡</span>
        <span>
            <span class="actie-titel">Sneltest</span><br>
            <span class="actie-sub"><?= $speedtestHighscore > 0 ? 'Kan je je record breken?' : 'Hoeveel sommen in 2 minuten?' ?></span>
        </span>
    </a>
    <?php if ($suggestion): ?>
    <a href="exercise.php?cat=<?= $suggestion ?>" class="actie-kaart verras">
        <span class="actie-icoon">🎲</span>
        <span>
            <span class="actie-titel">Oki kiest!</span><br>
            <span class="actie-sub">Een verrassing voor jou</span>
        </span>
    </a>
    <?php endif; ?>
</section>

<?php if ($newExercises): ?>
<section class="wereld wereld-nieuw" aria-labelledby="wereld-nieuw">
    <div class="wereld-kop">
        <div class="wereld-badge" aria-hidden="true">✨</div>
        <div>
            <h2 class="wereld-titel" id="wereld-nieuw">Nieuw!</h2>
            <div class="wereld-voortgang">Pas toegevoegd, probeer maar</div>
        </div>
    </div>
    <div class="oefeningen-grid">
    <?php foreach (array_keys($newExercises) as $exerciseKey)
        exerciseTile($exerciseKey, $progress, false, 'kleur-' . (exerciseInfo($exerciseKey)['color'] ?? 'blauw')); ?>
    </div>
</section>
<?php endif; ?>

<?php foreach ($WORLDS as $worldKey => $world):
    $count = count($world['exercises']);
    $tried = 0;
    foreach ($world['exercises'] as $k) if (($progress[$k]['done'] ?? 0) > 0) $tried++;
    $rest  = array_values(array_diff($world['exercises'], array_keys($newExercises)));
    if (!$rest) continue;
?>
<section class="wereld kleur-<?= $world['color'] ?>" aria-labelledby="wereld-<?= $worldKey ?>">
    <div class="wereld-kop">
        <div class="wereld-badge" aria-hidden="true"><?= $world['emoji'] ?></div>
        <div>
            <h2 class="wereld-titel" id="wereld-<?= $worldKey ?>"><?= htmlspecialchars($world['name']) ?></h2>
            <div class="wereld-voortgang"><?= $tried ?> van <?= $count ?> geprobeerd</div>
            <div class="wereld-balk" aria-hidden="true"><span style="width:<?= round($tried / max(1, $count) * 100) ?>%"></span></div>
        </div>
    </div>
    <div class="oefeningen-grid">
    <?php foreach ($rest as $exerciseKey) exerciseTile($exerciseKey, $progress, $anyDone); ?>
    </div>
</section>
<?php endforeach; ?>

</main>

<footer class="site-footer">
    <a href="privacy.php">Privacyverklaring</a>
    ·
    <a href="https://github.com/h3xh0und/elena.delamarche.be" target="_blank" rel="noopener noreferrer">Open source op GitHub ↗</a>
</footer>

<script src="<?= asset('assets/js/mascot.js') ?>"></script>
</body>
</html>
