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
  <title>Nomade – Entrar</title>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
</head>
<body>

<?php $base = ASSETS_URL; ?>

<div class="login-page">
  <div class="login-box">

    <div class="login-header">
      <img src="<?= ASSETS_URL ?>/icons/logo-nomade.png" alt="Nomade" class="login-logo-img">
      <p class="login-sub">Sistema de Agendamento do Condomínio</p>
    </div>

    <div class="login-card">

      <?php
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        if ($flash):
      ?>
      <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>" id="flash-msg">
        <?= $flash['type'] === 'error' ? '⚠' : '✓' ?>
        <?= htmlspecialchars($flash['msg']) ?>
      </div>
      <?php endif; ?>

      <div class="login-tabs" role="tablist">
        <button class="login-tab active" role="tab" onclick="switchTab('morador',this)">Morador</button>
        <button class="login-tab"        role="tab" onclick="switchTab('admin',this)">Administrador</button>
      </div>

      <!-- MORADOR -->
      <div id="tab-morador">
        <form action="index.php?page=do-login" method="POST" autocomplete="on">
          <div class="form-group">
            <label class="form-label" for="apt">Apartamento</label>
            <input type="text" id="apt" name="apartamento" class="form-input"
                   placeholder="Ex: 101" required autocomplete="username" inputmode="text">
          </div>
          <div class="form-group">
            <label class="form-label" for="senha-m">Senha</label>
            <input type="password" id="senha-m" name="senha" class="form-input"
                   placeholder="••••••••" required autocomplete="current-password">
          </div>
          <button type="submit" class="btn btn-primary btn-block" style="margin-top:6px">
            Entrar
          </button>
        </form>
      </div>

      <!-- ADMIN -->
      <div id="tab-admin" style="display:none">
        <form action="index.php?page=do-admin-login" method="POST">
          <div class="form-group">
            <label class="form-label" for="usuario">Usuário</label>
            <input type="text" id="usuario" name="usuario" class="form-input"
                   placeholder="admin" required autocomplete="username">
          </div>
          <div class="form-group">
            <label class="form-label" for="senha-a">Senha</label>
            <input type="password" id="senha-a" name="senha" class="form-input"
                   placeholder="••••••••" required autocomplete="current-password">
          </div>
          <button type="submit" class="btn btn-primary btn-block" style="margin-top:6px">
            Entrar como Admin
          </button>
        </form>
      </div>

      <div class="divider"></div>
      <p style="font-size:11px;color:var(--text-dim);text-align:center;line-height:1.6">
        © Desenvolvido por <strong style="color:var(--text-muted)">Bruno Araújo</strong>
      </p>

    </div>
  </div>
</div>

<div id="toast-container" aria-live="polite"></div>
<script src="<?= ASSETS_URL ?>/js/app.js"></script>
<script>
function switchTab(tab, el) {
  document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('tab-morador').style.display = tab === 'morador' ? '' : 'none';
  document.getElementById('tab-admin').style.display   = tab === 'admin'   ? '' : 'none';
}

// Auto-remove flash
document.addEventListener('DOMContentLoaded', function() {
  const fm = document.getElementById('flash-msg');
  if (fm) {
    setTimeout(() => {
      fm.style.transition = 'opacity 0.5s';
      fm.style.opacity = '0';
      setTimeout(() => { if (fm.parentNode) fm.remove(); }, 500);
    }, 5000);
  }
});

if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('<?= ASSETS_URL ?>/sw.js').catch(() => {});
}
</script>
</body>
</html>
