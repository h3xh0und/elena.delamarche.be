'use strict';

/* ── State ─────────────────────────────────────────────── */
const state = {
  currentType:    null,
  currentAnswer:  null,   // selected answer (choice/ordering)
  numVal:         '',     // current numeric input (numpad)
  orderingPool:   [],     // remaining numbers for ordering
  orderingChosen: [],     // chosen numbers for ordering
  scoreCorrect:   0,
  scoreTotal:     0,
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
  scoreCorrect:   document.getElementById('score-correct'),
  scoreTotal:     document.getElementById('score-totaal'),
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
  loadNextExercise();
  el.submitBtn.addEventListener('click', submit);
  el.npOk.addEventListener('click', submit);
  el.orderingReset.addEventListener('click', resetOrdering);

  document.getElementById('np-wis').addEventListener('click', deleteDigit);
  document.querySelectorAll('.np-btn[data-n]').forEach(btn => {
    btn.addEventListener('click', () => addDigit(btn.dataset.n));
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      if (!el.numpad.classList.contains('verborgen') && !el.npOk.disabled) { submit(); return; }
      if (!el.submitBtn.disabled) submit();
      return;
    }
    if (el.numpad.classList.contains('verborgen')) return;
    if (e.key >= '0' && e.key <= '9') { addDigit(e.key); e.preventDefault(); }
    else if (e.key === 'Backspace')    { deleteDigit();    e.preventDefault(); }
  });
});

/* ── Load exercise ─────────────────────────────────────── */
async function loadNextExercise() {
  showLoading(true);
  hideAllZones();

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

  el.label.textContent = data.label  || '';
  el.extra.innerHTML   = '';
  el.question.classList.remove('lang-vraag');

  const questionText = data.vraag || '';

  if (data.type === 'keuze' && questionText && isNaN(questionText)) {
    el.question.innerHTML = `<span class="taal-woord-display">${esc(questionText)}</span>`;
  } else {
    el.question.textContent = questionText;
  }

  if (questionText.length > 40) el.question.classList.add('lang-vraag');

  switch (data.type) {
    case 'invul':      setupFill(data);        break;
    case 'keuze':      setupChoice(data);      break;
    case 'ordenen':    setupOrdering(data);    break;
    case 'klok':       setupClock(data);       break;
    case 'rekenslang': setupNumberSnake(data); break;
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
    btn.className   = 'keuze-knop';
    btn.textContent = opt;
    btn.addEventListener('click', () => {
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
    btn.className   = 'getal-chip';
    btn.textContent = n;
    btn.dataset.idx = i;
    if (state.orderingChosen.includes(i)) btn.classList.add('gebruikt');
    btn.addEventListener('click', () => {
      if (btn.classList.contains('gebruikt')) return;
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
  const chip = document.createElement('div');
  chip.className   = 'antwoord-chip';
  chip.textContent = n;
  chip.title       = 'Tik om te verwijderen';
  chip.addEventListener('click', () => {
    const pos = state.orderingChosen.indexOf(idx);
    if (pos !== -1) state.orderingChosen.splice(pos, 1);
    renderOrdering();
    el.submitBtn.disabled = state.orderingChosen.length < state.orderingPool.length;
  });
  el.orderingAnswer.appendChild(chip);
}

function resetOrdering() {
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
      btn.className   = 'keuze-knop';
      btn.textContent = opt;
      btn.addEventListener('click', () => {
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
    toEl.className   = 'rsl-getal' + (step.naar === '?' ? ' ontbreekt' : '');
    toEl.textContent = step.naar;
    el.numberSnakeChain.appendChild(toEl);
  });

  showZone('numberSnake');
  showNumpad(true);
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
  const val = state.numVal;
  el.numpadDisplay.textContent = val || '?';
  el.numpadDisplay.classList.toggle('leeg', val === '');
  el.npOk.disabled = val === '';
}

function addDigit(d) {
  if (state.numVal.length >= 3) return;
  state.numVal += d;
  updateNumpad();
}

function deleteDigit() {
  state.numVal = state.numVal.slice(0, -1);
  updateNumpad();
}

/* ── Submit answer ─────────────────────────────────────── */
async function submit() {
  const answer = getAnswer();
  if (answer === null || answer === '') return;

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

    state.scoreTotal++;
    if (data.correct) state.scoreCorrect++;
    el.scoreCorrect.textContent = state.scoreCorrect;
    el.scoreTotal.textContent   = state.scoreTotal;

    showFeedback(data.correct, data.bericht);

  } catch (e) {
    el.submitBtn.disabled = false;
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
function showFeedback(correct, message) {
  el.feedback.className = 'feedback ' + (correct ? 'correct' : 'incorrect');
  el.fbIcon.textContent    = correct ? '🎉' : '😬';
  el.fbMessage.textContent = message;

  el.feedback.classList.remove('verborgen');

  const delay = correct ? 1400 : 2400;
  setTimeout(() => {
    el.feedback.classList.add('verborgen');
    loadNextExercise();
  }, delay);
}

/* ── Helpers ───────────────────────────────────────────── */
function showLoading(visible, text) {
  el.loading.textContent  = text || 'Even laden...';
  el.loading.style.display = visible ? 'block' : 'none';
  el.card.classList.toggle('verborgen', visible);
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
