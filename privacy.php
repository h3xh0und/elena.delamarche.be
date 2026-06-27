<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Privacyverklaring – Oefenwebsite</title>
<link rel="stylesheet" href="assets/css/fonts.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inhoud">
        <a href="<?= isLoggedIn() ? 'dashboard.php' : 'index.php' ?>" class="terug-link" style="font-weight:800">← Terug</a>
        <span style="font-weight:800">🔒 Privacyverklaring</span>
        <span></span>
    </div>
</header>

<main class="inst-main" style="max-width:720px">

    <div class="inst-kaart">
        <h2 class="inst-titel">Wat is deze website?</h2>
        <p class="inst-omschrijving">
            Deze oefenwebsite laat kinderen uit het eerste leerjaar rekenen en taalleerstof oefenen.
            Ze is gebouwd als een persoonlijk project en is volledig gratis en zonder reclame.
            De broncode is <a href="https://github.com/h3xh0und/elena.delamarche.be" target="_blank" rel="noopener noreferrer" style="color:var(--primair)">open source op GitHub</a>.
        </p>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">Welke gegevens bewaren we?</h2>
        <p class="inst-omschrijving">We bewaren zo min mogelijk:</p>
        <ul class="inst-omschrijving" style="margin-top:.5rem;padding-left:1.25rem;line-height:2">
            <li>Een <strong>gebruikersnaam</strong> — dit hoeft geen echte naam te zijn.</li>
            <li>Een <strong>pincode van 4 cijfers</strong> — opgeslagen als onleesbare hash (nooit als klaar getal).</li>
            <li><strong>Oefenresultaten</strong>: welke oefeningen gedaan, hoeveel correct.</li>
        </ul>
        <p class="inst-omschrijving" style="margin-top:.75rem">
            We bewaren <strong>geen</strong> e-mailadressen, foto's, locatiegegevens, telefoonnummers
            of andere persoonsgegevens.
        </p>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">Waarvoor worden de gegevens gebruikt?</h2>
        <p class="inst-omschrijving">
            Uitsluitend om bij te houden welke oefeningen een kind al heeft gedaan,
            zodat de voortgang bewaard blijft. Er is geen reclame, geen profilering
            en geen verkoop of doorgave van gegevens aan derden.
        </p>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">Wie heeft toegang?</h2>
        <p class="inst-omschrijving">
            Alleen het kind zelf — via de pincode. Er is geen beheerdersscherm
            dat individuele voortgang per kind toont. De beheerder heeft enkel
            technische toegang tot de serverbestanden.
        </p>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">Cookies</h2>
        <p class="inst-omschrijving">
            We gebruiken één cookie: een <strong>sessie-cookie</strong> die automatisch
            verdwijnt wanneer je de browser sluit. Geen tracking-cookies, geen
            advertentiecookies, geen analytics.
        </p>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">Derde partijen</h2>
        <p class="inst-omschrijving">
            Er worden <strong>geen gegevens gedeeld met derde partijen</strong>.
            Het lettertype (Nunito) is lokaal gehost — er worden geen externe lettertypeservers
            aangesproken.
        </p>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">🧒 GDPR &amp; kinderen</h2>
        <p class="inst-omschrijving">
            Kinderen in het eerste leerjaar zijn jonger dan 13 jaar. Conform
            <strong>GDPR artikel 8</strong> raden we aan dat een ouder of leerkracht
            aanwezig is bij het aanmaken van een account. Omdat er geen e-mailadres
            of echte naam vereist is, en de gegevens uitsluitend voor educatief gebruik
            worden bewaard, is de verzamelde informatie tot het strikt noodzakelijke
            beperkt (dataminimalisatie).
        </p>
        <p class="inst-omschrijving" style="margin-top:.75rem">
            Ouders of voogden kunnen op elk moment vragen om de gegevens van hun kind
            te verwijderen — stuur daarvoor een e-mail naar
            <a href="mailto:hello@jonasdlm.be" style="color:var(--primair)">hello@jonasdlm.be</a>.
        </p>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">Hoe lang bewaren we de gegevens?</h2>
        <p class="inst-omschrijving">
            Gegevens worden bewaard zolang de website actief is.
            Na een verzoek tot verwijdering worden de gegevens zo snel mogelijk gewist.
        </p>
    </div>

    <div class="inst-kaart">
        <h2 class="inst-titel">Contact</h2>
        <p class="inst-omschrijving">
            Vragen, opmerkingen of een verwijderingsverzoek?<br>
            <a href="mailto:hello@jonasdlm.be" style="color:var(--primair)">hello@jonasdlm.be</a>
        </p>
        <p class="inst-omschrijving" style="margin-top:.5rem;color:var(--tekst-zacht);font-size:.9rem">
            Verantwoordelijke: Jonas Delamarche · Laatste aanpassing: juni 2025
        </p>
    </div>

</main>

<footer class="site-footer">
    <a href="privacy.php">Privacyverklaring</a>
    ·
    <a href="https://github.com/h3xh0und/elena.delamarche.be" target="_blank" rel="noopener noreferrer">Open source op GitHub ↗</a>
</footer>

</body>
</html>
