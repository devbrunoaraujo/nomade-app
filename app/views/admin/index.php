<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#4B2C7F">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <link rel="manifest" href="<?= ASSETS_URL ?>/manifest.json">
  <title>Admin – Nomade</title>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <style>
    .admin-wrap { display:flex; min-height:100vh; min-height:100dvh; }

    /* ── Sidebar ── */
    .admin-side {
      width:220px; background:var(--bg-card); border-right:1px solid var(--border);
      padding:0 0 20px; display:flex; flex-direction:column;
      position:fixed; top:0; left:0; bottom:0; overflow-y:auto; z-index:50;
      transition:transform 0.3s ease, box-shadow 0.3s ease;
    }
    .admin-side-logo {
      display:flex; align-items:center;
      padding:20px 16px 18px; border-bottom:1px solid var(--border);
      margin-bottom:10px; flex-shrink:0;
    }
    .admin-side-logo img { height:26px; width:auto; }
    .admin-side-badge {
      margin-left:10px; background:var(--accent-dim);
      border:1px solid rgba(162,197,35,0.3); border-radius:5px;
      padding:2px 7px; font-size:10px; font-weight:700;
      color:var(--accent-text); letter-spacing:0.5px;
    }
    .admin-main { margin-left:220px; flex:1; padding:var(--content-pad); max-width:100%; min-width:0; }

    /* ── Topbar mobile ── */
    .admin-topbar {
      display:none; position:fixed; top:0; left:0; right:0;
      height:var(--topbar-h); background:var(--bg-card);
      border-bottom:1px solid var(--border);
      align-items:center; padding:0 14px; gap:10px; z-index:100;
    }
    .admin-topbar img { height:22px; width:auto; }
    .admin-topbar-badge {
      background:var(--accent-dim); border:1px solid rgba(162,197,35,0.3);
      border-radius:4px; padding:2px 6px; font-size:10px; font-weight:700;
      color:var(--accent-text);
    }

    /* ── Bottom nav mobile ── */
    .admin-bottom-nav {
      display:none; position:fixed; bottom:0; left:0; right:0;
      background:var(--bg-card); border-top:1px solid var(--border);
      z-index:100; padding-bottom:env(safe-area-inset-bottom);
    }
    .admin-bottom-nav .bottom-nav-inner { display:flex; height:64px; }

    /* ── Overlay ── */
    .admin-overlay {
      display:none; position:fixed; inset:0;
      background:rgba(20,8,40,0.7); z-index:49; backdrop-filter:blur(3px);
    }
    .admin-overlay.open { display:block; }

    /* ── Page tabs ── */
    .page-tabs {
      display:flex; border-bottom:1px solid var(--border);
      margin-bottom:20px; overflow-x:auto; -webkit-overflow-scrolling:touch;
    }
    .page-tab {
      display:flex; align-items:center; gap:7px;
      padding:10px 18px; font-size:13px; font-weight:600;
      cursor:pointer; border:none; background:none; color:var(--text-muted);
      font-family:'Inter',sans-serif; transition:var(--transition);
      white-space:nowrap; -webkit-tap-highlight-color:transparent;
      border-bottom:2px solid transparent; margin-bottom:-1px;
    }
    .page-tab svg { width:15px; height:15px; stroke:currentColor; fill:none; stroke-width:1.75; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0; }
    .page-tab.active { color:var(--accent-text); border-bottom-color:var(--accent); }
    .page-tab:hover  { color:var(--text); }

    /* ── Filter chips ── */
    .filter-chips { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px; }
    .chip {
      padding:5px 13px; border-radius:20px; font-size:11px; font-weight:600;
      cursor:pointer; border:1px solid var(--border); background:var(--bg-surface);
      color:var(--text-muted); font-family:'Inter',sans-serif; transition:var(--transition);
      -webkit-tap-highlight-color:transparent;
    }
    .chip.active { background:var(--accent-dim); border-color:rgba(162,197,35,0.4); color:var(--accent-text); }
    .chip:hover:not(.active) { border-color:var(--border-light); color:var(--text); }

    /* ── Morador cards mobile ── */
    .morador-card {
      background:var(--bg-surface); border:1px solid var(--border);
      border-radius:var(--radius-sm); padding:14px;
      display:flex; align-items:center; gap:12px;
    }
    .morador-avatar {
      width:40px; height:40px; border-radius:50%;
      background:var(--accent-dim); border:1px solid rgba(162,197,35,0.35);
      display:flex; align-items:center; justify-content:center;
      font-size:15px; font-weight:700; color:var(--accent-text); flex-shrink:0;
    }
    .morador-info { flex:1; min-width:0; }
    .morador-name { font-size:14px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .morador-meta { font-size:12px; color:var(--text-muted); margin-top:2px; }
    .morador-actions { display:flex; gap:6px; flex-shrink:0; }

    /* ── Agenda cards ── */
    .agenda-card {
      background:var(--bg-surface); border:1px solid var(--border);
      border-radius:var(--radius-sm); padding:13px 15px;
      display:flex; align-items:center; gap:12px; flex-wrap:wrap;
    }
    .agenda-card-left  { flex:1; min-width:0; }
    .agenda-card-right { flex-shrink:0; }
    .agenda-apt  { font-size:11px; color:var(--text-muted); margin-bottom:2px; }
    .agenda-name { font-size:13px; font-weight:600; color:var(--text); }
    .agenda-when { font-size:12px; color:var(--text-muted); margin-top:3px; font-family:'JetBrains Mono',monospace; }

    /* ── Mini stats ── */
    .mini-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:20px; }
    .mini-stat  {
      background:var(--bg-card); border:1px solid var(--border);
      border-radius:var(--radius-sm); padding:14px 12px; text-align:center;
    }
    .mini-stat-val { font-size:22px; font-weight:700; font-family:'JetBrains Mono',monospace; }
    .mini-stat-lbl { font-size:10px; font-weight:700; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.8px; margin-top:3px; }

    /* ── Settings ── */
    .settings-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .settings-field { display:flex; flex-direction:column; gap:6px; }
    .settings-label { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.6px; }
    .settings-hint  { font-size:11px; color:var(--text-dim); margin-top:2px; }

    /* ── Btn with icon ── */
    .btn-icon-text { display:inline-flex; align-items:center; gap:6px; }
    .btn-icon-text svg { width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0; }

    /* ── Responsive ── */
    @media (max-width:767px) {
      .admin-topbar     { display:flex; }
      .admin-bottom-nav { display:block; }
      .admin-side       { transform:translateX(-100%); }
      .admin-side.open  { transform:translateX(0); box-shadow:4px 0 30px rgba(20,8,40,0.7); }
      .admin-main       {
        margin-left:0;
        padding-top:calc(var(--topbar-h) + var(--content-pad));
        padding-bottom:calc(var(--bottomnav-h) + var(--content-pad));
      }
      .hide-mobile     { display:none !important; }
      .settings-grid   { grid-template-columns:1fr; }
    }
    @media (max-width:400px) {
      .mini-stats { grid-template-columns:1fr 1fr; }
      .mini-stats .mini-stat:last-child { grid-column:span 2; }
    }
  </style>
</head>
<body>


<!-- ── Topbar mobile ─────────────────────────────────── -->
<header class="admin-topbar" id="adminTopbar">
  <button id="btnToggleSide" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px;display:flex;align-items:center">
    <?= icon('menu', 20) ?>
  </button>
  <img src="<?= ASSETS_URL ?>/icons/logo-nomade.png" alt="Nomade">
  <span class="admin-topbar-badge">Admin</span>
  <button id="btnNovoMobile" class="btn btn-primary btn-sm" style="margin-left:auto">
    <span class="btn-icon-text"><?= icon('plus') ?> Novo</span>
  </button>
</header>

<!-- ── Overlay ───────────────────────────────────────── -->
<div class="admin-overlay" id="adminOverlay"></div>

<!-- ── Bottom nav mobile ─────────────────────────────── -->
<nav class="admin-bottom-nav">
  <div class="bottom-nav-inner">
    <button class="bottom-nav-item active" id="bn-moradores">
      <?= icon('users') ?> Moradores
    </button>
    <button class="bottom-nav-item" id="bn-agendamentos">
      <?= icon('calendar') ?> Agenda
    </button>
    <button class="bottom-nav-item" id="bn-configuracoes">
      <?= icon('settings') ?> Config
    </button>
    <a href="index.php?page=admin&action=logout" class="bottom-nav-item">
      <?= icon('logout') ?> Sair
    </a>
  </div>
</nav>

<div class="admin-wrap">

  <!-- ── Sidebar desktop ────────────────────────────── -->
  <aside class="admin-side" id="adminSide">
    <div class="admin-side-logo">
      <img src="<?= ASSETS_URL ?>/icons/logo-nomade.png" alt="Nomade">
      <span class="admin-side-badge">Admin</span>
    </div>

    <div style="padding:0 10px">
      <div class="nav-label">Painel</div>
      <button class="nav-item active" id="side-moradores">
        <span class="nav-icon"><?= icon('users') ?></span> Moradores
      </button>
      <button class="nav-item" id="side-agendamentos">
        <span class="nav-icon"><?= icon('calendar') ?></span> Agendamentos
      </button>
      <button class="nav-item" id="side-configuracoes">
        <span class="nav-icon"><?= icon('settings') ?></span> Configurações
      </button>
    </div>

    <div style="margin-top:auto;padding:14px 10px 0;border-top:1px solid var(--border);flex-shrink:0">
      <a href="index.php?page=admin&action=logout" class="nav-item">
        <span class="nav-icon"><?= icon('logout') ?></span> Sair
      </a>
    </div>
  </aside>

  <!-- ── Main ─────────────────────────────────────────── -->
  <main class="admin-main">

    <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
      <div>
        <h1 class="page-title">Painel Administrativo</h1>
        <p class="page-subtitle">Gerencie moradores e acompanhe todos os agendamentos</p>
      </div>
      <button id="btnNovoDesktop" class="btn btn-primary hide-mobile">
        <span class="btn-icon-text"><?= icon('plus') ?> Novo Morador</span>
      </button>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type']==='error'?'error':'success' ?>" id="flash-msg">
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="mini-stats">
      <div class="mini-stat">
        <div class="mini-stat-val" style="color:var(--accent-text)"><?= count($moradores) ?></div>
        <div class="mini-stat-lbl">Moradores</div>
      </div>
      <div class="mini-stat">
        <div class="mini-stat-val" style="color:var(--accent-text)"><?= count(array_filter($moradores, fn($m) => $m['ativo'])) ?></div>
        <div class="mini-stat-lbl">Ativos</div>
      </div>
      <div class="mini-stat">
        <div class="mini-stat-val" style="color:var(--lavender)" id="stat-agendamentos">—</div>
        <div class="mini-stat-lbl">Futuros</div>
      </div>
    </div>

    <!-- Page Tabs -->
    <div class="page-tabs">
      <button class="page-tab active" id="tab-btn-moradores">
        <?= icon('users', 15) ?> Moradores
      </button>
      <button class="page-tab" id="tab-btn-agendamentos">
        <?= icon('calendar', 15) ?> Agendamentos
      </button>
      <button class="page-tab" id="tab-btn-configuracoes">
        <?= icon('settings', 15) ?> Configurações
      </button>
    </div>

    <!-- ═══════════════════════════════════════
         TAB: MORADORES
    ═══════════════════════════════════════ -->
    <div id="maintab-moradores">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap">
        <div style="flex:1;position:relative;min-width:200px">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-dim);display:flex;align-items:center">
            <?= icon('search', 14) ?>
          </span>
          <input type="search" id="search-moradores" class="form-input"
                 placeholder="Buscar por nome ou apartamento..."
                 style="padding-left:36px">
        </div>
        <button id="btnNovoSearch" class="btn btn-primary hide-mobile">
          <span class="btn-icon-text"><?= icon('plus') ?> Novo Morador</span>
        </button>
      </div>

      <!-- Tabela desktop -->
      <div class="card" id="tabela-desktop">
        <div class="table-wrap">
          <table id="tabela-moradores">
            <thead>
              <tr>
                <th>Nome</th><th>Apartamento</th><th>Status</th>
                <th>Cadastro</th><th style="text-align:right">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($moradores as $m): ?>
              <tr data-search="<?= strtolower($m['nome'].' '.$m['apartamento']) ?>">
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--accent-dim);border:1px solid rgba(162,197,35,0.35);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--accent-text);flex-shrink:0">
                      <?= mb_strtoupper(mb_substr($m['nome'],0,1)) ?>
                    </div>
                    <strong><?= htmlspecialchars($m['nome']) ?></strong>
                  </div>
                </td>
                <td><span class="badge badge-blue">Apto <?= htmlspecialchars($m['apartamento']) ?></span></td>
                <td>
                  <?php if ($m['ativo']): ?>
                    <span class="badge badge-green">Ativo</span>
                  <?php else: ?>
                    <span class="badge" style="background:var(--bg-surface);color:var(--text-dim);border:1px solid var(--border)">Inativo</span>
                  <?php endif; ?>
                </td>
                <td style="color:var(--text-dim);font-size:12px;font-family:'JetBrains Mono',monospace">
                  <?= date('d/m/Y', strtotime($m['criado_em'])) ?>
                </td>
                <td>
                  <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap">
                    <button class="btn btn-ghost btn-sm btn-editar"
                      data-id="<?= $m['id'] ?>"
                      data-nome="<?= htmlspecialchars($m['nome'], ENT_QUOTES) ?>"
                      data-apt="<?= htmlspecialchars($m['apartamento'], ENT_QUOTES) ?>">
                      <span class="btn-icon-text"><?= icon('edit') ?> Editar</span>
                    </button>
                    <button class="btn btn-sm btn-toggle"
                      data-id="<?= $m['id'] ?>"
                      data-ativo="<?= $m['ativo'] ?>"
                      style="background:var(--amber-dim);color:var(--amber);border:1px solid rgba(245,200,66,0.25)">
                      <?php if ($m['ativo']): ?>
                        <span class="btn-icon-text"><?= icon('ban') ?> Desativar</span>
                      <?php else: ?>
                        <span class="btn-icon-text"><?= icon('check') ?> Ativar</span>
                      <?php endif; ?>
                    </button>
                    <button class="btn btn-danger btn-sm btn-deletar"
                      data-id="<?= $m['id'] ?>"
                      data-nome="<?= htmlspecialchars($m['nome'], ENT_QUOTES) ?>"
                      data-apt="<?= htmlspecialchars($m['apartamento'], ENT_QUOTES) ?>">
                      <span class="btn-icon-text"><?= icon('trash') ?> Excluir</span>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Cards mobile -->
      <div id="cards-mobile" style="display:none;flex-direction:column;gap:10px">
        <?php foreach ($moradores as $m): ?>
        <div class="morador-card" data-search="<?= strtolower($m['nome'].' '.$m['apartamento']) ?>">
          <div class="morador-avatar"><?= mb_strtoupper(mb_substr($m['nome'],0,1)) ?></div>
          <div class="morador-info">
            <div class="morador-name"><?= htmlspecialchars($m['nome']) ?></div>
            <div class="morador-meta">
              Apto <?= htmlspecialchars($m['apartamento']) ?> ·
              <?= $m['ativo']
                ? '<span style="color:var(--accent-text)">Ativo</span>'
                : '<span style="color:var(--text-dim)">Inativo</span>' ?>
            </div>
          </div>
          <div class="morador-actions">
            <button class="btn btn-ghost btn-sm btn-editar"
              data-id="<?= $m['id'] ?>"
              data-nome="<?= htmlspecialchars($m['nome'], ENT_QUOTES) ?>"
              data-apt="<?= htmlspecialchars($m['apartamento'], ENT_QUOTES) ?>"
              title="Editar">
              <?= icon('edit') ?>
            </button>
            <button class="btn btn-danger btn-sm btn-deletar"
              data-id="<?= $m['id'] ?>"
              data-nome="<?= htmlspecialchars($m['nome'], ENT_QUOTES) ?>"
              data-apt="<?= htmlspecialchars($m['apartamento'], ENT_QUOTES) ?>"
              title="Excluir">
              <?= icon('trash') ?>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($moradores)): ?>
        <div class="empty-state">
          <div class="empty-state-icon"><?= icon('users', 40) ?></div>
          <p>Nenhum morador cadastrado</p>
        </div>
        <?php endif; ?>
      </div>
    </div><!-- /moradores -->

    <!-- ═══════════════════════════════════════
         TAB: AGENDAMENTOS
    ═══════════════════════════════════════ -->
    <div id="maintab-agendamentos" style="display:none">

      <div class="tab-bar" style="margin-bottom:14px">
        <button class="tab-btn active" id="subtab-btn-lav">Lavanderia</button>
        <button class="tab-btn"        id="subtab-btn-churr">Churrasqueira</button>
      </div>

      <!-- Lavanderia -->
      <div id="subtab-lav">
        <div class="filter-chips">
          <button class="chip active" data-tipo="lav" data-filtro="todos">Todos</button>
          <button class="chip"        data-tipo="lav" data-filtro="futuros">Futuros</button>
          <button class="chip"        data-tipo="lav" data-filtro="historico">Histórico</button>
        </div>
        <div class="card">
          <div id="lav-loading" style="text-align:center;padding:32px"><span class="spinner"></span></div>
          <div id="lav-content" style="display:none">
            <div class="table-wrap" id="lav-table-wrap">
              <table>
                <thead><tr><th>Morador</th><th>Apto</th><th>Data</th><th>Início</th><th>Fim</th><th>Duração</th><th>Status</th></tr></thead>
                <tbody id="tbody-lav"></tbody>
              </table>
            </div>
            <div id="lav-cards" style="display:none;flex-direction:column;gap:8px"></div>
          </div>
        </div>
      </div>

      <!-- Churrasqueira -->
      <div id="subtab-churr" style="display:none">
        <div class="filter-chips">
          <button class="chip active" data-tipo="churr" data-filtro="todos">Todos</button>
          <button class="chip"        data-tipo="churr" data-filtro="futuros">Futuros</button>
          <button class="chip"        data-tipo="churr" data-filtro="historico">Histórico</button>
        </div>
        <div class="card">
          <div id="churr-loading" style="text-align:center;padding:32px"><span class="spinner"></span></div>
          <div id="churr-content" style="display:none">
            <div class="table-wrap" id="churr-table-wrap">
              <table>
                <thead><tr><th>Morador</th><th>Apto</th><th>Data</th><th>Turno</th><th>Horário</th><th>Status</th></tr></thead>
                <tbody id="tbody-churr"></tbody>
              </table>
            </div>
            <div id="churr-cards" style="display:none;flex-direction:column;gap:8px"></div>
          </div>
        </div>
      </div>

    </div><!-- /agendamentos -->

    <!-- ═══════════════════════════════════════
         TAB: CONFIGURAÇÕES
    ═══════════════════════════════════════ -->
    <div id="maintab-configuracoes" style="display:none">

      <div class="card" style="max-width:640px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
          <span style="color:var(--lavender)"><?= icon('sliders', 20) ?></span>
          <div>
            <div style="font-size:15px;font-weight:700;color:var(--text)">Parâmetros do Sistema</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Ajuste os limites de uso e horários de funcionamento</div>
          </div>
        </div>

        <div id="config-loading" style="text-align:center;padding:24px"><span class="spinner"></span></div>

        <div id="config-form" style="display:none">

          <div style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border)">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:1px;margin-bottom:14px">Nome do Condomínio</div>
            <div class="form-group" style="margin:0">
              <input type="text" id="cfg-nome" class="form-input" placeholder="Ex: Residencial das Flores">
              <div class="settings-hint">Exibido no topo do sistema</div>
            </div>
          </div>

          <div style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border)">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:1px;margin-bottom:14px">Lavanderia</div>
            <div class="settings-grid">
              <div class="settings-field">
                <label class="settings-label">Horas por semana</label>
                <input type="number" id="cfg-horas-lav" class="form-input" min="1" max="24" step="0.5">
                <div class="settings-hint">Crédito semanal por morador (ex: 4)</div>
              </div>
              <div class="settings-field">
                <label class="settings-label">Horário de abertura</label>
                <input type="time" id="cfg-abertura" class="form-input">
              </div>
              <div class="settings-field" style="grid-column:2">
                <label class="settings-label">Horário de fechamento</label>
                <input type="time" id="cfg-fechamento" class="form-input">
              </div>
            </div>
          </div>

          <div style="margin-bottom:24px">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:1px;margin-bottom:14px">Churrasqueira</div>
            <div class="settings-grid">
              <div class="settings-field">
                <label class="settings-label">Usos por mês</label>
                <input type="number" id="cfg-usos-churr" class="form-input" min="1" max="10" step="1">
                <div class="settings-hint">Reservas mensais por morador (ex: 2)</div>
              </div>
            </div>
          </div>

          <div id="config-erro" class="form-error" style="display:none;margin-bottom:14px"></div>

          <button id="btn-salvar-config" class="btn btn-primary">
            <span class="btn-icon-text"><?= icon('save') ?> Salvar Configurações</span>
          </button>
        </div>
      </div>
    </div><!-- /configuracoes -->

  </main>
</div><!-- /admin-wrap -->

<!-- ═══════════════════════════════════════════════
     MODAIS
═══════════════════════════════════════════════ -->

<!-- Modal Criar -->
<div id="modal-criar" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-title">Novo Morador</div>
    <div class="form-group">
      <label class="form-label">Nome Completo</label>
      <input type="text" id="c-nome" class="form-input" placeholder="Ex: João da Silva" autocomplete="off">
    </div>
    <div class="form-group">
      <label class="form-label">Apartamento</label>
      <input type="text" id="c-apt" class="form-input" placeholder="Ex: 101" autocomplete="off">
    </div>
    <div class="form-group">
      <label class="form-label">Senha</label>
      <input type="password" id="c-senha" class="form-input" placeholder="Mínimo 4 caracteres">
    </div>
    <div class="form-group">
      <label class="form-label">Confirmar Senha</label>
      <input type="password" id="c-senha2" class="form-input" placeholder="Repita a senha">
    </div>
    <div id="criar-erro" class="form-error" style="display:none"></div>
    <div class="modal-footer">
      <button id="btn-criar-cancelar" class="btn btn-ghost">Cancelar</button>
      <button id="btn-criar-salvar"   class="btn btn-primary">
        <span class="btn-icon-text"><?= icon('plus') ?> Cadastrar</span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div id="modal-editar" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-title">Editar Morador</div>
    <input type="hidden" id="e-id">
    <div class="form-group">
      <label class="form-label">Nome Completo</label>
      <input type="text" id="e-nome" class="form-input">
    </div>
    <div class="form-group">
      <label class="form-label">Apartamento</label>
      <input type="text" id="e-apt" class="form-input">
    </div>
    <div class="form-group">
      <label class="form-label">
        Nova Senha
        <span style="font-weight:400;color:var(--text-dim);text-transform:none;letter-spacing:0"> — deixe em branco para manter</span>
      </label>
      <input type="password" id="e-senha" class="form-input" placeholder="Nova senha (opcional)">
    </div>
    <div id="editar-erro" class="form-error" style="display:none"></div>
    <div class="modal-footer">
      <button id="btn-editar-cancelar" class="btn btn-ghost">Cancelar</button>
      <button id="btn-editar-salvar"   class="btn btn-primary">
        <span class="btn-icon-text"><?= icon('save') ?> Salvar</span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Deletar -->
<div id="modal-deletar" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:420px">
    <div class="modal-title" style="color:var(--red)">Excluir Morador</div>
    <div class="card-sm" style="margin-bottom:16px">
      <div style="font-size:13px;color:var(--text-muted);margin-bottom:6px">Você está prestes a excluir permanentemente:</div>
      <div style="font-size:16px;font-weight:700;color:var(--text)"  id="del-nome-display"></div>
      <div style="font-size:13px;color:var(--text-muted);margin-top:3px" id="del-apt-display"></div>
    </div>
    <div class="modal-warning">
      Esta ação é irreversível. Agendamentos futuros precisam ser cancelados antes de excluir o morador.
    </div>
    <input type="hidden" id="del-id">
    <div id="deletar-erro" class="form-error" style="display:none"></div>
    <div class="modal-footer">
      <button id="btn-deletar-cancelar" class="btn btn-ghost">Cancelar</button>
      <button id="btn-deletar-salvar"   class="btn btn-danger" style="background:var(--red);color:#fff;border:none">
        <span class="btn-icon-text"><?= icon('trash') ?> Confirmar Exclusão</span>
      </button>
    </div>
  </div>
</div>

<div id="toast-container" aria-live="polite"></div>
<script src="<?= ASSETS_URL ?>/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  const TL = { manha:'Manhã', tarde:'Tarde', noite:'Noite' };
  const TH = { manha:'08:00–12:00', tarde:'12:00–17:00', noite:'17:00–22:00' };

  let dadosAgenda     = { lavanderia:[], churrasqueira:[] };
  let filtroAtivo     = { lav:'todos', churr:'todos' };
  let agendaCarregada = false;
  let configCarregada = false;

  function $(id) { return document.getElementById(id); }
  function isMobile() { return window.innerWidth <= 767; }
  function hoje() { return new Date().toISOString().split('T')[0]; }
  function fmt(d) { const [y,m,v] = d.split('-'); return `${v}/${m}/${y}`; }

  function setLoading(btn, on, label) {
    btn.disabled = on;
    btn.innerHTML = on ? '<span class="spinner"></span>' : label;
  }
  function showErro(el, msg) { el.textContent = '⚠ ' + msg; el.style.display = ''; }
  function hideErro(el)      { el.style.display = 'none'; el.textContent = ''; }

  // ── Layout ───────────────────────────────────
  function aplicarLayout() {
    const mob = isMobile();
    $('tabela-desktop').style.display = mob ? 'none' : '';
    $('cards-mobile').style.display   = mob ? 'flex' : 'none';
    if (agendaCarregada) renderAgendamentos();
  }
  aplicarLayout();
  window.addEventListener('resize', aplicarLayout);

  // Flash auto-remove
  const fm = $('flash-msg');
  if (fm) setTimeout(() => { fm.style.transition='opacity 0.5s'; fm.style.opacity='0'; setTimeout(() => fm.remove(), 500); }, 5000);

  // ── Sidebar mobile ────────────────────────────
  $('btnToggleSide').addEventListener('click', () => {
    $('adminSide').classList.toggle('open');
    $('adminOverlay').classList.toggle('open');
    document.body.style.overflow = $('adminSide').classList.contains('open') ? 'hidden' : '';
  });
  $('adminOverlay').addEventListener('click', () => {
    $('adminSide').classList.remove('open');
    $('adminOverlay').classList.remove('open');
    document.body.style.overflow = '';
  });

  // ── Abrir modais ──────────────────────────────
  [$('btnNovoDesktop'), $('btnNovoMobile'), $('btnNovoSearch')].forEach(b => {
    if (b) b.addEventListener('click', abrirModalCriar);
  });

  // Delegação: editar / deletar / toggle
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('button');
    if (!btn) return;
    if (btn.classList.contains('btn-editar'))  abrirModalEditar(btn.dataset.id, btn.dataset.nome, btn.dataset.apt);
    if (btn.classList.contains('btn-deletar')) abrirModalDeletar(btn.dataset.id, btn.dataset.nome, btn.dataset.apt);
    if (btn.classList.contains('btn-toggle'))  toggleMorador(btn.dataset.id, btn.dataset.ativo);
  });

  // Fechar modais
  ['modal-criar','modal-editar','modal-deletar'].forEach(id => {
    $(id).addEventListener('click', e => { if (e.target === $(id)) fecharModal(id); });
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['modal-criar','modal-editar','modal-deletar'].forEach(id => {
      if ($(id) && $(id).style.display !== 'none') fecharModal(id);
    });
  });

  $('btn-criar-cancelar').addEventListener('click',   () => fecharModal('modal-criar'));
  $('btn-editar-cancelar').addEventListener('click',  () => fecharModal('modal-editar'));
  $('btn-deletar-cancelar').addEventListener('click', () => fecharModal('modal-deletar'));
  $('btn-criar-salvar').addEventListener('click',     criarMorador);
  $('btn-editar-salvar').addEventListener('click',    editarMorador);
  $('btn-deletar-salvar').addEventListener('click',   deletarMorador);
  $('btn-salvar-config').addEventListener('click',    salvarConfiguracoes);

  function fecharModal(id) { $(id).style.display = 'none'; }

  // ── Tabs principais ───────────────────────────
  const TABS = ['moradores', 'agendamentos', 'configuracoes'];

  function showMainTab(tab) {
    $('adminSide').classList.remove('open');
    $('adminOverlay').classList.remove('open');
    document.body.style.overflow = '';

    TABS.forEach(t => {
      const pageTab = $('tab-btn-' + t);
      const sideBtn = $('side-' + t);
      const bnBtn   = $('bn-' + t);
      const content = $('maintab-' + t);
      const active  = t === tab;
      if (pageTab) pageTab.classList.toggle('active', active);
      if (sideBtn) sideBtn.classList.toggle('active', active);
      if (bnBtn)   bnBtn.classList.toggle('active', active);
      if (content) content.style.display = active ? '' : 'none';
    });

    if (tab === 'agendamentos' && !agendaCarregada) carregarAgendamentos();
    if (tab === 'configuracoes' && !configCarregada) carregarConfiguracoes();
  }

  TABS.forEach(t => {
    const btns = [$('tab-btn-'+t), $('side-'+t), $('bn-'+t)];
    btns.forEach(b => { if (b) b.addEventListener('click', () => showMainTab(t)); });
  });

  // Sub-tabs agendamentos
  $('subtab-btn-lav').addEventListener('click', function() {
    this.classList.add('active'); $('subtab-btn-churr').classList.remove('active');
    $('subtab-lav').style.display = ''; $('subtab-churr').style.display = 'none';
  });
  $('subtab-btn-churr').addEventListener('click', function() {
    this.classList.add('active'); $('subtab-btn-lav').classList.remove('active');
    $('subtab-lav').style.display = 'none'; $('subtab-churr').style.display = '';
  });

  // Filter chips
  document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', function() {
      const tipo = this.dataset.tipo, filtro = this.dataset.filtro;
      document.querySelectorAll(`.chip[data-tipo="${tipo}"]`).forEach(c => c.classList.remove('active'));
      this.classList.add('active');
      filtroAtivo[tipo] = filtro;
      renderAgendamentos();
    });
  });

  // Busca moradores
  $('search-moradores').addEventListener('input', function() {
    const lq = this.value.toLowerCase();
    document.querySelectorAll('#tabela-moradores tbody tr').forEach(r => r.style.display = r.dataset.search.includes(lq) ? '' : 'none');
    document.querySelectorAll('#cards-mobile .morador-card').forEach(c => c.style.display = c.dataset.search.includes(lq) ? '' : 'none');
  });

  // ── CRUD Moradores ────────────────────────────
  function abrirModalCriar() {
    ['c-nome','c-apt','c-senha','c-senha2'].forEach(id => $(id).value = '');
    hideErro($('criar-erro'));
    $('modal-criar').style.display = '';
    setTimeout(() => $('c-nome').focus(), 150);
  }

  async function criarMorador() {
    const nome=  $('c-nome').value.trim(), apt=$('c-apt').value.trim();
    const senha= $('c-senha').value, conf=$('c-senha2').value;
    const erro=  $('criar-erro');
    if (!nome||!apt||!senha) { showErro(erro,'Preencha todos os campos.'); return; }
    if (senha.length < 4)    { showErro(erro,'Senha mínima de 4 caracteres.'); return; }
    if (senha !== conf)      { showErro(erro,'As senhas não coincidem.'); return; }
    const btn = $('btn-criar-salvar'); setLoading(btn, true, '');
    try {
      const r = await fetch('index.php?page=admin&action=criar', { method:'POST', body:new URLSearchParams({nome, apartamento:apt, senha}) });
      const d = await r.json();
      if (d.sucesso) { fecharModal('modal-criar'); showToast(d.mensagem,'success'); setTimeout(()=>location.reload(),1000); }
      else { showErro(erro, d.mensagem); setLoading(btn, false, '<span class="btn-icon-text">Cadastrar</span>'); }
    } catch { showErro(erro,'Erro de conexão.'); setLoading(btn, false, '<span class="btn-icon-text">Cadastrar</span>'); }
  }

  function abrirModalEditar(id, nome, apt) {
    $('e-id').value=$id; $('e-nome').value=nome; $('e-apt').value=apt; $('e-senha').value='';
    hideErro($('editar-erro')); $('modal-editar').style.display='';
    setTimeout(() => $('e-nome').focus(), 150);
  }

  // Fix: variable name conflict
  function abrirModalEditar(id, nome, apt) {
    document.getElementById('e-id').value   = id;
    document.getElementById('e-nome').value = nome;
    document.getElementById('e-apt').value  = apt;
    document.getElementById('e-senha').value = '';
    hideErro(document.getElementById('editar-erro'));
    document.getElementById('modal-editar').style.display = '';
    setTimeout(() => document.getElementById('e-nome').focus(), 150);
  }

  async function editarMorador() {
    const id=$('e-id').value, nome=$('e-nome').value.trim(), apt=$('e-apt').value.trim(), senha=$('e-senha').value, erro=$('editar-erro');
    if (!nome||!apt)           { showErro(erro,'Preencha nome e apartamento.'); return; }
    if (senha&&senha.length<4) { showErro(erro,'Nova senha: mínimo 4 caracteres.'); return; }
    const btn=$('btn-editar-salvar'); setLoading(btn,true,'');
    try {
      const body=new URLSearchParams({id,nome,apartamento:apt}); if (senha) body.append('senha',senha);
      const r=await fetch('index.php?page=admin&action=editar',{method:'POST',body}); const d=await r.json();
      if (d.sucesso) { fecharModal('modal-editar'); showToast(d.mensagem,'success'); setTimeout(()=>location.reload(),1000); }
      else { showErro(erro,d.mensagem); setLoading(btn,false,'<span class="btn-icon-text">Salvar</span>'); }
    } catch { showErro(erro,'Erro de conexão.'); setLoading(btn,false,'<span class="btn-icon-text">Salvar</span>'); }
  }

  function abrirModalDeletar(id, nome, apt) {
    $('del-id').value=id; $('del-nome-display').textContent=nome; $('del-apt-display').textContent='Apartamento '+apt;
    hideErro($('deletar-erro')); $('modal-deletar').style.display='';
  }

  async function deletarMorador() {
    const id=$('del-id').value, erro=$('deletar-erro'), btn=$('btn-deletar-salvar');
    setLoading(btn,true,'');
    try {
      const r=await fetch('index.php?page=admin&action=deletar',{method:'POST',body:new URLSearchParams({id})}); const d=await r.json();
      if (d.sucesso) { fecharModal('modal-deletar'); showToast(d.mensagem,'success'); setTimeout(()=>location.reload(),1000); }
      else { showErro(erro,d.mensagem); setLoading(btn,false,'<span class="btn-icon-text">Confirmar Exclusão</span>'); }
    } catch { showErro(erro,'Erro de conexão.'); setLoading(btn,false,'<span class="btn-icon-text">Confirmar Exclusão</span>'); }
  }

  async function toggleMorador(id, ativo) {
    if (!confirm(`Deseja ${+ativo?'desativar':'ativar'} este morador?`)) return;
    try {
      const r=await fetch('index.php?page=admin&action=toggle',{method:'POST',body:new URLSearchParams({id})}); const d=await r.json();
      if (d.sucesso) { showToast('Morador atualizado!','success'); setTimeout(()=>location.reload(),800); }
      else showToast('Erro ao atualizar.','error');
    } catch { showToast('Erro de conexão.','error'); }
  }

  // ── Configurações ─────────────────────────────
  async function carregarConfiguracoes() {
    $('config-loading').style.display = ''; $('config-form').style.display = 'none';
    try {
      const r = await fetch('index.php?page=admin&action=configuracoes'); const d = await r.json();
      if (d.sucesso) {
        const cfg = d.dados;
        $('cfg-horas-lav').value   = cfg.horas_lavanderia_semana || 4;
        $('cfg-usos-churr').value  = cfg.usos_churrasqueira_mes  || 2;
        $('cfg-abertura').value    = cfg.horario_abertura         || '07:00';
        $('cfg-fechamento').value  = cfg.horario_fechamento       || '22:00';
        $('cfg-nome').value        = cfg.nome_condominio          || '';
        configCarregada = true;
        $('config-loading').style.display = 'none'; $('config-form').style.display = '';
      }
    } catch { showToast('Erro ao carregar configurações.','error'); }
  }

  async function salvarConfiguracoes() {
    const btn = $('btn-salvar-config');
    const erro = $('config-erro');
    hideErro(erro);
    setLoading(btn, true, '');
    try {
      const body = new URLSearchParams({
        horas_lavanderia_semana: $('cfg-horas-lav').value,
        usos_churrasqueira_mes:  $('cfg-usos-churr').value,
        horario_abertura:        $('cfg-abertura').value,
        horario_fechamento:      $('cfg-fechamento').value,
        nome_condominio:         $('cfg-nome').value,
      });
      const r = await fetch('index.php?page=admin&action=salvar-config', {method:'POST', body});
      const d = await r.json();
      if (d.sucesso) { showToast(d.mensagem,'success'); setLoading(btn, false, 'Salvar Configurações'); }
      else { showErro(erro, d.mensagem); setLoading(btn, false, 'Salvar Configurações'); }
    } catch { showErro(erro,'Erro de conexão.'); setLoading(btn, false, 'Salvar Configurações'); }
  }

  // ── Agendamentos ──────────────────────────────
  async function carregarAgendamentos() {
    $('lav-loading').style.display=''; $('lav-content').style.display='none';
    $('churr-loading').style.display=''; $('churr-content').style.display='none';
    try {
      const r = await fetch('index.php?page=admin&action=agendamentos&filtro=todos');
      dadosAgenda = await r.json(); agendaCarregada = true;
      const fut = (dadosAgenda.lavanderia||[]).filter(a=>a.status==='confirmado'&&a.data_agendamento>=hoje()).length
                + (dadosAgenda.churrasqueira||[]).filter(a=>a.status==='confirmado'&&a.data_agendamento>=hoje()).length;
      $('stat-agendamentos').textContent = fut;
      renderAgendamentos();
    } catch { showToast('Erro ao carregar agendamentos.','error'); }
  }

  function renderAgendamentos() { renderLav(); renderChurr(); }

  function filtrarLista(lista, filtro) {
    const hj = hoje();
    if (filtro==='futuros')   return lista.filter(a=>a.data_agendamento>=hj&&a.status==='confirmado');
    if (filtro==='historico') return lista.filter(a=>a.data_agendamento<hj||a.status!=='confirmado');
    return lista;
  }

  function tagStatus(status, data) {
    if (status==='cancelado') return `<span class="badge badge-red">Cancelado</span>`;
    if (status==='concluido'||data<hoje()) return `<span class="badge badge-blue">Concluído</span>`;
    return `<span class="badge badge-green">Agendado</span>`;
  }

  function avatarCell(nome) {
    return `<div style="display:flex;align-items:center;gap:8px">
      <div style="width:28px;height:28px;border-radius:50%;background:var(--accent-dim);border:1px solid rgba(162,197,35,.3);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--accent-text);flex-shrink:0">${escHtml(nome.charAt(0).toUpperCase())}</div>
      <strong>${escHtml(nome)}</strong></div>`;
  }

  function renderLav() {
    const lista=filtrarLista(dadosAgenda.lavanderia||[],filtroAtivo.lav), mobile=isMobile();
    $('lav-loading').style.display='none'; $('lav-content').style.display='';
    $('lav-table-wrap').style.display=mobile?'none':''; $('lav-cards').style.display=mobile?'flex':'none';
    const empty=`<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-dim)">Nenhum registro encontrado</td></tr>`;
    $('tbody-lav').innerHTML = lista.length ? lista.map(a=>`<tr>
      <td>${avatarCell(a.nome)}</td>
      <td><span class="badge badge-blue">Apto ${escHtml(a.apartamento)}</span></td>
      <td style="font-family:'JetBrains Mono',monospace">${fmt(a.data_agendamento)}</td>
      <td style="font-family:'JetBrains Mono',monospace">${a.horario_inicio.substr(0,5)}</td>
      <td style="font-family:'JetBrains Mono',monospace">${a.horario_fim.substr(0,5)}</td>
      <td>${a.duracao_minutos>=60?(a.duracao_minutos/60)+'h':a.duracao_minutos+'min'}</td>
      <td>${tagStatus(a.status,a.data_agendamento)}</td></tr>`).join('') : empty;
    $('lav-cards').innerHTML = lista.length ? lista.map(a=>`<div class="agenda-card">
      <div class="agenda-card-left">
        <div class="agenda-apt">Apto ${escHtml(a.apartamento)}</div>
        <div class="agenda-name">${escHtml(a.nome)}</div>
        <div class="agenda-when">${fmt(a.data_agendamento)} · ${a.horario_inicio.substr(0,5)}–${a.horario_fim.substr(0,5)} · ${a.duracao_minutos>=60?(a.duracao_minutos/60)+'h':a.duracao_minutos+'min'}</div>
      </div>
      <div class="agenda-card-right">${tagStatus(a.status,a.data_agendamento)}</div></div>`).join('') : `<div class="empty-state"><p>Nenhum registro</p></div>`;
  }

  function renderChurr() {
    const lista=filtrarLista(dadosAgenda.churrasqueira||[],filtroAtivo.churr), mobile=isMobile();
    $('churr-loading').style.display='none'; $('churr-content').style.display='';
    $('churr-table-wrap').style.display=mobile?'none':''; $('churr-cards').style.display=mobile?'flex':'none';
    const empty=`<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-dim)">Nenhum registro encontrado</td></tr>`;
    $('tbody-churr').innerHTML = lista.length ? lista.map(a=>`<tr>
      <td>${avatarCell(a.nome)}</td>
      <td><span class="badge badge-blue">Apto ${escHtml(a.apartamento)}</span></td>
      <td style="font-family:'JetBrains Mono',monospace">${fmt(a.data_agendamento)}</td>
      <td>${TL[a.turno]||a.turno}</td>
      <td style="font-family:'JetBrains Mono',monospace;font-size:12px">${TH[a.turno]||''}</td>
      <td>${tagStatus(a.status,a.data_agendamento)}</td></tr>`).join('') : empty;
    $('churr-cards').innerHTML = lista.length ? lista.map(a=>`<div class="agenda-card">
      <div class="agenda-card-left">
        <div class="agenda-apt">Apto ${escHtml(a.apartamento)}</div>
        <div class="agenda-name">${escHtml(a.nome)}</div>
        <div class="agenda-when">${fmt(a.data_agendamento)} · ${TL[a.turno]||a.turno} · ${TH[a.turno]||''}</div>
      </div>
      <div class="agenda-card-right">${tagStatus(a.status,a.data_agendamento)}</div></div>`).join('') : `<div class="empty-state"><p>Nenhum registro</p></div>`;
  }

  // Carrega stat de futuros ao iniciar
  fetch('index.php?page=admin&action=agendamentos&filtro=futuros')
    .then(r=>r.json()).then(d=>{
      const tot=(d.lavanderia?.length||0)+(d.churrasqueira?.length||0);
      $('stat-agendamentos').textContent=tot;
    }).catch(()=>{});

}); // end DOMContentLoaded
</script>
</body>
</html>
