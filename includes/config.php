<?php
require_once __DIR__ . '/apm.php';
apm_start('elena-delamarche-be');

define('DATA_DIR',     __DIR__ . '/../data');
define('USERS_DIR',    DATA_DIR . '/users');
define('PROGRESS_DIR', DATA_DIR . '/progress');
define('RATELIMIT_DIR', DATA_DIR . '/ratelimit');

/* URL of a static file with its mtime as cache-buster */
function asset(string $path): string {
    return $path . '?v=' . (@filemtime(__DIR__ . '/../' . $path) ?: '1');
}

$CATEGORIES = [
    'arithmetic' => [
        'name'      => 'Rekenen',
        'color'     => 'blauw',
        'emoji'     => '🔢',
        'exercises' => [
            'addition'      => ['name' => 'Optellen',          'emoji' => '➕'],
            'subtraction'   => ['name' => 'Aftrekken',         'emoji' => '➖'],
            'mixed'         => ['name' => 'Gemengd',           'emoji' => '🔄'],
            'times_tables'  => ['name' => 'Maaltafels',        'emoji' => '✖️'],
            'division'      => ['name' => 'Delen',             'emoji' => '➗'],
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
            'digital_clock' => ['name' => 'Digitale klok',     'emoji' => '📟'],
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
        'color'     => 'paars',
        'emoji'     => '🧠',
        'exercises' => [
            'more_less' => ['name' => 'Meer en minder', 'emoji' => '🔁'],
            'pictogram' => ['name' => 'Tabel lezen',    'emoji' => '📊'],
        ],
    ],
];

/* Dashboard layout: smaller "worlds" (display only; $CATEGORIES stays the source
   for names, emoji and the exercise generators). Every exercise key appears once. */
$WORLDS = [
    'plus_min' => ['name' => 'Plus en min',   'emoji' => '➕', 'color' => 'blauw',
                   'exercises' => ['addition', 'subtraction', 'mixed', 'three_numbers', 'missing', 'commutative']],
    'maal'     => ['name' => 'Maal en delen', 'emoji' => '✖️', 'color' => 'roze',
                   'exercises' => ['times_tables', 'division', 'half']],
    'getallen' => ['name' => 'Getallen',      'emoji' => '🔢', 'color' => 'turkoois',
                   'exercises' => ['splitting', 'compare', 'ordering', 'neighbours', 'jumps', 'number_snake']],
    'klok'     => ['name' => 'Klok en geld',  'emoji' => '🕐', 'color' => 'geel',
                   'exercises' => ['clock', 'digital_clock', 'money']],
    'woorden'  => ['name' => 'Woordsommen',   'emoji' => '💬', 'color' => 'groen',
                   'exercises' => ['word_problems']],
    'logisch'  => ['name' => 'Logisch denken','emoji' => '🧠', 'color' => 'paars',
                   'exercises' => ['more_less', 'pictogram']],
];

/* Name, emoji and world colour of one exercise key, or null */
function exerciseInfo(string $key): ?array {
    global $CATEGORIES, $WORLDS;
    foreach ($CATEGORIES as $category) {
        if (isset($category['exercises'][$key])) {
            $info = $category['exercises'][$key];
            $info['color'] = $category['color'];
            foreach ($WORLDS as $world) {
                if (in_array($key, $world['exercises'], true)) { $info['color'] = $world['color']; break; }
            }
            return $info;
        }
    }
    return null;
}
