<?php
require_once __DIR__ . '/db.php';

/**
 * Autentificare și autorizare (FR-08, UC-03, NFR-03).
 * Rolurile sunt ierarhice: admin moștenește tot ce poate face coach.
 */

function current_user(): ?array
{
    if (empty($_SESSION['uid'])) {
        return null;
    }
    static $u = null;
    if ($u === null) {
        $u = q('SELECT id, name, email, role FROM users WHERE id = ? AND active = 1', [$_SESSION['uid']])->fetch() ?: null;
    }
    return $u;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

/** Încercare de login. Întoarce null la succes sau un mesaj de eroare. */
function attempt_login(string $email, string $password): ?string
{
    $u = q('SELECT * FROM users WHERE email = ?', [$email])->fetch();
    $generic = 'Email sau parolă greșită.';
    if (!$u || !$u['active']) {
        return $generic;
    }
    if ($u['locked_until'] && strtotime($u['locked_until']) > time()) {
        $min = ceil((strtotime($u['locked_until']) - time()) / 60);
        return "Cont blocat temporar după prea multe încercări. Reîncercați în $min minute.";
    }
    if (!password_verify($password, $u['password_hash'])) {
        $fails = (int)$u['failed_attempts'] + 1;
        $lock  = null;
        if ($fails >= MAX_LOGIN_ATTEMPTS) {
            $lock  = date('Y-m-d H:i:s', time() + LOCKOUT_MINUTES * 60);
            $fails = 0;
        }
        q('UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?', [$fails, $lock, $u['id']]);
        return $lock ? 'Prea multe încercări greșite. Contul este blocat ' . LOCKOUT_MINUTES . ' minute.' : $generic;
    }
    q('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?', [$u['id']]);
    session_regenerate_id(true);
    $_SESSION['uid'] = $u['id'];
    return null;
}

/** Oprește pagina dacă utilizatorul nu e logat (sau nu are rolul cerut). */
function require_login(?string $role = null): array
{
    $u = current_user();
    if (!$u) {
        redirect(base_url('login.php'));
    }
    if ($role === 'admin' && $u['role'] !== 'admin') {
        http_response_code(403);
        exit('Acces interzis.');
    }
    return $u;
}

/** Poate utilizatorul curent să modifice echipa dată? (UC-04, flux alternativ) */
function can_manage_team(int $teamId): bool
{
    $u = current_user();
    if (!$u) return false;
    if ($u['role'] === 'admin') return true;
    return (bool)q('SELECT 1 FROM teams WHERE id = ? AND coach_id = ?', [$teamId, $u['id']])->fetchColumn();
}

/** Echipele pe care le poate gestiona utilizatorul curent. */
function manageable_teams(): array
{
    $u = current_user();
    if ($u['role'] === 'admin') {
        return q('SELECT * FROM teams ORDER BY id')->fetchAll();
    }
    return q('SELECT * FROM teams WHERE coach_id = ? ORDER BY id', [$u['id']])->fetchAll();
}
