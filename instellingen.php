<?php
require_once 'includes/auth.php';
require_once 'includes/flatfile.php';

vereisInlog();

$kind       = huidigKind();
$maxGetal   = leesMaxGetal($kind);
$klokNiveau = leesKlokNiveau($kind);
$csrf       = csrfToken();

$klokNiveaus = [
    'uur'      => ['label' => 'Hele uren',   'voorbeeld' => '3:00',  'uitleg' => 'Typ het uur'],
    'half_uur' => ['label' => 'Halve uren',  'voorbeeld' => '3:30',  'uitleg' => '"half 4"'],
    'kwartier' => ['label' => 'Kwartier',    'voorbeeld' => '3:15',  'uitleg' => '"kwart over 3"'],
    '5_min'    => ['label' => '5 minuten',   'voorbeeld' => '3:20',  'uitleg' => '"3:20"'],
    'minuut'   => ['label' => 'Per minuut',  'voorbeeld' => '3:27',  'uitleg' => '"3:27"'],
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Instellingen – Oefenwebsite</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<meta name="csrf-token" content="<?= $csrf ?>">
</head>
<body>

<header class="site-header">
    <div class="header-inhoud">
        <a href="dashboard.php" class="terug-link" style="font-weight:800">← Dashboard</a>
        <span style="font-weight:800">⚙️ Instellingen</span>
        <a href="uitloggen.php" class="btn btn-klein btn-uitlog">Uitloggen</a>
    </div>
</header>

<main class="inst-main">

    <div id="melding" class="inst-melding verborgen"></div>

    <!-- Max getal -->
    <div class="inst-kaart">
        <h2 class="inst-titel">🔢 Tot welk getal oefenen?</h2>
        <p class="inst-omschrijving">
            Kies hoe groot de getallen mogen zijn in de rekenoefeningen.
        </p>
        <div class="getal-knoppen" id="getal-knoppen">
            <?php foreach ([10, 20, 30, 50, 100] as $opt): ?>
            <button class="getal-keuze-knop <?= $opt === $maxGetal ? 'actief' : '' ?>"
                    data-waarde="<?= $opt ?>">
                tot <?= $opt ?>
            </button>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-primair" id="sla-max-op" style="margin-top:1rem">
            Opslaan
        </button>
    </div>

    <!-- Kloklezen niveau -->
    <div class="inst-kaart">
        <h2 class="inst-titel">🕐 Kloklezen — moeilijkheidsgraad</h2>
        <p class="inst-omschrijving">Kies hoe nauwkeurig de klok afgelezen moet worden.</p>
        <div class="klok-niveaus" id="klok-niveaus">
            <?php foreach ($klokNiveaus as $sleutel => $info): ?>
            <button class="klok-niveau-knop <?= $sleutel === $klokNiveau ? 'actief' : '' ?>"
                    data-niveau="<?= $sleutel ?>">
                <span class="kn-label"><?= htmlspecialchars($info['label']) ?></span>
                <span class="kn-voorbeeld"><?= htmlspecialchars($info['voorbeeld']) ?></span>
                <span class="kn-uitleg"><?= htmlspecialchars($info['uitleg']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-primair" id="sla-klok-op" style="margin-top:1rem">
            Opslaan
        </button>
    </div>

    <!-- Pincode wijzigen -->
    <div class="inst-kaart">
        <h2 class="inst-titel">🔑 Pincode wijzigen</h2>
        <p class="inst-omschrijving">Vul je huidige pincode in en kies een nieuwe.</p>

        <div class="inst-form">
            <label for="oude-pin">Huidige pincode:</label>
            <input type="password" id="oude-pin" maxlength="4" inputmode="numeric"
                   pattern="\d{4}" placeholder="••••" autocomplete="current-password">

            <label for="nieuwe-pin">Nieuwe pincode:</label>
            <input type="password" id="nieuwe-pin" maxlength="4" inputmode="numeric"
                   pattern="\d{4}" placeholder="••••" autocomplete="new-password">

            <label for="herhaal-pin">Nieuwe pincode herhalen:</label>
            <input type="password" id="herhaal-pin" maxlength="4" inputmode="numeric"
                   pattern="\d{4}" placeholder="••••" autocomplete="new-password">

            <button class="btn btn-groen" id="wijzig-pin" style="margin-top:.5rem">
                Pincode wijzigen
            </button>
        </div>
    </div>

</main>

<script>
const CSRF = <?= json_encode($csrf) ?>;

// ── Max getal ────────────────────────────────────────────
let gekozenMax = <?= $maxGetal ?>;

document.querySelectorAll('.getal-keuze-knop').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.getal-keuze-knop').forEach(b => b.classList.remove('actief'));
        btn.classList.add('actief');
        gekozenMax = parseInt(btn.dataset.waarde);
    });
});

document.getElementById('sla-max-op').addEventListener('click', async () => {
    const fd = new FormData();
    fd.append('actie', 'max_getal');
    fd.append('max_getal', gekozenMax);
    const res  = await fetch('api/instellingen.php', { method:'POST', headers:{'X-CSRF-Token':CSRF}, body:fd });
    const data = await res.json();
    toonMelding(data.ok, data.bericht || data.fout);
});

// ── Pincode wijzigen ─────────────────────────────────────
document.getElementById('wijzig-pin').addEventListener('click', async () => {
    const oudePin   = document.getElementById('oude-pin').value;
    const nieuwePin = document.getElementById('nieuwe-pin').value;
    const herhaal   = document.getElementById('herhaal-pin').value;

    const fd = new FormData();
    fd.append('actie',      'pin_wijzigen');
    fd.append('oude_pin',   oudePin);
    fd.append('nieuwe_pin', nieuwePin);
    fd.append('herhaal',    herhaal);

    const res  = await fetch('api/instellingen.php', { method:'POST', headers:{'X-CSRF-Token':CSRF}, body:fd });
    const data = await res.json();
    toonMelding(data.ok, data.bericht || data.fout);

    if (data.ok) {
        document.getElementById('oude-pin').value   = '';
        document.getElementById('nieuwe-pin').value  = '';
        document.getElementById('herhaal-pin').value = '';
    }
});

// ── Klok niveau ─────────────────────────────────────────
let gekozenNiveau = <?= json_encode($klokNiveau) ?>;

document.querySelectorAll('.klok-niveau-knop').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.klok-niveau-knop').forEach(b => b.classList.remove('actief'));
        btn.classList.add('actief');
        gekozenNiveau = btn.dataset.niveau;
    });
});

document.getElementById('sla-klok-op').addEventListener('click', async () => {
    const fd = new FormData();
    fd.append('actie', 'klok_niveau');
    fd.append('klok_niveau', gekozenNiveau);
    const res  = await fetch('api/instellingen.php', { method:'POST', headers:{'X-CSRF-Token':CSRF}, body:fd });
    const data = await res.json();
    toonMelding(data.ok, data.bericht || data.fout);
});

// ── Melding tonen ────────────────────────────────────────
function toonMelding(ok, tekst) {
    const el = document.getElementById('melding');
    el.textContent = tekst;
    el.className   = 'inst-melding ' + (ok ? 'melding-ok' : 'melding-fout');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    setTimeout(() => el.classList.add('verborgen'), 3500);
}
</script>

</body>
</html>
