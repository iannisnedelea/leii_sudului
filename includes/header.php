<?php
/** @var string $title  titlul paginii, setat înainte de include */
$u = current_user();
$self = basename($_SERVER['SCRIPT_NAME']);
$inAdmin = str_contains($_SERVER['SCRIPT_NAME'], '/admin/');
$nav = [
    'index.php'    => 'Acasă',
    'teams.php'    => 'Echipe',
    'coaches.php'  => 'Antrenori',
    'matches.php'  => 'Meciuri',
    'sponsors.php' => 'Sponsori',
    'contact.php'  => 'Contact',
];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? '') ?> · LEII Sudului Craiova</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<a class="skip" href="#main">Sari la conținut</a>
<header class="site-header">
    <div class="wrap header-inner">
        <a class="brand" href="<?= base_url() ?>">
            <img class="crest" src="<?= base_url('assets/img/logo.jpg') ?>" alt="" width="48" height="48">
            <span><strong>LEII Sudului</strong><small>Academia de Fotbal · Craiova</small></span>
        </a>
        <button class="menu-btn" aria-expanded="false" aria-controls="nav">Meniu</button>
        <nav id="nav" class="nav" aria-label="Navigare principală">
            <?php foreach ($nav as $href => $label): ?>
                <a href="<?= base_url($href) ?>" <?= !$inAdmin && $self === $href ? 'aria-current="page"' : '' ?>><?= $label ?></a>
            <?php endforeach; ?>
            <?php if ($u): ?>
                <a class="nav-staff" href="<?= base_url('admin/dashboard.php') ?>" <?= $inAdmin ? 'aria-current="page"' : '' ?>>Panou staff</a>
            <?php else: ?>
                <a class="nav-staff" href="<?= base_url('login.php') ?>">Autentificare</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main id="main" class="wrap">
<?php if ($f = flash()): ?>
    <div class="flash flash-<?= e($f['type']) ?>" role="status"><?= e($f['msg']) ?></div>
<?php endif; ?>
