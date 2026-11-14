<?php
/** FR-01: pagina principală – prezentare, ultimele știri, următorul meci. */
require __DIR__ . '/includes/bootstrap.php';

$next = q("SELECT m.*, t.name AS team FROM matches m JOIN teams t ON t.id = m.team_id
           WHERE m.date_time >= ? ORDER BY m.date_time LIMIT 1", [date('Y-m-d H:i:s')])->fetch();
$news = q('SELECT * FROM news ORDER BY published_at DESC LIMIT 3')->fetchAll();
$stats = [
    'jucători' => q('SELECT COUNT(*) FROM players')->fetchColumn(),
    'grupe'    => q('SELECT COUNT(*) FROM teams')->fetchColumn(),
    'antrenori'=> q("SELECT COUNT(*) FROM users WHERE licence IS NOT NULL AND active = 1")->fetchColumn(),
];

$title = 'Acasă';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <img class="hero-logo" src="<?= base_url('assets/img/logo.jpg') ?>" alt="Sigla FC LEII Sudului" width="160" height="160">
    <div>
        <p class="eyebrow">Fotbal pentru copii și juniori · Craiova</p>
        <h1>Creștem campioni pe teren și în afara lui.</h1>
        <p class="lead">Academia de Fotbal LEII Sudului pregătește copii născuți între 2012 și 2020, în nouă grupe de vârstă, cu antrenori licențiați UEFA.</p>
        <div class="actions">
            <a class="btn" href="<?= base_url('teams.php') ?>">Vezi echipele</a>
            <a class="btn btn-ghost" href="<?= base_url('contact.php') ?>">Înscrie-ți copilul</a>
        </div>
    </div>
    <ul class="stats" aria-label="Clubul în cifre">
        <?php foreach ($stats as $label => $n): ?>
            <li><strong><?= (int)$n ?></strong><span><?= $label ?></span></li>
        <?php endforeach; ?>
    </ul>
</section>

<div class="grid-2">
    <section class="card">
        <h2>Următorul meci</h2>
        <?php if ($next): ?>
            <p class="match-big"><span class="tag"><?= e($next['team']) ?></span>
                LEII Sudului <span class="vs">vs</span> <?= e($next['opponent']) ?></p>
            <p class="muted"><?= fmt_date($next['date_time']) ?> · <?= e($next['location']) ?></p>
        <?php else: ?>
            <p class="muted">Nu există meciuri programate.</p>
        <?php endif; ?>
        <a href="<?= base_url('matches.php') ?>">Tot programul →</a>
    </section>
    <section class="card">
        <h2>Ultimele știri</h2>
        <?php foreach ($news as $n): ?>
            <article class="news">
                <h3><?= e($n['title']) ?></h3>
                <p class="muted small"><?= fmt_date($n['published_at']) ?></p>
                <p><?= e($n['body']) ?></p>
            </article>
        <?php endforeach; ?>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
