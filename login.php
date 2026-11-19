<?php
/** FR-08 / UC-03: autentificare staff. */
require __DIR__ . '/includes/bootstrap.php';

if (current_user()) redirect(base_url('admin/dashboard.php'));

$error = null;
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $error = attempt_login($email, $_POST['password'] ?? '');
    if ($error === null) {
        redirect(base_url('admin/dashboard.php'));
    }
}

$title = 'Autentificare';
require __DIR__ . '/includes/header.php';
?>
<form class="card form narrow" method="post">
    <h1>Autentificare staff</h1>
    <p class="muted">Acces pentru antrenori și administratori.</p>
    <?php if ($error): ?><div class="flash flash-err" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label for="email">Email</label>
    <input id="email" name="email" type="email" required autocomplete="username" value="<?= e($email) ?>">
    <label for="password">Parolă</label>
    <input id="password" name="password" type="password" required autocomplete="current-password">
    <button class="btn" type="submit">Intră în cont</button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
