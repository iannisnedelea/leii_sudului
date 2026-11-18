<?php
/** FR-07 / UC-02: formular de contact – validare client + server, salvare, email către club. */
require __DIR__ . '/includes/bootstrap.php';

$errors = [];
$data = ['name' => '', 'email' => '', 'text' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($data as $k => $_) $data[$k] = trim($_POST[$k] ?? '');

    if ($data['name'] === '' || mb_strlen($data['name']) > 100) $errors['name'] = 'Introduceți numele (max. 100 caractere).';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))   $errors['email'] = 'Adresa de email nu este validă.';
    if (mb_strlen($data['text']) < 10)                         $errors['text'] = 'Mesajul trebuie să aibă cel puțin 10 caractere.';

    if (!$errors) {
        q('INSERT INTO messages (name, email, text, sent_at) VALUES (?,?,?,?)',
            [$data['name'], $data['email'], $data['text'], date('Y-m-d H:i:s')]);
        // Trimiterea pe email: pe XAMPP mail() necesită configurarea sendmail; eșecul nu blochează salvarea.
        @mail(CLUB_EMAIL, 'Mesaj nou de pe site – ' . $data['name'], $data['text'], 'Reply-To: ' . $data['email']);
        flash('Mulțumim! Mesajul a fost trimis, vă vom răspunde în curând.');
        redirect(base_url('contact.php'));
    }
}

$title = 'Contact';
require __DIR__ . '/includes/header.php';

function field_err(array $errors, string $k): string
{
    return isset($errors[$k]) ? '<p class="err" id="err-' . $k . '">' . e($errors[$k]) . '</p>' : '';
}
?>
<h1>Contact</h1>
<div class="grid-2">
    <form class="card form" method="post" novalidate data-validate>
        <?= csrf_field() ?>
        <label for="name">Nume</label>
        <input id="name" name="name" required maxlength="100" value="<?= e($data['name']) ?>"
               <?= isset($errors['name']) ? 'aria-invalid="true" aria-describedby="err-name"' : '' ?>>
        <?= field_err($errors, 'name') ?>

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?= e($data['email']) ?>"
               <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="err-email"' : '' ?>>
        <?= field_err($errors, 'email') ?>

        <label for="text">Mesaj</label>
        <textarea id="text" name="text" rows="6" required minlength="10"
                  <?= isset($errors['text']) ? 'aria-invalid="true" aria-describedby="err-text"' : '' ?>><?= e($data['text']) ?></textarea>
        <?= field_err($errors, 'text') ?>

        <button class="btn" type="submit">Trimite mesajul</button>
    </form>
    <aside class="card">
        <h2>Unde ne găsiți</h2>
        <p>Baza sportivă LEII Sudului, Craiova</p>
        <p class="muted">Antrenamente: luni–vineri 17:00–19:00, sâmbătă 10:00–12:00</p>
        <p>Email: <a href="mailto:<?= e(CLUB_EMAIL) ?>"><?= e(CLUB_EMAIL) ?></a></p>
    </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
