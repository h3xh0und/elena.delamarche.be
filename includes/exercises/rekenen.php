<?php

function genereerRekenOefening(string $type, int $maxGetal = 20, string $klokNiveau = 'uur', int $sprongenStap = 2): array {
    $mx = max(10, $maxGetal);
    return match ($type) {
        'optellen'       => rOptellen($mx),
        'aftrekken'      => rAftrekken($mx),
        'gemengd'        => rGemengd($mx),
        'drie_optellen'  => rDrieOptellen($mx),
        'tellen_buren'   => rTellenBuren($mx),
        'splitsen'       => rSplitsen($mx),
        'de_helft'       => rHelft($mx),
        'vergelijken'    => rVergelijken($mx),
        'ordenen'        => rOrdenen($mx),
        'klok'           => rKlok($klokNiveau),
        'sprongen'       => rSprongen($mx, $sprongenStap),
        'rekenslang'     => rRekenslang($mx),
        'aftrekken_brug' => rAftrekkenBrug($mx),
        'ontbrekend'     => rOntbrekend($mx),
        'wisselsom'      => rWisselsom($mx),
        'geld'           => rGeld(),
        default          => rOptellen($mx),
    };
}

/* ── Helpers ──────────────────────────────────────────── */

function rOptellen(int $max = 20): array {
    $a = rand(1, $max - 1);
    $b = rand(1, $max - $a);
    return [
        'type'     => 'invul',
        'vraag'    => "$a + $b = ?",
        'antwoord' => (string)($a + $b),
        'invoer'   => 'getal',
    ];
}

function rAftrekken(int $max): array {
    $a = rand((int)($max / 2), $max);
    $b = rand(1, $a - 1);
    return [
        'type'     => 'invul',
        'vraag'    => "$a − $b = ?",
        'antwoord' => (string)($a - $b),
        'invoer'   => 'getal',
    ];
}

function rGemengd(int $max = 20): array {
    return rand(0, 1) ? rOptellen(2, $max - 1, $max) : rAftrekken($max);
}

function rDrieOptellen(int $max = 20): array {
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

function rTellenBuren(int $max = 20): array {
    $soort = rand(0, 3);
    $hi = max(11, $max);

    if ($soort === 0) {
        $n = rand(2, $hi);
        return ['type' => 'invul', 'vraag' => "Wat komt vóór $n?",
                'antwoord' => (string)($n - 1), 'invoer' => 'getal'];
    }
    if ($soort === 1) {
        $n = rand(1, $hi - 1);
        return ['type' => 'invul', 'vraag' => "Wat komt ná $n?",
                'antwoord' => (string)($n + 1), 'invoer' => 'getal'];
    }
    if ($soort === 2) {
        $n = rand(1, $hi - 2);
        return ['type' => 'invul',
                'vraag'    => "$n , __ , " . ($n + 2) . "  — welk getal hoort ertussen?",
                'antwoord' => (string)($n + 1), 'invoer' => 'getal'];
    }
    // reeks met ontbrekend getal
    $richting = rand(0, 1);
    $s = rand(1, max(1, $hi - 4));
    $rij = $richting ? [$s+4, $s+3, $s+2, $s+1, $s] : [$s, $s+1, $s+2, $s+3, $s+4];
    $pos = rand(1, 3);
    $ant = $rij[$pos];
    $rij[$pos] = '?';
    return ['type' => 'invul', 'vraag' => implode(' → ', $rij),
            'antwoord' => (string)$ant, 'invoer' => 'getal'];
}

function rSplitsen(int $max = 20): array {
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

function rHelft(int $max = 20): array {
    $maxEven = (int)(min($max, 100) / 2);
    $n = rand(1, $maxEven) * 2;
    return [
        'type'     => 'invul',
        'vraag'    => "De helft van $n is ?",
        'antwoord' => (string)($n / 2),
        'invoer'   => 'getal',
    ];
}

function rVergelijken(int $max = 20): array {
    $a = rand(1, $max);
    $b = rand(1, $max);
    if (rand(0, 5) === 0) $b = $a;
    $ant = $a < $b ? '<' : ($a > $b ? '>' : '=');
    return [
        'type'     => 'keuze',
        'vraag'    => "$a  __  $b",
        'label'    => 'Vul het goede teken in:',
        'opties'   => ['<', '>', '='],
        'antwoord' => $ant,
    ];
}

function rOrdenen(int $max = 20): array {
    $groot = rand(0, 1);
    $set = [];
    $lo  = max(1, $max - 15);
    while (count($set) < 5) {
        $n = rand($lo, $max);
        if (!in_array($n, $set)) $set[] = $n;
    }
    $gesorteerd = $set;
    $groot ? rsort($gesorteerd) : sort($gesorteerd);
    return [
        'type'      => 'ordenen',
        'vraag'     => 'Orden ' . ($groot ? 'van groot naar klein' : 'van klein naar groot') . ':',
        'getallen'  => $set,
        'antwoord'  => $gesorteerd,
    ];
}

function rKlok(string $niveau = 'uur'): array {
    $uur = rand(1, 12);

    if ($niveau === 'uur') {
        return [
            'type'        => 'klok',
            'vraag'       => 'Hoe laat is het?',
            'uur'         => $uur,
            'minuten'     => 0,
            'klok_invoer' => 'getal',
            'antwoord'    => (string)$uur,
            'hint'        => 'Typ alleen het uur (bijv. 3)',
        ];
    }

    $minutenPool = match ($niveau) {
        'half_uur' => [0, 30],
        'kwartier' => [0, 15, 30, 45],
        '5_min'    => [0,5,10,15,20,25,30,35,40,45,50,55],
        'minuut'   => range(0, 59),
        default    => [0],
    };
    $minuten  = $minutenPool[array_rand($minutenPool)];
    $digitaal = in_array($niveau, ['5_min', 'minuut']);
    $goed     = _klokTijdStr($uur, $minuten, $digitaal);

    // Genereer 3 foute opties
    $opties   = [$goed];
    $pogingen = 0;
    while (count($opties) < 4 && $pogingen++ < 200) {
        $h   = rand(1, 12);
        $m   = $minutenPool[array_rand($minutenPool)];
        $str = _klokTijdStr($h, $m, $digitaal);
        if (!in_array($str, $opties)) $opties[] = $str;
    }
    shuffle($opties);

    return [
        'type'        => 'klok',
        'vraag'       => 'Hoe laat is het?',
        'uur'         => $uur,
        'minuten'     => $minuten,
        'klok_invoer' => 'keuze',
        'opties'      => $opties,
        'antwoord'    => $goed,
    ];
}

function _klokTijdStr(int $uur, int $minuten, bool $digitaal): string {
    if ($digitaal) return sprintf('%d:%02d', $uur, $minuten);
    $volgend = ($uur % 12) + 1;
    return match ($minuten) {
        0  => "$uur uur",
        15 => "kwart over $uur",
        30 => "half $volgend",
        45 => "kwart voor $volgend",
        default => $minuten < 30
            ? "$minuten over $uur"
            : (60 - $minuten) . " voor $volgend",
    };
}

function rSprongen(int $max = 20, int $stap = 2): array {
    $cap      = max($stap * 5, $max);
    $maxStart = max(1, (int)(($cap - $stap * 4) / $stap));
    $start    = rand(1, $maxStart) * $stap;
    $seq      = array_values(array_filter(
        array_map(fn($i) => $start + $i * $stap, range(0, 4)),
        fn($v) => $v <= $cap
    ));
    if (count($seq) < 4) $seq = array_map(fn($i) => $i * $stap, range(1, 5));
    $seq     = array_slice($seq, 0, 5);
    $pos     = rand(0, count($seq) - 1);
    $ant     = $seq[$pos];
    $display = $seq;
    $display[$pos] = '?';
    return [
        'type'     => 'invul',
        'label'    => "Sprongen van $stap:",
        'vraag'    => implode('  →  ', $display),
        'antwoord' => (string)$ant,
        'invoer'   => 'getal',
    ];
}

function rAftrekkenBrug(int $max = 20): array {
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

function rOntbrekend(int $max = 20): array {
    if (rand(0, 1)) {
        $som = rand(4, $max);
        $a   = rand(1, $som - 1);
        $b   = $som - $a;
        return rand(0, 1)
            ? ['type' => 'invul', 'vraag' => "$a + ? = $som", 'antwoord' => (string)$b, 'invoer' => 'getal']
            : ['type' => 'invul', 'vraag' => "? + $b = $som", 'antwoord' => (string)$a, 'invoer' => 'getal'];
    }
    $a        = rand(4, $max);
    $b        = rand(1, $a - 1);
    $verschil = $a - $b;
    return rand(0, 1)
        ? ['type' => 'invul', 'vraag' => "$a − ? = $verschil", 'antwoord' => (string)$b, 'invoer' => 'getal']
        : ['type' => 'invul', 'vraag' => "? − $b = $verschil", 'antwoord' => (string)$a, 'invoer' => 'getal'];
}

function rWisselsom(int $max = 20): array {
    $a   = rand(2, max(2, (int)($max / 2)));
    $b   = rand(1, $max - $a);
    $som = $a + $b;
    return rand(0, 1)
        ? ['type' => 'invul', 'label' => "$a + $b = $som", 'vraag' => "$som − $b = ?", 'antwoord' => (string)$a, 'invoer' => 'getal']
        : ['type' => 'invul', 'label' => "$a + $b = $som", 'vraag' => "$som − $a = ?", 'antwoord' => (string)$b, 'invoer' => 'getal'];
}

function rGeld(): array {
    if (rand(0, 1)) {
        $betaalPool = [5, 10, 20];
        $betaal     = $betaalPool[array_rand($betaalPool)];
        $kost       = rand(1, $betaal - 1);
        $terug      = $betaal - $kost;
        $goed       = "€$terug";
        $opties     = [$goed];
        $poging     = 0;
        while (count($opties) < 4 && $poging++ < 100) {
            $v = "€" . rand(1, $betaal);
            if (!in_array($v, $opties)) $opties[] = $v;
        }
        shuffle($opties);
        return [
            'type'     => 'keuze',
            'label'    => "Je betaalt met €$betaal.",
            'vraag'    => "Iets kost €$kost. Hoeveel wisselgeld?",
            'opties'   => $opties,
            'antwoord' => $goed,
        ];
    }
    $a      = rand(1, 10);
    $b      = rand(1, 10);
    $som    = $a + $b;
    $goed   = "€$som";
    $opties = [$goed];
    $poging = 0;
    while (count($opties) < 4 && $poging++ < 100) {
        $v = "€" . rand(1, 20);
        if (!in_array($v, $opties)) $opties[] = $v;
    }
    shuffle($opties);
    return [
        'type'     => 'keuze',
        'vraag'    => "€$a + €$b = ?",
        'opties'   => $opties,
        'antwoord' => $goed,
    ];
}

function rRekenslang(int $max = 20): array {
    $cap    = max(10, $max);
    $start  = rand((int)($cap / 2), max((int)($cap / 2), $cap - 3));
    $huidig = $start;
    $stappen = [];

    for ($i = 0; $i < 4; $i++) {
        $plus   = rand(0, 1);
        $maxVal = $plus ? min(6, $cap - $huidig) : min(6, $huidig - 1);
        if ($maxVal < 1) { $plus = !$plus; $maxVal = $plus ? min(6, $cap - $huidig) : min(6, $huidig - 1); }
        if ($maxVal < 1) $maxVal = 1;
        $val    = rand(1, $maxVal);
        $huidig = $plus ? $huidig + $val : $huidig - $val;
        $huidig = max(0, min($cap, $huidig));
        $stappen[] = ['op' => ($plus ? '+' : '−') . $val, 'resultaat' => $huidig];
    }

    $vraagIdx = rand(0, 3);
    $antwoord = $stappen[$vraagIdx]['resultaat'];

    $keten = [];
    $prev  = $start;
    foreach ($stappen as $i => $s) {
        $keten[] = [
            'van'  => $prev,
            'op'   => $s['op'],
            'naar' => ($i === $vraagIdx) ? '?' : $s['resultaat'],
        ];
        $prev = $s['resultaat'];
    }

    return [
        'type'     => 'rekenslang',
        'vraag'    => 'Wat is het ontbrekende getal?',
        'start'    => $start,
        'keten'    => $keten,
        'antwoord' => (string)$antwoord,
        'invoer'   => 'getal',
    ];
}

/* ── SVG klok ─────────────────────────────────────────── */

function klokSVG(int $uur, int $minuten = 0): string {
    $cx = 60; $cy = 60;

    // Uurwijzer: 30° per uur + 0.5° per minuut (kruipt mee)
    $hRad = (($uur % 12) * 60 + $minuten) / 720 * 2 * M_PI;
    $hx   = round($cx + 30 * sin($hRad), 1);
    $hy   = round($cy - 30 * cos($hRad), 1);

    // Minuutwijzer
    $mRad = $minuten / 60 * 2 * M_PI;
    $mx   = round($cx + 42 * sin($mRad), 1);
    $my   = round($cy - 42 * cos($mRad), 1);

    $out  = '<svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" class="klok-svg" aria-label="Klok">';
    $out .= '<circle cx="60" cy="60" r="56" fill="#fff8f0" stroke="#334155" stroke-width="3"/>';

    // Streepjes
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

    // Uurcijfers
    for ($h = 1; $h <= 12; $h++) {
        $a  = $h / 12 * 2 * M_PI;
        $tx = round(60 + 38 * sin($a), 1);
        $ty = round(60 - 38 * cos($a) + 4, 1);
        $out .= "<text x=\"$tx\" y=\"$ty\" text-anchor=\"middle\" font-family=\"Nunito,sans-serif\" font-weight=\"800\" font-size=\"11\" fill=\"#1e293b\">$h</text>";
    }

    // Minuutwijzer (blauwgrijs, lang)
    $out .= "<line x1=\"60\" y1=\"60\" x2=\"$mx\" y2=\"$my\" stroke=\"#475569\" stroke-width=\"2.5\" stroke-linecap=\"round\"/>";
    // Uurwijzer (donker, kort)
    $out .= "<line x1=\"60\" y1=\"60\" x2=\"$hx\" y2=\"$hy\" stroke=\"#1e293b\" stroke-width=\"5\" stroke-linecap=\"round\"/>";
    // Middelpunt
    $out .= '<circle cx="60" cy="60" r="4" fill="#334155"/>';
    $out .= '</svg>';
    return $out;
}
