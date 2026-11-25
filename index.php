<?php
require_once 'includes/auth.php';
require_once 'includes/flatfile.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$step   = 1;
$name   = '';
$exists = false;
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawName = $_POST['name'] ?? '';
    $name    = mb_substr(trim(preg_replace('/[^\p{L}\s\-]/u', '', $rawName)), 0, 30);

    if (mb_strlen($name) < 2) {
        $error = 'Typ je naam (minstens 2 letters).';
        $step  = 1;
    } elseif (!empty($_POST['step2'])) {
        $exists = userExists($name);
        $pin    = $_POST['pin']  ?? '';
        $pin2   = $_POST['pin2'] ?? '';

        if ($exists) {
            if (!preg_match('/^\d{4}$/', $pin)) {
                $error = 'Typ je geheime code van 4 cijfers.';
                $step  = 2;
            } elseif (!checkRateLimit($name)) {
                $error = 'Te vaak fout geprobeerd. Wacht 5 minuutjes en probeer dan opnieuw.';
                $step  = 2;
            } elseif (!verifyPin($name, $pin)) {
                registerFailedAttempt($name);
                $error = 'Oei, dat is niet je code. Probeer het nog eens.';
                $step  = 2;
            } else {
                resetRateLimit($name);
                login($name);
                header('Location: dashboard.php');
                exit;
            }
        } else {
            if (!preg_match('/^\d{4}$/', $pin)) {
                $error = 'Kies een code van precies 4 cijfers.';
                $step  = 2;
            } elseif ($pin !== $pin2) {
                $error = 'De twee codes zijn niet hetzelfde. Probeer het nog eens.';
                $step  = 2;
            } else {
                if (register($name, $pin)) {
                    login($name);
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'De naam "' . htmlspecialchars($name) . '" is al bezet. Kies een andere naam.';
                    $step  = 1;
                    $name  = '';
                }
            }
        }
    } else {
        $exists = userExists($name);
        $step   = 2;
    }
}

$nameHtml = htmlspecialchars($name);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#EAF4FF">
<title>Rekenavontuur – 2de leerjaar</title>
<link rel="stylesheet" href="<?= asset('assets/css/fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<body class="auth-pagina">

<div class="auth-kaart">
    <div class="auth-oki" data-oki="<?= $step === 2 ? 'juich' : 'blij' ?>"></div>
    <div class="logo">
        <h1><span class="woord-1">Reken</span><span class="woord-2">avontuur</span></h1>
        <p class="logo-sub">⭐ 2de leerjaar</p>
    </div>

    <?php if ($error): ?>
    <div class="fout-bericht" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
    <form method="POST" class="auth-form" autocomplete="off">
        <label for="name">👋 Hoe heet jij?</label>
        <input type="text" id="name" name="name" value="<?= $nameHtml ?>"
               placeholder="Typ je naam" maxlength="30" required autofocus
               inputmode="text" autocapitalize="words">
        <button type="submit" class="btn btn-primair btn-groot">Verder ➜</button>
    </form>

    <?php elseif ($step === 2): ?>
    <form method="POST" class="auth-form" autocomplete="off">
        <input type="hidden" name="name" value="<?= $nameHtml ?>">
        <input type="hidden" name="step2" value="1">

        <?php if ($exists): ?>
        <p class="welkom-terug">Hoi <strong><?= $nameHtml ?></strong>! Fijn dat je er bent 🎉</p>
        <label for="pin">🔑 Jouw geheime code</label>
        <input type="password" id="pin" name="pin" maxlength="4" pattern="\d{4}"
               inputmode="numeric" placeholder="••••" required autofocus>
        <button type="submit" class="btn btn-groen btn-groot">Start! 🚀</button>

        <?php else: ?>
        <p class="welkom-nieuw">Hallo <strong><?= $nameHtml ?></strong>! Kies een geheime code van 4 cijfers 🤫</p>
        <label for="pin">🔑 Jouw geheime code</label>
        <input type="password" id="pin" name="pin" maxlength="4" pattern="\d{4}"
               inputmode="numeric" placeholder="••••" required autofocus>
        <label for="pin2">🔁 Nog een keer dezelfde code</label>
        <input type="password" id="pin2" name="pin2" maxlength="4" pattern="\d{4}"
               inputmode="numeric" placeholder="••••" required>
        <button type="submit" class="btn btn-groen btn-groot">Start! 🚀</button>
        <?php endif; ?>

        <a href="index.php" class="terug-link">← Dat ben ik niet</a>
    </form>
    <?php endif; ?>
</div>

<footer class="site-footer">
    <a href="privacy.php">Privacyverklaring</a>
    ·
    <a href="https://github.com/h3xh0und/elena.delamarche.be" target="_blank" rel="noopener noreferrer">Open source op GitHub ↗</a>
</footer>

<script src="<?= asset('assets/js/mascot.js') ?>"></script>
</body>
</html>
