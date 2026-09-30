# elena.delamarche.be

Oefenwebsite voor kinderen uit het 1ste leerjaar (6–7 jaar, Vlaams onderwijs). **Enkel rekenoefeningen** — taaloefeningen zijn bewust verwijderd en komen niet terug.

## Stack

- Plain PHP (geen framework, geen Composer), vanilla JS, één CSS-bestand. Geen build-stap.
- Opslag in flat files onder `data/` (gitignored, via `.htaccess` afgeschermd). Geen database.
- Hosting: Apache shared hosting. **Elke push naar `main` deployt meteen via FTP** (`.github/workflows/deploy.yml`). Nieuwe bestanden die niet publiek mogen staan, toevoegen aan de `exclude`-lijst daar.
- De repo is publiek op GitHub: geen geheimen of persoonsgegevens committen.
- De host adverteert HTTP/3 (`Alt-Svc: h3`) maar antwoordt er niet op; daardoor bleef de eerste klik hangen. `.htaccess` zet `Alt-Svc: clear`, maar de host overschrijft die header nog; HTTP/3 moet bij de hostingprovider (DIGI) uitgezet of gerepareerd worden. Controle: `curl -4 --http3-only -m 8 https://elena.delamarche.be/` moet antwoorden of de `alt-svc`-header moet weg zijn.

## Lokaal draaien

Er is geen PHP lokaal geïnstalleerd; gebruik een container:

```
mkdir -p data/users data/progress data/ratelimit
podman run --rm -p 8080:8080 -v "$PWD":/app:Z -w /app docker.io/library/php:8.3-cli php -S 0.0.0.0:8080
```

Er zijn geen tests of linters. Minimaal `php -l` draaien op gewijzigde PHP-bestanden.

## Hoe een oefening werkt

1. `includes/config.php` — `$CATEGORIES` bepaalt wat op het dashboard staat (key, naam, emoji).
2. `api/exercise.php?cat=<key>` — roept een generator aan in `includes/exercises/` en bewaart de oefening (met antwoord) in de sessie; het antwoord gaat **nooit** naar de client.
3. `assets/js/app.js` — `showExercise()` kiest op `type` een `setup…()`-functie die de oefening tekent.
4. `api/answer.php` — vergelijkt het antwoord met de sessie en bewaart de voortgang.

Oefening-types (`type`): `invul` (numpad), `keuze`, `ordenen`, `klok`, `rekenslang`, `splitsing`, `pictogram`.

Nieuwe oefening toevoegen: entry in `$CATEGORIES`, generator + `match`-tak in `arithmetic.php`, en alleen als er een nieuwe weergave nodig is ook een `setup…()` in `app.js` plus CSS.

## Conventies

- UI-teksten, CSS-klassen, element-id's en JSON-velden zijn **Nederlands** (`vraag`, `antwoord`, `opties`, `verborgen`). PHP/JS-identifiers en oefening-keys zijn Engels (`splitting`, `rSplitting`).
- Oefening-keys niet hernoemen: de voortgang per kind is erop opgeslagen. `flatfile.php` bevat mappings van oude Nederlandse keys.
- Klokoefeningen (`clock`, `digital_clock`) gebruiken de instelling `clock_level`. Tijd in woorden volgens de Vlaamse manier (`_clockWords`: "10 voor half 4"); digitaal schrijven gebeurt met een uur- en minutenvakje (`klok_invoer: 'tijd'`).
- Getallen respecteren altijd de instelling "tot welk getal" (`$maxNumber`: 10/20/30/50/100). Maaltafels hebben een eigen instelling (`tables_max`, 2–10): tafels van 1 t.e.m. die waarde, telkens 1–10 ×.
- Per-kind instellingen: lezen/schrijven in `flatfile.php`, actie in `api/settings.php`, kaart in `settings.php`.
- Didactiek volgt de Vlaamse methode. Splitsingen zijn een **driehoek**: het geheel bovenaan, één deel onderaan gegeven, de andere hoek invullen — geen som zoals `8 = 3 + ?`.
- Doelgroep is 6–7 jaar, vaak op tablet: grote tikdoelen, weinig tekst, numpad i.p.v. toetsenbord. Controleer ook de landscape-layout.
- CSS/JS altijd laden via `asset('pad')` (in `config.php`): die voegt de mtime toe als cache-buster.

## UI voor kinderen (6–7 jaar, tablet/telefoon/computer)

- Tikdoelen minstens ~64px (≈2 cm), met ruimte ertussen. Alleen tikken, geen slepen of dubbeltikken.
- Weinig tekst, groot en vet; emoji + woord samen. Geen hoofdletters-labels of kleine grijze tekst voor belangrijke info.
- Het getypte antwoord verschijnt ín de oefening (element met klasse `antwoord-live`), niet in een apart veld.
- Feedback meteen en vriendelijk: juist → groen + sterretjes, gaat vanzelf verder; fout → oranje (niet rood), juiste antwoord tonen, kind tikt zelf op "Verder". Geen straf, geen tijdsdruk buiten de Sneltest.
- Oefeningen in rondes van 10 met voortgangsbalk en eindscherm met sterren.
- Geluid staat standaard uit (klaslokaal), aan te zetten met 🔇/🔊; wordt onthouden in `localStorage`.
- Een oefenscherm moet zonder scrollen passen op telefoon (staand én liggend), iPad en desktop. Liggend: vraag links, numpad rechts.
- Respecteer `prefers-reduced-motion`; `:focus-visible` blijft zichtbaar voor toetsenbordgebruik.
- Beveiliging intact houden: CSRF-check op elke POST, `esc()` / `htmlspecialchars()` bij output, CSP staat geen externe bronnen toe.
