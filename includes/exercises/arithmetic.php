<?php

function generateArithmeticExercise(string $type, int $maxNumber = 20, string $clockLevel = 'hour', int $jumpStep = 2): array {
    $mx = max(10, $maxNumber);
    return match ($type) {
        'addition'      => rAddition($mx),
        'subtraction'   => rSubtraction($mx),
        'mixed'         => rMixed($mx),
        'three_numbers' => rThreeNumbers($mx),
        'neighbours'    => rCountingNeighbours($mx),
        'splitting'     => rSplitting($mx),
        'half'          => rHalf($mx),
        'compare'       => rCompare($mx),
        'ordering'      => rOrdering($mx),
        'clock'         => rClock($clockLevel),
        'jumps'         => rJumps($mx, $jumpStep),
        'number_snake'  => rNumberSnake($mx),
        'missing'       => rMissing($mx),
        'commutative'   => rCommutative($mx),
        'money'         => rMoney(),
        default         => rAddition($mx),
    };
}

/* ── Helpers ──────────────────────────────────────────── */

function rAddition(int $max = 20): array {
    $a = rand(1, $max - 1);
    $b = rand(1, $max - $a);
    return [
        'type'     => 'invul',
        'vraag'    => "$a + $b = ?",
        'antwoord' => (string)($a + $b),
        'invoer'   => 'getal',
    ];
}

function rSubtraction(int $max): array {
    if ($max >= 11 && rand(0, 2) === 0) {
        return rSubtractionBorrow($max);
    }
    $a = rand((int)($max / 2), $max);
    $b = rand(1, $a - 1);
    return [
        'type'     => 'invul',
        'vraag'    => "$a − $b = ?",
        'antwoord' => (string)($a - $b),
        'invoer'   => 'getal',
    ];
}

function rMixed(int $max = 20): array {
    return rand(0, 1) ? rAddition($max) : rSubtraction($max);
}

function rThreeNumbers(int $max = 20): array {
    $cap = max(10, $max);
    do {
        $a = rand(1, (int)($cap / 3) + 1);
        $b = rand(1, (int)($cap / 3) + 1);
        $c = rand(1, (int)($cap / 3) + 1);
    } while ($a + $b + $c > $cap);
    return [
        'type'     => 'invul',
        'vraag'    => "$a + $b + $c = ?",
        'antwoord' => (string)($a + $b + $c),
        'invoer'   => 'getal',
    ];
}

function rCountingNeighbours(int $max = 20): array {
    $kind = rand(0, 3);
    $hi = max(11, $max);

    if ($kind === 0) {
        $n = rand(2, $hi);
        return ['type' => 'invul', 'vraag' => "Wat komt vóór $n?",
                'antwoord' => (string)($n - 1), 'invoer' => 'getal'];
    }
    if ($kind === 1) {
        $n = rand(1, $hi - 1);
        return ['type' => 'invul', 'vraag' => "Wat komt ná $n?",
                'antwoord' => (string)($n + 1), 'invoer' => 'getal'];
    }
    if ($kind === 2) {
        $n = rand(1, $hi - 2);
        return ['type' => 'invul',
                'vraag'    => "$n , __ , " . ($n + 2) . "  — welk getal hoort ertussen?",
                'antwoord' => (string)($n + 1), 'invoer' => 'getal'];
    }
    $ascending = rand(0, 1);
    $s = rand(1, max(1, $hi - 4));
    $row = $ascending ? [$s, $s+1, $s+2, $s+3, $s+4] : [$s+4, $s+3, $s+2, $s+1, $s];
    $pos = rand(1, 3);
    $answer = $row[$pos];
    $row[$pos] = '?';
    return ['type' => 'invul', 'vraag' => implode(' → ', $row),
            'antwoord' => (string)$answer, 'invoer' => 'getal'];
}

function rSplitting(int $max = 20): array {
    $n = rand(3, $max);
    $a = rand(1, $n - 1);
    $b = $n - $a;
    if (rand(0, 1)) {
        return ['type' => 'invul', 'vraag' => "$n = $a + ?",
                'antwoord' => (string)$b, 'invoer' => 'getal'];
    }
    return ['type' => 'invul', 'vraag' => "$n = ? + $b",
            'antwoord' => (string)$a, 'invoer' => 'getal'];
}

function rHalf(int $max = 20): array {
    $maxEven = (int)(min($max, 100) / 2);
    $n = rand(1, $maxEven) * 2;
    return [
        'type'     => 'invul',
        'vraag'    => "De helft van $n is ?",
        'antwoord' => (string)($n / 2),
        'invoer'   => 'getal',
    ];
}

function rCompare(int $max = 20): array {
    $a = rand(1, $max);
    $b = rand(1, $max);
    if (rand(0, 5) === 0) $b = $a;
    $answer = $a < $b ? '<' : ($a > $b ? '>' : '=');
    return [
        'type'     => 'keuze',
        'vraag'    => "$a  __  $b",
        'label'    => 'Vul het goede teken in:',
        'opties'   => ['<', '>', '='],
        'antwoord' => $answer,
    ];
}

function rOrdering(int $max = 20): array {
    $descending = rand(0, 1);
    $set = [];
    $lo  = max(1, $max - 15);
    while (count($set) < 5) {
        $n = rand($lo, $max);
        if (!in_array($n, $set)) $set[] = $n;
    }
    $sorted = $set;
    $descending ? rsort($sorted) : sort($sorted);
    return [
        'type'     => 'ordenen',
        'vraag'    => 'Orden ' . ($descending ? 'van groot naar klein' : 'van klein naar groot') . ':',
        'getallen' => $set,
        'antwoord' => $sorted,
    ];
}

function rClock(string $level = 'hour'): array {
    $hour = rand(1, 12);

    if ($level === 'hour') {
        return [
            'type'        => 'klok',
            'vraag'       => 'Hoe laat is het?',
            'uur'         => $hour,
            'minuten'     => 0,
            'klok_invoer' => 'getal',
            'antwoord'    => (string)$hour,
            'hint'        => 'Typ alleen het uur (bijv. 3)',
        ];
    }

    $minutePool = match ($level) {
        'half_hour' => [0, 30],
        'quarter'   => [0, 15, 30, 45],
        '5_min'     => [0,5,10,15,20,25,30,35,40,45,50,55],
        'minute'    => range(0, 59),
        default     => [0],
    };
    $minutes = $minutePool[array_rand($minutePool)];
    $digital = in_array($level, ['5_min', 'minute']);
    $correct = _clockTimeStr($hour, $minutes, $digital);

    $options  = [$correct];
    $attempts = 0;
    while (count($options) < 4 && $attempts++ < 200) {
        $h   = rand(1, 12);
        $m   = $minutePool[array_rand($minutePool)];
        $str = _clockTimeStr($h, $m, $digital);
        if (!in_array($str, $options)) $options[] = $str;
    }
    shuffle($options);

    return [
        'type'        => 'klok',
        'vraag'       => 'Hoe laat is het?',
        'uur'         => $hour,
        'minuten'     => $minutes,
        'klok_invoer' => 'keuze',
        'opties'      => $options,
        'antwoord'    => $correct,
    ];
}

function _clockTimeStr(int $hour, int $minutes, bool $digital): string {
    if ($digital) return sprintf('%d:%02d', $hour, $minutes);
    $next = ($hour % 12) + 1;
    return match ($minutes) {
        0  => "$hour uur",
        15 => "kwart over $hour",
        30 => "half $next",
        45 => "kwart voor $next",
        default => $minutes < 30
            ? "$minutes over $hour"
            : (60 - $minutes) . " voor $next",
    };
}

function rJumps(int $max = 20, int $step = 2): array {
    $cap      = max($step * 5, $max);
    $maxStart = max(1, (int)(($cap - $step * 4) / $step));
    $start    = rand(1, $maxStart) * $step;
    $seq      = array_values(array_filter(
        array_map(fn($i) => $start + $i * $step, range(0, 4)),
        fn($v) => $v <= $cap
    ));
    if (count($seq) < 4) $seq = array_map(fn($i) => $i * $step, range(1, 5));
    $seq     = array_slice($seq, 0, 5);
    $pos     = rand(0, count($seq) - 1);
    $answer  = $seq[$pos];
    $display = $seq;
    $display[$pos] = '?';
    return [
        'type'     => 'invul',
        'label'    => "Sprongen van $step:",
        'vraag'    => implode('  →  ', $display),
        'antwoord' => (string)$answer,
        'invoer'   => 'getal',
    ];
}

function rSubtractionBorrow(int $max = 20): array {
    $a    = rand(11, min($max, 19));
    $minB = $a - 9;
    $maxB = min($a - 1, 9);
    $b    = rand($minB, $maxB);
    return [
        'type'     => 'invul',
        'vraag'    => "$a − $b = ?",
        'antwoord' => (string)($a - $b),
        'invoer'   => 'getal',
    ];
}

function rMissing(int $max = 20): array {
    if (rand(0, 1)) {
        $sum = rand(4, $max);
        $a   = rand(1, $sum - 1);
        $b   = $sum - $a;
        return rand(0, 1)
            ? ['type' => 'invul', 'vraag' => "$a + ? = $sum", 'antwoord' => (string)$b, 'invoer' => 'getal']
            : ['type' => 'invul', 'vraag' => "? + $b = $sum", 'antwoord' => (string)$a, 'invoer' => 'getal'];
    }
    $a    = rand(4, $max);
    $b    = rand(1, $a - 1);
    $diff = $a - $b;
    return rand(0, 1)
        ? ['type' => 'invul', 'vraag' => "$a − ? = $diff", 'antwoord' => (string)$b, 'invoer' => 'getal']
        : ['type' => 'invul', 'vraag' => "? − $b = $diff", 'antwoord' => (string)$a, 'invoer' => 'getal'];
}

function rCommutative(int $max = 20): array {
    $a   = rand(2, max(2, (int)($max / 2)));
    $b   = rand(1, $max - $a);
    $sum = $a + $b;
    return rand(0, 1)
        ? ['type' => 'invul', 'label' => "$a + $b = $sum", 'vraag' => "$sum − $b = ?", 'antwoord' => (string)$a, 'invoer' => 'getal']
        : ['type' => 'invul', 'label' => "$a + $b = $sum", 'vraag' => "$sum − $a = ?", 'antwoord' => (string)$b, 'invoer' => 'getal'];
}

function rSpeedTest(int $max = 20): array {
    $mx   = max(10, $max);
    $min  = max(2, (int)($mx / 10));
    $type = rand(0, 2);
    if ($type <= 1) {
        $a = rand($min, $mx - $min);
        $b = rand($min, $mx - $a);
        return ['type' => 'invul', 'vraag' => "$a + $b = ?",
                'antwoord' => (string)($a + $b), 'invoer' => 'getal'];
    }
    $a = rand($min * 2, $mx);
    $b = rand($min, $a - $min);
    return ['type' => 'invul', 'vraag' => "$a − $b = ?",
            'antwoord' => (string)($a - $b), 'invoer' => 'getal'];
}

function rMoney(): array {
    if (rand(0, 1)) {
        $payPool = [5, 10, 20];
        $pay     = $payPool[array_rand($payPool)];
        $cost    = rand(1, $pay - 1);
        $change  = $pay - $cost;
        $correct = "€$change";
        $options = [$correct];
        $attempt = 0;
        while (count($options) < 4 && $attempt++ < 100) {
            $v = "€" . rand(1, $pay);
            if (!in_array($v, $options)) $options[] = $v;
        }
        shuffle($options);
        return [
            'type'     => 'keuze',
            'label'    => "Je betaalt met €$pay.",
            'vraag'    => "Iets kost €$cost. Hoeveel wisselgeld?",
            'opties'   => $options,
            'antwoord' => $correct,
        ];
    }
    $a       = rand(1, 10);
    $b       = rand(1, 10);
    $sum     = $a + $b;
    $correct = "€$sum";
    $options = [$correct];
    $attempt = 0;
    while (count($options) < 4 && $attempt++ < 100) {
        $v = "€" . rand(1, 20);
        if (!in_array($v, $options)) $options[] = $v;
    }
    shuffle($options);
    return [
        'type'     => 'keuze',
        'vraag'    => "€$a + €$b = ?",
        'opties'   => $options,
        'antwoord' => $correct,
    ];
}

function rNumberSnake(int $max = 20): array {
    $cap     = max(10, $max);
    $start   = rand((int)($cap / 2), max((int)($cap / 2), $cap - 3));
    $current = $start;
    $steps   = [];

    for ($i = 0; $i < 4; $i++) {
        $plus   = rand(0, 1);
        $maxVal = $plus ? min(6, $cap - $current) : min(6, $current - 1);
        if ($maxVal < 1) { $plus = !$plus; $maxVal = $plus ? min(6, $cap - $current) : min(6, $current - 1); }
        if ($maxVal < 1) $maxVal = 1;
        $val     = rand(1, $maxVal);
        $current = $plus ? $current + $val : $current - $val;
        $current = max(0, min($cap, $current));
        $steps[] = ['op' => ($plus ? '+' : '−') . $val, 'result' => $current];
    }

    $questionIdx = rand(0, 3);
    $answer      = $steps[$questionIdx]['result'];

    $chain = [];
    $prev  = $start;
    foreach ($steps as $i => $s) {
        $chain[] = [
            'van'  => $prev,
            'op'   => $s['op'],
            'naar' => ($i === $questionIdx) ? '?' : $s['result'],
        ];
        $prev = $s['result'];
    }

    return [
        'type'     => 'rekenslang',
        'vraag'    => 'Wat is het ontbrekende getal?',
        'start'    => $start,
        'keten'    => $chain,
        'antwoord' => (string)$answer,
        'invoer'   => 'getal',
    ];
}

function rWordProblems(int $max = 20): array {
    $mx   = max(10, $max);
    $kind = rand(0, 7);
    switch ($kind) {
        case 0: {
            $x = rand(1, (int)($mx / 2));
            return ['type' => 'invul', 'vraag' => "Het dubbel van $x is ?",
                    'antwoord' => (string)($x * 2), 'invoer' => 'getal'];
        }
        case 1: {
            $x = rand(1, (int)($mx / 2)) * 2;
            return ['type' => 'invul', 'vraag' => "De helft van $x is ?",
                    'antwoord' => (string)($x / 2), 'invoer' => 'getal'];
        }
        case 2: {
            $a = rand(1, $mx - 1); $b = rand(1, $mx - $a);
            return ['type' => 'invul', 'vraag' => "De som van $a en $b is ?",
                    'antwoord' => (string)($a + $b), 'invoer' => 'getal'];
        }
        case 3: {
            $a = rand(2, $mx); $b = rand(1, $a - 1);
            return ['type' => 'invul', 'vraag' => "Het verschil van $a en $b is ?",
                    'antwoord' => (string)($a - $b), 'invoer' => 'getal'];
        }
        case 4: {
            $a = rand(1, $mx - 1); $b = rand(1, $mx - $a);
            return ['type' => 'invul', 'vraag' => "Vermeerder $a met $b, nu heb ik ?",
                    'antwoord' => (string)($a + $b), 'invoer' => 'getal'];
        }
        case 5: {
            $a = rand(2, $mx); $b = rand(1, $a - 1);
            return ['type' => 'invul', 'vraag' => "Verminder $a met $b, nu heb ik ?",
                    'antwoord' => (string)($a - $b), 'invoer' => 'getal'];
        }
        case 6: {
            $a = rand(2, $mx); $b = rand(1, $a - 1);
            return ['type' => 'invul', 'vraag' => "Als ik $b wegdoe van $a heb ik ?",
                    'antwoord' => (string)($a - $b), 'invoer' => 'getal'];
        }
        default: {
            $a = rand(1, $mx - 1); $b = rand(1, $mx - $a);
            return ['type' => 'invul', 'vraag' => "Ik neem $a en $b samen, nu heb ik ?",
                    'antwoord' => (string)($a + $b), 'invoer' => 'getal'];
        }
    }
}

/* ── Clock SVG ──────────────────────────────────────────── */

function clockSVG(int $hour, int $minutes = 0): string {
    $cx = 60; $cy = 60;

    $hRad = (($hour % 12) * 60 + $minutes) / 720 * 2 * M_PI;
    $hx   = round($cx + 30 * sin($hRad), 1);
    $hy   = round($cy - 30 * cos($hRad), 1);

    $mRad = $minutes / 60 * 2 * M_PI;
    $mx   = round($cx + 42 * sin($mRad), 1);
    $my   = round($cy - 42 * cos($mRad), 1);

    $out  = '<svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" class="klok-svg" aria-label="Klok">';
    $out .= '<circle cx="60" cy="60" r="56" fill="#fff8f0" stroke="#334155" stroke-width="3"/>';

    for ($t = 0; $t < 60; $t++) {
        $a   = $t / 60 * 2 * M_PI;
        $len = ($t % 5 === 0) ? 7 : 3;
        $sw  = ($t % 5 === 0) ? '2' : '1';
        $x1  = round(60 + 50 * sin($a), 1);
        $y1  = round(60 - 50 * cos($a), 1);
        $x2  = round(60 + (50 - $len) * sin($a), 1);
        $y2  = round(60 - (50 - $len) * cos($a), 1);
        $out .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" stroke=\"#64748b\" stroke-width=\"$sw\"/>";
    }

    for ($h = 1; $h <= 12; $h++) {
        $a  = $h / 12 * 2 * M_PI;
        $tx = round(60 + 38 * sin($a), 1);
        $ty = round(60 - 38 * cos($a) + 4, 1);
        $out .= "<text x=\"$tx\" y=\"$ty\" text-anchor=\"middle\" font-family=\"Nunito,sans-serif\" font-weight=\"800\" font-size=\"11\" fill=\"#1e293b\">$h</text>";
    }

    $out .= "<line x1=\"60\" y1=\"60\" x2=\"$mx\" y2=\"$my\" stroke=\"#475569\" stroke-width=\"2.5\" stroke-linecap=\"round\"/>";
    $out .= "<line x1=\"60\" y1=\"60\" x2=\"$hx\" y2=\"$hy\" stroke=\"#1e293b\" stroke-width=\"5\" stroke-linecap=\"round\"/>";
    $out .= '<circle cx="60" cy="60" r="4" fill="#334155"/>';
    $out .= '</svg>';
    return $out;
}
