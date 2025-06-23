# Webmaster DPL CMS

Teknisk dokumentation til videre udvikling på dpl-cms.

## Udvikling i lokalt udviklingsmiljø

Installation af lokalt udviklingsmiljø kræver installation af en Docker klient f.eks. Docker Desktop eller Orbstack.

Reload har i DPL-CMS projektet lavet et docker miljø, som køres med Task https://taskfile.dev/.

```sh
git clone https://github.com/danskernesdigitalebibliotek/dpl-cms.git
cd dpl-cms
# bygger et helt nyt Dpl Cms site og henter DPL React
task dev:reset
# sætter bruger 1 adgangskode
task dev:cli -- drush upwd admin etHeltNytUniktPassword
# list kørende docker tjenester
docker ps
```

I "docker ps" listen vil der være en Varnish docker for Dpl Cms, som er den man skal tilgå for at se sitet i en browser.
Find varnish under Images hedder f.eks. dpl-cms-varnish og tjek porten under PORTS f.eks. 80/tcp, 8443/tcp, 0.0.0.0:32770->8080/tcp, [::]:32770->8080/tcp.
Sitet kan tilgåes i en browser på f.eks. localhost:32770.

Hvis man ønsker særlige udgaver af Dpl Cms kan man bruge følgende.

```sh
# lister releases
git tag -l
# laver en ny branch udfra release tag
git checkout -b 2025-13-0
```

Der er lavet en masse kommandoer til f.eks. at starte xdebug, køre drush kommandoer osv. De kan ses i Taskfile.yml i roden af Dpl Cms projektet.

```sh
# kør drush kommando
task dev:cli -- drush ...
# slå dev moduler og indstillinger til
task dev:enable-dev-tools
# slå xdebug til
task dev:enable-xdebug
# stop og start site
task dev:stop
task dev:start
```

### Udvikling på server

Det kan være nødvendigt, at udvikle direkte på en server, hvis man har brug for at kunne logge ind som bruger / interagere med login.dbc.dk.

### Udvikling i DDEV

Alternativt til/parallet med det indbyggede Dpl Cms Docker setup, kan man også bruge DDEV.
Kræver at man bygger et Dpl Cms, dumper databasen, configurere DDEV og indlæser databasen.
DDEV er et mere standard udviklingsmiljø setup med forskellige add-ins som mailclient og solr.
Hvis man bruger dette, er det vigtigt også at teste sit module i Dpl Cms docker miljøet.

## Udviklingsmetoder

Der er følgende udviklingsmetoder
1 Konfigurationsændring via UI
2 Asset Injector / ...
3 Lokale Drupal moduler
4 Lokale React moduler
5 Udvikling

### 1 Rettelser via UI

Retter i konfiguration bliver tilføjet til "config ignore", og bliver således ikke overskrevet af standard konfiguration fra DPL-CMS.
Hvad kan man og hvad skal man være varsom med i UI - er det ok at man f.eks. opretter et View eller retter i eksisterende konfiguration f.eks. antallet af items i en paragraph?
Hvordan tjekker man, hvad der er blevet tilføjet til "config ignore" og hvordan tjekker man om det påvirker standard funktionalitet.
Se eksempel ...

### 2 Asset injector udvikling

Asset Injector kan indskyde CSS og JS.
Asset injector kode kan med fordel udvikles som et custom modul i det lokale udviklingsmiljø, og så kan det senere lægges op som i asset injectoren. Fordelene ved dette er, at man kan komme uden om mange caching lag der er i Dpl Cms, at man kan arbejde i en desktop editor og at man kan versionsstyre sin kode.
Man skal være opmærksom på "race conditions" altså hvilken kode indlæses første - specielt, hvis man ønsker at indskyde noget, som ændre i den React genererede DOM.
Hvis man skal rette i eksisterende asset injector kode, kræver det at man kloner en eksisterende indførsel (og sletter den gamle) for at komme udenom cache.
Se eksempel modul -> link til et repo.

### Custom modul udvikling

Custom moduler udvikles som alle andre Drupal moduler med nogle undtagelser

- Custom moduler kan kun lægges på sitet via ZIP upload via admin UI (kræver rettigheder), og de lægger sig i /web/modules/local folderen.
- Der er nogle særlig forhold omkring konfiguration, altså database indstilliger som er skrevet til kode, når det implementeres på Dpl Cms.
- Dele af sitet er React Apps (Dpl React), som indlæses på siden.
- Man kan genbruge library token og user tokens i sin JS kode

#### Konfiguration

Se, hvordan dette laves på:
https://danskernesdigitalebibliotek.github.io/dpl-docs/DPL-CMS/webmaster-modules/#initial-configuration

Udover der som står i dokumentationen skal man lave en MODULNAVN.services.yml fil, som også kan ses i kdb_brugbyen eksemplet.

Slå config ignore til

Test om konfiguration kan indlæses

```sh
task dev:cli -- drush deploy
```

Se eksempel modul -> link til et repo.

#### Xdebug

Ved VS Code og Chrome

.vscode/launch.json fil
Start Xdebug i docker projektet: task dev:enable-xdebug
Tilføg og start f.eks. Xdebug Chrome Extension
Start debug i VS Code

#### JS/CSS Assets

Der er muligvis issues med adgang til attach'ede JS og CSS filer som ikke kan tilgået, efter at der er kørt de ugentlige opdateringer.

## Dpl React Udvikling

Se eksempel modul -> link til et repo.

## Rettigheder

For at få adgang til, at uploade moduler, rette i konfiguration kræver det, at man sætter følgende rettigheder ...

## Ønsker i priotiteret rækkefølge

Mange af dem handler om at gøre specielt test miljøet mindre "black box" agtigt.

- Man skal erstatte de filer, som man uploader, istedet for at lægge dem oven i (test + produktion).
- Det skal læse muligt at se, hvad man har liggende i module/local mappen (test + produktion).
- Det skal være muligt at slette fra module/local mappen (minimum på test).
- Man skal kunne få et database dump fra test til lokal udvikling.
- Se databaselog eller andre logs (test + produktion).
- Man skal kunne triggere et test deploy (test).
- Asset injector og den hårde caching spiller ikke så godt sammen, hvis man skal teste.

## Andet

- Kunne der laves et DPL Example modul, som man kan arbejde udfra?
- Kunne der laves et DPL config modul, som automatisk indlæste custom config via service, som det også er beskrevet her: https://danskernesdigitalebibliotek.github.io/dpl-docs/DPL-CMS/webmaster-modules/#initial-configuration?

## Links

Servicedesk: https://detdigitalefolkebibliotek.atlassian.net/servicedesk/customer/portals
DPL CMS Manual: https://www.folkebibliotekernescms.dk/main/
DPL CMS Dokumentation: https://danskernesdigitalebibliotek.github.io/dpl-docs/
