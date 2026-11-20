<?php
/** Panoul de control – punctul de plecare pentru NFR-05 (max. 3 click-uri până la lot). */
require __DIR__ . '/../includes/bootstrap.php';
$u = require_login();
$teams = manageable_teams();
foreach ($teams as &$t) {
    $t['players'] = q('SELECT COUNT(*) FROM players WHERE team_id = ?', [$t['id']])->fetchColumn();
    $t['pending'] = q('SELECT COUNT(*) FROM matches WHERE team_id = ? AND goals_for IS NULL AND date_time < ?',
        [$t['id'], date('Y-m-d H:i:s')])->fetchColumn();
}
unset($t);
$unread = is_admin() ? q('SELECT COUNT(*) FROM messages WHERE archived = 0')->fetchColumn() : 0;

$title = 'Panou staff';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_nav.php';
?>
<h1>Bun venit, <?= e(explode(' ', $u['name'])[0]) ?>!</h1>
<?php if (is_admin() && $unread): ?>
    <p class="flash flash-info"><a href="messages.php">Aveți <?= (int)$unread ?> mesaje noi de la vizitatori →</a></p>
<?php endif; ?>
<h2><?= is_admin() ? 'Toate echipele' : 'Echipele mele' ?></h2>
<?php if (!$teams): ?>
    <p class="empty">Nu aveți echipe atribuite. Contactați administratorul.</p>
<?php endif; ?>
<div class="grid-3">
    <?php foreach ($teams as $t): ?>
        <article class="card">
            <h3><?= e($t['name']) ?></h3>
            <p class="muted"><?= (int)$t['players'] ?> jucători<?= $t['pending'] ? ' · <strong>' . (int)$t['pending'] . ' rezultat(e) de completat</strong>' : '' ?></p>
            <div class="actions">
                <a class="btn btn-sm" href="players.php?team=<?= (int)$t['id'] ?>">Gestionează lotul</a>
                <a class="btn btn-sm btn-ghost" href="matches.php?team=<?= (int)$t['id'] ?>">Meciuri</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
