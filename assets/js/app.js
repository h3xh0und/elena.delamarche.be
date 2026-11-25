'use strict';

const ROUND_LENGTH = 10;

/* ── State ─────────────────────────────────────────────── */
const state = {
  currentType:    null,
  currentAnswer:  null,   // selected answer (choice/ordering)
  numVal:         '',     // current numeric input (numpad)
  orderingPool:   [],     // remaining numbers for ordering
  orderingChosen: [],     // chosen numbers for ordering
  time:           null,   // digital time input: { h, m, active: 'h' | 'm' }
  round:          [],     // true/false per answered exercise in this round
  locked:         false,  // input blocked while checking / showing feedback
  waiting:        false,  // feedback shown, waiting for "Verder"
};

/* ── DOM refs ──────────────────────────────────────────── */
const el = {
  loading:        document.getElementById('laad-indicator'),
  card:           document.getElementById('oefening-kaart'),
  label:          document.getElementById('oef-label'),
  question:       document.getElementById('oef-vraag'),
  extra:          document.getElementById('oef-extra'),
  feedback:       document.getElementById('feedback'),
  oki:            document.getElementById('oef-oki'),
  fbOki:          document.getElementById('feedback-oki'),
  fbMessage:      document.getElementById('feedback-bericht'),
  fbSub:          document.getElementById('feedback-sub'),
  fbNext:         document.getElementById('feedback-verder'),
  scoreCounter:   document.getElementById('score-teller'),
  scoreCorrect:   document.getElementById('score-correct'),
  roundBar:       document.getElementById('ronde-balk'),
  roundDone:      document.getElementById('ronde-klaar'),
  roundOki:       document.getElementById('ronde-oki'),
  roundTip:       document.getElementById('ronde-tip'),
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
  // scratch pad with base-ten blocks
  scratchBtn:     document.getElementById('klad-knop'),
  scratch:        document.getElementById('klad'),
  scratchBoard:   document.getElementById('klad-bord'),
};

/* ── Init ──────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  Sound.init(el.soundBtn);
  renderRoundBar();
  loadNextExercise();

  el.submitBtn.addEventListener('click', submit);
  el.npOk.addEventListener('click', submit);
  el.orderingReset.addEventListener('click', resetOrdering);
  el.fbNext.addEventListener('click', next);
  el.roundAgain.addEventListener('click', startRound);
  Scratch.init();

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
    if (state.time && (e.key === ':' || e.key === '.')) { selectTimePart('m'); e.preventDefault(); }
    else if (e.key >= '0' && e.key <= '9') { addDigit(e.key); e.preventDefault(); }
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
  Oki.set(el.roundOki, stars >= 2 ? 'juich' : 'blij');
  el.roundTitle.textContent   = stars === 3 ? 'Fantastisch!' : stars === 2 ? 'Goed gedaan!' : 'Goed geoefend!';
  el.roundTip.textContent     = stars === 3 ? 'Je was heel geconcentreerd! Durf je iets moeilijkers? 🚀'
                              : stars === 2 ? 'Nog een ronde en misschien haal je 3 sterren!'
                              :               'Elke ronde maakt je sterker. Probeer het nog eens! 💪';
  el.roundStars.innerHTML     = '<span class="vol">★</span>'.repeat(stars) + '★'.repeat(3 - stars);
  el.roundCorrect.textContent = correct;
  el.roundTotal.textContent   = ROUND_LENGTH;
  el.roundDone.classList.remove('verborgen');
  el.roundAgain.focus({ preventScroll: true });

  Sound.play('round');
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
  state.time          = null;
  state.locked        = false;

  hideAllZones();
  Scratch.reset(!!data.blokjes);
  el.label.textContent = data.label || el.label.dataset.naam || '';
  el.extra.innerHTML   = '';
  el.question.classList.remove('lang-vraag');

  const questionText = data.vraag || '';

  if (data.type === 'invul' && BLANK.test(questionText)) {
    el.question.innerHTML = withBlank(questionText);
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
    case 'verhaal':    setupStory(data);       break;
    case 'hte':        setupPlaceValue(data);  break;
    case 'getallenas': setupNumberLine(data);  break;
    case 'rij':        setupRow(data);         break;
    default:           setupFill(data);
  }

  Oki.set(el.oki, 'denk');
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
  if (data.svg) {
    el.clockSvg.innerHTML = data.svg;
  } else if (data.digitaal) {
    const [h, m] = String(data.digitaal).split(':');
    el.clockSvg.innerHTML = `<div class="digitale-klok" aria-label="Digitale klok: ${esc(data.digitaal)}">`
      + `${esc(h)}<span class="dk-dubbelpunt">:</span>${esc(m)}</div>`;
  } else {
    el.clockSvg.innerHTML = '';
  }
  document.getElementById('tijd-invoer')?.remove();

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
    setupTimeInput(el.clockZone);
    showZone('clock');
    showNumpad(true, data.hint || '');
  }
}

/* Digital time input: an hour box and a minutes box, filled with the numpad */
function setupTimeInput(container) {
  state.time = { h: '', m: '', active: 'h' };
  container.insertAdjacentHTML('beforeend',
      `<div id="tijd-invoer" class="tijd-invoer">`
    + `<button type="button" class="tijd-deel" data-deel="h" aria-label="Uur"></button>`
    + `<span class="tijd-dubbelpunt">:</span>`
    + `<button type="button" class="tijd-deel" data-deel="m" aria-label="Minuten"></button>`
    + `</div>`);
  container.querySelectorAll('.tijd-deel').forEach(btn => {
    btn.addEventListener('click', () => selectTimePart(btn.dataset.deel));
  });
}

function selectTimePart(part) {
  if (state.locked || !state.time) return;
  state.time.active = part;
  updateNumpad();
}

function timeDigit(d) {
  const t = state.time;
  if (t.active === 'h') {
    if (t.h === '' || t.h === '0') {
      t.h = d;
      if (d >= '2') t.active = 'm';                // 2–9: hour is complete
    } else if (t.h === '1' && d <= '2') {
      t.h += d;                                    // 10, 11, 12
      t.active = 'm';
    } else {
      t.active = 'm';                              // "1" then 3–9: start of the minutes
      t.m = d;
    }
  } else if (t.m.length < 2) {
    t.m += d;
  }
}

function timeDelete() {
  const t = state.time;
  if (t.active === 'm' && t.m !== '') { t.m = t.m.slice(0, -1); return; }
  t.active = 'h';
  t.h = t.h.slice(0, -1);
}

function timeAnswer() {
  const t = state.time;
  return t.h !== '' && t.m.length === 2 ? `${parseInt(t.h, 10)}:${t.m}` : '';
}

function renderTime() {
  const box = document.getElementById('tijd-invoer');
  if (!box) return;
  const [hEl, mEl] = box.querySelectorAll('.tijd-deel');
  const t = state.time;
  hEl.textContent = t.h || '?';
  mEl.textContent = t.m.padEnd(2, '?');
  hEl.classList.toggle('leeg', t.h === '');
  mEl.classList.toggle('leeg', t.m.length < 2);
  hEl.classList.toggle('actief', !state.locked && t.active === 'h');
  mEl.classList.toggle('actief', !state.locked && t.active === 'm');
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

/* Word problem with a drawing to count on and an answer sentence ("Er zijn ? knikkers.") */
function setupStory(data) {
  const p    = data.plaatje || {};
  const item = `<span class="pl-item">${esc(p.emoji || '')}</span>`;
  let pic = '';
  if (p.groepen > 0) {
    pic = Array.from({ length: p.groepen }, () => `<span class="pl-groep">${item.repeat(p.per)}</span>`).join('');
  } else if (p.per > 0) {
    pic = `<span class="pl-los">${item.repeat(p.per)}</span>`;
  }
  const many = (p.groepen || 1) * (p.per || 0) > 20;
  el.extra.innerHTML = `<div class="plaatje${many ? ' veel' : ''}" aria-hidden="true">${pic}</div>`
                     + `<div class="antwoordzin">${withBlank(data.antwoordzin || '')}</div>`;
  state.currentType = 'invul';
  setupFill(data);
}

/* H T E table in the colours of the school book: blue hundreds, green tens, yellow units */
function setupPlaceValue(data) {
  const cell = v => v === '?' ? '<span class="antwoord-vak antwoord-live leeg">?</span>' : esc(v);
  const cols = Object.keys(data.kolommen || {});
  el.extra.innerHTML =
      `<table class="hte-tabel"><thead><tr><th class="hte-getal">Getal</th>`
    + cols.map(k => `<th class="hte-${k}">${k}</th>`).join('')
    + `</tr></thead><tbody><tr><td>${cell(data.getal)}</td>`
    + cols.map(k => `<td>${cell(data.kolommen[k])}</td>`).join('')
    + `</tr></tbody></table>`;
  state.currentType = 'invul';
  setupFill(data);
}

/* Number line with a tick per number, tens labelled, an arrow and the answer box below it */
function setupNumberLine(data) {
  const from = data.van, to = data.tot, left = 18, width = 304;
  const x = v => left + (v - from) / (to - from) * width;
  let ticks = '';
  for (let v = from; v <= to; v++) {
    const h = v % 10 === 0 ? 16 : v % 5 === 0 ? 11 : 6;
    ticks += `<line class="nl-streep" x1="${x(v)}" y1="${46 - h / 2}" x2="${x(v)}" y2="${46 + h / 2}"/>`;
    if (v % 10 === 0) ticks += `<text class="nl-tiental" x="${x(v)}" y="26">${v}</text>`;
  }
  const ax  = x(data.pijl);
  const box = Math.min(Math.max(ax, 46), 340 - 46);
  el.extra.innerHTML =
      `<svg viewBox="0 0 340 128" class="getallenas" aria-label="Getallenas van ${from} tot ${to}">`
    + `<line class="nl-as" x1="${left - 6}" y1="46" x2="${left + width + 6}" y2="46"/>`
    + ticks
    + `<path class="nl-pijl" d="M${ax} 56 l-7 11 h4.5 v14 h5 v-14 h4.5 z"/>`
    + `<g class="nl-antwoord"><rect x="${box - 30}" y="86" width="60" height="38" rx="9"/>`
    + `<text class="antwoord-live leeg" x="${box}" y="106">?</text></g>`
    + `</svg>`;
  state.currentType = 'invul';
  setupFill(data);
}

/* A row of things; the first one is on the left */
function setupRow(data) {
  const items = (data.items || []).map((it, i) =>
      `<span class="rij-plek${i === data.doel ? ' doel' : ''}"><span class="rij-item">${esc(it)}</span>`
    + (i === data.doel ? '<span class="antwoord-vak antwoord-live leeg">?</span>' : '')
    + `</span>`).join('');
  el.extra.innerHTML = `<div class="rij-wrap"><span class="rij-start" aria-label="De eerste staat hier">👉</span>`
                     + `<div class="rij" style="--n:${(data.items || []).length}">${items}</div></div>`;
  if (data.invoer === 'getal') { state.currentType = 'invul'; setupFill(data); }
  else                         { state.currentType = 'keuze'; setupChoice(data); }
}

/* ── Kladblad: tientalstaven en eenheidsblokjes ────────────
   Enkel om te tekenen zoals in de klas, het antwoord typt het kind daarna zelf.
   Slepen = verplaatsen, tikken = wegstrepen (en terug). */
const Scratch = {
  staven: 0,
  blokjes: 0,

  init() {
    el.scratchBtn.addEventListener('click', () => this.toggle());
    document.getElementById('klad-wis').addEventListener('click', () => this.clear());
    el.scratch.querySelectorAll('.klad-nieuw').forEach(btn =>
      btn.addEventListener('click', () => this.add(btn.dataset.stuk)));
  },

  reset(available) {
    this.toggle(false);
    this.clear();
    el.scratchBtn.classList.toggle('verborgen', !available);
  },

  isOpen() { return !el.scratch.classList.contains('verborgen'); },

  toggle(open = !this.isOpen()) {
    // Under the sum, next to the numpad: the child can lay blocks and type the answer at once
    el.scratch.classList.toggle('verborgen', !open);
    el.card.classList.toggle('klad-open', open);   // the board takes the free space
    el.scratchBtn.textContent = open ? '✖️' : '🧱';
    el.scratchBtn.setAttribute('aria-pressed', open);
    if (open) this.sizeUnit();
  },

  clear() {
    el.scratchBoard.innerHTML = '';
    this.staven = this.blokjes = 0;
  },

  // One cube; a ten-bar is ten of them. As big as the board allows.
  sizeUnit() {
    const w = el.scratchBoard.clientWidth, h = el.scratchBoard.clientHeight;
    this.unit = Math.max(10, Math.min(28, Math.floor((h - 16) / 10), Math.floor((w - 24) / 16)));
    el.scratchBoard.style.setProperty('--u', this.unit + 'px');
  },

  add(kind) {
    const u = this.unit, gap = 8;
    const w = el.scratchBoard.clientWidth, h = el.scratchBoard.clientHeight;
    const piece = document.createElement('div');
    piece.className = kind === 'staaf' ? 'mab-staaf' : 'mab-blokje';
    let x, y;
    if (kind === 'staaf') {
      // Bars stand next to each other from the left, like in class
      const i = this.staven++;
      x = gap + i * (u + 6);
      y = gap;
    } else {
      // Cubes in rows of five on the right
      const i = this.blokjes++;
      x = w - gap - (5 - (i % 5)) * (u + 4);
      y = h - gap - (Math.floor(i / 5) + 1) * (u + 4);
    }
    this.place(piece, x, y);
    el.scratchBoard.appendChild(piece);
    this.makeDraggable(piece);
  },

  place(piece, x, y) {
    const maxX = el.scratchBoard.clientWidth  - (piece.offsetWidth  || this.unit);
    const maxY = el.scratchBoard.clientHeight - (piece.offsetHeight || this.unit);
    piece.style.left = Math.max(0, Math.min(maxX, x)) + 'px';
    piece.style.top  = Math.max(0, Math.min(maxY, y)) + 'px';
  },

  makeDraggable(piece) {
    let start = null;
    piece.addEventListener('pointerdown', (e) => {
      e.preventDefault();
      piece.setPointerCapture(e.pointerId);
      start = { x: e.clientX, y: e.clientY, left: piece.offsetLeft, top: piece.offsetTop, moved: false };
      piece.classList.add('vast');
    });
    piece.addEventListener('pointermove', (e) => {
      if (!start) return;
      const dx = e.clientX - start.x, dy = e.clientY - start.y;
      if (!start.moved && Math.hypot(dx, dy) < 8) return;
      start.moved = true;
      this.place(piece, start.left + dx, start.top + dy);
    });
    const end = () => {
      if (start && !start.moved) piece.classList.toggle('weg');
      start = null;
      piece.classList.remove('vast');
    };
    piece.addEventListener('pointerup', end);
    piece.addEventListener('pointercancel', () => { start = null; piece.classList.remove('vast'); });
  },
};

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
  if (state.time) {
    renderTime();
    el.numpadDisplay.classList.add('verborgen');
    el.npOk.disabled = timeAnswer() === '';
    return;
  }
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
  if (state.locked) return;
  if (state.time) { timeDigit(d); updateNumpad(); return; }
  if (state.numVal.length >= 3) return;
  state.numVal += d;
  updateNumpad();
}

function deleteDigit() {
  if (state.locked) return;
  if (state.time) { timeDelete(); updateNumpad(); return; }
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
    updateNumpad();
  }
}

function getAnswer() {
  switch (state.currentType) {
    case 'invul':      return state.numVal;
    case 'keuze':      return state.currentAnswer;
    case 'klok':
      return state.time ? timeAnswer() : state.currentAnswer;
    case 'rekenslang': return state.numVal;
    case 'ordenen':
      return state.orderingChosen.map(i => state.orderingPool[i]).join(',');
    default:           return null;
  }
}

/* ── Feedback ──────────────────────────────────────────── */

// Show the result inside the exercise itself: the right answer is always visible afterwards.
function markAnswer(data) {
  const timeBox = document.getElementById('tijd-invoer');
  if (state.time && timeBox) {
    renderTime();
    if (!data.correct) {
      const [h, m] = String(data.correct_antwoord).split(':');
      const parts  = timeBox.querySelectorAll('.tijd-deel');
      parts[0].textContent = h;
      parts[1].textContent = m;
    }
    timeBox.classList.add(data.correct ? 'goed' : 'juist-getoond');
  }
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
  Oki.set(el.fbOki, correct ? 'juich' : 'troost');
  Oki.set(el.oki,   correct ? 'juich' : 'troost');
  el.fbMessage.textContent = message;
  el.fbSub.textContent     = Oki.phrase(correct ? 'goed' : 'fout');
  el.feedback.classList.remove('verborgen');

  Sound.play(correct ? 'good' : 'wrong');
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

// A stand-alone "?" is the blank to fill in: show the typed answer right there.
const BLANK = /(^|\s)\?(?=[\s.,]|$)/;

function withBlank(text) {
  return esc(text).replace(BLANK, '$1<span class="antwoord-vak antwoord-live leeg">?</span>');
}

function esc(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
