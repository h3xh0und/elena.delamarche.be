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
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#FFF6E9">
<title><?= $catName ?> – Oefenwebsite</title>
<link rel="stylesheet" href="<?= asset('assets/css/fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
<meta name="csrf-token" content="<?= $csrf ?>">
</head>
<body class="oefening-pagina kleur-<?= $catColor ?>">

<header class="oef-header">
    <a href="dashboard.php" class="terug-knop" aria-label="Terug naar alle oefeningen">🏠</a>
    <div id="ronde-balk" class="ronde-balk" role="img" aria-label="Voortgang van deze ronde"></div>
    <div id="score-teller" class="score-teller" aria-label="Aantal juist">⭐ <span id="score-correct">0</span></div>
    <button type="button" id="geluid-knop" class="geluid-knop" aria-label="Geluid aan of uit">🔇</button>
</header>

<main class="oefening-main">
    <div id="laad-indicator" class="laad-indicator">Even laden...</div>

    <div id="oefening-kaart" class="oefening-kaart verborgen">

        <div class="oef-inhoud">
            <div id="oef-label" class="oef-vraag-label" data-naam="<?= $catEmoji ?> <?= $catName ?>"></div>
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

    <div id="ronde-klaar" class="oefening-kaart ronde-klaar verborgen">
        <div id="ronde-emoji" class="ronde-emoji">🏆</div>
        <div id="ronde-titel" class="ronde-titel">Klaar!</div>
        <div id="ronde-sterren" class="ronde-sterren" aria-hidden="true"></div>
        <div class="ronde-score"><strong id="ronde-correct">0</strong> van de <span id="ronde-totaal">10</span> juist</div>
        <div class="ronde-knoppen">
            <button type="button" id="ronde-opnieuw" class="btn btn-groen btn-groot">🔁 Nog een keer</button>
            <a href="dashboard.php" class="btn btn-primair btn-groot">🏠 Iets anders</a>
        </div>
    </div>

    <div id="feedback" class="feedback verborgen" role="status" aria-live="polite">
        <div id="feedback-icoon" class="feedback-icoon"></div>
        <div id="feedback-bericht" class="feedback-bericht"></div>
        <button type="button" id="feedback-verder" class="btn btn-primair btn-groot">Verder →</button>
    </div>
</main>

<script>
    const CATEGORIE = <?= json_encode($cat) ?>;
    const CSRF      = <?= json_encode($csrf) ?>;
</script>
<script src="<?= asset('assets/js/app.js') ?>"></script>

</body>
</html>
