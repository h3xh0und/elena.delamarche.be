<?php

// [woord, lettergrepen, rijmgroep, beginletter]
const WOORDEN = [
    ['kat',     1, 'at',   'k'], ['mat',    1, 'at',   'm'], ['rat',    1, 'at',   'r'],
    ['lat',     1, 'at',   'l'], ['bat',    1, 'at',   'b'], ['gat',    1, 'at',   'g'],
    ['pat',     1, 'at',   'p'], ['vat',    1, 'at',   'v'], ['zat',    1, 'at',   'z'],
    ['dat',     1, 'at',   'd'], ['hat',    1, 'at',   'h'],
    ['vis',     1, 'is',   'v'], ['dis',    1, 'is',   'd'], ['mis',    1, 'is',   'm'],
    ['wis',     1, 'is',   'w'], ['lis',    1, 'is',   'l'], ['bis',    1, 'is',   'b'],
    ['bos',     1, 'os',   'b'], ['vos',    1, 'os',   'v'], ['pos',    1, 'os',   'p'],
    ['los',     1, 'os',   'l'], ['mos',    1, 'os',   'm'], ['dos',    1, 'os',   'd'],
    ['kom',     1, 'om',   'k'], ['bom',    1, 'om',   'b'], ['dom',    1, 'om',   'd'],
    ['som',     1, 'om',   's'], ['rom',    1, 'om',   'r'], ['tom',    1, 'om',   't'],
    ['kip',     1, 'ip',   'k'], ['lip',    1, 'ip',   'l'], ['tip',    1, 'ip',   't'],
    ['wip',     1, 'ip',   'w'], ['dip',    1, 'ip',   'd'], ['rip',    1, 'ip',   'r'],
    ['hek',     1, 'ek',   'h'], ['pek',    1, 'ek',   'p'], ['rek',    1, 'ek',   'r'],
    ['dek',     1, 'ek',   'd'], ['lek',    1, 'ek',   'l'], ['tek',    1, 'ek',   't'],
    ['pen',     1, 'en',   'p'], ['hen',    1, 'en',   'h'], ['ven',    1, 'en',   'v'],
    ['ten',     1, 'en',   't'], ['ren',    1, 'en',   'r'], ['ben',    1, 'en',   'b'],
    ['dag',     1, 'ag',   'd'], ['lag',    1, 'ag',   'l'], ['zag',    1, 'ag',   'z'],
    ['mag',     1, 'ag',   'm'], ['bag',    1, 'ag',   'b'], ['tag',    1, 'ag',   't'],
    ['zon',     1, 'on',   'z'], ['ton',    1, 'on',   't'], ['bon',    1, 'on',   'b'],
    ['don',     1, 'on',   'd'], ['kon',    1, 'on',   'k'], ['mon',    1, 'on',   'm'],
    ['bal',     1, 'al',   'b'], ['dal',    1, 'al',   'd'], ['hal',    1, 'al',   'h'],
    ['mal',     1, 'al',   'm'], ['pal',    1, 'al',   'p'], ['kal',    1, 'al',   'k'],
    ['tak',     1, 'ak',   't'], ['dak',    1, 'ak',   'd'], ['hak',    1, 'ak',   'h'],
    ['mak',     1, 'ak',   'm'], ['pak',    1, 'ak',   'p'], ['rak',    1, 'ak',   'r'],
    ['zak',     1, 'ak',   'z'], ['bak',    1, 'ak',   'b'], ['lak',    1, 'ak',   'l'],
    ['bed',     1, 'ed',   'b'], ['red',    1, 'ed',   'r'], ['wed',    1, 'ed',   'w'],
    ['led',     1, 'ed',   'l'], ['hed',    1, 'ed',   'h'],
    ['pot',     1, 'ot',   'p'], ['bot',    1, 'ot',   'b'], ['dot',    1, 'ot',   'd'],
    ['lot',     1, 'ot',   'l'], ['rot',    1, 'ot',   'r'], ['got',    1, 'ot',   'g'],
    ['bus',     1, 'us',   'b'], ['pus',    1, 'us',   'p'], ['mus',    1, 'us',   'm'],
    ['dus',     1, 'us',   'd'], ['zus',    1, 'us',   'z'], ['rus',    1, 'us',   'r'],
    ['tas',     1, 'as',   't'], ['das',    1, 'as',   'd'], ['gas',    1, 'as',   'g'],
    ['las',     1, 'as',   'l'], ['was',    1, 'as',   'w'], ['jas',    1, 'as',   'j'],
    ['vas',     1, 'as',   'v'], ['mas',    1, 'as',   'm'],
    ['net',     1, 'et',   'n'], ['jet',    1, 'et',   'j'], ['pet',    1, 'et',   'p'],
    ['vet',     1, 'et',   'v'], ['set',    1, 'et',   's'], ['wet',    1, 'et',   'w'],
    ['met',     1, 'et',   'm'], ['let',    1, 'et',   'l'],
    ['bit',     1, 'it',   'b'], ['fit',    1, 'it',   'f'], ['hit',    1, 'it',   'h'],
    ['kit',     1, 'it',   'k'], ['pit',    1, 'it',   'p'], ['sit',    1, 'it',   's'],
    ['wit',     1, 'it',   'w'], ['lit',    1, 'it',   'l'],
    ['rug',     1, 'ug',   'r'], ['bug',    1, 'ug',   'b'], ['dug',    1, 'ug',   'd'],
    ['mug',     1, 'ug',   'm'], ['pug',    1, 'ug',   'p'], ['tug',    1, 'ug',   't'],
    ['raam',    1, 'aam',  'r'], ['naam',   1, 'aam',  'n'], ['laam',   1, 'aam',  'l'],
    ['baam',    1, 'aam',  'b'], ['taam',   1, 'aam',  't'],
    ['boom',    1, 'oom',  'b'], ['room',   1, 'oom',  'r'], ['loom',   1, 'oom',  'l'],
    ['zoom',    1, 'oom',  'z'], ['doom',   1, 'oom',  'd'],
    ['roos',    1, 'oos',  'r'], ['boos',   1, 'oos',  'b'], ['loos',   1, 'oos',  'l'],
    ['moos',    1, 'oos',  'm'], ['poos',   1, 'oos',  'p'],
    ['maan',    1, 'aan',  'm'], ['laan',   1, 'aan',  'l'], ['baan',   1, 'aan',  'b'],
    ['taan',    1, 'aan',  't'], ['zaan',   1, 'aan',  'z'],
    // 2-lettergrepen
    ['appel',   2, 'el',   'a'], ['vogel',  2, 'el',   'v'], ['tafel',  2, 'el',   't'],
    ['lepel',   2, 'el',   'l'], ['wortel', 2, 'el',   'w'], ['zetel',  2, 'el',   'z'],
    ['winkel',  2, 'el',   'w'], ['engel',  2, 'el',   'e'], ['sleutel',2, 'el',   's'],
    ['boter',   2, 'er',   'b'], ['water',  2, 'er',   'w'], ['winter', 2, 'er',   'w'],
    ['zomer',   2, 'er',   'z'], ['hamer',  2, 'er',   'h'], ['peper',  2, 'er',   'p'],
    ['tijger',  2, 'er',   't'], ['kikker', 2, 'er',   'k'], ['vlinder',2, 'er',   'v'],
    ['suiker',  2, 'er',   's'], ['bloem',  1, 'oem',  'b'], ['trein',  1, 'ein',  't'],
    ['fiets',   1, 'iets', 'f'], ['kaas',   1, 'aas',  'k'], ['paard',  1, 'aard', 'p'],
    ['konijn',  2, 'ijn',  'k'], ['banaan', 2, 'aan',  'b'], ['gitaar', 2, 'aar',  'g'],
    ['molen',   2, 'olen', 'm'], ['toren',  2, 'oren', 't'], ['robot',  2, 'ot',   'r'],
    ['auto',    2, 'o',    'a'], ['piano',  3, 'o',    'p'], ['leeuw',  1, 'eeuw', 'l'],
    // 3-lettergrepen
    ['olifant',  3, 'ant',  'o'], ['ananas',   3, 'as',   'a'],
    ['telefoon', 3, 'oon',  't'], ['paraplu',  3, 'u',    'p'],
    ['kalender', 3, 'er',   'k'], ['kangaroe', 3, 'oe',   'k'],
    ['chocola',  3, 'a',    'c'], ['computer', 3, 'er',   'c'],
    ['elastiek', 3, 'iek',  'e'], ['spaghetti',3, 'i',    's'],
];

function genereerTaalOefening(string $type): array {
    return match ($type) {
        'rijmwoorden'    => tRijmwoorden(),
        'lettergrepen'   => tLettergrepen(),
        'hoort_niet_bij' => tHoortNietBij(),
        'tegengestelde'  => tTegengestelde(),
        default          => tRijmwoorden(),
    };
}

function tRijmwoorden(): array {
    // Collect words grouped by rhyme
    $groepen = [];
    foreach (WOORDEN as $w) {
        $groepen[$w[2]][] = $w[0];
    }
    // Pick a group with ≥ 2 words
    $geldige = array_filter($groepen, fn($g) => count($g) >= 2);
    if (!$geldige) return tRijmwoorden();
    $groepSleutels = array_keys($geldige);
    shuffle($groepSleutels);
    $groep = $groepSleutels[0];
    $rijmers = $geldige[$groep];
    shuffle($rijmers);
    $doelwoord = $rijmers[0];
    $goedAntwoord = $rijmers[1];

    // Two non-rhyming distractors
    $andereGroepen = array_filter($groepen, fn($g) => !in_array($doelwoord, $g) && !in_array($goedAntwoord, $g));
    $afleidersPool = array_merge(...array_values($andereGroepen));
    shuffle($afleidersPool);
    $afleiders = array_slice(array_unique($afleidersPool), 0, 2);
    if (count($afleiders) < 2) return tRijmwoorden();

    $opties = array_merge([$goedAntwoord], $afleiders);
    shuffle($opties);

    return [
        'type'     => 'keuze',
        'label'    => "Welk woord rijmt op:",
        'vraag'    => $doelwoord,
        'opties'   => $opties,
        'antwoord' => $goedAntwoord,
    ];
}

function tLettergrepen(): array {
    $woorden = WOORDEN;
    shuffle($woorden);
    $w = $woorden[0];
    $antwoord = (string)$w[1];
    $maxSylls = 3;
    $opties = array_map('strval', range(1, $maxSylls));

    return [
        'type'     => 'keuze',
        'label'    => 'Hoeveel lettergrepen heeft dit woord?',
        'vraag'    => $w[0],
        'opties'   => $opties,
        'antwoord' => $antwoord,
        'hint'     => 'Klap in je handen voor elke lettergreep.',
    ];
}

/* ── Hoort er niet bij ────────────────────────────────── */

const WOORDCATEGORIEEN = [
    'dieren'     => ['kat','hond','vis','vogel','konijn','koe','paard','muis','beer',
                     'aap','olifant','tijger','kikker','vlinder','leeuw','geit','eend'],
    'fruit'      => ['appel','peer','banaan','druif','aardbei','kers','pruim',
                     'citroen','mango','ananas','kiwi','meloen','perzik'],
    'groente'    => ['wortel','tomaat','sla','ui','aardappel','paprika',
                     'komkommer','broccoli','courgette','prei','biet'],
    'kleuren'    => ['rood','blauw','groen','geel','paars','oranje',
                     'roze','wit','zwart','bruin','grijs','beige'],
    'meubels'    => ['tafel','stoel','bed','kast','bank','bureau','lamp','spiegel'],
    'kleding'    => ['jas','broek','shirt','sok','schoen','hoed','sjaal','muts','trui','jurk'],
    'voertuigen' => ['auto','fiets','trein','bus','vliegtuig','boot','motor','tram','vrachtwagen'],
    'lichaam'    => ['hand','voet','oor','oog','neus','mond','arm','been','hoofd','rug','buik'],
    'eten'       => ['brood','kaas','melk','ei','soep','rijst','pasta','boter','chips','cake'],
    'speelgoed'  => ['bal','pop','blok','puzzel','trein','knuffel','fiets'],
    'weer'       => ['regen','zon','wind','sneeuw','hagel','wolken','storm','mist'],
    'natuur'     => ['boom','bloem','gras','blad','steen','zand','water','berg','woud'],
];

function tHoortNietBij(): array {
    $cats = WOORDCATEGORIEEN;
    $catSleutels = array_keys($cats);

    // Kies een hoofdcategorie
    shuffle($catSleutels);
    $hoofdCat = $catSleutels[0];
    $hoofdPool = $cats[$hoofdCat];
    if (count($hoofdPool) < 3) return tHoortNietBij(); // fallback

    // 3 woorden uit de hoofdcategorie
    shuffle($hoofdPool);
    $goede = array_slice($hoofdPool, 0, 3);

    // 1 woord uit een andere categorie (het vreemde eend)
    $andereCat = $catSleutels[1];
    $anderePool = array_values(array_diff($cats[$andereCat], $hoofdPool));
    if (!$anderePool) return tHoortNietBij();
    shuffle($anderePool);
    $vreemde = $anderePool[0];

    $opties = array_merge($goede, [$vreemde]);
    shuffle($opties);

    return [
        'type'     => 'keuze',
        'label'    => 'Welk woord hoort er NIET bij?',
        'vraag'    => '',
        'opties'   => $opties,
        'antwoord' => $vreemde,
    ];
}

/* ── Tegengestelde ────────────────────────────────────── */

const TEGENSTELLINGEN = [
    ['groot','klein'],   ['snel','traag'],    ['warm','koud'],
    ['oud','jong'],      ['blij','droevig'],  ['hard','zacht'],
    ['hoog','laag'],     ['lang','kort'],     ['licht','donker'],
    ['vol','leeg'],      ['open','dicht'],    ['schoon','vuil'],
    ['sterk','zwak'],    ['zwaar','licht'],   ['dik','dun'],
    ['nat','droog'],     ['nieuw','oud'],     ['voor','achter'],
    ['links','rechts'],  ['boven','onder'],   ['vroeg','laat'],
    ['veel','weinig'],   ['mooi','lelijk'],   ['luid','stil'],
    ['aan','uit'],       ['dag','nacht'],     ['zomer','winter'],
    ['vrolijk','verdrietig'], ['gevaarlijk','veilig'], ['makkelijk','moeilijk'],
    ['binnen','buiten'], ['smal','breed'],    ['scherp','bot'],
    ['diep','ondiep'],   ['helder','troebel'],['fijn','ruw'],
];

function tTegengestelde(): array {
    $paren = TEGENSTELLINGEN;
    shuffle($paren);
    $paar = $paren[0];
    // Willekeurig: vraag het eerste of tweede woord
    [$woord, $goed] = rand(0, 1) ? $paar : array_reverse($paar);

    // 3 afleiders: andere woorden uit de lijst (niet het correcte antwoord)
    $alleMogelijkheden = [];
    foreach ($paren as $p) {
        $alleMogelijkheden[] = $p[0];
        $alleMogelijkheden[] = $p[1];
    }
    $pool = array_values(array_unique(array_filter(
        $alleMogelijkheden,
        fn($w) => $w !== $woord && $w !== $goed
    )));
    shuffle($pool);
    $afleiders = array_slice($pool, 0, 3);

    $opties = array_merge([$goed], $afleiders);
    shuffle($opties);

    return [
        'type'     => 'keuze',
        'label'    => 'Wat is het tegengestelde van:',
        'vraag'    => $woord,
        'opties'   => $opties,
        'antwoord' => $goed,
    ];
}
