<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flatfile.php';

header('Content-Type: application/json');

if (!isIngelogd()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'fout' => 'Niet ingelogd']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'fout' => 'Methode niet toegestaan']);
    exit;
}

$actie = $_POST['actie'] ?? '';
$kind  = huidigKind();

if ($actie === 'max_getal') {
    $geldigeWaarden = [10, 20, 30, 50, 100];
    $waarde = (int)($_POST['max_getal'] ?? 20);
    if (!in_array($waarde, $geldigeWaarden)) {
        echo json_encode(['ok' => false, 'fout' => 'Ongeldige waarde']);
        exit;
    }
    slaMaxGetalOp($kind, $waarde);
    echo json_encode(['ok' => true, 'bericht' => 'Instelling opgeslagen!']);

} elseif ($actie === 'pin_wijzigen') {
    $oudePin   = $_POST['oude_pin']   ?? '';
    $nieuwePin = $_POST['nieuwe_pin'] ?? '';
    $herhaal   = $_POST['herhaal']    ?? '';

    if (!preg_match('/^\d{4}$/', $oudePin)) {
        echo json_encode(['ok' => false, 'fout' => 'Vul je huidige pincode in (4 cijfers).']);
        exit;
    }
    if (!preg_match('/^\d{4}$/', $nieuwePin)) {
        echo json_encode(['ok' => false, 'fout' => 'Nieuwe pincode moet 4 cijfers zijn.']);
        exit;
    }
    if ($nieuwePin !== $herhaal) {
        echo json_encode(['ok' => false, 'fout' => 'De twee nieuwe pincodes zijn niet gelijk.']);
        exit;
    }
    if (!wijzigPin($kind, $oudePin, $nieuwePin)) {
        echo json_encode(['ok' => false, 'fout' => 'Huidige pincode is verkeerd.']);
        exit;
    }
    echo json_encode(['ok' => true, 'bericht' => 'Pincode gewijzigd!']);

} elseif ($actie === 'klok_niveau') {
    $niveau = $_POST['klok_niveau'] ?? '';
    if (!slaKlokNiveauOp($kind, $niveau)) {
        echo json_encode(['ok' => false, 'fout' => 'Ongeldige waarde']);
        exit;
    }
    echo json_encode(['ok' => true, 'bericht' => 'Instelling opgeslagen!']);

} else {
    echo json_encode(['ok' => false, 'fout' => 'Onbekende actie']);
}
