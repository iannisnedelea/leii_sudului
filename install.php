<?php
/**
 * Creează baza de date și o populează cu date demonstrative.
 * Rulare: http://localhost/leii-prototype/install.php  sau  php install.php
 * ATENȚIE: șterge și recreează toate tabelele.
 */
require_once __DIR__ . '/config.php';

$cli = PHP_SAPI === 'cli';
$out = function (string $m) use ($cli) { echo $cli ? "$m\n" : '<p>' . htmlspecialchars($m) . '</p>'; };

// MySQL: creează baza dacă nu există
if (DB_DRIVER === 'mysql') {
    $root = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $root->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci');
} elseif (!is_dir(dirname(SQLITE_PATH))) {
    mkdir(dirname(SQLITE_PATH), 0775, true);
}

require_once __DIR__ . '/includes/db.php';
$pdo = db();

$mysql = DB_DRIVER === 'mysql';
$PK    = $mysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
$ENG   = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';

if ($mysql) $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['messages', 'sponsors', 'news', 'matches', 'players', 'teams', 'users'] as $t) {
    $pdo->exec("DROP TABLE IF EXISTS $t");
}
if ($mysql) $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$schema = [
"CREATE TABLE users (
    id $PK,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL CHECK (role IN ('coach','admin')),
    licence VARCHAR(40),
    title VARCHAR(60),
    active TINYINT NOT NULL DEFAULT 1,
    failed_attempts INT NOT NULL DEFAULT 0,
    locked_until DATETIME NULL
)$ENG",
"CREATE TABLE teams (
    id $PK,
    name VARCHAR(60) NOT NULL,
    age_group VARCHAR(5) NOT NULL UNIQUE,
    coach_id INT NULL,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE SET NULL
)$ENG",
"CREATE TABLE players (
    id $PK,
    team_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    position VARCHAR(20) NOT NULL,
    shirt_number INT NOT NULL,
    photo VARCHAR(255) NULL,
    consent TINYINT NOT NULL DEFAULT 0,
    UNIQUE (team_id, shirt_number),
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
)$ENG",
"CREATE TABLE matches (
    id $PK,
    team_id INT NOT NULL,
    opponent VARCHAR(100) NOT NULL,
    date_time DATETIME NOT NULL,
    location VARCHAR(120) NOT NULL,
    goals_for INT NULL,
    goals_against INT NULL,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
)$ENG",
"CREATE TABLE news (
    id $PK,
    title VARCHAR(150) NOT NULL,
    body TEXT NOT NULL,
    published_at DATETIME NOT NULL
)$ENG",
"CREATE TABLE sponsors (
    id $PK,
    name VARCHAR(100) NOT NULL,
    logo VARCHAR(255) NULL,
    description VARCHAR(255) NOT NULL,
    website VARCHAR(255) NOT NULL
)$ENG",
"CREATE TABLE messages (
    id $PK,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    text TEXT NOT NULL,
    sent_at DATETIME NOT NULL,
    archived TINYINT NOT NULL DEFAULT 0
)$ENG",
];
foreach ($schema as $sql) $pdo->exec($sql);
$out('Tabele create (' . DB_DRIVER . ').');

// ---------- Date demonstrative (fictive) ----------
$ins = fn(string $sql, array $p) => $pdo->prepare($sql)->execute($p);

// Adi Nedelea este patron și antrenor, deci are rolul de administrator și conduce și echipe.
$users = [
    ['Adi Nedelea',    'adi@leii.ro',     'admin123',    'admin', 'UEFA B (juniori)', 'Patron și antrenor'],
    ['Claudiu Mirea',  'claudiu@leii.ro', 'antrenor123', 'coach', 'UEFA C',           'Antrenor'],
];
foreach ($users as [$n, $em, $pw, $r, $lic, $tit]) {
    $ins('INSERT INTO users (name, email, password_hash, role, licence, title) VALUES (?,?,?,?,?,?)',
        [$n, $em, password_hash($pw, PASSWORD_BCRYPT), $r, $lic, $tit]);
}

// Grupe pe ani de naștere, 2012–2020. Împărțirea pe antrenori este demonstrativă.
for ($year = 2012; $year <= 2020; $year++) {
    $ins('INSERT INTO teams (name, age_group, coach_id) VALUES (?,?,?)',
        ["Leii $year", (string)$year, $year <= 2016 ? 1 : 2]);
}

$first = ['Andrei', 'Darius', 'Luca', 'Matei', 'Ștefan', 'Vlad', 'Rareș', 'David', 'Alexandru', 'Ianis', 'Tudor', 'Eric'];
$last  = ['Popa', 'Dumitru', 'Stan', 'Marin', 'Radu', 'Tudor', 'Ene', 'Stoica', 'Nistor', 'Barbu', 'Dinu', 'Neagu'];
$pos   = ['Portar', 'Fundaș', 'Fundaș', 'Fundaș', 'Mijlocaș', 'Mijlocaș', 'Mijlocaș', 'Atacant', 'Atacant', 'Fundaș', 'Mijlocaș', 'Atacant'];
// Grupa 2020 (id 9) rămâne fără jucători ca să se vadă fluxul alternativ din UC-01
foreach ([1 => 12, 2 => 12, 3 => 11, 4 => 10, 5 => 10, 6 => 9, 7 => 8, 8 => 8] as $teamId => $count) {
    for ($i = 0; $i < $count; $i++) {
        $ins('INSERT INTO players (team_id, first_name, last_name, position, shirt_number, consent) VALUES (?,?,?,?,?,?)',
            [$teamId, $first[($i + $teamId * 3) % 12], $last[($i * 5 + $teamId) % 12], $pos[$i], $i + 1, $i % 4 ? 1 : 0]);
    }
}

$now = time();
$d = fn(int $days, string $h) => date('Y-m-d', $now + $days * 86400) . " $h:00";
$matches = [
    [3, 'ACS Dunărea Juniori', $d(-14, '10:00'), 'Baza Leii Sudului', 3, 1],
    [3, 'CS Jiul Demo', $d(-7, '11:00'), 'Deplasare', 2, 2],
    [1, 'AS Olt Juniori', $d(-10, '12:00'), 'Baza Leii Sudului', 1, 0],
    [6, 'CS Bănie Juniori', $d(-6, '10:30'), 'Deplasare', 4, 3],
    [8, 'ACS Mofleni', $d(-3, '09:30'), 'Baza Leii Sudului', 5, 2],
    [7, 'AS Romanați', $d(4, '10:00'), 'Baza Leii Sudului', null, null],
    [1, 'CS Slatina Demo', $d(6, '12:00'), 'Deplasare', null, null],
    [5, 'ACS Gorj Juniori', $d(9, '11:00'), 'Baza Leii Sudului', null, null],
];
foreach ($matches as $m) {
    $ins('INSERT INTO matches (team_id, opponent, date_time, location, goals_for, goals_against) VALUES (?,?,?,?,?,?)', $m);
}

$news = [
    ['Încep înscrierile pentru grupa 2020', 'Copiii născuți în 2020 sunt așteptați la antrenamentele de selecție. Detalii prin formularul de contact.', $d(-2, '18:00')],
    ['Victorie pentru grupa 2014', 'Grupa 2014 a câștigat cu 3–1 meciul de acasă. Felicitări băieților și antrenorului Adi Nedelea!', $d(-13, '20:00')],
];
foreach ($news as $n) $ins('INSERT INTO news (title, body, published_at) VALUES (?,?,?)', $n);

$sponsors = [
    ['Sponsor Demo 1', 'Partener principal al academiei – echipamente de joc.', 'https://example.com'],
    ['Sponsor Demo 2', 'Susține transportul echipelor la meciurile din deplasare.', 'https://example.com'],
    ['Sponsor Demo 3', 'Partener pentru nutriția și hidratarea sportivilor.', 'https://example.com'],
];
foreach ($sponsors as $s) $ins('INSERT INTO sponsors (name, description, website) VALUES (?,?,?)', $s);

$out('Date demonstrative inserate.');
$out('Conturi: adi@leii.ro / admin123 (administrator)   ·   claudiu@leii.ro / antrenor123 (antrenor)');
