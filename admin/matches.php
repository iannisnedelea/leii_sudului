<?php
/** FR-10 / UC-05: programare meciuri și înregistrare rezultate. */
require __DIR__ . '/../includes/bootstrap.php';
require_login();

$teams = manageable_teams();
if (!$teams) redirect('dashboard.php');
$teamIds = array_map(fn($t) => (int)$t['id'], $teams);
$filter = (int)($_GET['team'] ?? 0);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'result') {
        $id = (int)$_POST['id'];
        $m = q('SELECT * FROM matches WHERE id = ?', [$id])->fetch();
        $gf = $_POST['goals_for'];
        $ga = $_POST['goals_against'];
        if (!$m || !can_manage_team((int)$m['team_id'])) {
            http_response_code(403);
            exit('Acces interzis.');
        }
        if (!ctype_digit($gf) || !ctype_digit($ga) || $gf > 99 || $ga > 99) {
            flash('Scorul trebuie să conțină numere întregi între 0 și 99.', 'err');
        } else {
            q('UPDATE matches SET goals_for = ?, goals_against = ? WHERE id = ?', [(int)$gf, (int)$ga, $id]);
            flash("Rezultat salvat: $gf – $ga. Apare acum pe pagina Meciuri.");
        }
        redirect('matches.php' . ($filter ? "?team=$filter" : ''));
    }

    if ($action === 'add') {
        $teamId = (int)$_POST['team_id'];
        $opp = trim($_POST['opponent'] ?? '');
        $loc = trim($_POST['location'] ?? '');
        $dt  = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['date_time'] ?? '');
        if (!can_manage_team($teamId)) $errors[] = 'Nu puteți programa meciuri pentru această echipă.';
        if ($opp === '') $errors[] = 'Introduceți adversarul.';
        if ($loc === '') $errors[] = 'Introduceți locația.';
        if (!$dt)        $errors[] = 'Data și ora nu sunt valide.';
        if (!$errors) {
            q('INSERT INTO matches (team_id, opponent, date_time, location) VALUES (?,?,?,?)',
                [$teamId, $opp, $dt->format('Y-m-d H:i:s'), $loc]);
            flash('Meci programat.');
            redirect('matches.php' . ($filter ? "?team=$filter" : ''));
        }
    }
}

$in = implode(',', $teamIds);   // id-uri întregi, provenite din baza de date
$sql = "SELECT m.*, t.age_group FROM matches m JOIN teams t ON t.id = m.team_id WHERE m.team_id IN ($in)";
$params = [];
if ($filter && in_array($filter, $teamIds, true)) { $sql .= ' AND m.team_id = ?'; $params[] = $filter; }
$matches = q($sql . ' ORDER BY m.date_time DESC', $params)->fetchAll();

$title = 'Meciuri';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_nav.php';
?>
<h1>Meciuri</h1>
<nav class="tabs" aria-label="Echipe">
    <a href="matches.php" <?= !$filter ? 'aria-current="page"' : '' ?>>Toate</a>
    <?php foreach ($teams as $t): ?>
        <a href="?team=<?= (int)$t['id'] ?>" <?= (int)$t['id'] === $filter ? 'aria-current="page"' : '' ?>><?= e($t['age_group']) ?></a>
    <?php endforeach; ?>
</nav>

<div class="grid-admin">
    <section class="card">
        <h2>Program și rezultate</h2>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Grupa</th><th>Data</th><th>Adversar</th><th>Scor (noi – ei)</th></tr></thead>
            <tbody>
            <?php foreach ($matches as $m): ?>
                <tr>
                    <td><span class="tag"><?= e($m['age_group']) ?></span></td>
                    <td><?= fmt_date($m['date_time']) ?></td>
                    <td><?= e($m['opponent']) ?><br><span class="muted small"><?= e($m['location']) ?></span></td>
                    <td>
                        <form method="post" class="score-form" aria-label="Rezultat vs <?= e($m['opponent']) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="result">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <input name="goals_for" inputmode="numeric" pattern="\d{1,2}" required aria-label="Goluri LEII" value="<?= e((string)$m['goals_for']) ?>">
                            <span>–</span>
                            <input name="goals_against" inputmode="numeric" pattern="\d{1,2}" required aria-label="Goluri adversar" value="<?= e((string)$m['goals_against']) ?>">
                            <button class="btn btn-sm"><?= $m['goals_for'] === null ? 'Salvează' : 'Modifică' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </section>

    <form class="card form" method="post">
        <h2>Programează meci</h2>
        <?php foreach ($errors as $err): ?><p class="flash flash-err" role="alert"><?= e($err) ?></p><?php endforeach; ?>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <label for="team">Echipa</label>
        <select id="team" name="team_id">
            <?php foreach ($teams as $t): ?>
                <option value="<?= (int)$t['id'] ?>" <?= (int)$t['id'] === $filter ? 'selected' : '' ?>><?= e($t['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="opp">Adversar</label>
        <input id="opp" name="opponent" required maxlength="100">
        <label for="dt">Data și ora</label>
        <input id="dt" name="date_time" type="datetime-local" required>
        <label for="loc">Locație</label>
        <input id="loc" name="location" required maxlength="120" value="Baza Leii Sudului">
        <button class="btn" type="submit">Adaugă meciul</button>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
