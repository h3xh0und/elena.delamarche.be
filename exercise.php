<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';

requireLogin();

global $CATEGORIES;
$cat = preg_replace('/[^a-z0-9_]/', '', $_GET['cat'] ?? '');

$found    = null;
$catColor = 'oranje';
foreach ($CATEGORIES as $catKey => $category) {
    if (isset($category['exercises'][$cat])) {
        $found    = $category['exercises'][$cat];
        $catColor = $category['color'];
        break;
    }
}

if (!$cat || !$found) {
    header('Location: dashboard.php');
    exit;
}

$catName  = htmlspecialchars($found['name']);
$catEmoji = $found['emoji'];
$csrf     = csrfToken();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $catName ?> – Oefenwebsite</title>
<link rel="stylesheet" href="assets/css/fonts.css">
<link rel="stylesheet" href="assets/css/style.css">
<meta name="csrf-token" content="<?= $csrf ?>">
</head>
<body class="oefening-pagina kleur-<?= $catColor ?>">

<header class="oef-header">
    <a href="dashboard.php" class="terug-knop" aria-label="Terug naar dashboard">←</a>
    <div class="oef-header-midden">
        <span class="oef-header-emoji"><?= $catEmoji ?></span>
        <span class="oef-header-naam"><?= $catName ?></span>
    </div>
    <div class="score-teller">
        <span id="score-correct">0</span>/<span id="score-totaal">0</span>
    </div>
</header>

<main class="oefening-main">
    <div id="laad-indicator" class="laad-indicator">Even laden...</div>

    <div id="oefening-kaart" class="oefening-kaart verborgen">

        <div class="oef-inhoud">
            <div id="oef-label" class="oef-vraag-label"></div>
            <div id="oef-vraag" class="oef-vraag-tekst"></div>
            <div id="oef-extra" class="oef-extra"></div>

            <div id="invul-zone" class="invoer-zone verborgen"></div>

            <div id="keuze-zone" class="invoer-zone verborgen">
                <div id="keuze-knoppen" class="keuze-knoppen"></div>
            </div>

            <div id="ordenen-zone" class="invoer-zone verborgen">
                <div id="ordenen-rij" class="ordenen-rij"></div>
                <div class="ordenen-label">Jouw volgorde:</div>
                <div id="ordenen-antwoord" class="ordenen-antwoord"></div>
                <button type="button" class="btn btn-klein btn-grijs" id="ordenen-reset">↩ Opnieuw</button>
            </div>

            <div id="klok-zone" class="invoer-zone verborgen">
                <div id="klok-svg-container"></div>
            </div>

            <div id="rekenslang-zone" class="invoer-zone verborgen">
                <div id="rekenslang-keten" class="rekenslang-keten"></div>
            </div>

            <button id="indienen-knop" class="btn btn-primair btn-groot indienen-knop verborgen" disabled>
                Controleer ✓
            </button>
        </div>

        <div id="numpad" class="numpad verborgen">
            <div id="numpad-display" class="numpad-display leeg">?</div>
            <p id="numpad-hint" class="invoer-hint numpad-hint"></p>
            <div class="numpad-knoppen">
                <button type="button" class="np-btn" data-n="7">7</button>
                <button type="button" class="np-btn" data-n="8">8</button>
                <button type="button" class="np-btn" data-n="9">9</button>
                <button type="button" class="np-btn" data-n="4">4</button>
                <button type="button" class="np-btn" data-n="5">5</button>
                <button type="button" class="np-btn" data-n="6">6</button>
                <button type="button" class="np-btn" data-n="1">1</button>
                <button type="button" class="np-btn" data-n="2">2</button>
                <button type="button" class="np-btn" data-n="3">3</button>
                <button type="button" class="np-btn np-wis" id="np-wis">⌫</button>
                <button type="button" class="np-btn" data-n="0">0</button>
                <button type="button" class="np-btn np-ok" id="np-ok" disabled>✓</button>
            </div>
        </div>

    </div>

    <div id="feedback" class="feedback verborgen">
        <div id="feedback-icoon" class="feedback-icoon"></div>
        <div id="feedback-bericht" class="feedback-bericht"></div>
    </div>
</main>

<script>
    const CATEGORIE = <?= json_encode($cat) ?>;
    const CSRF      = <?= json_encode($csrf) ?>;
</script>
<script src="assets/js/app.js?v=2"></script>

</body>
</html>
