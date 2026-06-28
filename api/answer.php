<?php
ini_set('display_errors', '0');
ob_start();

try {
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/flatfile.php';

    ob_end_clean();
    header('Content-Type: application/json');

    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['fout' => 'Niet ingelogd']);
        exit;
    }

    if (!checkCsrf()) {
        http_response_code(403);
        echo json_encode(['fout' => 'Ongeldige CSRF-token']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['fout' => 'Methode niet toegestaan']);
        exit;
    }

    $exercise = $_SESSION['exercise']     ?? null;
    $cat      = $_SESSION['exercise_cat'] ?? '';

    if (!$exercise || !$cat) {
        echo json_encode(['fout' => 'Geen actieve oefening']);
        exit;
    }

    $submitted = trim($_POST['antwoord'] ?? '');
    $type      = $exercise['type'];
    $correct   = false;

    if ($type === 'ordenen') {
        $submittedArr = array_map('intval', explode(',', $submitted));
        $correct      = ($submittedArr === $exercise['antwoord']);
    } else {
        $normalized = strtolower(preg_replace('/\s+uur$/i', '', trim($submitted)));
        $expected   = strtolower(trim((string)$exercise['antwoord']));
        $correct    = ($normalized === $expected);
    }

    saveProgress(currentUser(), $cat, $correct);

    if ($correct) {
        $messages = ['Super! 🎉', 'Geweldig! ⭐', 'Bravo! 🌟', 'Heel goed! 👏', 'Fantastisch! 🏆', 'Top! 🎯'];
        $message  = $messages[array_rand($messages)];
    } else {
        $message = 'Bijna! Het goede antwoord is: ' . (
            is_array($exercise['antwoord'])
                ? implode(' → ', $exercise['antwoord'])
                : $exercise['antwoord']
        );
    }

    echo json_encode([
        'correct'          => $correct,
        'bericht'          => $message,
        'correct_antwoord' => is_array($exercise['antwoord'])
                                ? implode(',', $exercise['antwoord'])
                                : $exercise['antwoord'],
    ]);

} catch (Throwable $e) {
    ob_end_clean();
    error_log('answer API: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['fout' => 'Kon antwoord niet verwerken. Ververs de pagina.']);
}
