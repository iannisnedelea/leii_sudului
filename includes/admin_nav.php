<?php $u = current_user(); $s = basename($_SERVER['SCRIPT_NAME']); ?>
<div class="admin-bar">
    <div>
        <p class="eyebrow">Panou staff</p>
        <p><strong><?= e($u['name']) ?></strong> · <?= $u['role'] === 'admin' ? 'Administrator' : 'Antrenor' ?></p>
    </div>
    <nav class="tabs" aria-label="Meniu staff">
        <a href="dashboard.php" <?= $s === 'dashboard.php' ? 'aria-current="page"' : '' ?>>Panou</a>
        <a href="players.php" <?= $s === 'players.php' ? 'aria-current="page"' : '' ?>>Jucători</a>
        <a href="matches.php" <?= $s === 'matches.php' ? 'aria-current="page"' : '' ?>>Meciuri</a>
        <?php if ($u['role'] === 'admin'): ?>
            <a href="messages.php" <?= $s === 'messages.php' ? 'aria-current="page"' : '' ?>>Mesaje</a>
        <?php endif; ?>
    </nav>
    <form method="post" action="<?= base_url('logout.php') ?>"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">Ieșire</button></form>
</div>
