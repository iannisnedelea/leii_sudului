# LEII Sudului Craiova – prototip aplicație web

Prototip minimal pentru Academia de Fotbal LEII Sudului Craiova (Tema 3).
Tehnologii: HTML5, CSS3, JavaScript, PHP 8, MySQL – conform raportului de specificații.

## Rulare cu XAMPP (recomandat)

1. Copiați folderul `leii-prototype` în `C:\xampp\htdocs\`.
2. Porniți **Apache** și **MySQL** din XAMPP Control Panel.
3. Deschideți în browser `http://localhost/leii-prototype/install.php`
   (creează baza `leii_sudului`, tabelele și datele demonstrative).
4. Deschideți `http://localhost/leii-prototype/`.

Setările bazei de date (host, utilizator, parolă) sunt în `config.php`.

## Rulare rapidă fără MySQL (SQLite)

```bash
cd leii-prototype
LEII_DB=sqlite php install.php
LEII_DB=sqlite php -S localhost:8080
```

Pe Windows (PowerShell): `$env:LEII_DB="sqlite"; php install.php; php -S localhost:8080`

## Conturi demonstrative

| Cont | Email | Parolă | Rol | Grupe |
|---|---|---|---|---|
| Adi Nedelea (patron și antrenor, UEFA B juniori) | adi@leii.ro | admin123 | administrator | 2015–2020 (vede toate) |
| Claudiu Mirea (antrenor și preparator fizic, UEFA C) | claudiu@leii.ro | antrenor123 | antrenor | 2012–2014 |
| Daniel Vasile (antrenor de portari) | daniel@leii.ro | antrenor123 | antrenor | – (antrenamente individuale) |

Grupele sunt pe ani de naștere, 2012–2020. Jucătorii, datele și scorurile meciurilor sunt demonstrative și se modifică
din `install.php`. Adversarii sunt echipe reale din competițiile de copii AJF Dolj (sezon 2026/2027), iar sponsorii
(Auto Pyo, King Rolls) sunt cei reali, cu logourile în `assets/img/sponsors/`. Sigla clubului este în `assets/img/logo.jpg`.

## Ce este implementat

| Cerință | Unde |
|---|---|
| FR-01 Pagina principală | `index.php` |
| FR-02 / UC-01 Loturi pe grupe (ani de naștere) | `teams.php` |
| FR-03 Antrenori | `coaches.php` |
| FR-04 Program și rezultate + filtru | `matches.php`, `assets/js/main.js` |
| FR-06 Sponsori | `sponsors.php` |
| FR-07 / UC-02 Formular de contact | `contact.php` |
| FR-08 / UC-03 Autentificare (bcrypt, blocare 15 min după 5 încercări) | `login.php`, `includes/auth.php` |
| FR-09 / UC-04 Gestionare jucători | `admin/players.php` |
| FR-10 / UC-05 Meciuri și rezultate | `admin/matches.php` |
| FR-14 Mesaje de contact | `admin/messages.php` |
| Înscriere copii (formular public + cereri în panoul staff) | `inscriere.php`, `admin/enrollments.php` |
| Antrenamente individuale 6–19 ani (prezentare + programare) | `individual.php`, `includes/enrollment.php` |
| Pagina principală: grupe U7–U15, rezultate, bază, întrebări frecvente | `index.php` |

Neincluse în prototip (planificate pentru versiunea completă): galeria foto (FR-05, FR-12),
editarea știrilor și sponsorilor din interfață (parte din FR-11), gestionarea conturilor (FR-13).

## Structura

```
leii-prototype/
├── config.php            setări (bază de date, blocare login)
├── install.php           creează tabelele + date demo
├── index.php … contact.php, login.php, logout.php   pagini publice
├── admin/                pagini staff (dashboard, players, matches, messages)
├── includes/             db.php, auth.php, helpers.php, header/footer
└── assets/css, assets/js
```
