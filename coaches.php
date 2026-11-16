<?php
/** FR-03: profilurile antrenorilor. */
require __DIR__ . '/includes/bootstrap.php';

// Antrenor = orice cont activ cu licență (administratorul poate fi și antrenor).
$coaches = q("SELECT id, name, licence, title FROM users WHERE licence IS NOT NULL AND active = 1 ORDER BY role, name")->fetchAll();
foreach ($coaches as &$c) {
    $c['teams'] = q('SELECT age_group FROM teams WHERE coach_id = ? ORDER BY id', [$c['id']])->fetchAll(PDO::FETCH_COLUMN);
}
unset($c);

$title = 'Antrenori';
require __DIR__ . '/includes/header.php';
?>
<h1>Antrenori</h1>
<div class="grid-3">
    <?php foreach ($coaches as $c): $parts = explode(' ', $c['name']); ?>
        <article class="card coach">
            <span class="avatar avatar-lg" aria-hidden="true"><?= e(initials($parts[0], $parts[1] ?? '')) ?></span>
            <h2><?= e($c['name']) ?></h2>
            <p class="muted"><?= e($c['title'] ?: 'Antrenor') ?></p>
            <p><span class="tag">Licență <?= e($c['licence'] ?: '–') ?></span></p>
            <p class="muted">Grupe: <?= $c['teams'] ? e(implode(', ', $c['teams'])) : '–' ?></p>
        </article>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
