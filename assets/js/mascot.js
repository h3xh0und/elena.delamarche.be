'use strict';

/* Oki the owl: the site's mascot. Drawn as inline SVG (no external files, CSP-safe).
   Moods: 'blij' (happy), 'juich' (cheering), 'denk' (thinking), 'troost' (comforting).
   Every element with a data-oki="<mood>" attribute is filled on load. */
const Oki = (() => {
  const SVG = `
<svg viewBox="0 0 120 120" class="oki" aria-hidden="true" focusable="false">
  <ellipse class="oki-schaduw" cx="60" cy="113" rx="30" ry="5"/>
  <g class="oki-lijf-groep">
    <path class="oki-oor" d="M30 34 L26 12 L46 26 Z"/>
    <path class="oki-oor" d="M90 34 L94 12 L74 26 Z"/>
    <g class="oki-vleugel oki-vleugel-l"><path d="M24 62 C10 70 12 92 26 96 C30 84 32 72 24 62 Z"/></g>
    <g class="oki-vleugel oki-vleugel-r"><path d="M96 62 C110 70 108 92 94 96 C90 84 88 72 96 62 Z"/></g>
    <ellipse class="oki-lijf" cx="60" cy="66" rx="38" ry="44"/>
    <ellipse class="oki-buik" cx="60" cy="80" rx="24" ry="25"/>
    <path class="oki-veer" d="M48 74 q4 4 8 0 M60 74 q4 4 8 0 M54 84 q4 4 8 0 M48 94 q4 4 8 0 M60 94 q4 4 8 0"/>
    <circle class="oki-wang" cx="34" cy="62" r="5"/>
    <circle class="oki-wang" cx="86" cy="62" r="5"/>
    <g class="oki-ogen-open">
      <circle class="oki-oogwit" cx="45" cy="48" r="14"/>
      <circle class="oki-oogwit" cx="75" cy="48" r="14"/>
      <g class="oki-pupillen">
        <circle class="oki-pupil" cx="47" cy="49" r="7"/>
        <circle class="oki-pupil" cx="73" cy="49" r="7"/>
        <circle class="oki-glans" cx="49.5" cy="46" r="2.4"/>
        <circle class="oki-glans" cx="75.5" cy="46" r="2.4"/>
      </g>
    </g>
    <g class="oki-ogen-dicht">
      <path d="M35 50 q10 -11 20 0"/>
      <path d="M65 50 q10 -11 20 0"/>
    </g>
    <path class="oki-snavel" d="M54 58 L66 58 L60 68 Z"/>
    <path class="oki-mond" d="M53 72 q7 7 14 0"/>
    <g class="oki-voeten">
      <ellipse cx="48" cy="108" rx="7" ry="4"/>
      <ellipse cx="72" cy="108" rx="7" ry="4"/>
    </g>
  </g>
</svg>`;

  const MOODS = ['blij', 'juich', 'denk', 'troost'];

  const PHRASES = {
    goed: ['Goed nagedacht!', 'Jij bent goed bezig!', 'Knap gedaan!', 'Dat ging vlot!', 'Je hersenen worden sterker!'],
    fout: ['Fouten maken mag, zo leer je!', 'Kijk goed naar het antwoord. Volgende keer lukt het!',
           'Niet erg! Oefenen maakt je sterker.', 'Goed geprobeerd! We doen er nog een.'],
    start: ['Klaar voor een nieuwe ronde?', 'Kom, we gaan oefenen!', 'Jij kan dit!'],
  };

  function svg(mood = 'blij') {
    return SVG.replace('class="oki"', `class="oki oki--${MOODS.includes(mood) ? mood : 'blij'}"`);
  }

  function set(target, mood) {
    const el = target?.querySelector?.('.oki');
    if (!el) return;
    MOODS.forEach(m => el.classList.remove(`oki--${m}`));
    el.classList.add(`oki--${mood}`);
    // Restart the little reaction animation
    el.classList.remove('oki--hup');
    void el.getBoundingClientRect();
    el.classList.add('oki--hup');
  }

  function phrase(kind) {
    const list = PHRASES[kind] || PHRASES.goed;
    return list[Math.floor(Math.random() * list.length)];
  }

  function mountAll(root = document) {
    root.querySelectorAll('[data-oki]').forEach(el => {
      if (!el.querySelector('.oki')) el.insertAdjacentHTML('afterbegin', svg(el.dataset.oki));
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => mountAll());
  } else {
    mountAll();
  }

  return { svg, set, phrase, mountAll };
})();
