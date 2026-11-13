<?php
/** Funcții ajutătoare folosite în toate paginile. */

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/** Token CSRF pentru toate formularele care modifică date. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Cerere invalidă (token CSRF).');
    }
}

/** Inițialele unui nume – afișate în locul fotografiei (NFR-04). */
function initials(string $first, string $last = ''): string
{
    return mb_strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));
}

function fmt_date(string $dt): string
{
    $luni = ['ian', 'feb', 'mar', 'apr', 'mai', 'iun', 'iul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    $t = strtotime($dt);
    return date('j', $t) . ' ' . $luni[(int)date('n', $t) - 1] . ' ' . date('Y, H:i', $t);
}

const POSITIONS  = ['Portar', 'Fundaș', 'Mijlocaș', 'Atacant'];
