<?php
$pageTitle  = 'Início – Nomade Agenda';
$activePage = 'dashboard';

function formatarDataBr(string $data): string {
    $dt    = new DateTime($data);
    $dias  = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
    $meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
              'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    return $dias[(int)$dt->format('w')] . ', ' . $dt->format('d') . ' de ' . $meses[(int)$dt->format('n')-1];
}

function turnoLabel(string $t): string {
    return match($t) { 'manha' => 'Manhã', 'tarde' => 'Tarde', 'noite' => 'Noite', default => ucfirst($t) };
}
?>
<?php include __DIR__ . '/_layout_top.php'; ?>

<div class="page-header">
  <h1 class="page-title">Olá, <?= htmlspecialchars(explode(' ', $morador['nome'])[0]) ?></h1>
  <p class="page-subtitle">Seus agendamentos de lavanderia e churrasqueira</p>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>" id="flash-msg">
  <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card accent">
    <div class="stat-label">Lavanderia · Semana</div>
    <div class="stat-value"><?= number_format($horasDisp, 1) ?>h</div>
    <div class="stat-sub">de <?= number_format($limiteLav,1) ?>h disponíveis</div>
    <div class="progress-bar"><div class="progress-fill" style="width:<?= min(($horasDisp/$limiteLav)*100,100) ?>%;background:var(--accent)"></div></div>
  </div>
  <div class="stat-card lavender">
    <div class="stat-label">Churrasqueira · Mês</div>
    <div class="stat-value"><?= $churrDisp ?></div>
    <div class="stat-sub">de <?= $limiteChurr ?> usos disponíveis</div>
    <div class="progress-bar"><div class="progress-fill" style="width:<?= min(($churrDisp/$limiteChurr)*100,100) ?>%;background:var(--lavender)"></div></div>
  </div>
  <div class="stat-card mid">
    <div class="stat-label">Agendamentos Ativos</div>
    <div class="stat-value"><?= count($proxLav) + count($proxChurr) ?></div>
    <div class="stat-sub">no total</div>
  </div>
</div>

<!-- Ações + Créditos -->
<div class="grid-2" style="margin-bottom:16px">
  <div class="card">
    <div class="section-title">Agendar</div>
    <div style="display:flex;flex-direction:column;gap:10px">
      <a href="index.php?page=lavanderia" class="btn btn-primary btn-block">Reservar Lavanderia</a>
      <a href="index.php?page=churrasqueira" class="btn btn-ghost btn-block" style="color:var(--lavender);border-color:var(--border-light)">Reservar Churrasqueira</a>
    </div>
  </div>

  <div class="card">
    <div class="section-title">Créditos Disponíveis</div>
    <div style="display:flex;flex-direction:column;gap:14px">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <span style="font-size:13px;color:var(--text-muted)">Lavanderia (semana)</span>
          <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--accent-text)"><?= number_format($horasDisp,1) ?>h / <?= number_format($limiteLav,1) ?>h</span>
        </div>
        <div class="progress-bar"><div class="progress-fill" style="width:<?= min(($horasDisp/$limiteLav)*100,100) ?>%;background:var(--accent)"></div></div>
      </div>
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <span style="font-size:13px;color:var(--text-muted)">Churrasqueira (mês)</span>
          <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--lavender)"><?= $churrDisp ?> / <?= $limiteChurr ?></span>
        </div>
        <div class="progress-bar"><div class="progress-fill" style="width:<?= min(($churrDisp/$limiteChurr)*100,100) ?>%;background:var(--lavender)"></div></div>
      </div>
    </div>
  </div>
</div>

<!-- Próximos -->
<div class="grid-2">
  <div class="card">
    <div class="section-title">Lavanderia – Próximos</div>
    <?php if (empty($proxLav)): ?>
    <div class="empty-state">
      <div class="empty-state-icon">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <p>Nenhum agendamento ativo</p>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:8px">
      <?php foreach ($proxLav as $ag): ?>
      <div class="card-sm" style="display:flex;align-items:center;justify-content:space-between;gap:10px">
        <div style="min-width:0">
          <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= formatarDataBr($ag['data_agendamento']) ?></div>
          <div style="font-size:12px;color:var(--text-muted);font-family:'JetBrains Mono',monospace"><?= substr($ag['horario_inicio'],0,5) ?> – <?= substr($ag['horario_fim'],0,5) ?></div>
        </div>
        <button class="btn btn-danger btn-sm" style="flex-shrink:0" onclick="cancelarLav(<?= $ag['id'] ?>)">Cancelar</button>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="section-title">Churrasqueira – Próximas</div>
    <?php if (empty($proxChurr)): ?>
    <div class="empty-state">
      <div class="empty-state-icon">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <p>Nenhum agendamento ativo</p>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:8px">
      <?php foreach ($proxChurr as $ag): ?>
      <div class="card-sm" style="display:flex;align-items:center;justify-content:space-between;gap:10px">
        <div style="min-width:0">
          <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= formatarDataBr($ag['data_agendamento']) ?></div>
          <div style="font-size:12px;color:var(--text-muted)"><?= turnoLabel($ag['turno']) ?></div>
        </div>
        <button class="btn btn-danger btn-sm" style="flex-shrink:0" onclick="cancelarChurr(<?= $ag['id'] ?>)">Cancelar</button>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
async function cancelarLav(id) {
  if (!confirm('Cancelar este agendamento de lavanderia?')) return;
  const r = await fetch('index.php?page=lavanderia&action=cancelar', { method:'POST', body:new URLSearchParams({id}) });
  const d = await r.json();
  showToast(d.mensagem, d.sucesso ? 'success' : 'error');
  if (d.sucesso) setTimeout(() => location.reload(), 1200);
}
async function cancelarChurr(id) {
  if (!confirm('Cancelar este agendamento de churrasqueira?')) return;
  const r = await fetch('index.php?page=churrasqueira&action=cancelar', { method:'POST', body:new URLSearchParams({id}) });
  const d = await r.json();
  showToast(d.mensagem, d.sucesso ? 'success' : 'error');
  if (d.sucesso) setTimeout(() => location.reload(), 1200);
}
</script>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
