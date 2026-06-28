<?php
define('DATA_DIR',     __DIR__ . '/../data');
define('USERS_DIR',    DATA_DIR . '/users');
define('PROGRESS_DIR', DATA_DIR . '/progress');
define('RATELIMIT_DIR', DATA_DIR . '/ratelimit');

$CATEGORIES = [
    'arithmetic' => [
        'name'      => 'Rekenen',
        'color'     => 'oranje',
        'emoji'     => '🔢',
        'exercises' => [
            'addition'      => ['name' => 'Optellen',          'emoji' => '➕'],
            'subtraction'   => ['name' => 'Aftrekken',         'emoji' => '➖'],
            'mixed'         => ['name' => 'Gemengd',           'emoji' => '🔄'],
            'three_numbers' => ['name' => 'Drie getallen',     'emoji' => '3️⃣'],
            'splitting'     => ['name' => 'Splitsen',          'emoji' => '✂️'],
            'missing'       => ['name' => 'Ontbrekend getal',  'emoji' => '❓'],
            'commutative'   => ['name' => 'Wisselsom',         'emoji' => '🔁'],
            'half'          => ['name' => 'De helft',          'emoji' => '½'],
            'compare'       => ['name' => 'Vergelijken',       'emoji' => '⚖️'],
            'ordering'      => ['name' => 'Ordenen',           'emoji' => '📊'],
            'neighbours'    => ['name' => 'Tellen & buren',    'emoji' => '🔢'],
            'jumps'         => ['name' => 'Sprongen',          'emoji' => '🐸'],
            'clock'         => ['name' => 'Kloklezen',         'emoji' => '🕐'],
            'money'         => ['name' => 'Geld',              'emoji' => '💶'],
            'number_snake'  => ['name' => 'Rekenslang',        'emoji' => '🐍'],
        ],
    ],
    'word_problems' => [
        'name'      => 'Woordsommen',
        'color'     => 'groen',
        'emoji'     => '💬',
        'exercises' => [
            'word_problems' => ['name' => 'Woordsommen', 'emoji' => '💬'],
        ],
    ],
    'logical_thinking' => [
        'name'      => 'Logisch denken',
        'color'     => 'oranje',
        'emoji'     => '🧠',
        'exercises' => [
            'more_less' => ['name' => 'Meer en minder', 'emoji' => '🔁'],
            'pictogram' => ['name' => 'Tabel lezen',    'emoji' => '📊'],
        ],
    ],
];
