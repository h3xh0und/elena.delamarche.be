<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flatfile.php';

header('Content-Type: application/json');

$name = trim($_GET['name'] ?? '');
if (mb_strlen($name) < 2) {
    echo json_encode(['exists' => false]);
    exit;
}

echo json_encode(['exists' => userExists($name)]);
