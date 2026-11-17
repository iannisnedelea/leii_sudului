<?php
/** FR-04: program și rezultate, filtrabile după grupa de vârstă (filtrare în JavaScript). */
require __DIR__ . '/includes/bootstrap.php';

$groups = q('SELECT age_group FROM teams ORDER BY age_group')->fetchAll(PDO::FETCH_COLUMN);
$all = q('SELECT m.*, t.age_group FROM matches m JOIN teams t ON t.id = m.team_id ORDER BY m.date_time')->fetchAll();
$upcoming = array_filter($all, fn($m) => $m['goals_for'] === null);
$results  = array_reverse(array_filter($all, fn($m) => $m['goals_for'] !== null));

function match_row(array $m): string
{
    $score = $m['goals_for'] === null
        ? '<span class="score score-pending">–</span>'
        : sprintf('<span class="score %s">%d – %d</span>',
            $m['goals_for'] > $m['goals_against'] ? 'win' : ($m['goals_for'] < $m['goals_against'] ? 'loss' : 'draw'),
            $m['goals_for'], $m['goals_against']);
    return sprintf('<tr data-group="%s"><td><span class="tag">%s</span></td><td>%s</td><td>LEII Sudului vs %s</td><td>%s</td><td>%s</td></tr>',
        e($m['age_group']), e($m['age_group']), fmt_date($m['date_time']), e($m['opponent']), e($m['location']), $score);
}

$title = 'Meciuri';
require __DIR__ . '/includes/header.php';
?>
<h1>Meciuri</h1>
<div class="filter" role="group" aria-label="Filtrează după generație">
    <button class="chip" data-filter="all" aria-pressed="true">Toate</button>
    <?php foreach ($groups as $g): ?>
        <button class="chip" data-filter="<?= $g ?>" aria-pressed="false"><?= $g ?></button>
    <?php endforeach; ?>
</div>

<?php foreach (['Program' => $upcoming, 'Rezultate' => $results] as $h => $rows): ?>
<section class="card">
    <h2><?= $h ?></h2>
    <div class="table-wrap">
    <table class="matches">
        <thead><tr><th>Grupa</th><th>Data</th><th>Meci</th><th>Locație</th><th>Scor</th></tr></thead>
        <tbody>
            <?php foreach ($rows as $m) echo match_row($m); ?>
            <tr class="none" hidden><td colspan="5" class="muted">Niciun meci pentru grupa selectată.</td></tr>
        </tbody>
    </table>
    </div>
</section>
<?php endforeach; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
