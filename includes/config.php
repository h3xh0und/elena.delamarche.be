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

/* Oefeningen waarbij het kind een kladblad met tientalstaven en eenheidsblokjes kan openen */
const BLOCKS_EXERCISES = ['no_bridge', 'addition', 'subtraction', 'mixed', 'three_numbers', 'missing'];

$CATEGORIES = [
    'arithmetic' => [
        'name'      => 'Rekenen',
        'color'     => 'blauw',
        'emoji'     => '🔢',
        'exercises' => [
            // 'since': de nieuwste reeks staat bovenaan het dashboard onder "Nieuw" (zie newExercises()).
            'no_bridge'     => ['name' => 'Tot 100 zonder brug', 'emoji' => '🟩', 'since' => '2026-11-25'],
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
        'color'     => 'paars',
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

/* Dashboard layout: smaller "worlds" (display only; $CATEGORIES stays the source
   for names, emoji and the exercise generators). Every exercise key appears once. */
$WORLDS = [
    'plus_min' => ['name' => 'Plus en min',   'emoji' => '➕', 'color' => 'blauw',
                   'exercises' => ['no_bridge', 'addition', 'subtraction', 'mixed', 'three_numbers', 'missing',
                                   'commutative', 'true_false']],
    'maal'     => ['name' => 'Maal en delen', 'emoji' => '✖️', 'color' => 'roze',
                   'exercises' => ['times_tables', 'division', 'repeated', 'sign_choice', 'table_stories', 'half']],
    'getallen' => ['name' => 'Getallen',      'emoji' => '🔢', 'color' => 'turkoois',
                   'exercises' => ['place_value', 'number_facts', 'splitting', 'compare', 'ordering', 'neighbours']],
    'tellen'   => ['name' => 'Tellen en springen', 'emoji' => '🦘', 'color' => 'hemel',
                   'exercises' => ['count_on', 'jumps', 'number_line', 'ordinal', 'number_snake']],
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

/* "Rekenavontuur" as rainbow letters (each letter its own colour, see .regenboog) */
function rainbow(string $text): string {
    $out = '';
    foreach (mb_str_split($text) as $ch) $out .= '<span>' . htmlspecialchars($ch) . '</span>';
    return '<span class="regenboog" aria-label="' . htmlspecialchars($text) . '">' . $out . '</span>';
}
