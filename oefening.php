<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';

vereisInlog();

global $CATEGORIEEN;
$cat = preg_replace('/[^a-z0-9_]/', '', $_GET['cat'] ?? '');

// Validate category exists
$gevonden = null;
$vakKleur = 'oranje';
foreach ($CATEGORIEEN as $vakKey => $vak) {
    if (isset($vak['oefeningen'][$cat])) {
        $gevonden = $vak['oefeningen'][$cat];
        $vakKleur = $vak['kleur'];
        break;
    }
}

if (!$gevonden) {
    header('Location: dashboard.php');
    exit;
}

$naamCat  = htmlspecialchars($gevonden['naam']);
$emojiCat = $gevonden['emoji'];
$csrf     = csrfToken();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $naamCat ?> – Oefenwebsite</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<meta name="csrf-token" content="<?= $csrf ?>">
</head>
<body class="oefening-pagina kleur-<?= $vakKleur ?>">

<header class="oef-header">
    <a href="dashboard.php" class="terug-knop" aria-label="Terug naar dashboard">←</a>
    <div class="oef-header-midden">
        <span class="oef-header-emoji"><?= $emojiCat ?></span>
        <span class="oef-header-naam"><?= $naamCat ?></span>
    </div>
    <div class="score-teller">
        <span id="score-correct">0</span>/<span id="score-totaal">0</span>
    </div>
</header>

<main class="oefening-main">
    <div id="laad-indicator" class="laad-indicator">Even laden...</div>

    <!-- Oefening kaart -->
    <div id="oefening-kaart" class="oefening-kaart verborgen">

        <div id="oef-label" class="oef-vraag-label"></div>
        <div id="oef-vraag" class="oef-vraag-tekst"></div>
        <div id="oef-extra" class="oef-extra"></div>

        <!-- Invul (getal/tekst) -->
        <div id="invul-zone" class="invoer-zone verborgen">
            <input type="number" id="invul-input" class="groot-invulveld"
                   inputmode="numeric" placeholder="?" min="0" max="100" autocomplete="off">
            <p id="invul-hint" class="invoer-hint"></p>
        </div>

        <!-- Keuze (meerkeuze knoppen) -->
        <div id="keuze-zone" class="invoer-zone verborgen">
            <div id="keuze-knoppen" class="keuze-knoppen"></div>
        </div>

        <!-- Ordenen -->
        <div id="ordenen-zone" class="invoer-zone verborgen">
            <div id="ordenen-rij" class="ordenen-rij"></div>
            <div class="ordenen-label">Jouw volgorde:</div>
            <div id="ordenen-antwoord" class="ordenen-antwoord"></div>
            <button type="button" class="btn btn-klein btn-grijs" id="ordenen-reset">↩ Opnieuw</button>
        </div>

        <!-- Klok -->
        <div id="klok-zone" class="invoer-zone verborgen">
            <div id="klok-svg-container"></div>
            <input type="number" id="klok-input" class="groot-invulveld"
                   inputmode="numeric" placeholder="uur" min="1" max="12" autocomplete="off">
            <p class="invoer-hint">Typ het uur (1–12)</p>
        </div>

        <!-- Rekenslang -->
        <div id="rekenslang-zone" class="invoer-zone verborgen">
            <div id="rekenslang-keten" class="rekenslang-keten"></div>
            <input type="number" id="rekenslang-input" class="groot-invulveld"
                   inputmode="numeric" placeholder="?" min="0" max="20" autocomplete="off">
        </div>

        <button id="indienen-knop" class="btn btn-primair btn-groot indienen-knop" disabled>
            Controleer ✓
        </button>
    </div>

    <!-- Feedback overlay -->
    <div id="feedback" class="feedback verborgen">
        <div id="feedback-icoon" class="feedback-icoon"></div>
        <div id="feedback-bericht" class="feedback-bericht"></div>
    </div>
</main>

<script>
    const CATEGORIE = <?= json_encode($cat) ?>;
    const CSRF      = <?= json_encode($csrf) ?>;
</script>
<script src="assets/js/app.js"></script>

</body>
</html>
