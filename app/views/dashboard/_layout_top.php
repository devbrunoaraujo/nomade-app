<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#4B2C7F">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Nomade">
  <link rel="manifest" href="<?= ASSETS_URL ?>/manifest.json">
  <link rel="apple-touch-icon" href="<?= ASSETS_URL ?>/icons/icon-192.svg">
  <title><?= $pageTitle ?? 'Nomade Agenda' ?></title>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
</head>
<body>

<?php
$ap   = $activePage ?? '';
$nome = $_SESSION['morador_nome'] ?? '';
$apt  = $_SESSION['morador_apartamento'] ?? '';

?>

<!-- ── TOPBAR (mobile) ────────────────────────────────── -->
<header class="topbar">
  <div class="topbar-logo">
    <img src="<?= ASSETS_URL ?>/icons/logo-nomade.png" alt="Nomade" height="22">
  </div>
  <span class="topbar-apt">Apto <?= htmlspecialchars($apt) ?></span>
</header>

<!-- ── SIDEBAR OVERLAY ───────────────────────────────── -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ── APP WRAPPER ───────────────────────────────────── -->
<div class="app-wrapper">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <img src="<?= ASSETS_URL ?>/icons/logo-nomade.png" alt="Nomade">
    </div>

    <div class="nav-section">
      <div class="nav-label">Menu</div>

      <a href="index.php?page=dashboard" class="nav-item <?= $ap === 'dashboard' ? 'active' : '' ?>">
        <span class="nav-icon"><?= icon('home') ?></span> Início
      </a>
      <a href="index.php?page=lavanderia" class="nav-item <?= $ap === 'lavanderia' ? 'active' : '' ?>">
        <span class="nav-icon"><?= icon('laundry') ?></span> Lavanderia
      </a>
      <a href="index.php?page=churrasqueira" class="nav-item <?= $ap === 'churrasqueira' ? 'active' : '' ?>">
        <span class="nav-icon"><?= icon('bbq') ?></span> Churrasqueira
      </a>
    </div>

    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-name"><?= htmlspecialchars($nome) ?></div>
        <div class="user-apt">Apt <?= htmlspecialchars($apt) ?></div>
      </div>
      <a href="index.php?page=logout" class="nav-item">
        <span class="nav-icon"><?= icon('logout') ?></span> Sair
      </a>
    </div>
  </aside>

  <main class="main-content">

<!-- ── BOTTOM NAV (mobile) ───────────────────────────── -->
<nav class="bottom-nav">
  <div class="bottom-nav-inner">
    <a href="index.php?page=dashboard" class="bottom-nav-item <?= $ap === 'dashboard' ? 'active' : '' ?>">
      <?= icon('home') ?> Início
    </a>
    <a href="index.php?page=lavanderia" class="bottom-nav-item <?= $ap === 'lavanderia' ? 'active' : '' ?>">
      <?= icon('laundry') ?> Lavanderia
    </a>
    <a href="index.php?page=churrasqueira" class="bottom-nav-item <?= $ap === 'churrasqueira' ? 'active' : '' ?>">
      <?= icon('bbq') ?> Churrasqueira
    </a>
    <a href="index.php?page=logout" class="bottom-nav-item">
      <?= icon('logout') ?> Sair
    </a>
  </div>
</nav>
