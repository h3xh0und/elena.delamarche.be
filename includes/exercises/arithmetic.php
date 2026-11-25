<?php

function generateArithmeticExercise(string $type, int $maxNumber = 20, string $clockLevel = 'hour', int $jumpStep = 2, int $tablesMax = 5): array {
    $mx = max(10, $maxNumber);
    return match ($type) {
        'addition'      => rAddition($mx),
        'subtraction'   => rSubtraction($mx),
        'mixed'         => rMixed($mx),
        'times_tables'  => rTimesTables($tablesMax),
        'division'      => rDivision($tablesMax),
        'three_numbers' => rThreeNumbers($mx),
        'neighbours'    => rCountingNeighbours($mx),
        'splitting'     => rSplitting($mx),
        'half'          => rHalf($mx),
        'compare'       => rCompare($mx),
        'ordering'      => rOrdering($mx),
        'clock'         => rClock($clockLevel),
        'digital_clock' => rDigitalClock($clockLevel),
        'jumps'         => rJumps($mx, $jumpStep),
        'number_snake'  => rNumberSnake($mx),
        'missing'       => rMissing($mx),
        'commutative'   => rCommutative($mx),
        'money'         => rMoney(),
        'no_bridge'     => rNoBridge($mx),
        'true_false'    => rTrueFalse($tablesMax),
        'sign_choice'   => rSignChoice($tablesMax),
        'repeated'      => rRepeated($tablesMax),
        'table_stories' => rTableStories($tablesMax),
        'count_on'      => rCountOn($mx),
        'number_facts'  => rNumberFacts($mx),
        'place_value'   => rPlaceValue($mx),
        'number_line'   => rNumberLine($mx),
        'ordinal'       => rOrdinal($mx),
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

/* Tafels van 2 t.e.m. $maxTable, telkens 2–10 × (× 1 heeft geen nut) */
function rTimesTables(int $maxTable = 5): array {
    $table = rand(2, max(2, min(10, $maxTable)));
    $n     = rand(2, 10);
    return [
        'type'     => 'invul',
        'vraag'    => "$n × $table = ?",
        'antwoord' => (string)($n * $table),
        'invoer'   => 'getal',
    ];
}

/* Deeltafels: omgekeerde van de maaltafels, zelfde bereik, Vlaamse notatie "12 : 3" */
function rDivision(int $maxTable = 5): array {
    $table = rand(2, max(2, min(10, $maxTable)));
    $n     = rand(2, 10);
    $total = $n * $table;
    return [
        'type'     => 'invul',
        'vraag'    => "$total : $table = ?",
        'antwoord' => (string)$n,
        'invoer'   => 'getal',
    ];
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
    $leftMissing = (bool)rand(0, 1);
    return [
        'type'     => 'splitsing',
        'label'    => 'Splits het getal:',
        'vraag'    => '',
        'boven'    => $n,
        'links'    => $leftMissing ? '?' : $a,
        'rechts'   => $leftMissing ? $b : '?',
        'antwoord' => (string)($leftMissing ? $a : $b),
        'invoer'   => 'getal',
    ];
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
    [$hour, $minutes] = _clockRandomTime($level);

    // Half of the time: write the time as on a digital clock
    if (rand(0, 1)) {
        return [
            'type'        => 'klok',
            'label'       => 'Schrijf de tijd zoals op een digitale klok:',
            'vraag'       => '',
            'uur'         => $hour,
            'minuten'     => $minutes,
            'klok_invoer' => 'tijd',
            'antwoord'    => _clockDigital($hour, $minutes),
        ];
    }

    return [
        'type'        => 'klok',
        'vraag'       => 'Hoe laat is het?',
        'uur'         => $hour,
        'minuten'     => $minutes,
        'klok_invoer' => 'keuze',
        'opties'      => _clockWordOptions($hour, $minutes, $level),
        'antwoord'    => _clockWords($hour, $minutes),
    ];
}

function rDigitalClock(string $level = 'hour'): array {
    [$hour, $minutes] = _clockRandomTime($level);

    // Read the digital clock and pick the time in words
    if (rand(0, 1)) {
        return [
            'type'        => 'klok',
            'vraag'       => 'Hoe laat is het?',
            'digitaal'    => _clockDigital($hour, $minutes),
            'klok_invoer' => 'keuze',
            'opties'      => _clockWordOptions($hour, $minutes, $level),
            'antwoord'    => _clockWords($hour, $minutes),
        ];
    }

    // Time in words: write it as on a digital clock
    return [
        'type'        => 'klok',
        'label'       => 'Schrijf zoals op een digitale klok:',
        'vraag'       => _clockWords($hour, $minutes),
        'klok_invoer' => 'tijd',
        'antwoord'    => _clockDigital($hour, $minutes),
    ];
}

function _clockMinutePool(string $level): array {
    return match ($level) {
        'half_hour' => [0, 30],
        'quarter'   => [0, 15, 30, 45],
        '5_min'     => [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55],
        'minute'    => range(0, 59),
        default     => [0],
    };
}

function _clockRandomTime(string $level): array {
    $pool = _clockMinutePool($level);
    return [rand(1, 12), $pool[array_rand($pool)]];
}

function _clockDigital(int $hour, int $minutes): string {
    return sprintf('%d:%02d', $hour, $minutes);
}

/* Time in words, the way it is taught in Flemish schools */
function _clockWords(int $hour, int $minutes): string {
    $next = ($hour % 12) + 1;
    return match (true) {
        $minutes === 0  => "$hour uur",
        $minutes < 15   => "$minutes over $hour",
        $minutes === 15 => "kwart over $hour",
        $minutes < 30   => (30 - $minutes) . " voor half $next",
        $minutes === 30 => "half $next",
        $minutes < 45   => ($minutes - 30) . " over half $next",
        $minutes === 45 => "kwart voor $next",
        default         => (60 - $minutes) . " voor $next",
    };
}

/* Four options: the right one, the classic "one hour off" mistake, and random times */
function _clockWordOptions(int $hour, int $minutes, string $level): array {
    $pool    = _clockMinutePool($level);
    $correct = _clockWords($hour, $minutes);
    $options = [$correct];
    $nearby  = [_clockWords(($hour % 12) + 1, $minutes), _clockWords((($hour + 10) % 12) + 1, $minutes)];
    $options[] = $nearby[array_rand($nearby)];

    $attempts = 0;
    while (count($options) < 4 && $attempts++ < 200) {
        $str = _clockWords(rand(1, 12), $pool[array_rand($pool)]);
        if (!in_array($str, $options)) $options[] = $str;
    }
    shuffle($options);
    return $options;
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

/* Sneltest: + − × : om de beurt ($step telt de vragen), elk binnen de eigen instelling */
function rSpeedTest(int $max = 20, int $tablesMax = 5, int $step = 0): array {
    $mx  = max(10, $max);
    $min = max(2, (int)($mx / 10));
    switch ($step % 4) {
        case 0:
            $a = rand($min, $mx - $min);
            $b = rand($min, $mx - $a);
            return ['type' => 'invul', 'vraag' => "$a + $b = ?",
                    'antwoord' => (string)($a + $b), 'invoer' => 'getal'];
        case 1:
            $a = rand($min * 2, $mx);
            $b = rand($min, $a - $min);
            return ['type' => 'invul', 'vraag' => "$a − $b = ?",
                    'antwoord' => (string)($a - $b), 'invoer' => 'getal'];
        case 2:
            return rTimesTables($tablesMax);
        default:
            return rDivision($tablesMax);
    }
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

/* Optellen en aftrekken met tientallen, zonder brug over het tiental:
   20 + 30, 90 − 40, 34 + 20, 72 − 40, 50 + 25, 85 − 45 */
function rNoBridge(int $max = 100): array {
    $max = max(20, $max);
    $tens = fn(int $lo, int $hi) => rand(intdiv($lo + 9, 10), intdiv($hi, 10)) * 10;
    for ($try = 0; $try < 200; $try++) {
        $kind = rand(0, $max <= 20 ? 7 : 5);   // tot 20: ook 12 + 5 en 17 − 4
        $plus = $kind % 2 === 0;
        switch ($kind) {
            case 0: $a = $tens(10, $max - 10); $b = $tens(10, $max - $a); break;   // T + T
            case 1: $a = $tens(20, $max);      $b = $tens(10, $a - 10);   break;   // T − T
            case 2: $a = rand(11, $max - 10);  $b = $tens(10, $max - $a); break;   // TE + T
            case 3: $a = rand(21, $max);       $b = $tens(10, $a - 1);    break;   // TE − T
            case 4: $a = rand(10, $max - 11);  $b = rand(11, $max - $a);  break;   // TE + TE
            case 5: $a = rand(21, $max);       $b = rand(11, $a - 10);    break;   // TE − TE
            case 6: $a = rand(11, $max - 1);   $b = rand(1, 9);           break;   // TE + E
            default: $a = rand(11, $max);      $b = rand(1, 9);           break;   // TE − E
        }
        if ($a < 1 || $b < 1 || $a > $max || $b > $max) continue;
        $result = $plus ? $a + $b : $a - $b;
        $noBridge = $plus ? ($a % 10) + ($b % 10) <= 9 : ($a % 10) >= ($b % 10);
        if ($noBridge && $result >= 0 && $result <= $max && ($a % 10 || $b % 10 || $kind <= 1)) {
            return [
                'type'     => 'invul',
                'vraag'    => $plus ? "$a + $b = ?" : "$a − $b = ?",
                'antwoord' => (string)$result,
                'invoer'   => 'getal',
            ];
        }
    }
    return ['type' => 'invul', 'vraag' => '10 + 10 = ?', 'antwoord' => '20', 'invoer' => 'getal'];
}

/* ── Maal- en deeltafels: begrip (toets blok 2) ──────────── */

function _randTable(int $tablesMax): int {
    return rand(2, max(2, min(10, $tablesMax)));
}

/* "7 × 2 = 14": waar of niet waar? Foute sommen zitten één rij of één ernaast. */
function rTrueFalse(int $tablesMax = 5): array {
    $table  = _randTable($tablesMax);
    $n      = rand(2, 10);
    $divide = (bool)rand(0, 1);
    $isTrue = (bool)rand(0, 1);
    $result = $divide ? $n : $n * $table;
    if (!$isTrue) {
        $wrong  = $divide ? [$n + 1, $n - 1, $table] : [$result + $table, $result - $table, $result + 1, $result - 1];
        $wrong  = array_values(array_filter($wrong, fn($w) => $w > 0 && $w !== $result));
        $result = $wrong[array_rand($wrong)];
    }
    $yes = '👍 Waar';
    $no  = '👎 Niet waar';
    return [
        'type'     => 'keuze',
        'label'    => 'Waar of niet waar?',
        'vraag'    => $divide ? ($n * $table) . " : $table = $result" : "$n × $table = $result",
        'opties'   => [$yes, $no],
        'antwoord' => $isTrue ? $yes : $no,
    ];
}

/* "6 __ 2 = 3": maal of gedeeld door? Met tafels vanaf 2 is er altijd maar één juist teken. */
function rSignChoice(int $tablesMax = 5): array {
    $table   = _randTable($tablesMax);
    $n       = rand(2, 10);
    $product = $n * $table;
    $times   = (bool)rand(0, 1);
    return [
        'type'     => 'keuze',
        'label'    => 'Kies het juiste teken:',
        'vraag'    => $times ? "$n  __  $table = $product" : "$product  __  $table = $n",
        'opties'   => ['×', ':'],
        'antwoord' => $times ? '×' : ':',
    ];
}

function _repeat(int $start, int $value, int $count, string $op): string {
    $terms = array_fill(0, $count, $value);
    return ($start ? "$start $op " : '') . implode(" $op ", $terms);
}

/* Herhaalde optelling ↔ vermenigvuldiging, herhaalde aftrekking ↔ deling.
   Zoals in de methode: 3 × 7 = 7 + 7 + 7 en 10 − 5 − 5 hoort bij 10 : 5. */
function rRepeated(int $tablesMax = 5): array {
    $table = _randTable($tablesMax);
    switch (rand(0, 3)) {
        case 0: {   // 2 + 2 + 2 + 2 + 2 = ? × 2
            $count = rand(2, 8);
            return [
                'type'     => 'invul',
                'label'    => 'Schrijf als maalsom:',
                'vraag'    => _repeat(0, $table, $count, '+') . " = ? × $table",
                'antwoord' => (string)$count,
                'invoer'   => 'getal',
            ];
        }
        case 1: {   // 3 × 7 → 7 + 7 + 7
            $count   = rand(2, 4);
            $correct = _repeat(0, $table, $count, '+');
            $options = [$correct, _repeat(0, $table, $count + 1, '+'), "$count + $table"];
            if ($count > 2) $options[] = _repeat(0, $table, $count - 1, '+');
            if ($count !== $table) $options[] = _repeat(0, $count, 2, '+');
            return _repeatedChoice('Welke optelling hoort erbij?', "$count × $table", $correct, $options);
        }
        case 2: {   // 18 − 6 − 6 − 6 → 18 : 6
            $count   = rand(2, 5);
            $total   = $count * $table;
            $correct = "$total : $table";
            $options = [$correct];
            foreach ([$count, $table + 1, $table - 1, $table + 2] as $d) {
                if ($d >= 2) $options[] = "$total : $d";
            }
            return _repeatedChoice('Welke deling hoort erbij?', _repeat($total, $table, $count, '−'), $correct, $options);
        }
        default: {  // 9 : 3 → 9 − 3 − 3 − 3
            $count   = rand(2, 4);
            $total   = $count * $table;
            $correct = _repeat($total, $table, $count, '−');
            $options = [$correct, _repeat($total, $table, $count + 1, '−'), "$total − $table"];
            if ($count !== $table && $table <= 5) $options[] = _repeat($total, $count, $table, '−');
            return _repeatedChoice('Welke aftrekking hoort erbij?', "$total : $table", $correct, $options);
        }
    }
}

function _repeatedChoice(string $label, string $question, string $correct, array $options): array {
    $wrong = array_values(array_diff(array_unique($options), [$correct]));
    shuffle($wrong);
    $options = array_merge([$correct], array_slice($wrong, 0, 2));
    shuffle($options);
    return [
        'type'     => 'keuze',
        'label'    => $label,
        'vraag'    => $question,
        'opties'   => $options,
        'antwoord' => $correct,
    ];
}

/* Vraagstukjes met de tafels: groepjes maken (×), eerlijk verdelen (:) en groeperen (:).
   Een tekening met emoji helpt tellen, en het antwoord komt in een antwoordzin. */
function rTableStories(int $tablesMax = 5): array {
    $themes = [
        ['groep' => 'zakjes', 'item' => 'knikkers',  'emoji' => '🔵', 'per' => 'in elk zakje'],
        ['groep' => 'dozen',  'item' => 'eieren',    'emoji' => '🥚', 'per' => 'in elke doos'],
        ['groep' => 'borden', 'item' => 'koekjes',   'emoji' => '🍪', 'per' => 'op elk bord'],
        ['groep' => 'vazen',  'item' => 'bloemen',   'emoji' => '🌷', 'per' => 'in elke vaas'],
        ['groep' => 'bakjes', 'item' => 'aardbeien', 'emoji' => '🍓', 'per' => 'in elk bakje'],
        ['groep' => 'potjes', 'item' => 'stiften',   'emoji' => '🖍️', 'per' => 'in elk potje'],
        ['groep' => 'manden', 'item' => 'ballen',    'emoji' => '⚽', 'per' => 'in elke mand'],
        ['groep' => 'tafels', 'item' => 'potloden',  'emoji' => '✏️', 'per' => 'op elke tafel'],
    ];
    $th    = $themes[array_rand($themes)];
    $table = _randTable($tablesMax);
    $n     = rand(2, min(10, max(2, intdiv(40, $table))));   // hoogstens 40 tekeningetjes
    $total = $n * $table;
    ['groep' => $groep, 'item' => $item, 'per' => $per] = $th;

    switch (rand(0, 2)) {
        case 0:
            return [
                'type'       => 'verhaal',
                'vraag'      => "Er zijn $n $groep met telkens $table $item. Hoeveel $item zijn er samen?",
                'plaatje'    => ['emoji' => $th['emoji'], 'groepen' => $n, 'per' => $table],
                'antwoordzin'=> "Er zijn ? $item.",
                'antwoord'   => (string)$total,
                'invoer'     => 'getal',
            ];
        case 1:
            return [
                'type'       => 'verhaal',
                'vraag'      => "$total $item worden eerlijk verdeeld over $table $groep. Hoeveel $item komen er $per?",
                'plaatje'    => ['emoji' => $th['emoji'], 'groepen' => 0, 'per' => $total],
                'antwoordzin'=> "Er komen ? $item $per.",
                'antwoord'   => (string)$n,
                'invoer'     => 'getal',
            ];
        default:
            return [
                'type'       => 'verhaal',
                'vraag'      => "Je hebt $total $item. Er gaan er $table $per. Hoeveel $groep heb je nodig?",
                'plaatje'    => ['emoji' => $th['emoji'], 'groepen' => 0, 'per' => $total],
                'antwoordzin'=> "Ik heb ? $groep nodig.",
                'antwoord'   => (string)$n,
                'invoer'     => 'getal',
            ];
    }
}

/* ── Getallenkennis tot 100 (toets blok 2) ─────────────────── */

/* Tel verder of terug met sprongen van 1, 2, 5 of 10, vanaf een willekeurig getal (14, 16, 18, ?) */
function rCountOn(int $max = 20): array {
    $steps = [1, 2];
    if ($max >= 30) $steps[] = 5;
    if ($max >= 50) $steps[] = 10;
    $step    = $steps[array_rand($steps)];
    $forward = (bool)rand(0, 1);
    $span    = 4 * $step;

    if ($step === 1 && $max >= 20 && rand(0, 1)) {
        // Over het tiental heen: daar lopen kinderen vast (31, 30, 29, ...)
        $ten   = rand(1, intdiv($max - 1, 10)) * 10;
        $start = $forward ? $ten - rand(1, 3) : $ten + rand(1, 2);
        $start = max($forward ? 0 : $span, min($forward ? $max - $span : $max, $start));
    } else {
        $start = $forward ? rand(0, $max - $span) : rand($span, $max);
    }

    $row     = array_map(fn($i) => $forward ? $start + $i * $step : $start - $i * $step, range(0, 4));
    $pos     = rand(0, 2) ? 4 : 3;
    $answer  = $row[$pos];
    $row[$pos] = '?';
    return [
        'type'     => 'invul',
        'label'    => ($forward ? 'Tel verder' : 'Tel terug') . " met sprongen van $step:",
        'vraag'    => implode('  →  ', $row),
        'antwoord' => (string)$answer,
        'invoer'   => 'getal',
    ];
}

/* "56 komt juist na ?", "Het tiental na 56 is ?", "Twee plaatsen na 38 komt ?" */
function rNumberFacts(int $max = 20): array {
    $n = rand($max >= 30 ? 11 : 3, $max - 3);
    if ($n % 10 === 0) $n++;
    $q = match (rand(0, 7)) {
        0 => ["$n komt juist na ?", $n - 1],
        1 => ["$n komt juist voor ?", $n + 1],
        2 => ["Juist voor $n komt ?", $n - 1],
        3 => ["Juist na $n komt ?", $n + 1],
        4 => ["$n staat tussen ? en " . ($n + 1), $n - 1],
        5 => ["$n staat tussen " . ($n - 1) . " en ?", $n + 1],
        6 => rand(0, 1) ? ["Het tiental voor $n is ?", intdiv($n, 10) * 10]
                        : ["Het tiental na $n is ?", (intdiv($n, 10) + 1) * 10],
        default => rand(0, 1) ? ["Twee plaatsen na $n komt ?", $n + 2]
                              : ["Twee plaatsen voor $n komt ?", $n - 2],
    };
    return [
        'type'     => 'invul',
        'vraag'    => $q[0],
        'antwoord' => (string)$q[1],
        'invoer'   => 'getal',
    ];
}

/* Getal in woorden zoals op de toets: "drie-en-zestig", "dertien" */
function numberWords(int $n): string {
    $units = ['nul', 'een', 'twee', 'drie', 'vier', 'vijf', 'zes', 'zeven', 'acht', 'negen',
              'tien', 'elf', 'twaalf', 'dertien', 'veertien', 'vijftien', 'zestien', 'zeventien', 'achttien', 'negentien'];
    $tens  = [2 => 'twintig', 'dertig', 'veertig', 'vijftig', 'zestig', 'zeventig', 'tachtig', 'negentig'];
    if ($n >= 100) return 'honderd';
    if ($n < 20)   return $units[$n];
    $t = intdiv($n, 10);
    $e = $n % 10;
    return $e ? $units[$e] . '-en-' . $tens[$t] : $tens[$t];
}

/* H T E: van tientallen en eenheden naar het getal, omgekeerd, of van woorden naar het getal */
function rPlaceValue(int $max = 20): array {
    $n = ($max >= 100 && rand(0, 7) === 0) ? 100 : rand(min(11, $max), min(99, $max));
    if (rand(0, 3) === 0) {
        return [
            'type'     => 'invul',
            'label'    => 'Schrijf het getal:',
            'vraag'    => numberWords($n) . ' = ?',
            'antwoord' => (string)$n,
            'invoer'   => 'getal',
        ];
    }
    $digits = $n === 100 ? ['H' => 1, 'T' => 0, 'E' => 0] : ['T' => intdiv($n, 10), 'E' => $n % 10];
    if ($max >= 100) $digits = ['H' => $digits['H'] ?? ''] + $digits;
    $cells  = array_map(fn($d) => (string)$d, $digits);
    $getal  = (string)$n;

    if (rand(0, 1)) {                       // T en E gegeven → het getal
        $getal  = '?';
        $answer = $n;
    } else {                                // het getal gegeven → T of E
        $col          = rand(0, 1) ? 'T' : 'E';
        $answer       = $digits[$col];
        $cells[$col]  = '?';
    }
    return [
        'type'     => 'hte',
        'label'    => 'Vul in:',
        'vraag'    => '',
        'getal'    => $getal,
        'kolommen' => $cells,
        'antwoord' => (string)$answer,
        'invoer'   => 'getal',
    ];
}

/* Getallenas van 20 lang (van tiental tot tiental) met een pijl: welk getal is het? */
function rNumberLine(int $max = 20): array {
    $span = $max >= 20 ? 20 : 10;
    $from = rand(0, intdiv($max - $span, 10)) * 10;
    do { $n = rand($from + 1, $from + $span - 1); } while ($n % 10 === 0);
    return [
        'type'     => 'getallenas',
        'vraag'    => 'Welk getal staat bij de pijl?',
        'van'      => $from,
        'tot'      => $from + $span,
        'pijl'     => $n,
        'antwoord' => (string)$n,
        'invoer'   => 'getal',
    ];
}

function ordinalWord(int $n): string {
    return $n . (($n === 1 || $n === 8 || $n >= 20) ? 'ste' : 'de');
}

/* Rangtelwoorden: een rij voorwerpen, de eerste staat links */
function rOrdinal(int $max = 20): array {
    $pool = ['🍎', '⚽', '🔑', '🎈', '🧸', '🚗', '🌟', '🐸', '🍌', '🎁', '📚', '🦋', '🍓', '🚲', '🐟', '🍪', '🎸', '🌻', '🐞', '🥕'];
    shuffle($pool);
    $items = array_slice($pool, 0, rand(8, min(12, $max)));
    $count = count($items);

    if (rand(0, 2) === 0) {
        $idx = rand(0, $count - 1);
        return [
            'type'     => 'rij',
            'vraag'    => 'Op welke plaats staat ' . $items[$idx] . '?',
            'items'    => $items,
            'doel'     => $idx,
            'antwoord' => (string)($idx + 1),
            'invoer'   => 'getal',
        ];
    }
    $kind = rand(0, 3);
    if ($kind === 0) { $idx = $count - 1; $place = 'laatste'; }
    elseif ($kind === 1) { $idx = $count - 2; $place = 'voorlaatste'; }
    else { $idx = rand(1, $count - 3); $place = ordinalWord($idx + 1); }

    $others = array_values(array_diff($items, [$items[$idx]]));
    shuffle($others);
    $options = array_merge([$items[$idx]], array_slice($others, 0, 3));
    shuffle($options);
    return [
        'type'     => 'rij',
        'vraag'    => "Wat staat op de $place plaats?",
        'items'    => $items,
        'doel'     => null,
        'opties'   => $options,
        'antwoord' => $items[$idx],
        'invoer'   => 'keuze',
    ];
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
