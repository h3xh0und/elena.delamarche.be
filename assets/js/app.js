'use strict';

/* ── State ─────────────────────────────────────────────── */
const staat = {
  huidigType:     null,
  huidigAntwoord: null,   // selected answer (keuze/ordenen)
  ordenenPool:    [],     // remaining numbers for ordenen
  ordenenGekozen: [],     // chosen numbers for ordenen
  scoreCorrect:   0,
  scoreTotaal:    0,
};

/* ── DOM refs ──────────────────────────────────────────── */
const el = {
  laad:          document.getElementById('laad-indicator'),
  kaart:         document.getElementById('oefening-kaart'),
  label:         document.getElementById('oef-label'),
  vraag:         document.getElementById('oef-vraag'),
  extra:         document.getElementById('oef-extra'),
  feedback:      document.getElementById('feedback'),
  fbIcoon:       document.getElementById('feedback-icoon'),
  fbBericht:     document.getElementById('feedback-bericht'),
  scoreCorrect:  document.getElementById('score-correct'),
  scoreTotaal:   document.getElementById('score-totaal'),
  indienenKnop:  document.getElementById('indienen-knop'),
  // zones
  invulZone:      document.getElementById('invul-zone'),
  invulInput:     document.getElementById('invul-input'),
  invulHint:      document.getElementById('invul-hint'),
  keuzeZone:      document.getElementById('keuze-zone'),
  keuzeKnoppen:   document.getElementById('keuze-knoppen'),
  ordenenZone:    document.getElementById('ordenen-zone'),
  ordenenRij:     document.getElementById('ordenen-rij'),
  ordenenAntw:    document.getElementById('ordenen-antwoord'),
  ordenenReset:   document.getElementById('ordenen-reset'),
  klokZone:       document.getElementById('klok-zone'),
  klokSvg:        document.getElementById('klok-svg-container'),
  klokInput:      document.getElementById('klok-input'),
  rekenslangZone: document.getElementById('rekenslang-zone'),
  rekenslangKeten:document.getElementById('rekenslang-keten'),
  rekenslangInput:document.getElementById('rekenslang-input'),
};

/* ── Init ──────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  laadVolgendeOefening();
  el.indienenKnop.addEventListener('click', dienIn);
  el.ordenenReset.addEventListener('click', resetOrdenen);

  // Enter key submits
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !el.indienenKnop.disabled) dienIn();
  });
});

/* ── Oefening laden ────────────────────────────────────── */
async function laadVolgendeOefening() {
  toonLaad(true);
  verbergAlleZones();

  try {
    const res  = await fetch(`api/oefening.php?cat=${encodeURIComponent(CATEGORIE)}`);
    const data = await res.json();
    if (data.fout) { toonLaad(true, data.fout); return; }
    toonOefening(data);
  } catch (e) {
    toonLaad(true, 'Kon oefening niet laden. Ververs de pagina.');
  }
}

function toonOefening(data) {
  staat.huidigType    = data.type;
  staat.huidigAntwoord = null;

  el.label.textContent = data.label  || '';
  el.extra.innerHTML   = '';

  // Taal woord: grote weergave
  if (data.type === 'keuze' && data.vraag && isNaN(data.vraag)) {
    el.vraag.innerHTML = `<span class="taal-woord-display">${esc(data.vraag)}</span>`;
  } else {
    el.vraag.textContent = data.vraag || '';
  }

  switch (data.type) {
    case 'invul':      setupInvul(data);      break;
    case 'keuze':      setupKeuze(data);      break;
    case 'ordenen':    setupOrdenen(data);    break;
    case 'klok':       setupKlok(data);       break;
    case 'rekenslang': setupRekenslang(data); break;
    default:           setupInvul(data);
  }

  toonLaad(false);
  el.kaart.classList.add('pop-in');
  setTimeout(() => el.kaart.classList.remove('pop-in'), 350);
}

/* ── Setup per type ────────────────────────────────────── */
function setupInvul(data) {
  el.invulHint.textContent = data.hint || '';
  el.invulInput.value      = '';
  el.invulInput.placeholder = '?';
  toonZone('invul');
  el.invulInput.focus();
  el.invulInput.addEventListener('input', () => {
    el.indienenKnop.disabled = el.invulInput.value.trim() === '';
  }, { once: false });
  el.indienenKnop.disabled = true;
}

function setupKeuze(data) {
  el.keuzeKnoppen.innerHTML = '';
  staat.huidigAntwoord = null;
  (data.opties || []).forEach(opt => {
    const btn = document.createElement('button');
    btn.className   = 'keuze-knop';
    btn.textContent = opt;
    btn.addEventListener('click', () => {
      document.querySelectorAll('.keuze-knop').forEach(b => b.classList.remove('geselecteerd'));
      btn.classList.add('geselecteerd');
      staat.huidigAntwoord = opt;
      el.indienenKnop.disabled = false;
    });
    el.keuzeKnoppen.appendChild(btn);
  });
  toonZone('keuze');
  el.indienenKnop.disabled = true;
}

function setupOrdenen(data) {
  staat.ordenenPool    = [...data.getallen];
  staat.ordenenGekozen = [];
  renderOrdenen();
  toonZone('ordenen');
  el.indienenKnop.disabled = true;
}

function renderOrdenen() {
  el.ordenenRij.innerHTML  = '';
  el.ordenenAntw.innerHTML = '';

  staat.ordenenPool.forEach((n, i) => {
    const btn = document.createElement('button');
    btn.className   = 'getal-chip';
    btn.textContent = n;
    btn.dataset.idx = i;
    if (staat.ordenenGekozen.includes(i)) btn.classList.add('gebruikt');
    btn.addEventListener('click', () => {
      if (btn.classList.contains('gebruikt')) return;
      btn.classList.add('gebruikt');
      staat.ordenenGekozen.push(i);
      voegAntwoordChipToe(n, i);
      if (staat.ordenenGekozen.length === staat.ordenenPool.length) {
        el.indienenKnop.disabled = false;
      }
    });
    el.ordenenRij.appendChild(btn);
  });

  staat.ordenenGekozen.forEach(idx => voegAntwoordChipToe(staat.ordenenPool[idx], idx));
}

function voegAntwoordChipToe(n, idx) {
  const chip = document.createElement('div');
  chip.className   = 'antwoord-chip';
  chip.textContent = n;
  chip.title       = 'Tik om te verwijderen';
  chip.addEventListener('click', () => {
    const pos = staat.ordenenGekozen.indexOf(idx);
    if (pos !== -1) staat.ordenenGekozen.splice(pos, 1);
    renderOrdenen();
    el.indienenKnop.disabled = staat.ordenenGekozen.length < staat.ordenenPool.length;
  });
  el.ordenenAntw.appendChild(chip);
}

function resetOrdenen() {
  staat.ordenenGekozen = [];
  renderOrdenen();
  el.indienenKnop.disabled = true;
}

function setupKlok(data) {
  el.klokSvg.innerHTML = data.svg || '';

  if (data.klok_invoer === 'keuze') {
    // Toon meerkeuze knoppen onder de klok
    el.klokInput.classList.add('verborgen');
    document.querySelector('.klok-zone-hint')?.remove();

    // Hergebruik keuze-knoppen maar in de klok-zone
    let klokKeuze = document.getElementById('klok-keuze');
    if (!klokKeuze) {
      klokKeuze = document.createElement('div');
      klokKeuze.id = 'klok-keuze';
      klokKeuze.className = 'keuze-knoppen';
      el.klokZone.appendChild(klokKeuze);
    }
    klokKeuze.innerHTML = '';
    staat.huidigAntwoord = null;

    (data.opties || []).forEach(opt => {
      const btn = document.createElement('button');
      btn.className   = 'keuze-knop';
      btn.textContent = opt;
      btn.addEventListener('click', () => {
        klokKeuze.querySelectorAll('.keuze-knop').forEach(b => b.classList.remove('geselecteerd'));
        btn.classList.add('geselecteerd');
        staat.huidigAntwoord = opt;
        el.indienenKnop.disabled = false;
      });
      klokKeuze.appendChild(btn);
    });

    toonZone('klok');
    el.indienenKnop.disabled = true;
  } else {
    // Hele uren: gewoon getal invoeren
    const bestaandKeuze = document.getElementById('klok-keuze');
    if (bestaandKeuze) bestaandKeuze.innerHTML = '';
    el.klokInput.classList.remove('verborgen');
    el.klokInput.value = '';
    toonZone('klok');
    el.klokInput.focus();
    el.klokInput.addEventListener('input', () => {
      el.indienenKnop.disabled = el.klokInput.value.trim() === '';
    });
    el.indienenKnop.disabled = true;
  }
}

function setupRekenslang(data) {
  // Render chain
  el.rekenslangKeten.innerHTML = '';

  // Start getal
  const startEl = document.createElement('div');
  startEl.className   = 'rsl-getal start';
  startEl.textContent = data.start;
  el.rekenslangKeten.appendChild(startEl);

  (data.keten || []).forEach(stap => {
    const stapEl = document.createElement('div');
    stapEl.className = 'rsl-stap';
    stapEl.innerHTML = `<span class="rsl-op">${esc(stap.op)}</span><span class="rsl-pijl">→</span>`;
    el.rekenslangKeten.appendChild(stapEl);

    const naarEl = document.createElement('div');
    naarEl.className   = 'rsl-getal' + (stap.naar === '?' ? ' ontbreekt' : '');
    naarEl.textContent = stap.naar;
    el.rekenslangKeten.appendChild(naarEl);
  });

  el.rekenslangInput.value = '';
  toonZone('rekenslang');
  el.rekenslangInput.focus();
  el.rekenslangInput.addEventListener('input', () => {
    el.indienenKnop.disabled = el.rekenslangInput.value.trim() === '';
  });
  el.indienenKnop.disabled = true;
}

/* ── Antwoord indienen ─────────────────────────────────── */
async function dienIn() {
  const antwoord = haalAntwoord();
  if (antwoord === null || antwoord === '') return;

  el.indienenKnop.disabled = true;

  try {
    const fd = new FormData();
    fd.append('antwoord', antwoord);

    const res  = await fetch('api/antwoord.php', {
      method: 'POST',
      headers: { 'X-CSRF-Token': CSRF },
      body: fd,
    });
    const data = await res.json();

    staat.scoreTotaal++;
    if (data.correct) staat.scoreCorrect++;
    el.scoreCorrect.textContent = staat.scoreCorrect;
    el.scoreTotaal.textContent  = staat.scoreTotaal;

    toonFeedback(data.correct, data.bericht);

  } catch (e) {
    el.indienenKnop.disabled = false;
  }
}

function haalAntwoord() {
  switch (staat.huidigType) {
    case 'invul':      return el.invulInput.value.trim();
    case 'keuze':      return staat.huidigAntwoord;
    case 'klok':
      // keuze of getal invoer
      return staat.huidigAntwoord !== null
        ? staat.huidigAntwoord
        : el.klokInput.value.trim();
    case 'rekenslang': return el.rekenslangInput.value.trim();
    case 'ordenen':
      return staat.ordenenGekozen.map(i => staat.ordenenPool[i]).join(',');
    default:           return null;
  }
}

/* ── Feedback ──────────────────────────────────────────── */
function toonFeedback(correct, bericht) {
  el.feedback.className = 'feedback ' + (correct ? 'correct' : 'incorrect');
  el.fbIcoon.textContent  = correct ? '🎉' : '😬';
  el.fbBericht.textContent = bericht;

  el.feedback.classList.remove('verborgen');

  const wacht = correct ? 1400 : 2400;
  setTimeout(() => {
    el.feedback.classList.add('verborgen');
    laadVolgendeOefening();
  }, wacht);
}

/* ── Helpers ───────────────────────────────────────────── */
function toonLaad(zichtbaar, tekst) {
  el.laad.textContent  = tekst || 'Even laden...';
  el.laad.style.display = zichtbaar ? 'block' : 'none';
  el.kaart.classList.toggle('verborgen', zichtbaar);
}

function verbergAlleZones() {
  [el.invulZone, el.keuzeZone, el.ordenenZone,
   el.klokZone, el.rekenslangZone].forEach(z => z.classList.add('verborgen'));
  el.indienenKnop.disabled = true;
}

function toonZone(naam) {
  const map = {
    invul:      el.invulZone,
    keuze:      el.keuzeZone,
    ordenen:    el.ordenenZone,
    klok:       el.klokZone,
    rekenslang: el.rekenslangZone,
  };
  Object.values(map).forEach(z => z.classList.add('verborgen'));
  if (map[naam]) map[naam].classList.remove('verborgen');
}

function esc(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
