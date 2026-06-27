<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flatfile.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'fout' => 'Niet ingelogd']);
    exit;
}

if (!checkCsrf()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'fout' => 'Ongeldige CSRF-token']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'fout' => 'Methode niet toegestaan']);
    exit;
}

$action = $_POST['action'] ?? '';
$user   = currentUser();

if ($action === 'max_number') {
    $validValues = [10, 20, 30, 50, 100];
    $value = (int)($_POST['max_number'] ?? 20);
    if (!in_array($value, $validValues)) {
        echo json_encode(['ok' => false, 'fout' => 'Ongeldige waarde']);
        exit;
    }
    saveMaxNumber($user, $value);
    echo json_encode(['ok' => true, 'bericht' => 'Instelling opgeslagen!']);

} elseif ($action === 'change_pin') {
    $oldPin = $_POST['old_pin'] ?? '';
    $newPin = $_POST['new_pin'] ?? '';
    $repeat = $_POST['repeat']  ?? '';

    if (!preg_match('/^\d{4}$/', $oldPin)) {
        echo json_encode(['ok' => false, 'fout' => 'Vul je huidige pincode in (4 cijfers).']);
        exit;
    }
    if (!preg_match('/^\d{4}$/', $newPin)) {
        echo json_encode(['ok' => false, 'fout' => 'Nieuwe pincode moet 4 cijfers zijn.']);
        exit;
    }
    if ($newPin !== $repeat) {
        echo json_encode(['ok' => false, 'fout' => 'De twee nieuwe pincodes zijn niet gelijk.']);
        exit;
    }
    if (!changePin($user, $oldPin, $newPin)) {
        echo json_encode(['ok' => false, 'fout' => 'Huidige pincode is verkeerd.']);
        exit;
    }
    echo json_encode(['ok' => true, 'bericht' => 'Pincode gewijzigd!']);

} elseif ($action === 'clock_level') {
    $level = $_POST['clock_level'] ?? '';
    if (!saveClockLevel($user, $level)) {
        echo json_encode(['ok' => false, 'fout' => 'Ongeldige waarde']);
        exit;
    }
    echo json_encode(['ok' => true, 'bericht' => 'Instelling opgeslagen!']);

} elseif ($action === 'speedtest_score') {
    $score = (int)($_POST['score'] ?? 0);
    if ($score < 0 || $score > 500) {
        echo json_encode(['ok' => false, 'fout' => 'Ongeldige score']);
        exit;
    }
    $newRecord = saveSpeedtestHighscore($user, $score);
    $highscore = readSpeedtestHighscore($user);
    echo json_encode(['ok' => true, 'new_record' => $newRecord, 'highscore' => $highscore]);

} elseif ($action === 'jump_step') {
    $step = (int)($_POST['jump_step'] ?? 2);
    if (!saveJumpStep($user, $step)) {
        echo json_encode(['ok' => false, 'fout' => 'Ongeldige waarde']);
        exit;
    }
    echo json_encode(['ok' => true, 'bericht' => 'Instelling opgeslagen!']);

} else {
    echo json_encode(['ok' => false, 'fout' => 'Onbekende actie']);
}
