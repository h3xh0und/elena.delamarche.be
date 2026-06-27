<?php
require_once __DIR__ . '/config.php';

function _userPath(string $name): string {
    return USERS_DIR . '/' . hash('sha256', mb_strtolower(trim($name))) . '.json';
}

function _progressPath(string $name): string {
    return PROGRESS_DIR . '/' . hash('sha256', mb_strtolower(trim($name))) . '.json';
}

function _rateLimitPath(string $name): string {
    return DATA_DIR . '/ratelimit/' . hash('sha256', mb_strtolower(trim($name))) . '.json';
}

function _writeJson(string $path, array $data): bool {
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) return false;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $fp = fopen($path, 'c');
    if (!$fp) return false;
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $json);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

function _readJson(string $path): ?array {
    if (!file_exists($path)) return null;
    $raw = file_get_contents($path);
    return $raw ? json_decode($raw, true) : null;
}

function userExists(string $name): bool {
    return file_exists(_userPath($name));
}

function register(string $name, string $pin): bool {
    if (userExists($name)) return false;
    return _writeJson(_userPath($name), [
        'name'    => $name,
        'pin_hash'  => password_hash($pin, PASSWORD_BCRYPT),
        'created' => date('Y-m-d'),
    ]);
}

function verifyPin(string $name, string $pin): bool {
    $data = _readJson(_userPath($name));
    if (!$data) return false;
    return password_verify($pin, $data['pin_hash']);
}

function changePin(string $name, string $oldPin, string $newPin): bool {
    if (!verifyPin($name, $oldPin)) return false;
    $data = _readJson(_userPath($name));
    if (!$data) return false;
    $data['pin_hash'] = password_hash($newPin, PASSWORD_BCRYPT);
    return _writeJson(_userPath($name), $data);
}

/* Maps legacy Dutch clock level values to English */
const CLOCK_LEVEL_MAP = [
    'uur'      => 'hour',
    'half_uur' => 'half_hour',
    'kwartier' => 'quarter',
    '5_min'    => '5_min',
    'minuut'   => 'minute',
];

function readMaxNumber(string $name): int {
    $data = _readJson(_userPath($name));
    return (int)($data['max_number'] ?? $data['max_getal'] ?? 20);
}

function saveMaxNumber(string $name, int $maxNumber): bool {
    $data = _readJson(_userPath($name));
    if (!$data) return false;
    $data['max_number'] = $maxNumber;
    unset($data['max_getal']);
    return _writeJson(_userPath($name), $data);
}

function readClockLevel(string $name): string {
    $data = _readJson(_userPath($name));
    $level = $data['clock_level'] ?? null;
    if ($level) return $level;
    $legacy = $data['klok_niveau'] ?? 'uur';
    return CLOCK_LEVEL_MAP[$legacy] ?? 'hour';
}

function saveClockLevel(string $name, string $level): bool {
    $valid = ['hour', 'half_hour', 'quarter', '5_min', 'minute'];
    if (!in_array($level, $valid)) return false;
    $data = _readJson(_userPath($name));
    if (!$data) return false;
    $data['clock_level'] = $level;
    unset($data['klok_niveau']);
    return _writeJson(_userPath($name), $data);
}

function readSpeedtestHighscore(string $name): int {
    $data = _readJson(_userPath($name));
    return (int)($data['speedtest_highscore'] ?? $data['sneltest_highscore'] ?? 0);
}

function saveSpeedtestHighscore(string $name, int $score): bool {
    $data = _readJson(_userPath($name));
    if (!$data) return false;
    $current = (int)($data['speedtest_highscore'] ?? $data['sneltest_highscore'] ?? 0);
    if ($score > $current) {
        $data['speedtest_highscore'] = $score;
        unset($data['sneltest_highscore']);
        return _writeJson(_userPath($name), $data);
    }
    return false;
}

function readJumpStep(string $name): int {
    $data = _readJson(_userPath($name));
    return (int)($data['jump_step'] ?? $data['sprongen_stap'] ?? 2);
}

function saveJumpStep(string $name, int $step): bool {
    $valid = [2, 3, 5, 10];
    if (!in_array($step, $valid)) return false;
    $data = _readJson(_userPath($name));
    if (!$data) return false;
    $data['jump_step'] = $step;
    unset($data['sprongen_stap']);
    return _writeJson(_userPath($name), $data);
}

/* Maps legacy Dutch exercise keys to English (for progress data migration) */
const EXERCISE_KEY_MAP = [
    'optellen'      => 'addition',
    'aftrekken'     => 'subtraction',
    'gemengd'       => 'mixed',
    'drie_optellen' => 'three_numbers',
    'splitsen'      => 'splitting',
    'ontbrekend'    => 'missing',
    'wisselsom'     => 'commutative',
    'de_helft'      => 'half',
    'vergelijken'   => 'compare',
    'ordenen'       => 'ordering',
    'tellen_buren'  => 'neighbours',
    'sprongen'      => 'jumps',
    'klok'          => 'clock',
    'geld'          => 'money',
    'rekenslang'    => 'number_snake',
    'woordsommen'   => 'word_problems',
    'meer_minder'   => 'more_less',
];

function _migrateProgressKeys(array $progress): array {
    $migrated = [];
    foreach ($progress as $key => $stats) {
        $newKey = EXERCISE_KEY_MAP[$key] ?? $key;
        $migrated[$newKey] = [
            'done'    => (int)($stats['done']    ?? $stats['gedaan']  ?? 0),
            'correct' => (int)($stats['correct'] ?? 0),
            'last'    => $stats['last'] ?? $stats['laatste'] ?? '',
        ];
    }
    return $migrated;
}

function readProgress(string $name): array {
    $raw = _readJson(_progressPath($name));
    if (!$raw) return [];
    $firstKey = array_key_first($raw);
    if ($firstKey && isset(EXERCISE_KEY_MAP[$firstKey])) {
        return _migrateProgressKeys($raw);
    }
    return $raw;
}

function saveProgress(string $name, string $exercise, bool $correct): void {
    $progress = readProgress($name);
    if (!isset($progress[$exercise])) {
        $progress[$exercise] = ['done' => 0, 'correct' => 0, 'last' => ''];
    }
    $progress[$exercise]['done']++;
    if ($correct) $progress[$exercise]['correct']++;
    $progress[$exercise]['last'] = date('Y-m-d');
    _writeJson(_progressPath($name), $progress);
}

function checkRateLimit(string $name): bool {
    $path = _rateLimitPath($name);
    $data = _readJson($path);
    if (!$data) return true;
    return empty($data['blocked_until']) || time() >= (int)$data['blocked_until'];
}

function registerFailedAttempt(string $name): void {
    $dir = DATA_DIR . '/ratelimit';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $path = _rateLimitPath($name);
    $now  = time();
    $data = _readJson($path) ?? ['attempts' => [], 'blocked_until' => 0];

    $data['attempts'] = array_values(array_filter(
        $data['attempts'] ?? [],
        fn($t) => $t > $now - 300
    ));
    $data['attempts'][] = $now;

    if (count($data['attempts']) >= 5) {
        $data['blocked_until'] = $now + 300;
    }

    _writeJson($path, $data);
}

function resetRateLimit(string $name): void {
    $path = _rateLimitPath($name);
    if (file_exists($path)) unlink($path);
}
