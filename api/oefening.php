<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/flatfile.php';
require_once __DIR__ . '/../includes/exercises/rekenen.php';

header('Content-Type: application/json');

if (!isIngelogd()) {
    http_response_code(401);
    echo json_encode(['fout' => 'Niet ingelogd']);
    exit;
}

$cat = preg_replace('/[^a-z0-9_]/', '', $_GET['cat'] ?? '');
if (!$cat) {
    http_response_code(400);
    echo json_encode(['fout' => 'Geen categorie']);
    exit;
}

global $CATEGORIEEN;
$rekenTypes = array_keys($CATEGORIEEN['rekenen']['oefeningen']);

$kind         = huidigKind();
$maxGetal     = leesMaxGetal($kind);
$klokNiveau   = leesKlokNiveau($kind);
$sprongenStap = leesSprongenStap($kind);

if ($cat === 'sneltest') {
    $oefening = rSneltest($maxGetal);
} elseif (in_array($cat, $rekenTypes)) {
    $oefening = genereerRekenOefening($cat, $maxGetal, $klokNiveau, $sprongenStap);
} else {
    http_response_code(400);
    echo json_encode(['fout' => 'Onbekende categorie']);
    exit;
}

// Voor klok: SVG met correcte minuten genereren
if (isset($oefening['uur'])) {
    $oefening['svg'] = klokSVG((int)$oefening['uur'], (int)($oefening['minuten'] ?? 0));
}

// Store full exercise (incl. answer) in session for server-side checking
$_SESSION['oefening'] = $oefening;
$_SESSION['oefening_cat'] = $cat;

// Send to client WITHOUT the answer
$client = $oefening;
unset($client['antwoord']);

echo json_encode($client);
