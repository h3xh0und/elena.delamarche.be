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
<body class="oefening-pagina kleur-oranje">

<header class="oef-header">
    <a href="dashboard.php" class="terug-knop" aria-label="Terug naar dashboard">←</a>
    <div class="oef-header-midden">
        <span class="oef-header-emoji">⚡</span>
        <span class="oef-header-naam">Sneltest</span>
    </div>
    <div class="score-teller" id="header-timer">2:00</div>
</header>

<main class="oefening-main">

    <!-- Startscherm -->
    <div id="fase-start" class="oefening-kaart">
        <div style="text-align:center;padding:.5rem 0">
            <div style="font-size:3.5rem;line-height:1;margin-bottom:.5rem">⚡</div>
            <div class="oef-vraag-tekst" style="font-size:2rem;margin-bottom:.5rem">Sneltest</div>
            <p style="color:var(--tekst-zacht);font-size:1.05rem;margin-bottom:1.25rem">
                Maak zoveel mogelijk sommen in <strong>2 minuten</strong>!
            </p>
            <?php if ($highscore > 0): ?>
            <p style="font-size:1rem;font-weight:800;color:var(--oranje);margin-bottom:1.25rem">
                🏆 Jouw record: <strong><?= $highscore ?></strong>
            </p>
            <?php endif; ?>
        </div>
        <button id="start-knop" class="btn btn-primair btn-groot indienen-knop">Start!</button>
    </div>

    <!-- Actief -->
    <div id="fase-bezig" class="oefening-kaart verborgen">

        <div class="snel-ring-wrapper">
            <svg class="snel-ring" viewBox="0 0 120 120" width="120" height="120" aria-hidden="true">
                <circle class="ring-bg"   cx="60" cy="60" r="50"/>
                <circle class="ring-prog" cx="60" cy="60" r="50" id="ring-prog"/>
            </svg>
            <div class="snel-ring-tijd" id="snel-timer">2:00</div>
        </div>

        <div style="text-align:center;color:var(--tekst-zacht);font-weight:700;font-size:1rem">
            <span id="snel-correct">0</span> ✓ &nbsp;·&nbsp; <span id="snel-totaal">0</span> geprobeerd
        </div>

        <div id="snel-vraag" class="oef-vraag-tekst"></div>

        <input type="number" id="snel-input" class="groot-invulveld"
               inputmode="numeric" placeholder="?" min="0" max="999" autocomplete="off">

        <button id="snel-indienen" class="btn btn-primair btn-groot indienen-knop" disabled>Controleer ✓</button>

        <div id="snel-flash" class="snel-flash verborgen"></div>

    </div>

    <!-- Eindscherm -->
    <div id="fase-klaar" class="oefening-kaart verborgen">
        <div style="text-align:center;padding:.5rem 0">
            <div id="eind-emoji" style="font-size:3.5rem;line-height:1;margin-bottom:.5rem">🏆</div>
            <div class="oef-vraag-tekst" style="font-size:2rem;margin-bottom:.75rem">Tijd is om!</div>
            <div style="font-size:3.5rem;font-weight:900;color:var(--primair)">
                <span id="eind-correct">0</span>
                <span style="font-size:1.4rem;font-weight:700;color:var(--tekst-zacht)"> juist</span>
            </div>
            <div style="color:var(--tekst-zacht);font-size:1rem;margin-bottom:.5rem">
                van <span id="eind-totaal">0</span> pogingen
            </div>
            <div id="eind-pct" style="font-size:1.3rem;font-weight:800;margin-bottom:.75rem"></div>
            <div id="nieuw-record" class="verborgen"
                 style="font-size:1.3rem;font-weight:900;color:var(--oranje);margin-bottom:.75rem">
                🎉 Nieuw record!
            </div>
        </div>
        <div style="display:flex;gap:1rem;justify-content:center">
            <button id="opnieuw-knop" class="btn btn-primair btn-groot indienen-knop" style="width:auto">Opnieuw ↺</button>
            <a href="dashboard.php" class="btn btn-groot btn-uitlog" style="width:auto">Dashboard</a>
        </div>
    </div>

</main>

<script>
const CSRF        = <?= json_encode($csrf) ?>;
const DUUR        = 120;
const CIRCUMFERENCE = 2 * Math.PI * 50;

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
    headerTimer: document.getElementById('header-timer'),
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
    const txt = `${min}:${sec.toString().padStart(2, '0')}`;
    el.timer.textContent       = txt;
    el.headerTimer.textContent = txt;

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
