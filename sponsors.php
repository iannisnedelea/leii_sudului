<?php
/** FR-06: sponsori cu logo, descriere și link. */
require __DIR__ . '/includes/bootstrap.php';
$sponsors = q('SELECT * FROM sponsors ORDER BY id')->fetchAll();

$title = 'Sponsori';
require __DIR__ . '/includes/header.php';
?>
<h1>Sponsori și parteneri</h1>
<div class="grid-3">
    <?php foreach ($sponsors as $s): ?>
        <article class="card sponsor">
            <?php if ($s['logo']): ?>
                <img src="<?= e(base_url($s['logo'])) ?>" alt="Logo <?= e($s['name']) ?>">
            <?php else: ?>
                <div class="logo-ph" aria-hidden="true"><?= e(mb_substr($s['name'], 0, 1)) ?></div>
            <?php endif; ?>
            <h2><?= e($s['name']) ?></h2>
            <p><?= e($s['description']) ?></p>
            <a href="<?= e($s['website']) ?>" target="_blank" rel="noopener">Vizitează site-ul →</a>
        </article>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
