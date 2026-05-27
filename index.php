<?php
require_once 'includes/auth.php';
require_once 'includes/flatfile.php';

if (isIngelogd()) {
    header('Location: dashboard.php');
    exit;
}

$stap   = 1;
$naam   = '';
$bestaat = false;
$fout   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize name: only letters, spaces, hyphens
    $ruwNaam = $_POST['naam'] ?? '';
    $naam    = mb_substr(trim(preg_replace('/[^\p{L}\s\-]/u', '', $ruwNaam)), 0, 30);

    if (mb_strlen($naam) < 2) {
        $fout = 'Vul je naam in (minstens 2 letters).';
        $stap = 1;
    } elseif (!empty($_POST['stap2'])) {
        // Stap 2: pincode verwerken
        $bestaat = gebruikerBestaat($naam);
        $pin  = $_POST['pin']  ?? '';
        $pin2 = $_POST['pin2'] ?? '';

        if ($bestaat) {
            // Login flow
            if (!preg_match('/^\d{4}$/', $pin)) {
                $fout = 'Vul je 4-cijferige pincode in.';
                $stap = 2;
            } elseif (!controleerPin($naam, $pin)) {
                $fout = 'Verkeerde pincode. Probeer het opnieuw.';
                $stap = 2;
            } else {
                inloggen($naam);
                header('Location: dashboard.php');
                exit;
            }
        } else {
            // Register flow
            if (!preg_match('/^\d{4}$/', $pin)) {
                $fout = 'Kies een pincode van precies 4 cijfers.';
                $stap = 2;
            } elseif ($pin !== $pin2) {
                $fout = 'De twee pincodes zijn niet gelijk. Probeer opnieuw.';
                $stap = 2;
            } else {
                if (registreer($naam, $pin)) {
                    inloggen($naam);
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $fout = 'De naam "' . htmlspecialchars($naam) . '" is al bezet. Kies een andere naam.';
                    $stap = 1;
                    $naam = '';
                }
            }
        }
    } else {
        // Stap 1 → 2
        $bestaat = gebruikerBestaat($naam);
        $stap    = 2;
    }
}

$naamHtml = htmlspecialchars($naam);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Oefenwebsite 1ste leerjaar</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-pagina">

<div class="auth-kaart">
    <div class="logo">
        <span class="logo-emoji">🌟</span>
        <h1>Oefenwebsite</h1>
        <p class="logo-sub">1ste leerjaar</p>
    </div>

    <?php if ($fout): ?>
    <div class="fout-bericht"><?= htmlspecialchars($fout) ?></div>
    <?php endif; ?>

    <?php if ($stap === 1): ?>
    <!-- Stap 1: naam ingeven -->
    <form method="POST" class="auth-form" autocomplete="off">
        <label for="naam">Wat is jouw naam?</label>
        <input type="text" id="naam" name="naam" value="<?= $naamHtml ?>"
               placeholder="Typ hier je naam" maxlength="30" required autofocus
               inputmode="text" autocapitalize="words">
        <button type="submit" class="btn btn-primair btn-groot">Verder →</button>
    </form>

    <?php elseif ($stap === 2): ?>
    <!-- Stap 2: pincode -->
    <form method="POST" class="auth-form" autocomplete="off">
        <input type="hidden" name="naam" value="<?= $naamHtml ?>">
        <input type="hidden" name="stap2" value="1">

        <?php if ($bestaat): ?>
        <p class="welkom-terug">Welkom terug, <strong><?= $naamHtml ?></strong>! 👋</p>
        <label for="pin">Jouw pincode (4 cijfers):</label>
        <input type="password" id="pin" name="pin" maxlength="4" pattern="\d{4}"
               inputmode="numeric" placeholder="••••" required autofocus>
        <button type="submit" class="btn btn-primair btn-groot">Inloggen</button>

        <?php else: ?>
        <p class="welkom-nieuw">Hallo <strong><?= $naamHtml ?></strong>! Kies een pincode 🎉</p>
        <label for="pin">Kies een pincode (4 cijfers):</label>
        <input type="password" id="pin" name="pin" maxlength="4" pattern="\d{4}"
               inputmode="numeric" placeholder="••••" required autofocus>
        <label for="pin2">Herhaal je pincode:</label>
        <input type="password" id="pin2" name="pin2" maxlength="4" pattern="\d{4}"
               inputmode="numeric" placeholder="••••" required>
        <button type="submit" class="btn btn-groen btn-groot">Account aanmaken</button>
        <?php endif; ?>

        <a href="index.php" class="terug-link">← Andere naam</a>
    </form>
    <?php endif; ?>
</div>

</body>
</html>
