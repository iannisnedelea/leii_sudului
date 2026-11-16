<?php
/** FR-02 / UC-01: lotul fiecărei grupe de vârstă. */
require __DIR__ . '/includes/bootstrap.php';

$teams = q('SELECT t.*, u.name AS coach FROM teams t LEFT JOIN users u ON u.id = t.coach_id ORDER BY t.id')->fetchAll();
$group = $_GET['group'] ?? $teams[0]['age_group'];
$team = null;
foreach ($teams as $t) if ($t['age_group'] === $group) $team = $t;
if (!$team) { http_response_code(404); $team = $teams[0]; }

$players = q('SELECT * FROM players WHERE team_id = ? ORDER BY shirt_number', [$team['id']])->fetchAll();

$title = 'Echipe – ' . $team['age_group'];
require __DIR__ . '/includes/header.php';
?>
<h1>Echipe</h1>
<p class="muted">Grupele sunt organizate pe ani de naștere.</p>
<nav class="tabs" aria-label="Grupe pe ani de naștere">
    <?php foreach ($teams as $t): ?>
        <a href="?group=<?= e($t['age_group']) ?>" <?= $t['id'] === $team['id'] ? 'aria-current="page"' : '' ?>><?= e($t['age_group']) ?></a>
    <?php endforeach; ?>
</nav>

<section class="card">
    <div class="card-head">
        <h2><?= e($team['name']) ?></h2>
        <p class="muted">Antrenor: <?= $team['coach'] ? e($team['coach']) : 'în curs de numire' ?></p>
    </div>
    <?php if (!$players): ?>
        <p class="empty">Lotul va fi publicat în curând.</p>
    <?php else: ?>
        <ul class="roster">
            <?php foreach ($players as $p): ?>
                <li class="player">
                    <span class="avatar" aria-hidden="true"><?= e(initials($p['first_name'], $p['last_name'])) ?></span>
                    <span class="num">#<?= (int)$p['shirt_number'] ?></span>
                    <?php if ($p['consent']): ?>
                        <strong><?= e($p['first_name'] . ' ' . $p['last_name']) ?></strong>
                    <?php else: ?>
                        <strong><?= e($p['first_name'] . ' ' . mb_substr($p['last_name'], 0, 1) . '.') ?></strong>
                    <?php endif; ?>
                    <span class="muted"><?= e($p['position']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="muted small">Pentru jucătorii fără acord parental scris se afișează doar prenumele și inițiala (NFR-04).</p>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
