<?php
require_once 'includes/auth.php';
require_once 'includes/flatfile.php';

requireLogin();

$user       = currentUser();
$maxNumber  = readMaxNumber($user);
$clockLevel = readClockLevel($user);
$jumpStep   = readJumpStep($user);
$csrf       = csrfToken();

$clockLevels = [
    'hour'      => ['label' => 'Hele uren',   'example' => '3:00',  'hint' => 'Typ het uur'],
    'half_hour' => ['label' => 'Halve uren',  'example' => '3:30',  'hint' => '"half 4"'],
    'quarter'   => ['label' => 'Kwartier',    'example' => '3:15',  'hint' => '"kwart over 3"'],
    '5_min'     => ['label' => '5 minuten',   'example' => '3:20',  'hint' => '"3:20"'],
    'minute'    => ['label' => 'Per minuut',  'example' => '3:27',  'hint' => '"3:27"'],
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Instellingen – Oefenwebsite</title>
<link rel="stylesheet" href="assets/css/fonts.css">
<link rel="stylesheet" href="assets/css/style.css">
<meta name="csrf-token" content="<?= $csrf ?>">
</head>
<body class="site-page">

<header class="site-header">
    <div class="header-inhoud">
        <a href="dashboard.php" class="terug-link" style="font-weight:800">← Dashboard</a>
        <span style="font-weight:800">⚙️ Instellingen</span>
        <a href="logout.php" class="btn btn-klein btn-uitlog">Uitloggen</a>
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
            <button class="getal-keuze-knop <?= $opt === $maxNumber ? 'actief' : '' ?>"
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
            <?php foreach ($clockLevels as $key => $info): ?>
            <button class="klok-niveau-knop <?= $key === $clockLevel ? 'actief' : '' ?>"
                    data-niveau="<?= $key ?>">
                <span class="kn-label"><?= htmlspecialchars($info['label']) ?></span>
                <span class="kn-voorbeeld"><?= htmlspecialchars($info['example']) ?></span>
                <span class="kn-uitleg"><?= htmlspecialchars($info['hint']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-primair" id="sla-klok-op" style="margin-top:1rem">
            Opslaan
        </button>
    </div>

    <!-- Sprongen stap -->
    <div class="inst-kaart">
        <h2 class="inst-titel">🐸 Sprongen — stapgrootte</h2>
        <p class="inst-omschrijving">Kies hoe groot de stappen zijn bij de sprong-oefeningen.</p>
        <div class="getal-knoppen" id="sprongen-knoppen">
            <?php foreach ([2, 3, 5, 10] as $opt): ?>
            <button class="getal-keuze-knop <?= $opt === $jumpStep ? 'actief' : '' ?>"
                    data-stap="<?= $opt ?>">
                stap <?= $opt ?>
            </button>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-primair" id="sla-sprongen-op" style="margin-top:1rem">
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
let selectedMax = <?= $maxNumber ?>;

document.querySelectorAll('.getal-keuze-knop').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.getal-keuze-knop').forEach(b => b.classList.remove('actief'));
        btn.classList.add('actief');
        selectedMax = parseInt(btn.dataset.waarde);
    });
});

document.getElementById('sla-max-op').addEventListener('click', async () => {
    const fd = new FormData();
    fd.append('action', 'max_number');
    fd.append('max_number', selectedMax);
    const res  = await fetch('api/settings.php', { method:'POST', headers:{'X-CSRF-Token':CSRF}, body:fd });
    const data = await res.json();
    showMessage(data.ok, data.bericht || data.fout);
});

// ── Pincode wijzigen ─────────────────────────────────────
document.getElementById('wijzig-pin').addEventListener('click', async () => {
    const oldPin = document.getElementById('oude-pin').value;
    const newPin = document.getElementById('nieuwe-pin').value;
    const repeat = document.getElementById('herhaal-pin').value;

    const fd = new FormData();
    fd.append('action',   'change_pin');
    fd.append('old_pin',  oldPin);
    fd.append('new_pin',  newPin);
    fd.append('repeat',   repeat);

    const res  = await fetch('api/settings.php', { method:'POST', headers:{'X-CSRF-Token':CSRF}, body:fd });
    const data = await res.json();
    showMessage(data.ok, data.bericht || data.fout);

    if (data.ok) {
        document.getElementById('oude-pin').value   = '';
        document.getElementById('nieuwe-pin').value  = '';
        document.getElementById('herhaal-pin').value = '';
    }
});

// ── Sprongen stap ───────────────────────────────────────
let selectedStep = <?= $jumpStep ?>;

document.querySelectorAll('#sprongen-knoppen .getal-keuze-knop').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#sprongen-knoppen .getal-keuze-knop').forEach(b => b.classList.remove('actief'));
        btn.classList.add('actief');
        selectedStep = parseInt(btn.dataset.stap);
    });
});

document.getElementById('sla-sprongen-op').addEventListener('click', async () => {
    const fd = new FormData();
    fd.append('action',    'jump_step');
    fd.append('jump_step', selectedStep);
    const res  = await fetch('api/settings.php', { method:'POST', headers:{'X-CSRF-Token':CSRF}, body:fd });
    const data = await res.json();
    showMessage(data.ok, data.bericht || data.fout);
});

// ── Klok niveau ─────────────────────────────────────────
let selectedClockLevel = <?= json_encode($clockLevel) ?>;

document.querySelectorAll('.klok-niveau-knop').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.klok-niveau-knop').forEach(b => b.classList.remove('actief'));
        btn.classList.add('actief');
        selectedClockLevel = btn.dataset.niveau;
    });
});

document.getElementById('sla-klok-op').addEventListener('click', async () => {
    const fd = new FormData();
    fd.append('action',      'clock_level');
    fd.append('clock_level', selectedClockLevel);
    const res  = await fetch('api/settings.php', { method:'POST', headers:{'X-CSRF-Token':CSRF}, body:fd });
    const data = await res.json();
    showMessage(data.ok, data.bericht || data.fout);
});

// ── Melding tonen ────────────────────────────────────────
function showMessage(ok, text) {
    const el = document.getElementById('melding');
    el.textContent = text;
    el.className   = 'inst-melding ' + (ok ? 'melding-ok' : 'melding-fout');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    setTimeout(() => el.classList.add('verborgen'), 3500);
}
</script>

</body>
</html>
