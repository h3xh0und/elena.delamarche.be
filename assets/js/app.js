'use strict';

const ROUND_LENGTH = 10;

/* ── State ─────────────────────────────────────────────── */
const state = {
  currentType:    null,
  currentAnswer:  null,   // selected answer (choice/ordering)
  numVal:         '',     // current numeric input (numpad)
  orderingPool:   [],     // remaining numbers for ordering
  orderingChosen: [],     // chosen numbers for ordering
  round:          [],     // true/false per answered exercise in this round
  locked:         false,  // input blocked while checking / showing feedback
  waiting:        false,  // feedback shown, waiting for "Verder"
  sound:          false,
};

/* ── DOM refs ──────────────────────────────────────────── */
const el = {
  loading:        document.getElementById('laad-indicator'),
  card:           document.getElementById('oefening-kaart'),
  label:          document.getElementById('oef-label'),
  question:       document.getElementById('oef-vraag'),
  extra:          document.getElementById('oef-extra'),
  feedback:       document.getElementById('feedback'),
  fbIcon:         document.getElementById('feedback-icoon'),
  fbMessage:      document.getElementById('feedback-bericht'),
  fbNext:         document.getElementById('feedback-verder'),
  scoreCounter:   document.getElementById('score-teller'),
  scoreCorrect:   document.getElementById('score-correct'),
  roundBar:       document.getElementById('ronde-balk'),
  roundDone:      document.getElementById('ronde-klaar'),
  roundEmoji:     document.getElementById('ronde-emoji'),
  roundTitle:     document.getElementById('ronde-titel'),
  roundStars:     document.getElementById('ronde-sterren'),
  roundCorrect:   document.getElementById('ronde-correct'),
  roundTotal:     document.getElementById('ronde-totaal'),
  roundAgain:     document.getElementById('ronde-opnieuw'),
  soundBtn:       document.getElementById('geluid-knop'),
  submitBtn:      document.getElementById('indienen-knop'),
  // zones
  fillZone:       document.getElementById('invul-zone'),
  choiceZone:     document.getElementById('keuze-zone'),
  choiceButtons:  document.getElementById('keuze-knoppen'),
  orderingZone:   document.getElementById('ordenen-zone'),
  orderingRow:    document.getElementById('ordenen-rij'),
  orderingAnswer: document.getElementById('ordenen-antwoord'),
  orderingReset:  document.getElementById('ordenen-reset'),
  clockZone:      document.getElementById('klok-zone'),
  clockSvg:       document.getElementById('klok-svg-container'),
  numberSnakeZone: document.getElementById('rekenslang-zone'),
  numberSnakeChain: document.getElementById('rekenslang-keten'),
  // numpad
  numpad:         document.getElementById('numpad'),
  numpadDisplay:  document.getElementById('numpad-display'),
  numpadHint:     document.getElementById('numpad-hint'),
  npOk:           document.getElementById('np-ok'),
};

/* ── Init ──────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  initSound();
  renderRoundBar();
  loadNextExercise();

  el.submitBtn.addEventListener('click', submit);
  el.npOk.addEventListener('click', submit);
  el.orderingReset.addEventListener('click', resetOrdering);
  el.fbNext.addEventListener('click', next);
  el.roundAgain.addEventListener('click', startRound);

  document.getElementById('np-wis').addEventListener('click', deleteDigit);
  document.querySelectorAll('.np-btn[data-n]').forEach(btn => {
    btn.addEventListener('click', () => addDigit(btn.dataset.n));
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      if (state.waiting) { next(); return; }
      if (!el.roundDone.classList.contains('verborgen')) { startRound(); return; }
      if (!el.numpad.classList.contains('verborgen') && !el.npOk.disabled) { submit(); return; }
      if (!el.submitBtn.disabled) submit();
      return;
    }
    if (el.numpad.classList.contains('verborgen')) return;
    if (e.key >= '0' && e.key <= '9') { addDigit(e.key); e.preventDefault(); }
    else if (e.key === 'Backspace')    { deleteDigit();    e.preventDefault(); }
  });
});

/* ── Round (10 exercises) ──────────────────────────────── */
function startRound() {
  state.round = [];
  el.scoreCorrect.textContent = 0;
  el.roundDone.classList.add('verborgen');
  renderRoundBar();
  loadNextExercise();
}

function renderRoundBar() {
  el.roundBar.innerHTML = '';
  for (let i = 0; i < ROUND_LENGTH; i++) {
    const dot = document.createElement('span');
    dot.className = 'ronde-stip';
    if (i < state.round.length)        dot.classList.add(state.round[i] ? 'goed' : 'fout');
    else if (i === state.round.length) dot.classList.add('huidig');
    el.roundBar.appendChild(dot);
  }
}

function showRoundEnd() {
  const correct = state.round.filter(Boolean).length;
  const stars   = correct >= 9 ? 3 : correct >= 6 ? 2 : 1;

  el.card.classList.add('verborgen');
  el.roundEmoji.textContent   = stars === 3 ? '🏆' : stars === 2 ? '🎉' : '💪';
  el.roundTitle.textContent   = stars === 3 ? 'Fantastisch!' : stars === 2 ? 'Goed gedaan!' : 'Goed geoefend!';
  el.roundStars.innerHTML     = '<span class="vol">★</span>'.repeat(stars) + '★'.repeat(3 - stars);
  el.roundCorrect.textContent = correct;
  el.roundTotal.textContent   = ROUND_LENGTH;
  el.roundDone.classList.remove('verborgen');
  el.roundAgain.focus({ preventScroll: true });

  play('round');
  confetti(24);
}

/* ── Load exercise ─────────────────────────────────────── */
async function loadNextExercise() {
  showLoading(true);

  try {
    const res  = await fetch(`api/exercise.php?cat=${encodeURIComponent(CATEGORIE)}`);
    const data = await res.json();
    if (data.fout) { showLoading(true, data.fout); return; }
    showExercise(data);
  } catch (e) {
    showLoading(true, 'Kon oefening niet laden. Ververs de pagina.');
  }
}

function showExercise(data) {
  state.currentType   = data.type;
  state.currentAnswer = null;
  state.locked        = false;

  hideAllZones();
  el.label.textContent = data.label || el.label.dataset.naam || '';
  el.extra.innerHTML   = '';
  el.question.classList.remove('lang-vraag');

  const questionText = data.vraag || '';
  // A stand-alone "?" is the blank to fill in: show the typed answer right there.
  const blank = /(^|\s)\?(?=\s|$)/;

  if (data.type === 'invul' && blank.test(questionText)) {
    el.question.innerHTML = esc(questionText)
      .replace(blank, '$1<span class="antwoord-vak antwoord-live leeg">?</span>');
  } else if (data.type === 'keuze' && questionText && isNaN(questionText)) {
    el.question.innerHTML = `<span class="keuze-vraag-display">${esc(questionText)}</span>`;
  } else {
    el.question.textContent = questionText;
  }

  if (questionText.length > 22) el.question.classList.add('lang-vraag');

  switch (data.type) {
    case 'invul':      setupFill(data);        break;
    case 'keuze':      setupChoice(data);      break;
    case 'ordenen':    setupOrdering(data);    break;
    case 'klok':       setupClock(data);       break;
    case 'rekenslang': setupNumberSnake(data); break;
    case 'splitsing':  setupSplitting(data);   break;
    case 'pictogram':  setupPictogram(data);   break;
    default:           setupFill(data);
  }

  showLoading(false);
  el.card.classList.add('pop-in');
  setTimeout(() => el.card.classList.remove('pop-in'), 350);
}

/* ── Setup per type ────────────────────────────────────── */
function setupFill(data) {
  showZone('fill');
  showNumpad(true, data.hint || '');
}

function setupChoice(data) {
  el.choiceButtons.innerHTML = '';
  state.currentAnswer = null;
  (data.opties || []).forEach(opt => {
    const btn = document.createElement('button');
    btn.type        = 'button';
    btn.className   = 'keuze-knop';
    btn.textContent = opt;
    btn.addEventListener('click', () => {
      if (state.locked) return;
      document.querySelectorAll('.keuze-knop').forEach(b => b.classList.remove('geselecteerd'));
      btn.classList.add('geselecteerd');
      state.currentAnswer = opt;
      el.submitBtn.disabled = false;
    });
    el.choiceButtons.appendChild(btn);
  });
  showZone('choice');
  el.submitBtn.disabled = true;
}

function setupOrdering(data) {
  state.orderingPool   = [...data.getallen];
  state.orderingChosen = [];
  renderOrdering();
  showZone('ordering');
  el.submitBtn.disabled = true;
}

function renderOrdering() {
  el.orderingRow.innerHTML    = '';
  el.orderingAnswer.innerHTML = '';

  state.orderingPool.forEach((n, i) => {
    const btn = document.createElement('button');
    btn.type        = 'button';
    btn.className   = 'getal-chip';
    btn.textContent = n;
    btn.dataset.idx = i;
    if (state.orderingChosen.includes(i)) btn.classList.add('gebruikt');
    btn.addEventListener('click', () => {
      if (state.locked || btn.classList.contains('gebruikt')) return;
      btn.classList.add('gebruikt');
      state.orderingChosen.push(i);
      addAnswerChip(n, i);
      if (state.orderingChosen.length === state.orderingPool.length) {
        el.submitBtn.disabled = false;
      }
    });
    el.orderingRow.appendChild(btn);
  });

  state.orderingChosen.forEach(idx => addAnswerChip(state.orderingPool[idx], idx));
}

function addAnswerChip(n, idx) {
  const chip = document.createElement('button');
  chip.type        = 'button';
  chip.className   = 'antwoord-chip';
  chip.textContent = n;
  chip.title       = 'Tik om terug te leggen';
  chip.addEventListener('click', () => {
    if (state.locked) return;
    const pos = state.orderingChosen.indexOf(idx);
    if (pos !== -1) state.orderingChosen.splice(pos, 1);
    renderOrdering();
    el.submitBtn.disabled = state.orderingChosen.length < state.orderingPool.length;
  });
  el.orderingAnswer.appendChild(chip);
}

function resetOrdering() {
  if (state.locked) return;
  state.orderingChosen = [];
  renderOrdering();
  el.submitBtn.disabled = true;
}

function setupClock(data) {
  el.clockSvg.innerHTML = data.svg || '';

  if (data.klok_invoer === 'keuze') {
    let clockChoice = document.getElementById('klok-keuze');
    if (!clockChoice) {
      clockChoice = document.createElement('div');
      clockChoice.id = 'klok-keuze';
      clockChoice.className = 'keuze-knoppen';
      el.clockZone.appendChild(clockChoice);
    }
    clockChoice.innerHTML = '';
    state.currentAnswer = null;
    (data.opties || []).forEach(opt => {
      const btn = document.createElement('button');
      btn.type        = 'button';
      btn.className   = 'keuze-knop';
      btn.textContent = opt;
      btn.addEventListener('click', () => {
        if (state.locked) return;
        clockChoice.querySelectorAll('.keuze-knop').forEach(b => b.classList.remove('geselecteerd'));
        btn.classList.add('geselecteerd');
        state.currentAnswer = opt;
        el.submitBtn.disabled = false;
      });
      clockChoice.appendChild(btn);
    });
    showZone('clock');
    showNumpad(false);
    el.submitBtn.disabled = true;
  } else {
    const existing = document.getElementById('klok-keuze');
    if (existing) existing.innerHTML = '';
    showZone('clock');
    showNumpad(true, 'Typ het uur (1–12)');
  }
}

function setupNumberSnake(data) {
  el.numberSnakeChain.innerHTML = '';

  const startEl = document.createElement('div');
  startEl.className   = 'rsl-getal start';
  startEl.textContent = data.start;
  el.numberSnakeChain.appendChild(startEl);

  (data.keten || []).forEach(step => {
    const stepEl = document.createElement('div');
    stepEl.className = 'rsl-stap';
    stepEl.innerHTML = `<span class="rsl-op">${esc(step.op)}</span><span class="rsl-pijl">→</span>`;
    el.numberSnakeChain.appendChild(stepEl);

    const toEl = document.createElement('div');
    toEl.className   = 'rsl-getal' + (step.naar === '?' ? ' ontbreekt antwoord-live' : '');
    toEl.textContent = step.naar;
    el.numberSnakeChain.appendChild(toEl);
  });

  showZone('numberSnake');
  showNumpad(true);
}

function setupSplitting(data) {
  const corner = (cls, x, y, val) => {
    const missing = val === '?';
    return `<g class="spl-hoek ${cls}${missing ? ' ontbreekt' : ''}">`
         + `<circle cx="${x}" cy="${y}" r="34"/>`
         + `<text x="${x}" y="${y}"${missing ? ' class="antwoord-live"' : ''}>${esc(val)}</text></g>`;
  };
  el.extra.innerHTML =
      `<svg viewBox="0 0 220 190" class="splits-driehoek" aria-label="Splitsing">`
    + `<polygon class="spl-lijn" points="110,40 45,150 175,150"/>`
    + corner('boven', 110, 40, data.boven)
    + corner('onder', 45, 150, data.links)
    + corner('onder', 175, 150, data.rechts)
    + `</svg>`;
  state.currentType = 'invul';
  setupFill(data);
}

function setupPictogram(data) {
  renderPictogram(data);
  state.currentType = data.invoer === 'getal' ? 'invul' : 'keuze';
  if (data.invoer === 'getal') setupFill(data);
  else setupChoice(data);
}

function renderPictogram(data) {
  let html = `<div class="pictogram-tabel">`;
  html += `<div class="pic-titel">${esc(data.emoji)} ${esc(data.titel)}</div>`;
  (data.rijen || []).forEach(row => {
    const dots = Array(row.aantal).fill('<span class="pic-dot"></span>').join('');
    html += `<div class="pic-rij">`;
    html += `<span class="pic-naam">${esc(row.naam)}</span>`;
    html += `<span class="pic-balk">${dots}<strong class="pic-getal">${row.aantal}</strong></span>`;
    html += `</div>`;
  });
  html += `</div>`;
  el.extra.innerHTML = html;
}

/* ── Numpad ────────────────────────────────────────────── */

// Places in the question itself (blank box, snake cell, triangle corner) that mirror the typed answer.
function liveTargets() {
  return el.card.querySelectorAll('.antwoord-live');
}

function showNumpad(visible, hint = '') {
  el.numpad.classList.toggle('verborgen', !visible);
  el.submitBtn.classList.toggle('verborgen', visible);
  if (visible) resetNumpad(hint);
}

function resetNumpad(hint = '') {
  state.numVal = '';
  el.numpadHint.textContent = hint;
  updateNumpad();
}

function updateNumpad() {
  const val  = state.numVal;
  const live = liveTargets();
  live.forEach(t => {
    t.textContent = val || '?';
    t.classList.toggle('leeg', val === '');
  });
  el.numpadDisplay.classList.toggle('verborgen', live.length > 0);
  el.numpadDisplay.textContent = val || '?';
  el.numpadDisplay.classList.toggle('leeg', val === '');
  el.npOk.disabled = val === '';
}

function addDigit(d) {
  if (state.locked || state.numVal.length >= 3) return;
  state.numVal += d;
  updateNumpad();
}

function deleteDigit() {
  if (state.locked) return;
  state.numVal = state.numVal.slice(0, -1);
  updateNumpad();
}

/* ── Submit answer ─────────────────────────────────────── */
async function submit() {
  if (state.locked) return;
  const answer = getAnswer();
  if (answer === null || answer === '') return;

  state.locked = true;
  el.submitBtn.disabled = true;
  el.npOk.disabled = true;

  try {
    const fd = new FormData();
    fd.append('antwoord', answer);

    const res  = await fetch('api/answer.php', {
      method: 'POST',
      headers: { 'X-CSRF-Token': CSRF },
      body: fd,
    });
    const data = await res.json();
    if (data.fout) throw new Error(data.fout);

    state.round.push(!!data.correct);
    renderRoundBar();
    if (data.correct) {
      el.scoreCorrect.textContent = state.round.filter(Boolean).length;
      el.scoreCounter.classList.remove('plop');
      void el.scoreCounter.offsetWidth;
      el.scoreCounter.classList.add('plop');
    }

    markAnswer(data);
    showFeedback(data.correct, data.bericht);

  } catch (e) {
    state.locked = false;
    el.submitBtn.disabled = false;
    el.npOk.disabled = state.numVal === '';
  }
}

function getAnswer() {
  switch (state.currentType) {
    case 'invul':      return state.numVal;
    case 'keuze':      return state.currentAnswer;
    case 'klok':
      return state.currentAnswer !== null ? state.currentAnswer : state.numVal;
    case 'rekenslang': return state.numVal;
    case 'ordenen':
      return state.orderingChosen.map(i => state.orderingPool[i]).join(',');
    default:           return null;
  }
}

/* ── Feedback ──────────────────────────────────────────── */

// Show the result inside the exercise itself: the right answer is always visible afterwards.
function markAnswer(data) {
  liveTargets().forEach(t => {
    t.classList.remove('leeg');
    if (data.correct) { t.classList.add('goed'); return; }
    t.textContent = data.correct_antwoord;
    t.classList.add('juist-getoond');
  });
  el.card.querySelectorAll('.keuze-knop').forEach(b => {
    if (b.textContent === String(data.correct_antwoord)) b.classList.add('juist');
    else if (b.classList.contains('geselecteerd'))       b.classList.add('mis');
  });
}

function showFeedback(correct, message) {
  el.feedback.className = 'feedback ' + (correct ? 'correct' : 'incorrect');
  el.fbIcon.textContent    = correct ? '🎉' : '🤔';
  el.fbMessage.textContent = message;
  el.feedback.classList.remove('verborgen');

  play(correct ? 'good' : 'wrong');
  if (navigator.vibrate) navigator.vibrate(correct ? 40 : [30, 60, 30]);

  if (correct) {
    confetti(12);
    setTimeout(next, 1200);
  } else {
    // No timer on mistakes: the child reads the right answer and continues when ready.
    state.waiting = true;
    el.fbNext.focus({ preventScroll: true });
  }
}

function next() {
  state.waiting = false;
  el.feedback.classList.add('verborgen');
  if (state.round.length >= ROUND_LENGTH) showRoundEnd();
  else loadNextExercise();
}

function confetti(count) {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const box = document.createElement('div');
  box.className = 'confetti';
  const icons = ['⭐', '🌟', '✨', '🎉', '💛'];
  for (let i = 0; i < count; i++) {
    const s = document.createElement('span');
    const angle = Math.random() * Math.PI * 2;
    const dist  = 120 + Math.random() * 220;
    s.textContent = icons[i % icons.length];
    s.style.setProperty('--dx',  `${Math.cos(angle) * dist}px`);
    s.style.setProperty('--dy',  `${Math.sin(angle) * dist}px`);
    s.style.setProperty('--rot', `${(Math.random() - .5) * 540}deg`);
    box.appendChild(s);
  }
  document.body.appendChild(box);
  setTimeout(() => box.remove(), 1000);
}

/* ── Sound (generated, no audio files) ─────────────────── */
let audioCtx = null;

function initSound() {
  try { state.sound = localStorage.getItem('geluid') === 'aan'; } catch (e) {}
  updateSoundBtn();
  el.soundBtn.addEventListener('click', () => {
    state.sound = !state.sound;
    try { localStorage.setItem('geluid', state.sound ? 'aan' : 'uit'); } catch (e) {}
    updateSoundBtn();
    play('good');
  });
}

function updateSoundBtn() {
  el.soundBtn.textContent = state.sound ? '🔊' : '🔇';
  el.soundBtn.setAttribute('aria-pressed', state.sound ? 'true' : 'false');
}

function play(kind) {
  if (!state.sound) return;
  const Ctx = window.AudioContext || window.webkitAudioContext;
  if (!Ctx) return;
  try {
    audioCtx = audioCtx || new Ctx();
    if (audioCtx.state === 'suspended') audioCtx.resume();
    const notes = {
      good:  [[660, 0], [880, .1]],
      wrong: [[300, 0], [250, .14]],
      round: [[523, 0], [659, .12], [784, .24], [1047, .36]],
    }[kind];
    notes.forEach(([freq, at]) => {
      const osc  = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      const t    = audioCtx.currentTime + at;
      osc.type = kind === 'wrong' ? 'sine' : 'triangle';
      osc.frequency.value = freq;
      gain.gain.setValueAtTime(.0001, t);
      gain.gain.exponentialRampToValueAtTime(.25, t + .02);
      gain.gain.exponentialRampToValueAtTime(.0001, t + .22);
      osc.connect(gain).connect(audioCtx.destination);
      osc.start(t);
      osc.stop(t + .25);
    });
  } catch (e) {}
}

/* ── Helpers ───────────────────────────────────────────── */
function showLoading(visible, text) {
  el.loading.textContent  = text || '';
  el.loading.style.display = visible && text ? 'block' : 'none';
  // Keep the previous card in place while the next one loads, so the screen doesn't flash.
  if (!visible || text) el.card.classList.toggle('verborgen', visible);
}

function hideAllZones() {
  [el.fillZone, el.choiceZone, el.orderingZone,
   el.clockZone, el.numberSnakeZone].forEach(z => z.classList.add('verborgen'));
  el.numpad.classList.add('verborgen');
  el.submitBtn.classList.remove('verborgen');
  el.submitBtn.disabled = true;
}

function showZone(name) {
  const map = {
    fill:        el.fillZone,
    choice:      el.choiceZone,
    ordering:    el.orderingZone,
    clock:       el.clockZone,
    numberSnake: el.numberSnakeZone,
  };
  Object.values(map).forEach(z => z.classList.add('verborgen'));
  if (map[name]) map[name].classList.remove('verborgen');
}

function esc(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
