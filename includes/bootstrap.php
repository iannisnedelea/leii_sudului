<?php
/** Inclus la începutul fiecărei pagini. */
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

/** URL relativ la rădăcina aplicației (funcționează și din /admin). */
function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        $root   = str_replace('\\', '/', realpath(__DIR__ . '/..'));
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? $root));
        $base   = rtrim(substr($root, strlen($docRoot)), '/') . '/';
    }
    return $base . ltrim($path, '/');
}
