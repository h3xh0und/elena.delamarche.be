<?php
require_once 'includes/auth.php';
require_once 'includes/flatfile.php';

requireLogin();

$user      = currentUser();
$csrf      = csrfToken();
$highscore = readSpeedtestHighscore($user);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sneltest – Oefenwebsite</title>
<link rel="stylesheet" href="assets/css/fonts.css">
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

        <div class="snel-inhoud">
            <div class="snel-ring-wrapper">
                <svg class="snel-ring" viewBox="0 0 120 120" width="120" height="120" aria-hidden="true">
                    <circle cx="60" cy="60" r="50"
                            fill="none" stroke="#e2e8f0" stroke-width="10"/>
                    <circle id="ring-prog" cx="60" cy="60" r="50"
                            fill="none" stroke="#22c55e" stroke-width="10"
                            stroke-linecap="round"
                            stroke-dasharray="314.16" stroke-dashoffset="0"/>
                </svg>
                <div class="snel-ring-tijd" id="snel-timer">2:00</div>
            </div>

            <div style="text-align:center;color:var(--tekst-zacht);font-weight:700;font-size:1rem">
                <span id="snel-correct">0</span> ✓ &nbsp;·&nbsp; <span id="snel-totaal">0</span> geprobeerd
            </div>

            <div id="snel-vraag" class="oef-vraag-tekst"></div>

            <div id="snel-flash" class="snel-flash verborgen"></div>
        </div>

        <!-- Numpad -->
        <div class="numpad">
            <div id="snel-display" class="numpad-display leeg">?</div>
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
                <button type="button" class="np-btn np-wis" id="snel-wis">⌫</button>
                <button type="button" class="np-btn" data-n="0">0</button>
                <button type="button" class="np-btn np-ok" id="snel-ok" disabled>✓</button>
            </div>
        </div>

    </div><!-- /fase-bezig -->

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
const DURATION    = 120;
const CIRCUMFERENCE = 2 * Math.PI * 50;

let timerInterval = null;
let remaining     = DURATION;
let scoreCorrect  = 0;
let scoreTotal    = 0;
let running       = false;
let questionHistory = [];

let numVal = '';

const el = {
    phaseStart:  document.getElementById('fase-start'),
    phaseRunning: document.getElementById('fase-bezig'),
    phaseDone:   document.getElementById('fase-klaar'),
    timer:       document.getElementById('snel-timer'),
    headerTimer: document.getElementById('header-timer'),
    ringProg:    document.getElementById('ring-prog'),
    question:    document.getElementById('snel-vraag'),
    display:     document.getElementById('snel-display'),
    ok:          document.getElementById('snel-ok'),
    flash:       document.getElementById('snel-flash'),
    correct:     document.getElementById('snel-correct'),
    total:       document.getElementById('snel-totaal'),
    endCorrect:  document.getElementById('eind-correct'),
    endTotal:    document.getElementById('eind-totaal'),
    endPct:      document.getElementById('eind-pct'),
    endEmoji:    document.getElementById('eind-emoji'),
    newRecord:   document.getElementById('nieuw-record'),
};

function addDigit(d) {
    if (numVal.length >= 3) return;
    numVal += d;
    updateDisplay();
}
function deleteDigit() {
    numVal = numVal.slice(0, -1);
    updateDisplay();
}
function updateDisplay() {
    el.display.textContent = numVal || '?';
    el.display.classList.toggle('leeg', numVal === '');
    el.ok.disabled = numVal === '';
}
function resetInput() {
    numVal = '';
    updateDisplay();
}

document.getElementById('start-knop').addEventListener('click', startTest);
document.getElementById('opnieuw-knop').addEventListener('click', () => location.reload());
el.ok.addEventListener('click', submit);
document.getElementById('snel-wis').addEventListener('click', deleteDigit);
document.querySelectorAll('.np-btn[data-n]').forEach(btn => {
    btn.addEventListener('click', () => addDigit(btn.dataset.n));
});
document.addEventListener('keydown', e => {
    if (!running) return;
    if (e.key === 'Enter' && !el.ok.disabled) { submit(); return; }
    if (e.key >= '0' && e.key <= '9') { addDigit(e.key); e.preventDefault(); }
    else if (e.key === 'Backspace')    { deleteDigit();   e.preventDefault(); }
});

async function startTest() {
    remaining    = DURATION;
    scoreCorrect = 0;
    scoreTotal   = 0;
    running      = true;
    questionHistory = [];

    el.phaseStart.classList.add('verborgen');
    el.phaseRunning.classList.remove('verborgen');

    updateTimer();
    timerInterval = setInterval(() => {
        remaining--;
        updateTimer();
        if (remaining <= 0) endTest();
    }, 1000);

    await loadQuestion();
}

function updateTimer() {
    const min = Math.floor(remaining / 60);
    const sec = remaining % 60;
    const txt = `${min}:${sec.toString().padStart(2, '0')}`;
    el.timer.textContent       = txt;
    el.headerTimer.textContent = txt;

    const pct = remaining / DURATION;
    el.ringProg.style.strokeDashoffset = CIRCUMFERENCE * (1 - pct);
    el.ringProg.style.stroke = remaining > 60 ? '#22c55e'
                             : remaining > 20 ? '#f97316'
                             : '#ef4444';
}

async function loadQuestion() {
    if (!running) return;
    try {
        let data, attempts = 0;
        do {
            const res = await fetch('api/exercise.php?cat=speedtest');
            data = await res.json();
            attempts++;
        } while (questionHistory.includes(data.vraag) && attempts < 6);

        questionHistory.push(data.vraag);
        if (questionHistory.length > 12) questionHistory.shift();
        el.question.textContent = data.vraag || '';
        resetInput();
        el.flash.classList.add('verborgen');
    } catch (e) {}
}

async function submit() {
    if (!running || remaining <= 0) return;
    const answer = numVal;
    if (!answer) return;

    el.ok.disabled = true;
    scoreTotal++;

    const fd = new FormData();
    fd.append('antwoord', answer);
    const res  = await fetch('api/answer.php', {
        method: 'POST',
        headers: { 'X-CSRF-Token': CSRF },
        body: fd,
    });
    const data = await res.json();

    if (data.correct) scoreCorrect++;
    el.correct.textContent = scoreCorrect;
    el.total.textContent   = scoreTotal;

    el.flash.textContent = data.correct ? '✓' : `✗  ${data.correct_antwoord}`;
    el.flash.className   = 'snel-flash ' + (data.correct ? 'flash-goed' : 'flash-fout');

    setTimeout(loadQuestion, data.correct ? 250 : 700);
}

function endTest() {
    running = false;
    clearInterval(timerInterval);

    el.phaseRunning.classList.add('verborgen');
    el.phaseDone.classList.remove('verborgen');

    el.endCorrect.textContent = scoreCorrect;
    el.endTotal.textContent   = scoreTotal;

    const pct = scoreTotal > 0 ? Math.round(scoreCorrect / scoreTotal * 100) : 0;
    el.endPct.textContent = pct + '% correct';

    if      (pct >= 90) el.endEmoji.textContent = '🏆';
    else if (pct >= 70) el.endEmoji.textContent = '⭐';
    else                el.endEmoji.textContent = '💪';

    const fd = new FormData();
    fd.append('action', 'speedtest_score');
    fd.append('score', scoreCorrect);
    fetch('api/settings.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF }, body: fd })
        .then(r => r.json())
        .then(data => { if (data.new_record) el.newRecord.classList.remove('verborgen'); });
}
</script>

</body>
</html>
