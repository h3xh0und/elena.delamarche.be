<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flatfile.php';

header('Content-Type: application/json');

$naam = trim($_GET['naam'] ?? '');
if (mb_strlen($naam) < 2) {
    echo json_encode(['bestaat' => false]);
    exit;
}

echo json_encode(['bestaat' => gebruikerBestaat($naam)]);
