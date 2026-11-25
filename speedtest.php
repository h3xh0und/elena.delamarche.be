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
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#FFFFFF">
<title>Sneltest – Rekenavontuur</title>
<link rel="stylesheet" href="<?= asset('assets/css/fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<body class="oefening-pagina kleur-geel">

<header class="oef-header">
    <a href="dashboard.php" class="terug-knop" aria-label="Terug naar alle oefeningen">🏠</a>
    <div class="oef-header-midden">
        <span class="oef-header-emoji">⚡</span>
        <span class="oef-header-naam">Sneltest</span>
    </div>
    <div class="score-teller" id="header-timer">2:00</div>
    <button type="button" id="geluid-knop" class="geluid-knop" aria-label="Geluid aan of uit">🔇</button>
</header>

<main class="oefening-main">

    <!-- Startscherm -->
    <div id="fase-start" class="oefening-kaart kolom">
        <div class="snel-intro">
            <div class="ronde-oki" data-oki="blij"></div>
            <div class="titel">Sneltest</div>
            <p>Maak zoveel mogelijk sommen in <strong>2 minuten</strong>!</p>
            <div class="bewerkingen" aria-label="plus, min, maal en delen om de beurt">
                <span>+</span><span>−</span><span>×</span><span>:</span>
            </div>
            <?php if ($highscore > 0): ?>
            <p class="record">🏆 Jouw record: <strong><?= $highscore ?></strong></p>
            <?php endif; ?>
        </div>
        <button id="start-knop" class="btn btn-groen btn-groot snel-start-knop">Start! ▶</button>
    </div>

    <!-- Bezig -->
    <div id="fase-bezig" class="oefening-kaart verborgen">

        <div class="snel-inhoud">
            <div class="snel-ring-wrapper">
                <svg class="snel-ring" viewBox="0 0 120 120" width="120" height="120" aria-hidden="true">
                    <circle class="snel-ring-achter" cx="60" cy="60" r="50" fill="none" stroke-width="12"/>
                    <circle id="ring-prog" cx="60" cy="60" r="50"
                            fill="none" stroke="#1FA968" stroke-width="12"
                            stroke-linecap="round"
                            stroke-dasharray="314.16" stroke-dashoffset="0"/>
                </svg>
                <div class="snel-ring-tijd" id="snel-timer">2:00</div>
            </div>

            <div class="snel-stand">
                <span id="snel-correct">0</span> ✓ &nbsp;·&nbsp; <span id="snel-totaal">0</span> geprobeerd
            </div>

            <div id="snel-vraag" class="oef-vraag-tekst"></div>

            <div id="snel-flash" class="snel-flash verborgen"></div>
        </div>

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
                <button type="button" class="np-btn np-wis" id="snel-wis" aria-label="Wissen">⌫</button>
                <button type="button" class="np-btn" data-n="0">0</button>
                <button type="button" class="np-btn np-ok" id="snel-ok" aria-label="Klaar" disabled>✓</button>
            </div>
        </div>

    </div>

    <!-- Eindscherm -->
    <div id="fase-klaar" class="oefening-kaart kolom ronde-klaar verborgen">
        <div id="eind-oki" class="ronde-oki" data-oki="juich"></div>
        <div class="ronde-titel">Tijd is om! <span id="eind-emoji">🏆</span></div>
        <div class="snel-eind-getal"><span id="eind-correct">0</span> <small>juist</small></div>
        <div class="snel-eind-sub">van <span id="eind-totaal">0</span> sommen</div>
        <div id="eind-pct" class="snel-pct"></div>
        <div id="nieuw-record" class="nieuw-record verborgen">🎉 Nieuw record!</div>
        <div class="ronde-knoppen">
            <button id="opnieuw-knop" class="btn btn-groen btn-groot">🔁 Nog een keer</button>
            <a href="dashboard.php" class="btn btn-primair btn-groot">🏠 Iets anders</a>
        </div>
    </div>

</main>

<script src="<?= asset('assets/js/sound.js') ?>"></script>
<script src="<?= asset('assets/js/mascot.js') ?>"></script>
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
let questionNr      = 0;

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
    const live = document.getElementById('snel-live');
    if (live) {
        live.textContent = numVal || '?';
        live.classList.toggle('leeg', numVal === '');
    }
    el.display.classList.toggle('verborgen', !!live);
    el.display.textContent = numVal || '?';
    el.display.classList.toggle('leeg', numVal === '');
    el.ok.disabled = numVal === '';
}
function resetInput() {
    numVal = '';
    updateDisplay();
}

Sound.init(document.getElementById('geluid-knop'));
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
    questionNr      = 0;

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
    el.ringProg.style.stroke = remaining > 60 ? '#1FA968'
                             : remaining > 20 ? '#FF8A3D'
                             : '#F0569B';
}

async function loadQuestion() {
    if (!running) return;
    try {
        let data, attempts = 0;
        do {
            const res = await fetch('api/exercise.php?cat=speedtest&stap=' + questionNr);
            data = await res.json();
            attempts++;
        } while (questionHistory.includes(data.vraag) && attempts < 6);

        questionNr++;
        questionHistory.push(data.vraag);
        if (questionHistory.length > 12) questionHistory.shift();
        // Show the typed answer in place of the "?"
        el.question.innerHTML = String(data.vraag || '')
            .replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'})[c])
            .replace(/(^|\s)\?(?=\s|$)/, '$1<span id="snel-live" class="antwoord-vak leeg">?</span>');
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
    Sound.play(data.correct ? 'good' : 'wrong');
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
    Sound.play('round');

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
