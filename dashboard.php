<?php
require_once 'includes/auth.php';
require_once 'includes/flatfile.php';

vereisInlog();

global $CATEGORIEEN;
$kind      = huidigKind();
$voortgang = leesVoortgang($kind);

function sterren(int $correct): string {
    $vol   = min(5, intdiv($correct, 10));
    $leeg  = 5 - $vol;
    return str_repeat('⭐', $vol) . str_repeat('☆', $leeg);
}

function voortgangBalk(array $stats): string {
    if (!$stats || $stats['gedaan'] === 0) return '';
    $pct = round($stats['correct'] / $stats['gedaan'] * 100);
    return "<div class='v-balk'><div class='v-balk-vulling' style='width:{$pct}%'></div></div>";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard – Oefenwebsite</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inhoud">
        <div>
            <span class="header-hoi">Hoi, <strong><?= htmlspecialchars($kind) ?></strong>! 👋</span>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center">
            <a href="instellingen.php" class="btn btn-klein btn-uitlog" title="Instellingen" aria-label="Instellingen">⚙️</a>
            <a href="uitloggen.php" class="btn btn-klein btn-uitlog">Uitloggen</a>
        </div>
    </div>
</header>

<main class="dashboard">

<div class="oefeningen-grid">

<a href="sneltest.php" class="oefening-kaart sneltest-kaart">
    <div class="oef-emoji">⚡</div>
    <div class="oef-naam">Sneltest</div>
    <div class="oef-nieuw">2 minuten</div>
</a>

<?php foreach ($CATEGORIEEN as $vakKey => $vak): foreach ($vak['oefeningen'] as $oefeningKey => $oef):
    $stats   = $voortgang[$oefeningKey] ?? null;
    $gedaan  = $stats['gedaan']  ?? 0;
    $correct = $stats['correct'] ?? 0;
    $actief  = $gedaan > 0;
?>
<a href="oefening.php?cat=<?= $oefeningKey ?>" class="oefening-kaart <?= $actief ? 'heeft-voortgang' : '' ?>">
    <div class="oef-emoji"><?= $oef['emoji'] ?></div>
    <div class="oef-naam"><?= htmlspecialchars($oef['naam']) ?></div>
    <?php if ($actief): ?>
    <div class="oef-sterren"><?= sterren($correct) ?></div>
    <?php echo voortgangBalk($stats); ?>
    <div class="oef-score"><?= $correct ?>/<?= $gedaan ?> correct</div>
    <?php else: ?>
    <div class="oef-nieuw">Nog niet gedaan</div>
    <?php endif; ?>
</a>
<?php endforeach; endforeach; ?>
</div>

</main>

</body>
</html>
