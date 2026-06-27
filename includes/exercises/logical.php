<?php

function rMeerMinder(int $max = 20): array {
    $mx   = max(10, $max);
    $namen = ['Emma', 'Lena', 'Tom', 'Sara', 'Noor', 'Tim', 'Julia', 'Bas', 'Mia', 'Lars'];
    $onderwerpen = ['appels', 'stickers', 'koekjes', 'bloemen', 'potloden', 'ballonnen', 'boeken'];
    shuffle($namen);
    $naam1 = $namen[0];
    $naam2 = $namen[1];
    $ondw  = $onderwerpen[array_rand($onderwerpen)];
    $soort = rand(0, 3);

    switch ($soort) {
        case 0: {
            $x = rand(1, $mx - 3); $y = rand(1, min(5, $mx - $x));
            return ['type' => 'invul', 'invoer' => 'getal', 'antwoord' => (string)($x + $y),
                    'vraag' => "$naam1 heeft $x $ondw. $naam2 heeft $y meer. Hoeveel heeft $naam2?"];
        }
        case 1: {
            $x = rand(3, $mx); $y = rand(1, $x - 1);
            return ['type' => 'invul', 'invoer' => 'getal', 'antwoord' => (string)($x - $y),
                    'vraag' => "$naam1 heeft $x $ondw. $naam1 geeft er $y weg. Hoeveel houdt $naam1 over?"];
        }
        case 2: {
            $x = rand(5, $mx - 3); $y = rand(1, min(5, $mx - $x));
            return ['type' => 'invul', 'invoer' => 'getal', 'antwoord' => (string)($x + $y),
                    'vraag' => "In de klas zijn $x leerlingen. Er komen er $y bij. Hoeveel zijn er nu?"];
        }
        default: {
            $x = rand(5, $mx); $y = rand(1, $x - 2);
            return ['type' => 'invul', 'invoer' => 'getal', 'antwoord' => (string)($x - $y),
                    'vraag' => "Er zijn $x kinderen. $y gaan naar huis. Hoeveel blijven er over?"];
        }
    }
}

function rPictogram(): array {
    $namen = ['Emma', 'Lena', 'Tom', 'Sara', 'Noor', 'Tim'];
    $items = [
        ['mv' => 'appels',    'emoji' => '🍎'],
        ['mv' => 'stickers',  'emoji' => '⭐'],
        ['mv' => 'koekjes',   'emoji' => '🍪'],
        ['mv' => 'bloemen',   'emoji' => '🌸'],
        ['mv' => 'potloden',  'emoji' => '✏️'],
        ['mv' => 'ballonnen', 'emoji' => '🎈'],
        ['mv' => 'ballen',    'emoji' => '🏀'],
    ];

    $n    = rand(3, 4);
    shuffle($namen);
    $item = $items[array_rand($items)];

    $pool = range(1, 10);
    shuffle($pool);
    $rijen = [];
    for ($i = 0; $i < $n; $i++) {
        $rijen[] = ['naam' => $namen[$i], 'aantal' => $pool[$i]];
    }

    $vraagType = rand(0, 3);

    switch ($vraagType) {
        case 0: {
            $sorted = $rijen; usort($sorted, fn($a, $b) => $b['aantal'] - $a['aantal']);
            return [
                'type'    => 'pictogram',
                'titel'   => "Hoeveel {$item['mv']} heeft iedereen?",
                'emoji'   => $item['emoji'],
                'rijen'   => $rijen,
                'vraag'   => "Wie heeft de meeste {$item['mv']}?",
                'invoer'  => 'keuze',
                'opties'  => array_column($rijen, 'naam'),
                'antwoord'=> $sorted[0]['naam'],
            ];
        }
        case 1: {
            $sorted = $rijen; usort($sorted, fn($a, $b) => $a['aantal'] - $b['aantal']);
            return [
                'type'    => 'pictogram',
                'titel'   => "Hoeveel {$item['mv']} heeft iedereen?",
                'emoji'   => $item['emoji'],
                'rijen'   => $rijen,
                'vraag'   => "Wie heeft de minste {$item['mv']}?",
                'invoer'  => 'keuze',
                'opties'  => array_column($rijen, 'naam'),
                'antwoord'=> $sorted[0]['naam'],
            ];
        }
        case 2: {
            $idx    = array_rand($rijen, 2);
            [$i, $j] = $idx;
            $som    = $rijen[$i]['aantal'] + $rijen[$j]['aantal'];
            return [
                'type'    => 'pictogram',
                'titel'   => "Hoeveel {$item['mv']} heeft iedereen?",
                'emoji'   => $item['emoji'],
                'rijen'   => $rijen,
                'vraag'   => "Hoeveel {$item['mv']} hebben {$rijen[$i]['naam']} en {$rijen[$j]['naam']} samen?",
                'invoer'  => 'getal',
                'antwoord'=> (string)$som,
            ];
        }
        default: {
            $sorted = $rijen; usort($sorted, fn($a, $b) => $b['aantal'] - $a['aantal']);
            $groot    = $sorted[0];
            $klein    = $sorted[count($sorted) - 1];
            $verschil = $groot['aantal'] - $klein['aantal'];
            return [
                'type'    => 'pictogram',
                'titel'   => "Hoeveel {$item['mv']} heeft iedereen?",
                'emoji'   => $item['emoji'],
                'rijen'   => $rijen,
                'vraag'   => "Hoeveel meer heeft {$groot['naam']} dan {$klein['naam']}?",
                'invoer'  => 'getal',
                'antwoord'=> (string)$verschil,
            ];
        }
    }
}
