<?php
define('DATA_DIR',     __DIR__ . '/../data');
define('USERS_DIR',    DATA_DIR . '/users');
define('PROGRESS_DIR', DATA_DIR . '/progress');

$CATEGORIEEN = [
    'rekenen' => [
        'naam'  => 'Rekenen',
        'kleur' => 'oranje',
        'emoji' => '🔢',
        'oefeningen' => [
            'optellen'       => ['naam' => 'Optellen',            'emoji' => '➕'],
            'aftrekken'      => ['naam' => 'Aftrekken',           'emoji' => '➖'],
            'gemengd'        => ['naam' => 'Gemengd',             'emoji' => '🔄'],
            'drie_optellen'  => ['naam' => 'Drie getallen',       'emoji' => '3️⃣'],
            'splitsen'       => ['naam' => 'Splitsen',            'emoji' => '✂️'],
            'ontbrekend'     => ['naam' => 'Ontbrekend getal',    'emoji' => '❓'],
            'wisselsom'      => ['naam' => 'Wisselsom',           'emoji' => '🔁'],
            'de_helft'       => ['naam' => 'De helft',            'emoji' => '½'],
            'vergelijken'    => ['naam' => 'Vergelijken',         'emoji' => '⚖️'],
            'ordenen'        => ['naam' => 'Ordenen',             'emoji' => '📊'],
            'tellen_buren'   => ['naam' => 'Tellen & buren',      'emoji' => '🔢'],
            'sprongen'       => ['naam' => 'Sprongen',            'emoji' => '🐸'],
            'klok'           => ['naam' => 'Kloklezen',           'emoji' => '🕐'],
            'geld'           => ['naam' => 'Geld',                'emoji' => '💶'],
            'rekenslang'     => ['naam' => 'Rekenslang',          'emoji' => '🐍'],
        ],
    ],
];
