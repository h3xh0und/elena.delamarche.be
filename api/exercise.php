<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/flatfile.php';
require_once __DIR__ . '/../includes/exercises/arithmetic.php';
require_once __DIR__ . '/../includes/exercises/logical.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
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

global $CATEGORIES;
$arithmeticTypes = array_keys($CATEGORIES['arithmetic']['exercises']);

$user       = currentUser();
$maxNumber  = readMaxNumber($user);
$clockLevel = readClockLevel($user);
$jumpStep   = readJumpStep($user);

if ($cat === 'speedtest') {
    $exercise = rSpeedTest($maxNumber);
} elseif (in_array($cat, $arithmeticTypes)) {
    $exercise = generateArithmeticExercise($cat, $maxNumber, $clockLevel, $jumpStep);
} elseif ($cat === 'word_problems') {
    $exercise = rWordProblems($maxNumber);
} elseif ($cat === 'more_less') {
    $exercise = rMeerMinder($maxNumber);
} elseif ($cat === 'pictogram') {
    $exercise = rPictogram();
} else {
    http_response_code(400);
    echo json_encode(['fout' => 'Onbekende categorie']);
    exit;
}

if (isset($exercise['uur'])) {
    $exercise['svg'] = clockSVG((int)$exercise['uur'], (int)($exercise['minuten'] ?? 0));
}

$_SESSION['exercise']     = $exercise;
$_SESSION['exercise_cat'] = $cat;

$client = $exercise;
unset($client['antwoord']);

echo json_encode($client);
