<?php
$pageTitle  = 'Churrasqueira – CondoAgenda';
$activePage = 'churrasqueira';
?>
<?php include __DIR__ . '/../dashboard/_layout_top.php'; ?>

<div class="page-header">
  <h1 class="page-title">Churrasqueira</h1>
  <p class="page-subtitle">2 usos disponíveis por mês · manhã, tarde ou noite</p>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
  <?= $flash['type'] === 'error' ? '⚠️' : '✅' ?> <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- Steps -->
<div class="steps">
  <div class="step active" id="step1-indicator"><div class="step-num">1</div><span>Escolha o dia</span></div>
  <div class="step-arrow">›</div>
  <div class="step" id="step2-indicator"><div class="step-num">2</div><span>Turno</span></div>
  <div class="step-arrow">›</div>
  <div class="step" id="step3-indicator"><div class="step-num">3</div><span>Confirmar</span></div>
</div>

<div class="grid-booking">

  <!-- CALENDÁRIO -->
  <div class="card" id="card-calendar">
    <div class="section-title">Selecione o Dia</div>
    <div class="calendar-wrapper">
      <div class="calendar-header">
        <button class="cal-nav" onclick="mudaMes(-1)" aria-label="Mês anterior">‹</button>
        <span class="calendar-month" id="cal-month-label"></span>
        <button class="cal-nav" onclick="mudaMes(1)" aria-label="Próximo mês">›</button>
      </div>
      <div class="calendar-grid" id="cal-grid">
        <div class="cal-day-name">Dom</div>
        <div class="cal-day-name">Seg</div>
        <div class="cal-day-name">Ter</div>
        <div class="cal-day-name">Qua</div>
        <div class="cal-day-name">Qui</div>
        <div class="cal-day-name">Sex</div>
        <div class="cal-day-name">Sáb</div>
      </div>
    </div>
    <div id="creditos-churr" style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);font-size:13px;color:var(--text-muted);text-align:center">
      Selecione um dia para continuar
    </div>
  </div>

  <!-- PAINEL DIREITO -->
  <div style="display:flex;flex-direction:column;gap:14px">

    <div class="card" id="card-turnos" style="display:none">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:8px;flex-wrap:wrap">
        <div class="section-title" style="margin:0">Turnos Disponíveis</div>
        <span id="turnos-date-label" style="font-size:12px;color:var(--text-muted);font-family:'JetBrains Mono',monospace"></span>
      </div>
      <div id="turnos-loading" style="text-align:center;padding:24px"><span class="spinner"></span></div>
      <div class="turno-grid" id="turno-grid" style="display:none"></div>
    </div>

    <div class="card" id="card-confirm" style="display:none">
      <div class="section-title">Confirmar Reserva</div>
      <div class="card-sm" style="margin-bottom:16px">
        <div style="display:grid;grid-template-columns:auto 1fr;gap:8px 16px;font-size:14px">
          <span style="color:var(--text-muted)"> Data</span>
          <span id="confirm-data"    style="color:var(--text);font-weight:600"></span>
          <span style="color:var(--text-muted)">🕒 Turno</span>
          <span id="confirm-turno"   style="color:var(--text);font-weight:600"></span>
          <span style="color:var(--text-muted)">⏰ Horário</span>
          <span id="confirm-horario" style="color:var(--text-muted);font-family:'JetBrains Mono',monospace;font-size:13px"></span>
        </div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn btn-ghost" onclick="resetar()">← Recomeçar</button>
        <button class="btn btn-green" id="btn-confirmar" onclick="confirmarAgendamento()" style="flex:1;min-width:160px">
          Confirmar Reserva
        </button>
      </div>
    </div>

  </div>
</div>

<!-- MINHAS RESERVAS -->
<div class="card" style="margin-top:20px">
  <div class="section-title">Minhas Reservas Futuras</div>
  <?php if (empty($agendamentos)): ?>
  <div class="empty-state">
    <div class="empty-state-icon">🔥</div>
    <p>Você não tem reservas ativas</p>
  </div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Data</th><th>Turno</th><th>Horário</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php
        $turnosInfo = [
          'manha' => ['label'=>'☀ Manhã',  'horario'=>'08:00–12:00'],
          'tarde'  => ['label'=>'◑ Tarde',  'horario'=>'12:00–17:00'],
          'noite'  => ['label'=>'☽ Noite',  'horario'=>'17:00–22:00'],
        ];
        foreach ($agendamentos as $ag):
          $ti = $turnosInfo[$ag['turno']] ?? ['label'=>$ag['turno'],'horario'=>''];
        ?>
        <tr>
          <td><strong><?= date('d/m/Y', strtotime($ag['data_agendamento'])) ?></strong></td>
          <td><?= $ti['label'] ?></td>
          <td style="font-family:'JetBrains Mono',monospace;font-size:12px"><?= $ti['horario'] ?></td>
          <td><span class="badge badge-green">Confirmado</span></td>
          <td><button class="btn btn-danger btn-sm" onclick="cancelar(<?= $ag['id'] ?>)">Cancelar</button></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<script>
const MESES      = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
const DIAS_SEM   = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
const TURNOS_META = {
  manha: { label:'☀ Manhã',  horario:'08:00 – 12:00', icon:'☀' },
  tarde: { label:'◑ Tarde',  horario:'12:00 – 17:00', icon:'◑' },
  noite: { label:'☽ Noite',  horario:'17:00 – 22:00', icon:'☽' },
};

let state = { dataSelecionada:null, turnoSelecionado:null };
let calViewDate = new Date();

function scrollTo(id) {
  if (window.innerWidth < 1024) {
    setTimeout(() => document.getElementById(id)?.scrollIntoView({ behavior:'smooth', block:'nearest' }), 80);
  }
}

function renderCalendar() {
  const y=calViewDate.getFullYear(), m=calViewDate.getMonth();
  document.getElementById('cal-month-label').textContent=MESES[m]+' '+y;
  const grid=document.getElementById('cal-grid');
  while (grid.children.length>7) grid.removeChild(grid.lastChild);
  const hoje=new Date(); hoje.setHours(0,0,0,0);
  const primeiro=new Date(y,m,1), ultimo=new Date(y,m+1,0);
  for (let i=0;i<primeiro.getDay();i++) {
    const e=document.createElement('div'); e.className='cal-day cal-day--empty'; grid.appendChild(e);
  }
  for (let d=1;d<=ultimo.getDate();d++) {
    const dt=new Date(y,m,d);
    const ymd=`${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const div=document.createElement('div'); div.className='cal-day'; div.textContent=d;
    if (dt<hoje) { div.classList.add('cal-day--disabled'); }
    else {
      if (dt.toDateString()===hoje.toDateString()) div.classList.add('cal-day--today');
      if (state.dataSelecionada===ymd) div.classList.add('cal-day--selected');
      div.onclick=()=>selecionarData(ymd,div);
    }
    grid.appendChild(div);
  }
}

function mudaMes(dir) { calViewDate.setMonth(calViewDate.getMonth()+dir); renderCalendar(); }

async function selecionarData(data, el) {
  state.dataSelecionada=data; state.turnoSelecionado=null;
  document.querySelectorAll('.cal-day--selected').forEach(d=>d.classList.remove('cal-day--selected'));
  el.classList.add('cal-day--selected');
  setStep(2);
  document.getElementById('card-turnos').style.display='';
  document.getElementById('card-confirm').style.display='none';
  document.getElementById('turno-grid').style.display='none';
  document.getElementById('turnos-loading').style.display='';
  scrollTo('card-turnos');
  const p=data.split('-'), dt=new Date(+p[0],+p[1]-1,+p[2]);
  document.getElementById('turnos-date-label').textContent=DIAS_SEM[dt.getDay()]+', '+parseInt(p[2])+' de '+MESES[+p[1]-1];
  const r=await fetch(`index.php?page=churrasqueira&action=turnos&data=${data}`);
  const d=await r.json();
  document.getElementById('creditos-churr').innerHTML=
    `<span style="color:var(--green);font-weight:600">${d.creditos}</span> uso(s) disponível(is) este mês`;
  renderTurnos(d.turnos, d.creditos);
  document.getElementById('turnos-loading').style.display='none';
  document.getElementById('turno-grid').style.display='';
}

function renderTurnos(turnos, creditos) {
  const grid=document.getElementById('turno-grid'); grid.innerHTML='';
  turnos.forEach(t=>{
    const meta=TURNOS_META[t.key]||{label:t.key,horario:'',icon:'🔥'};
    const card=document.createElement('div');
    card.className='turno-card'+(t.disponivel?'':' turno-occupied');
    if (state.turnoSelecionado===t.key) card.classList.add('selected');
    card.innerHTML=`
      <div class="turno-icon">${meta.icon}</div>
      <div class="turno-label">${meta.label.replace(/^\S+\s/,'')}</div>
      <div class="turno-horario">${t.horario}</div>
      ${!t.disponivel && t.ocupado_por ? `<div class="turno-ocupado-por">Reservado · Apto ${escHtml(t.ocupado_por)}</div>` : ''}
    `;
    if (t.disponivel && creditos>0) {
      card.onclick=()=>selecionarTurno(t,card);
    } else if (creditos<=0 && t.disponivel) {
      card.style.opacity='0.38'; card.style.cursor='not-allowed';
      card.title='Sem créditos disponíveis este mês';
    }
    grid.appendChild(card);
  });
  if (creditos<=0) {
    const aviso=document.createElement('div');
    aviso.style.cssText='grid-column:1/-1;padding:12px;background:var(--amber-dim);border:1px solid rgba(245,158,11,0.2);border-radius:var(--radius-sm);font-size:13px;color:var(--amber);text-align:center';
    aviso.textContent='⚠️ Você já utilizou todos os agendamentos deste mês.';
    grid.appendChild(aviso);
  }
}

function selecionarTurno(turno, cardEl) {
  state.turnoSelecionado=turno.key;
  document.querySelectorAll('.turno-card').forEach(c=>c.classList.remove('selected'));
  cardEl.classList.add('selected');
  setStep(3); preencherConfirm(turno);
  document.getElementById('card-confirm').style.display=''; scrollTo('card-confirm');
}

function preencherConfirm(turno) {
  const meta=TURNOS_META[turno.key]||{label:turno.key,horario:''};
  const p=state.dataSelecionada.split('-'), dt=new Date(+p[0],+p[1]-1,+p[2]);
  document.getElementById('confirm-data').textContent=DIAS_SEM[dt.getDay()]+', '+parseInt(p[2])+'/'+p[1]+'/'+p[0];
  document.getElementById('confirm-turno').textContent=meta.label;
  document.getElementById('confirm-horario').textContent=turno.horario;
}

async function confirmarAgendamento() {
  const btn=document.getElementById('btn-confirmar');
  btn.disabled=true; btn.innerHTML='<span class="spinner"></span> Reservando...';
  const r=await fetch('index.php?page=churrasqueira&action=agendar',{method:'POST',body:new URLSearchParams({data:state.dataSelecionada,turno:state.turnoSelecionado})});
  const d=await r.json();
  showToast(d.mensagem,d.sucesso?'success':'error');
  if (d.sucesso) { setTimeout(()=>location.reload(),1200); }
  else { btn.disabled=false; btn.innerHTML='Confirmar Reserva'; }
}

async function cancelar(id) {
  if (!confirm('Cancelar esta reserva? O crédito será devolvido.')) return;
  const r=await fetch('index.php?page=churrasqueira&action=cancelar',{method:'POST',body:new URLSearchParams({id})});
  const d=await r.json();
  showToast(d.mensagem,d.sucesso?'success':'error');
  if (d.sucesso) setTimeout(()=>location.reload(),1200);
}

function resetar() {
  state={dataSelecionada:null,turnoSelecionado:null};
  document.getElementById('card-turnos').style.display='none';
  document.getElementById('card-confirm').style.display='none';
  document.querySelectorAll('.cal-day--selected').forEach(d=>d.classList.remove('cal-day--selected'));
  setStep(1); window.scrollTo({top:0,behavior:'smooth'});
}

function setStep(n) {
  for (let i=1;i<=3;i++) {
    const el=document.getElementById(`step${i}-indicator`);
    el.classList.remove('active','done');
    if (i<n) el.classList.add('done');
    if (i===n) el.classList.add('active');
  }
}

renderCalendar();
</script>

<?php include __DIR__ . '/../dashboard/_layout_bottom.php'; ?>
