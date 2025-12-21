# Study-Tracker

Spletna aplikacija za učinkovito spremljanje učnih aktivnosti in organizacijo študijskih obveznosti.

## Opis projekta

Study-Tracker je osebni učni asistent, ki omogoča študentom enostavno beleženje učnih sej, spremljanje napredka po predmetih in časovnih obdobjih ter pregled statistike učenja. Aplikacija vključuje motivacijske citate, avtomatska e-poštna obvestila in možnost izvoza podatkov v PDF in Excel format.

## Funkcionalnosti

- **Uporabniška avtentikacija** - Registracija in prijava uporabnikov z varnim hashiranjem gesel
- **Beleženje učnih sej** - Dodajanje predmetov, poglavij in spremljanje časa učenja
- **Statistika in grafi** - Vizualizacija napredka po predmetih in časovnih obdobjih
- **E-poštna obvestila** - Avtomatski opomniki za izpite (2 dni pred rokom)
- **Motivacijski citati** - Dnevni motivacijski citati za spodbujanje učenja
- **Izvoz podatkov** - Izvoz statistike v PDF in Excel formatu
- **Docker podpora** - Enostavna postavitev z Docker Compose

## Tehnologije

| Kategorija           | Tehnologija    | Opis                               |
| -------------------- | -------------- | ---------------------------------- |
| **Backend**          | PHP 8.x        | Strežniška logika aplikacije       |
| **Backend**          | PDO            | Komunikacija z bazo podatkov       |
| **Frontend**         | HTML5          | Struktura spletnih strani          |
| **Frontend**         | CSS3           | Oblikovanje uporabniškega vmesnika |
| **Frontend**         | Bootstrap 5    | Responzivni dizajn                 |
| **Frontend**         | JavaScript     | Interaktivnost (grafi, AJAX)       |
| **Baza podatkov**    | MySQL 8.x      | Relacijska baza podatkov           |
| **Containerizacija** | Docker         | Kontejnerizacija aplikacije        |
| **Containerizacija** | Docker Compose | Orkestracija večih kontejnerjev    |
| **E-pošta**          | SMTP           | Protokol za pošiljanje e-pošte     |
| **E-pošta**          | Inbucket       | SMTP simulator za testiranje       |

## Predpogoji

Pred zagonom aplikacije potrebujete:

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Namestitev in zagon

### 1. Klonirajte repozitorij

```bash
git clone https://github.com/AnejVollmeier/Study-Tracker.git
cd Study-Tracker
```

### 2. Zaženite Docker kontejnerje

```bash
docker-compose up -d
```

Ta ukaz zgradi in zažene:

- **Spletni strežnik** (Apache + PHP) na http://localhost:8000
- **MySQL podatkovno bazo** na portu 3307
- **Inbucket** (SMTP simulator) na http://localhost:8025

### 3. Inicializirajte bazo podatkov

Povežite se na MySQL kontejner in uvozite SQL skript:

```bash
docker exec -i mysql mysql -uroot -psuperVarnoGeslo < docs/Study-Tracker_MySQL. sql
```

### 4. Dostop do aplikacije

Odprite brskalnik in pojdite na:

```
http://localhost:8000
```

## Struktura projekta

```
Study-Tracker/
├── data/
│   ├── www/                    # PHP spletna aplikacija
│   │   ├── login.php          # Prijava uporabnika
│   │   ├── registration.php   # Registracija
│   │   ├── predmeti.php       # Seznam predmetov
│   │   ├── dodajPredmet.php   # Dodajanje predmetov
│   │   ├── podobnostiPredmeta.php  # Podrobnosti predmeta
│   │   ├── rokIzpita.php      # Cron job za e-mail opomnike
│   │   ├── db.php             # Povezava na bazo
│   │   ├── izvozi.php         # Izvoz v PDF/Excel
│   │   └── style.css          # CSS stil
│   └── mysql/                 # MySQL podatki (perzistentni)
├── docs/
│   ├── Study-Tracker_MySQL.sql     # SQL shema baze
│   └── Osebni učni tracker.txt     # Projektna dokumentacija
├── docker-compose.yml         # Docker konfiguracija
└── README.md                  # Ta datoteka
```

## Podatkovna shema

Aplikacija uporablja relacijsko bazo s sledečimi tabelami:

- **tip_osebe** - Tipi uporabnikov (email_yes/email_no)
- **oseba** - Podatki uporabnikov
- **predmet** - Študijski predmeti
- **poglavje** - Poglavja znotraj predmetov
- **seja** - Učne seje (trajanje, čas, opombe)

## E-poštna obvestila

Aplikacija pošilja avtomatska obvestila:

- **Opomnik za izpit** - 2 dni pred datumom izpita
- **Potrditev ustvarjanja predmeta** - Ob dodajanju novega predmeta

Za testiranje e-pošte odprite Inbucket vmesnik na `http://localhost:8025`.

### Cron job za opomnike

Za avtomatično pošiljanje opomnikov nastavite cron job:

```bash
# Vsak dan ob 9:00 uri
0 9 * * * docker exec spletni-streznik php /var/www/html/rokIzpita.php
```

## Varnost

- Gesla so shranjena kot bcrypt hashi
- PDO prepared statements za zaščito pred SQL injection napadi
- Session management za avtentikacijo
- Preverjanje lastništva podatkov pred dostopom

## Odpravljanje težav

### Aplikacija ni dostopna na localhost:8000

```bash
# Preverite status kontejnerjev
docker-compose ps

# Oglejte si logs
docker-compose logs spletni-streznik
```

### Napaka pri povezavi na bazo

Preverite geslo in hostname v `data/www/db.php`:

- Hostname: `podatkovna-baza`
- Uporabnik: `root`
- Geslo: `superVarnoGeslo`

### Reset baze podatkov

```bash
docker-compose down -v
docker-compose up -d
# Ponovno uvozite SQL
```

## Avtor

**Anej Vollmeier**
