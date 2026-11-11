<?php
/**
 * Configurare aplicație LEII Sudului Craiova – prototip.
 *
 * Implicit: MySQL din XAMPP (utilizator root, fără parolă).
 * Pentru o rulare rapidă fără MySQL se poate pune DB_DRIVER = 'sqlite'.
 */
define('DB_DRIVER', getenv('LEII_DB') ?: 'mysql');   // 'mysql' sau 'sqlite'

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'leii_sudului');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SQLITE_PATH', __DIR__ . '/data/leii.sqlite');

define('SITE_NAME', 'Academia de Fotbal LEII Sudului Craiova');
define('CLUB_EMAIL', 'contact@leiisudului.ro');

// UC-03: blocare după 5 încercări greșite, timp de 15 minute
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES', 15);
