<?php
/** FR-14: administratorul citește și arhivează mesajele de contact. */
require __DIR__ . '/../includes/bootstrap.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    q('UPDATE messages SET archived = 1 WHERE id = ?', [(int)$_POST['id']]);
    flash('Mesaj arhivat.');
    redirect('messages.php');
}
$showArchived = isset($_GET['archived']);
$messages = q('SELECT * FROM messages WHERE archived = ? ORDER BY sent_at DESC', [$showArchived ? 1 : 0])->fetchAll();

$title = 'Mesaje';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_nav.php';
?>
<h1>Mesaje de la vizitatori</h1>
<nav class="tabs">
    <a href="messages.php" <?= !$showArchived ? 'aria-current="page"' : '' ?>>Noi</a>
    <a href="?archived=1" <?= $showArchived ? 'aria-current="page"' : '' ?>>Arhivate</a>
</nav>
<?php if (!$messages): ?><p class="empty">Niciun mesaj.</p><?php endif; ?>
<?php foreach ($messages as $m): ?>
    <article class="card message">
        <div class="card-head">
            <h2><?= e($m['name']) ?> <a class="small" href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></h2>
            <span class="muted small"><?= fmt_date($m['sent_at']) ?></span>
        </div>
        <p><?= nl2br(e($m['text'])) ?></p>
        <?php if (!$showArchived): ?>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <button class="btn btn-sm btn-ghost">Arhivează</button></form>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
