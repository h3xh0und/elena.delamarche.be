'use strict';

/* ── Sound (generated, no audio files) ─────────────────── */
// Off by default (classroom); the choice is remembered in localStorage.
const Sound = (() => {
  let on    = false;
  let ctx   = null;
  let noise = null;   // white-noise buffer for applause

  function init(btn) {
    try { on = localStorage.getItem('geluid') === 'aan'; } catch (e) {}
    if (!btn) return;
    const update = () => {
      btn.textContent = on ? '🔊' : '🔇';
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    };
    update();
    btn.addEventListener('click', () => {
      on = !on;
      try { localStorage.setItem('geluid', on ? 'aan' : 'uit'); } catch (e) {}
      update();
      play('good');
    });
  }

  function tone(freq, at, type) {
    const osc  = ctx.createOscillator();
    const gain = ctx.createGain();
    const t    = ctx.currentTime + at;
    osc.type = type;
    osc.frequency.value = freq;
    gain.gain.setValueAtTime(.0001, t);
    gain.gain.exponentialRampToValueAtTime(.25, t + .02);
    gain.gain.exponentialRampToValueAtTime(.0001, t + .22);
    osc.connect(gain).connect(ctx.destination);
    osc.start(t);
    osc.stop(t + .25);
  }

  // Many short filtered noise bursts at random moments sound like a clapping crowd
  function applause(start) {
    if (!noise) {
      noise = ctx.createBuffer(1, ctx.sampleRate, ctx.sampleRate);
      const data = noise.getChannelData(0);
      for (let i = 0; i < data.length; i++) data[i] = Math.random() * 2 - 1;
    }
    const length = 2.4;
    for (let i = 0; i < 110; i++) {
      const at = Math.random() * length;
      // swell in fast, fade out slowly
      const level = Math.min(1, at / .3) * Math.min(1, (length - at) / 1);
      const t    = ctx.currentTime + start + at;
      const src  = ctx.createBufferSource();
      const bp   = ctx.createBiquadFilter();
      const gain = ctx.createGain();
      src.buffer = noise;
      bp.type = 'bandpass';
      bp.frequency.value = 900 + Math.random() * 1600;
      bp.Q.value = 1.2;
      gain.gain.setValueAtTime(.0001, t);
      gain.gain.exponentialRampToValueAtTime(.05 + .25 * level, t + .003);
      gain.gain.exponentialRampToValueAtTime(.0001, t + .07);
      src.connect(bp).connect(gain).connect(ctx.destination);
      src.start(t, Math.random() * .9, .1);
    }
  }

  function play(kind) {
    if (!on) return;
    const Ctx = window.AudioContext || window.webkitAudioContext;
    if (!Ctx) return;
    try {
      ctx = ctx || new Ctx();
      if (ctx.state === 'suspended') ctx.resume();
      if (kind === 'good')  { tone(660, 0, 'triangle'); tone(880, .1, 'triangle'); }
      if (kind === 'wrong') { tone(300, 0, 'sine');     tone(250, .14, 'sine'); }
      if (kind === 'round') {
        [523, 659, 784, 1047].forEach((f, i) => tone(f, i * .12, 'triangle'));
        applause(.45);
      }
    } catch (e) {}
  }

  return { init, play };
})();
