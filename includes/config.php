<?php
require_once __DIR__ . '/apm.php';
apm_start('elena-delamarche-be');

define('DATA_DIR',     __DIR__ . '/../data');
define('USERS_DIR',    DATA_DIR . '/users');
define('PROGRESS_DIR', DATA_DIR . '/progress');
define('RATELIMIT_DIR', DATA_DIR . '/ratelimit');
define('STATS_DIR',    DATA_DIR . '/stats');

/* URL of a static file with its mtime as cache-buster */
function asset(string $path): string {
    return $path . '?v=' . (@filemtime(__DIR__ . '/../' . $path) ?: '1');
}

$CATEGORIES = [
    'arithmetic' => [
        'name'      => 'Rekenen',
        'color'     => 'oranje',
        'emoji'     => '🔢',
        'exercises' => [
            // 'since': de nieuwste reeks staat bovenaan het dashboard onder "Nieuw" (zie newExercises()).
            'true_false'    => ['name' => 'Waar of niet waar?', 'emoji' => '👍', 'since' => '2026-11-15'],
            'sign_choice'   => ['name' => '× of : ?',           'emoji' => '🤔', 'since' => '2026-11-15'],
            'repeated'      => ['name' => 'Herhaald optellen en aftrekken', 'emoji' => '🔁', 'since' => '2026-11-15'],
            'table_stories' => ['name' => 'Vraagstukjes × :',   'emoji' => '🧺', 'since' => '2026-11-15'],
            'count_on'      => ['name' => 'Tel verder en terug', 'emoji' => '🦘', 'since' => '2026-11-15'],
            'number_facts'  => ['name' => 'Buren en tientallen', 'emoji' => '🏘️', 'since' => '2026-11-15'],
            'place_value'   => ['name' => 'Tientallen en eenheden', 'emoji' => '🧱', 'since' => '2026-11-15'],
            'number_line'   => ['name' => 'Getallenas',         'emoji' => '📏', 'since' => '2026-11-15'],
            'ordinal'       => ['name' => 'Op welke plaats?',   'emoji' => '🥇', 'since' => '2026-11-15'],
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
        'color'     => 'oranje',
        'emoji'     => '🧠',
        'exercises' => [
            'more_less' => ['name' => 'Meer en minder', 'emoji' => '🔁'],
            'pictogram' => ['name' => 'Tabel lezen',    'emoji' => '📊'],
        ],
    ],
];

/* De nieuwste oefeningen: alles met een 'since' tot 30 dagen voor de recentste toevoeging.
   Zo staat er altijd een "Nieuw"-reeks bovenaan, zonder dat het van de datum van vandaag afhangt. */
function newExercises(array $categories): array {
    $all = [];
    foreach ($categories as $category) {
        foreach ($category['exercises'] as $key => $exercise) {
            if (!empty($exercise['since'])) $all[$key] = $exercise;
        }
    }
    if (!$all) return [];
    $latest = max(array_map(fn($e) => strtotime($e['since']), $all));
    $all = array_filter($all, fn($e) => strtotime($e['since']) >= $latest - 30 * 86400);
    uasort($all, fn($a, $b) => strcmp($b['since'], $a['since']));
    return $all;
}
