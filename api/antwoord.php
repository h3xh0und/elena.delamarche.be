<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flatfile.php';

header('Content-Type: application/json');

if (!isIngelogd()) {
    http_response_code(401);
    echo json_encode(['fout' => 'Niet ingelogd']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['fout' => 'Methode niet toegestaan']);
    exit;
}

$oefening = $_SESSION['oefening'] ?? null;
$cat      = $_SESSION['oefening_cat'] ?? '';

if (!$oefening || !$cat) {
    echo json_encode(['fout' => 'Geen actieve oefening']);
    exit;
}

$ingediend = trim($_POST['antwoord'] ?? '');
$type      = $oefening['type'];
$correct   = false;

if ($type === 'ordenen') {
    // Antwoord komt als kommalijst: "11,13,15,17,19"
    $ingediendArr = array_map('intval', explode(',', $ingediend));
    $correct      = ($ingediendArr === $oefening['antwoord']);
} else {
    // Normalize: strip trailing "uur" for clock, trim spaces
    $genormaliseerd = strtolower(preg_replace('/\s+uur$/i', '', trim($ingediend)));
    $verwacht       = strtolower(trim((string)$oefening['antwoord']));
    $correct        = ($genormaliseerd === $verwacht);
}

// Save progress
slaVoortgangOp(huidigKind(), $cat, $correct);

// Build feedback messages
if ($correct) {
    $berichten = ['Super! 🎉', 'Geweldig! ⭐', 'Bravo! 🌟', 'Heel goed! 👏', 'Fantastisch! 🏆', 'Top! 🎯'];
    $bericht = $berichten[array_rand($berichten)];
} else {
    $bericht = 'Bijna! Het goede antwoord is: ' . (
        is_array($oefening['antwoord'])
            ? implode(' → ', $oefening['antwoord'])
            : $oefening['antwoord']
    );
}

echo json_encode([
    'correct'          => $correct,
    'bericht'          => $bericht,
    'correct_antwoord' => is_array($oefening['antwoord'])
                            ? implode(',', $oefening['antwoord'])
                            : $oefening['antwoord'],
]);
