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
            'optellen_10'   => ['naam' => 'Optellen tot 10',    'emoji' => '➕'],
            'optellen_20'   => ['naam' => 'Optellen tot 20',    'emoji' => '➕'],
            'aftrekken_20'  => ['naam' => 'Aftrekken tot 20',   'emoji' => '➖'],
            'gemengd'       => ['naam' => 'Gemengd rekenen',    'emoji' => '🔄'],
            'drie_optellen' => ['naam' => 'Drie getallen',      'emoji' => '3️⃣'],
            'tellen_buren'  => ['naam' => 'Tellen & buren',     'emoji' => '🔢'],
            'splitsen'      => ['naam' => 'Splitsen',           'emoji' => '✂️'],
            'de_helft'      => ['naam' => 'De helft',           'emoji' => '½'],
            'vergelijken'   => ['naam' => 'Vergelijken',        'emoji' => '⚖️'],
            'ordenen'       => ['naam' => 'Ordenen',            'emoji' => '📊'],
            'klok'          => ['naam' => 'Kloklezen',          'emoji' => '🕐'],
            'sprongen_2'    => ['naam' => 'Sprongen van 2',     'emoji' => '🐸'],
            'rekenslang'    => ['naam' => 'Rekenslang',         'emoji' => '🐍'],
        ],
    ],
    'taal' => [
        'naam'  => 'Taal',
        'kleur' => 'groen',
        'emoji' => '📚',
        'oefeningen' => [
            'rijmwoorden'    => ['naam' => 'Rijmwoorden',        'emoji' => '🎵'],
            'lettergrepen'   => ['naam' => 'Lettergrepen',       'emoji' => '📝'],
            'hoort_niet_bij' => ['naam' => 'Hoort er niet bij',  'emoji' => '🚫'],
            'tegengestelde'  => ['naam' => 'Tegengestelde',      'emoji' => '↔️'],
        ],
    ],
];
