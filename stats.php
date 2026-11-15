<?php
/**
 * Gebruiksstatistiek voor de beheerder: enkel totalen, geen namen, geen IP-adressen, geen tracking.
 * Beveiligd met een sleutel uit includes/stats.local.php (geschreven door de deploy uit het
 * GitHub-secret STATS_KEY). Zonder dat bestand staat de pagina uit.
 */
require_once 'includes/auth.php';
require_once 'includes/flatfile.php';

global $CATEGORIES;
$configFile = __DIR__ . '/includes/stats.local.php';
$statsKey   = is_file($configFile) ? (string)((require $configFile)['key'] ?? '') : '';
$error      = '';

if ($statsKey !== '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkCsrf()) {
        $error = 'Sessie verlopen, probeer opnieuw.';
    } elseif (!checkRateLimit('__stats__')) {
        $error = 'Te veel pogingen. Wacht 5 minuten.';
    } elseif (hash_equals($statsKey, (string)($_POST['sleutel'] ?? ''))) {
        resetRateLimit('__stats__');
        session_regenerate_id(true);
        $_SESSION['stats_ok'] = true;
        header('Location: stats.php');
        exit;
    } else {
        registerFailedAttempt('__stats__');
        $error = 'Verkeerde sleutel.';
    }
}

$allowed = $statsKey !== '' && !empty($_SESSION['stats_ok']);

if ($allowed) {
    $today   = date('Y-m-d');
    $since7  = date('Y-m-d', strtotime('-6 days'));
    $since30 = date('Y-m-d', strtotime('-29 days'));

    // Accounts en wanneer ze gemaakt zijn (per maand)
    $accounts = 0;
    $perMonth = [];
    foreach (glob(USERS_DIR . '/*.json') ?: [] as $file) {
        $user = json_decode((string)file_get_contents($file), true) ?: [];
        $accounts++;
        $month = substr($user['created'] ?? '', 0, 7) ?: 'onbekend';
        $perMonth[$month] = ($perMonth[$month] ?? 0) + 1;
    }
    ksort($perMonth);

    // Voortgang: totalen, laatste activiteit per account, populariteit per oefening
    $withExercises = $active7 = $active30 = $answers = $correct = 0;
    $perExercise = [];
    foreach (glob(PROGRESS_DIR . '/*.json') ?: [] as $file) {
        $progress = json_decode((string)file_get_contents($file), true) ?: [];
        $progress = _migrateProgressKeys($progress);
        if (!$progress) continue;
        $withExercises++;
        $last = max(array_column($progress, 'last') ?: ['']);
        if ($last >= $since7)  $active7++;
        if ($last >= $since30) $active30++;
        foreach ($progress as $key => $s) {
            $answers += $s['done'];
            $correct += $s['correct'];
            $perExercise[$key] ??= ['done' => 0, 'kinderen' => 0];
            $perExercise[$key]['done'] += $s['done'];
            $perExercise[$key]['kinderen']++;
        }
    }
    uasort($perExercise, fn($a, $b) => $b['done'] <=> $a['done']);

    $names = ['speedtest' => '⚡ Sneltest'];
    foreach ($CATEGORIES as $category) {
        foreach ($category['exercises'] as $key => $e) $names[$key] = $e['emoji'] . ' ' . $e['name'];
    }

    // Dagteller (pas sinds deze pagina bestaat)
    $daily = readDailyUsage();
    $days  = [];
    for ($i = 29; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $days[$d] = $daily[$d] ?? ['kinderen' => 0, 'oefeningen' => 0];
    }
    $maxDay   = max(1, max(array_column($days, 'oefeningen')));
    $kids30   = array_sum(array_column($days, 'kinderen'));
    $answers30 = array_sum(array_column($days, 'oefeningen'));
    $maxEx    = max(1, ...array_column($perExercise, 'done') ?: [1]);
    $countFrom = $daily ? array_key_first($daily) : null;
}

$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex">
<meta name="theme-color" content="#FFF6E9">
<title>Gebruik – Oefenwebsite</title>
<link rel="stylesheet" href="<?= asset('assets/css/fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<body class="site-page">

<header class="site-header">
    <div class="header-inhoud">
        <a href="index.php" class="terug-link" style="font-weight:800">← Terug</a>
        <span class="header-titel">📈 Gebruik</span>
        <span></span>
    </div>
</header>

<main class="inst-main" style="max-width:820px">

<?php if ($statsKey === ''): ?>
    <div class="inst-kaart">
        <h2 class="inst-titel">Statistieken staan uit</h2>
        <p class="inst-omschrijving">
            Voeg in GitHub een secret <strong>STATS_KEY</strong> toe (Settings → Secrets and variables → Actions)
            en push opnieuw naar <code>main</code>. Daarna kan je hier inloggen met die sleutel.
        </p>
    </div>

<?php elseif (!$allowed): ?>
    <div class="inst-kaart">
        <h2 class="inst-titel">🔑 Sleutel</h2>
        <?php if ($error): ?><div class="fout-bericht"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" class="inst-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <label for="sleutel">Beheerderssleutel</label>
            <input type="password" id="sleutel" name="sleutel" autocomplete="current-password" required autofocus>
            <button type="submit" class="btn btn-primair btn-groot">Bekijk</button>
        </form>
    </div>

<?php else: ?>
    <div class="stat-tegels">
        <div class="stat-tegel"><div class="stat-getal"><?= $accounts ?></div><div class="stat-label">accounts</div></div>
        <div class="stat-tegel"><div class="stat-getal"><?= $active7 ?></div><div class="stat-label">actief deze week</div></div>
        <div class="stat-tegel"><div class="stat-getal"><?= $active30 ?></div><div class="stat-label">actief deze maand</div></div>
        <div class="stat-tegel"><div class="stat-getal"><?= number_format($answers, 0, ',', '.') ?></div><div class="stat-label">oefeningen ooit</div></div>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">Laatste 30 dagen</h2>
        <p class="inst-omschrijving">
            <strong><?= number_format($answers30, 0, ',', '.') ?></strong> oefeningen gemaakt,
            samen <strong><?= $kids30 ?></strong> kind-dagen (een kind dat op een dag oefent telt één keer).
            <?php if ($countFrom): ?>Geteld sinds <?= htmlspecialchars($countFrom) ?>.<?php else: ?>Er is nog niets geteld.<?php endif; ?>
        </p>
        <div class="stat-dagen" role="img" aria-label="Oefeningen per dag, laatste 30 dagen">
            <?php foreach ($days as $d => $v): $h = round($v['oefeningen'] / $maxDay * 100); ?>
            <div class="stat-dag" title="<?= htmlspecialchars($d) ?>: <?= $v['oefeningen'] ?> oefeningen, <?= $v['kinderen'] ?> kinderen">
                <span class="stat-staaf" style="height:<?= max($v['oefeningen'] ? 3 : 0, $h) ?>%"></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="stat-as"><span><?= htmlspecialchars(date('j/n', strtotime($since30))) ?></span><span>vandaag</span></div>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">Oefeningen</h2>
        <p class="inst-omschrijving">
            <?= $withExercises ?> van de <?= $accounts ?> accounts hebben al geoefend.
            <?php if ($answers): ?><?= round($correct / $answers * 100) ?>% van de antwoorden is juist.<?php endif; ?>
        </p>
        <table class="stat-tabel">
            <thead><tr><th>Oefening</th><th>Gemaakt</th><th>Kinderen</th></tr></thead>
            <tbody>
            <?php foreach ($perExercise as $key => $v): ?>
            <tr>
                <td><?= htmlspecialchars($names[$key] ?? $key) ?>
                    <span class="stat-balk" style="width:<?= round($v['done'] / $maxEx * 100) ?>%"></span></td>
                <td><?= $v['done'] ?></td>
                <td><?= $v['kinderen'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">Nieuwe accounts per maand</h2>
        <table class="stat-tabel">
            <tbody>
            <?php foreach ($perMonth as $m => $n): ?>
            <tr><td><?= htmlspecialchars($m) ?></td><td><?= $n ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p class="inst-omschrijving" style="text-align:center">
        Alleen totalen: geen namen, geen IP-adressen, geen cookies of scripts van derden.
    </p>
<?php endif; ?>

</main>

</body>
</html>
