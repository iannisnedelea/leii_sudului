<?php
/** FR-09 / FR-11 / UC-04: adăugare, editare, ștergere jucători. */
require __DIR__ . '/../includes/bootstrap.php';
require_login();

$teams = manageable_teams();
if (!$teams) redirect('dashboard.php');
$teamId = (int)($_GET['team'] ?? $_POST['team_id'] ?? $teams[0]['id']);

// UC-04 flux alternativ: antrenorul încearcă să modifice o echipă care nu e a lui
if (!can_manage_team($teamId)) {
    flash('Acces interzis: această echipă nu vă este atribuită.', 'err');
    redirect('players.php?team=' . (int)$teams[0]['id']);
}

$errors = [];
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    // la editare/ștergere verificăm că jucătorul aparține echipei permise
    if ($id && !q('SELECT 1 FROM players WHERE id = ? AND team_id = ?', [$id, $teamId])->fetchColumn()) {
        http_response_code(403);
        exit('Acces interzis.');
    }

    if ($action === 'delete') {
        q('DELETE FROM players WHERE id = ?', [$id]);
        flash('Jucătorul a fost șters.');
        redirect("players.php?team=$teamId");
    }

    $p = [
        'first_name'   => trim($_POST['first_name'] ?? ''),
        'last_name'    => trim($_POST['last_name'] ?? ''),
        'position'     => $_POST['position'] ?? '',
        'shirt_number' => (int)($_POST['shirt_number'] ?? 0),
        'consent'      => isset($_POST['consent']) ? 1 : 0,
    ];
    if ($p['first_name'] === '' || $p['last_name'] === '') $errors[] = 'Prenumele și numele sunt obligatorii.';
    if (!in_array($p['position'], POSITIONS, true))          $errors[] = 'Alegeți un post valid.';
    if ($p['shirt_number'] < 1 || $p['shirt_number'] > 99)    $errors[] = 'Numărul de tricou trebuie să fie între 1 și 99.';
    // UC-04 pas 3: număr de tricou unic în echipă
    if (q('SELECT 1 FROM players WHERE team_id = ? AND shirt_number = ? AND id <> ?', [$teamId, $p['shirt_number'], $id])->fetchColumn()) {
        $errors[] = "Numărul {$p['shirt_number']} este deja folosit în această echipă.";
    }

    if (!$errors) {
        if ($id) {
            q('UPDATE players SET first_name=?, last_name=?, position=?, shirt_number=?, consent=? WHERE id=?',
                [...array_values($p), $id]);
            flash('Datele jucătorului au fost actualizate.');
        } else {
            q('INSERT INTO players (team_id, first_name, last_name, position, shirt_number, consent) VALUES (?,?,?,?,?,?)',
                [$teamId, ...array_values($p)]);
            flash('Jucător adăugat. Lotul public a fost actualizat.');
        }
        redirect("players.php?team=$teamId");
    }
    $editing = $p + ['id' => $id];
}

if (!$editing && isset($_GET['edit'])) {
    $editing = q('SELECT * FROM players WHERE id = ? AND team_id = ?', [(int)$_GET['edit'], $teamId])->fetch() ?: null;
}
$players = q('SELECT * FROM players WHERE team_id = ? ORDER BY shirt_number', [$teamId])->fetchAll();
$team = q('SELECT * FROM teams WHERE id = ?', [$teamId])->fetch();

$title = 'Jucători';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_nav.php';
?>
<h1>Lot – <?= e($team['name']) ?></h1>
<nav class="tabs" aria-label="Echipe">
    <?php foreach ($teams as $t): ?>
        <a href="?team=<?= (int)$t['id'] ?>" <?= (int)$t['id'] === $teamId ? 'aria-current="page"' : '' ?>><?= e($t['age_group']) ?></a>
    <?php endforeach; ?>
</nav>

<div class="grid-admin">
    <section class="card">
        <h2><?= count($players) ?> jucători</h2>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Nr.</th><th>Nume</th><th>Post</th><th>Acord</th><th><span class="sr-only">Acțiuni</span></th></tr></thead>
            <tbody>
            <?php foreach ($players as $p): ?>
                <tr>
                    <td><?= (int)$p['shirt_number'] ?></td>
                    <td><?= e($p['first_name'] . ' ' . $p['last_name']) ?></td>
                    <td><?= e($p['position']) ?></td>
                    <td><?= $p['consent'] ? '✓' : '<span class="muted">nu</span>' ?></td>
                    <td class="row-actions">
                        <a href="?team=<?= $teamId ?>&edit=<?= (int)$p['id'] ?>">Editează</a>
                        <form method="post" data-confirm="Ștergeți jucătorul <?= e($p['first_name'] . ' ' . $p['last_name']) ?>?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="team_id" value="<?= $teamId ?>">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button class="link-danger" name="action" value="delete">Șterge</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$players): ?><tr><td colspan="5" class="muted">Niciun jucător încă.</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>
    </section>

    <form class="card form" method="post" action="players.php?team=<?= $teamId ?>">
        <h2><?= !empty($editing['id']) ? 'Editează jucător' : 'Adaugă jucător' ?></h2>
        <?php foreach ($errors as $err): ?><p class="flash flash-err" role="alert"><?= e($err) ?></p><?php endforeach; ?>
        <?= csrf_field() ?>
        <input type="hidden" name="team_id" value="<?= $teamId ?>">
        <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
        <label for="fn">Prenume</label>
        <input id="fn" name="first_name" required value="<?= e($editing['first_name'] ?? '') ?>">
        <label for="ln">Nume</label>
        <input id="ln" name="last_name" required value="<?= e($editing['last_name'] ?? '') ?>">
        <label for="pos">Post</label>
        <select id="pos" name="position" required>
            <?php foreach (POSITIONS as $pos): ?>
                <option <?= ($editing['position'] ?? '') === $pos ? 'selected' : '' ?>><?= $pos ?></option>
            <?php endforeach; ?>
        </select>
        <label for="nr">Număr tricou</label>
        <input id="nr" name="shirt_number" type="number" min="1" max="99" required value="<?= e((string)($editing['shirt_number'] ?? '')) ?>">
        <label class="check"><input type="checkbox" name="consent" <?= !empty($editing['consent']) ? 'checked' : '' ?>>
            Acord parental scris pentru publicarea numelui complet</label>
        <button class="btn" type="submit">Salvează</button>
        <?php if (!empty($editing['id'])): ?><a href="?team=<?= $teamId ?>">Anulează</a><?php endif; ?>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
