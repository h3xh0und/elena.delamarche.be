<?php
// Temporary diagnostic — delete this file after use
require_once 'includes/auth.php';
$token = $_GET['t'] ?? '';
if (!isLoggedIn() || !hash_equals('elena2025', $token)) { http_response_code(404); exit; }

echo '<pre>';

echo "PHP: " . PHP_VERSION . "\n\n";

echo "=== Includes ===\n";
try {
    require_once 'includes/auth.php';
    echo "auth.php: OK\n";
} catch (Throwable $e) { echo "auth.php: FAIL — " . $e->getMessage() . "\n"; }

try {
    require_once 'includes/config.php';
    echo "config.php: OK\n";
    echo "  \$CATEGORIES defined: " . (isset($CATEGORIES) ? 'yes' : 'NO') . "\n";
    if (isset($CATEGORIES)) {
        echo "  keys: " . implode(', ', array_keys($CATEGORIES)) . "\n";
        echo "  arithmetic exercises: " . implode(', ', array_keys($CATEGORIES['arithmetic']['exercises'] ?? [])) . "\n";
    }
} catch (Throwable $e) { echo "config.php: FAIL — " . $e->getMessage() . "\n"; }

try {
    require_once 'includes/flatfile.php';
    echo "flatfile.php: OK\n";
    echo "  readMaxNumber exists: " . (function_exists('readMaxNumber') ? 'yes' : 'NO') . "\n";
    echo "  readClockLevel exists: " . (function_exists('readClockLevel') ? 'yes' : 'NO') . "\n";
    echo "  EXERCISE_KEY_MAP defined: " . (defined('EXERCISE_KEY_MAP') ? 'yes' : 'NO') . "\n";
} catch (Throwable $e) { echo "flatfile.php: FAIL — " . $e->getMessage() . "\n"; }

try {
    require_once 'includes/exercises/arithmetic.php';
    echo "arithmetic.php: OK\n";
    echo "  generateArithmeticExercise exists: " . (function_exists('generateArithmeticExercise') ? 'yes' : 'NO') . "\n";
    echo "  clockSVG exists: " . (function_exists('clockSVG') ? 'yes' : 'NO') . "\n";
} catch (Throwable $e) { echo "arithmetic.php: FAIL — " . $e->getMessage() . "\n"; }

echo "\n=== Test clock exercise ===\n";
try {
    $ex = generateArithmeticExercise('clock', 20, 'hour', 2);
    echo "generateArithmeticExercise('clock'): OK\n";
    echo "  type: " . ($ex['type'] ?? '?') . "\n";
    echo "  has uur: " . (isset($ex['uur']) ? 'yes' : 'no') . "\n";
    if (isset($ex['uur'])) {
        $svg = clockSVG((int)$ex['uur'], 0);
        echo "  clockSVG: " . (strlen($svg) > 10 ? 'OK (' . strlen($svg) . ' chars)' : 'FAIL') . "\n";
    }
    $json = json_encode($ex);
    echo "  json_encode: " . ($json !== false ? 'OK' : 'FAIL — ' . json_last_error_msg()) . "\n";
} catch (Throwable $e) { echo "clock test: FAIL — " . $e->getMessage() . "\n"; }

echo "\n=== Session ===\n";
echo "isLoggedIn: " . (function_exists('isLoggedIn') && isLoggedIn() ? 'yes' : 'no') . "\n";

echo '</pre>';
