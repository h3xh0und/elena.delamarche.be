<?php
require_once __DIR__ . '/config.php';

function _gebruikerPad(string $naam): string {
    return USERS_DIR . '/' . hash('sha256', mb_strtolower(trim($naam))) . '.json';
}

function _voortgangPad(string $naam): string {
    return PROGRESS_DIR . '/' . hash('sha256', mb_strtolower(trim($naam))) . '.json';
}

function _schrijfJson(string $pad, array $data): bool {
    $dir = dirname($pad);
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) return false;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $fp = fopen($pad, 'c');
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

function _leesJson(string $pad): ?array {
    if (!file_exists($pad)) return null;
    $raw = file_get_contents($pad);
    return $raw ? json_decode($raw, true) : null;
}

function gebruikerBestaat(string $naam): bool {
    return file_exists(_gebruikerPad($naam));
}

function registreer(string $naam, string $pin): bool {
    if (gebruikerBestaat($naam)) return false;
    return _schrijfJson(_gebruikerPad($naam), [
        'naam'       => $naam,
        'pin_hash'   => password_hash($pin, PASSWORD_BCRYPT),
        'aangemaakt' => date('Y-m-d'),
    ]);
}

function controleerPin(string $naam, string $pin): bool {
    $data = _leesJson(_gebruikerPad($naam));
    if (!$data) return false;
    return password_verify($pin, $data['pin_hash']);
}

function wijzigPin(string $naam, string $oudePin, string $nieuwePin): bool {
    if (!controleerPin($naam, $oudePin)) return false;
    $data = _leesJson(_gebruikerPad($naam));
    if (!$data) return false;
    $data['pin_hash'] = password_hash($nieuwePin, PASSWORD_BCRYPT);
    return _schrijfJson(_gebruikerPad($naam), $data);
}

function leesMaxGetal(string $naam): int {
    $data = _leesJson(_gebruikerPad($naam));
    return (int)($data['max_getal'] ?? 20);
}

function slaMaxGetalOp(string $naam, int $maxGetal): bool {
    $data = _leesJson(_gebruikerPad($naam));
    if (!$data) return false;
    $data['max_getal'] = $maxGetal;
    return _schrijfJson(_gebruikerPad($naam), $data);
}

function leesKlokNiveau(string $naam): string {
    $data = _leesJson(_gebruikerPad($naam));
    return $data['klok_niveau'] ?? 'uur';
}

function slaKlokNiveauOp(string $naam, string $niveau): bool {
    $geldig = ['uur', 'half_uur', 'kwartier', '5_min', 'minuut'];
    if (!in_array($niveau, $geldig)) return false;
    $data = _leesJson(_gebruikerPad($naam));
    if (!$data) return false;
    $data['klok_niveau'] = $niveau;
    return _schrijfJson(_gebruikerPad($naam), $data);
}

function leesSneltestHighscore(string $naam): int {
    $data = _leesJson(_gebruikerPad($naam));
    return (int)($data['sneltest_highscore'] ?? 0);
}

function slaSneltestHighscoreOp(string $naam, int $score): bool {
    $data = _leesJson(_gebruikerPad($naam));
    if (!$data) return false;
    if ($score > (int)($data['sneltest_highscore'] ?? 0)) {
        $data['sneltest_highscore'] = $score;
        return _schrijfJson(_gebruikerPad($naam), $data);
    }
    return false;
}

function leesSprongenStap(string $naam): int {
    $data = _leesJson(_gebruikerPad($naam));
    return (int)($data['sprongen_stap'] ?? 2);
}

function slaSprongenStapOp(string $naam, int $stap): bool {
    $geldig = [2, 3, 5, 10];
    if (!in_array($stap, $geldig)) return false;
    $data = _leesJson(_gebruikerPad($naam));
    if (!$data) return false;
    $data['sprongen_stap'] = $stap;
    return _schrijfJson(_gebruikerPad($naam), $data);
}

function leesVoortgang(string $naam): array {
    return _leesJson(_voortgangPad($naam)) ?? [];
}

function slaVoortgangOp(string $naam, string $oefening, bool $correct): void {
    $v = leesVoortgang($naam);
    if (!isset($v[$oefening])) {
        $v[$oefening] = ['gedaan' => 0, 'correct' => 0, 'laatste' => ''];
    }
    $v[$oefening]['gedaan']++;
    if ($correct) $v[$oefening]['correct']++;
    $v[$oefening]['laatste'] = date('Y-m-d');
    _schrijfJson(_voortgangPad($naam), $v);
}
