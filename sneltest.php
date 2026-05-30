<?php
require_once 'includes/auth.php';
require_once 'includes/flatfile.php';

vereisInlog();

$kind      = huidigKind();
$csrf      = csrfToken();
$highscore = leesSneltestHighscore($kind);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sneltest – Oefenwebsite</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inhoud">
        <a href="dashboard.php" class="btn btn-klein btn-uitlog">← Dashboard</a>
        <span style="font-weight:900">⚡ Sneltest</span>
        <div></div>
    </div>
</header>

<main class="sneltest-main">

    <!-- Startscherm -->
    <div id="fase-start" class="snel-fase">
        <div class="snel-kaart">
            <div class="snel-groot-emoji">⚡</div>
            <h2 class="snel-titel">Sneltest</h2>
            <p class="snel-uitleg">Maak zoveel mogelijk sommen<br>in <strong>2 minuten</strong>!</p>
            <?php if ($highscore > 0): ?>
            <div class="snel-huidig-record">🏆 Jouw record: <strong><?= $highscore ?></strong></div>
            <?php endif; ?>
            <button id="start-knop" class="btn btn-primair snel-start-knop">Start!</button>
        </div>
    </div>

    <!-- Actief -->
    <div id="fase-bezig" class="snel-fase verborgen">

        <!-- Cirkeltimer -->
        <div class="snel-ring-wrapper">
            <svg class="snel-ring" viewBox="0 0 120 120" width="120" height="120" aria-hidden="true">
                <circle class="ring-bg"   cx="60" cy="60" r="50"/>
                <circle class="ring-prog" cx="60" cy="60" r="50" id="ring-prog"/>
            </svg>
            <div class="snel-ring-tijd" id="snel-timer">2:00</div>
        </div>

        <!-- Live score -->
        <div class="snel-live-score">
            <span id="snel-correct">0</span> ✓ &nbsp;·&nbsp; <span id="snel-totaal">0</span> geprobeerd
        </div>

        <!-- Som -->
        <div id="snel-vraag" class="snel-vraag-tekst"></div>

        <!-- Input -->
        <input type="number" id="snel-input" class="groot-invulveld snel-getal-input"
               inputmode="numeric" placeholder="?" min="0" max="999" autocomplete="off">

        <!-- Knop -->
        <button id="snel-indienen" class="btn btn-primair snel-check-knop" disabled>Controleer ✓</button>

        <!-- Flash feedback -->
        <div id="snel-flash" class="snel-flash verborgen"></div>

    </div>

    <!-- Eindscherm -->
    <div id="fase-klaar" class="snel-fase verborgen">
        <div class="snel-kaart">
            <div id="eind-emoji" class="snel-groot-emoji">🏆</div>
            <h2 class="snel-titel">Tijd is om!</h2>
            <div class="snel-eind-score">
                <span id="eind-correct">0</span>
                <span class="snel-eind-label"> juist</span>
            </div>
            <div class="snel-eind-pogingen">van <span id="eind-totaal">0</span> pogingen</div>
            <div id="eind-pct" class="snel-eind-pct"></div>
            <div id="nieuw-record" class="snel-nieuw-record verborgen">🎉 Nieuw record!</div>
            <div class="snel-eind-knoppen">
                <button id="opnieuw-knop" class="btn btn-primair">Opnieuw ↺</button>
                <a href="dashboard.php" class="btn btn-uitlog">Dashboard</a>
            </div>
        </div>
    </div>

</main>

<script>
const CSRF         = <?= json_encode($csrf) ?>;
const DUUR         = 120;
const CIRCUMFERENCE = 2 * Math.PI * 50; // r=50 → ≈ 314.16

let timerInterval = null;
let resterend     = DUUR;
let scoreCorrect  = 0;
let scoreTotaal   = 0;
let bezig         = false;
let vorigeVraag   = '';

const el = {
    faseStart:   document.getElementById('fase-start'),
    faseBezig:   document.getElementById('fase-bezig'),
    faseKlaar:   document.getElementById('fase-klaar'),
    timer:       document.getElementById('snel-timer'),
    ringProg:    document.getElementById('ring-prog'),
    vraag:       document.getElementById('snel-vraag'),
    input:       document.getElementById('snel-input'),
    indienen:    document.getElementById('snel-indienen'),
    flash:       document.getElementById('snel-flash'),
    correct:     document.getElementById('snel-correct'),
    totaal:      document.getElementById('snel-totaal'),
    eindCorrect: document.getElementById('eind-correct'),
    eindTotaal:  document.getElementById('eind-totaal'),
    eindPct:     document.getElementById('eind-pct'),
    eindEmoji:   document.getElementById('eind-emoji'),
    nieuwRecord: document.getElementById('nieuw-record'),
};

document.getElementById('start-knop').addEventListener('click', startTest);
document.getElementById('opnieuw-knop').addEventListener('click', () => location.reload());
el.indienen.addEventListener('click', dienIn);
el.input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !el.indienen.disabled) dienIn();
});
el.input.addEventListener('input', () => {
    el.indienen.disabled = el.input.value.trim() === '';
});

async function startTest() {
    resterend    = DUUR;
    scoreCorrect = 0;
    scoreTotaal  = 0;
    bezig        = true;
    vorigeVraag  = '';

    el.faseStart.classList.add('verborgen');
    el.faseBezig.classList.remove('verborgen');

    updateTimer();
    timerInterval = setInterval(() => {
        resterend--;
        updateTimer();
        if (resterend <= 0) eindTest();
    }, 1000);

    await laadVraag();
}

function updateTimer() {
    const min = Math.floor(resterend / 60);
    const sec = resterend % 60;
    el.timer.textContent = `${min}:${sec.toString().padStart(2, '0')}`;

    const pct = resterend / DUUR;
    el.ringProg.style.strokeDashoffset = CIRCUMFERENCE * (1 - pct);
    el.ringProg.style.stroke = resterend > 60 ? '#22c55e'
                             : resterend > 20 ? '#f97316'
                             : '#ef4444';
}

async function laadVraag() {
    if (!bezig) return;
    try {
        let data, pogingen = 0;
        do {
            const res = await fetch('api/oefening.php?cat=gemengd');
            data = await res.json();
            pogingen++;
        } while (data.vraag === vorigeVraag && pogingen < 4);

        vorigeVraag          = data.vraag;
        el.vraag.textContent = data.vraag || '';
        el.input.value       = '';
        el.indienen.disabled = true;
        el.flash.classList.add('verborgen');
        el.input.focus();
    } catch (e) {}
}

async function dienIn() {
    if (!bezig || resterend <= 0) return;
    const antwoord = el.input.value.trim();
    if (!antwoord) return;

    el.indienen.disabled = true;
    scoreTotaal++;

    const fd = new FormData();
    fd.append('antwoord', antwoord);
    const res  = await fetch('api/antwoord.php', {
        method: 'POST',
        headers: { 'X-CSRF-Token': CSRF },
        body: fd,
    });
    const data = await res.json();

    if (data.correct) scoreCorrect++;
    el.correct.textContent = scoreCorrect;
    el.totaal.textContent  = scoreTotaal;

    el.flash.textContent = data.correct ? '✓' : `✗  ${data.correct_antwoord}`;
    el.flash.className   = 'snel-flash ' + (data.correct ? 'flash-goed' : 'flash-fout');

    setTimeout(laadVraag, data.correct ? 250 : 700);
}

function eindTest() {
    bezig = false;
    clearInterval(timerInterval);

    el.faseBezig.classList.add('verborgen');
    el.faseKlaar.classList.remove('verborgen');

    el.eindCorrect.textContent = scoreCorrect;
    el.eindTotaal.textContent  = scoreTotaal;

    const pct = scoreTotaal > 0 ? Math.round(scoreCorrect / scoreTotaal * 100) : 0;
    el.eindPct.textContent = pct + '% correct';

    if      (pct >= 90) el.eindEmoji.textContent = '🏆';
    else if (pct >= 70) el.eindEmoji.textContent = '⭐';
    else                el.eindEmoji.textContent = '💪';

    const fd = new FormData();
    fd.append('actie', 'sneltest_score');
    fd.append('score', scoreCorrect);
    fetch('api/instellingen.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF }, body: fd })
        .then(r => r.json())
        .then(data => { if (data.nieuw_record) el.nieuwRecord.classList.remove('verborgen'); });
}
</script>

</body>
</html>
