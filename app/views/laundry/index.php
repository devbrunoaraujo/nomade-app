<?php
$pageTitle  = 'Lavanderia – CondoAgenda';
$activePage = 'lavanderia';
?>
<?php include __DIR__ . '/../dashboard/_layout_top.php'; ?>

<div class="page-header">
  <h1 class="page-title">Lavanderia</h1>
  <p class="page-subtitle">4 horas disponíveis por semana · slots de 30 minutos</p>
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
  <div class="step" id="step2-indicator"><div class="step-num">2</div><span>Horário</span></div>
  <div class="step-arrow">›</div>
  <div class="step" id="step3-indicator"><div class="step-num">3</div><span>Duração</span></div>
  <div class="step-arrow">›</div>
  <div class="step" id="step4-indicator"><div class="step-num">4</div><span>Confirmar</span></div>
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
    <div id="creditos-lav" style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);font-size:13px;color:var(--text-muted);text-align:center">
      Selecione um dia para ver créditos disponíveis
    </div>
  </div>

  <!-- PAINEL DIREITO -->
  <div style="display:flex;flex-direction:column;gap:14px">

    <div class="card" id="card-slots" style="display:none">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;gap:8px;flex-wrap:wrap">
        <div class="section-title" style="margin:0">Horários Disponíveis</div>
        <span id="slots-date-label" style="font-size:12px;color:var(--text-muted);font-family:'JetBrains Mono',monospace"></span>
      </div>
      <div id="slots-loading" style="text-align:center;padding:24px"><span class="spinner"></span></div>
      <div class="slots-grid" id="slots-grid" style="display:none"></div>
    </div>

    <div class="card" id="card-duration" style="display:none">
      <div class="section-title">Quantas horas deseja usar?</div>
      <div class="duration-grid" id="duration-grid"></div>
      <div id="duration-info" style="margin-top:10px;font-size:12px;color:var(--text-muted)"></div>
    </div>

    <div class="card" id="card-confirm" style="display:none">
      <div class="section-title">Confirmar Agendamento</div>
      <div class="card-sm" style="margin-bottom:16px">
        <div style="display:grid;grid-template-columns:auto 1fr;gap:8px 16px;font-size:14px">
          <span style="color:var(--text-muted)"> Data</span>
          <span id="confirm-data" style="color:var(--text);font-weight:600"></span>
          <span style="color:var(--text-muted)">⏰ Início</span>
          <span id="confirm-hora" style="color:var(--text);font-weight:600;font-family:'JetBrains Mono',monospace"></span>
          <span style="color:var(--text-muted)">⏱️ Duração</span>
          <span id="confirm-dur"  style="color:var(--text);font-weight:600"></span>
          <span style="color:var(--text-muted)">🕛 Término</span>
          <span id="confirm-fim"  style="color:var(--text);font-weight:600;font-family:'JetBrains Mono',monospace"></span>
        </div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn btn-ghost" onclick="resetar()">← Recomeçar</button>
        <button class="btn btn-primary" id="btn-confirmar" onclick="confirmarAgendamento()" style="flex:1;min-width:160px">
          Confirmar Agendamento
        </button>
      </div>
    </div>

  </div>
</div>

<!-- MEUS AGENDAMENTOS -->
<div class="card" style="margin-top:20px">
  <div class="section-title">Meus Agendamentos Futuros</div>
  <?php if (empty($agendamentos)): ?>
  <div class="empty-state">
    <div class="empty-state-icon"></div>
    <p>Você não tem agendamentos ativos</p>
  </div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Data</th><th>Início</th><th>Fim</th><th>Duração</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($agendamentos as $ag): ?>
        <tr>
          <td><strong><?= date('d/m/Y', strtotime($ag['data_agendamento'])) ?></strong></td>
          <td style="font-family:'JetBrains Mono',monospace"><?= substr($ag['horario_inicio'],0,5) ?></td>
          <td style="font-family:'JetBrains Mono',monospace"><?= substr($ag['horario_fim'],0,5) ?></td>
          <td><?= $ag['duracao_minutos'] >= 60 ? ($ag['duracao_minutos']/60).'h' : $ag['duracao_minutos'].'min' ?></td>
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
const MESES    = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
const DIAS_SEM = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
let state = { dataSelecionada:null, horaSelecionada:null, duracao:null, horasDisp:4 };
let calViewDate = new Date();

function scrollTo(id) {
  if (window.innerWidth < 1024) {
    setTimeout(() => document.getElementById(id)?.scrollIntoView({ behavior:'smooth', block:'nearest' }), 80);
  }
}

function renderCalendar() {
  const y = calViewDate.getFullYear(), m = calViewDate.getMonth();
  document.getElementById('cal-month-label').textContent = MESES[m] + ' ' + y;
  const grid = document.getElementById('cal-grid');
  while (grid.children.length > 7) grid.removeChild(grid.lastChild);
  const hoje = new Date(); hoje.setHours(0,0,0,0);
  const primeiro = new Date(y,m,1), ultimo = new Date(y,m+1,0);
  for (let i = 0; i < primeiro.getDay(); i++) {
    const e = document.createElement('div'); e.className='cal-day cal-day--empty'; grid.appendChild(e);
  }
  for (let d = 1; d <= ultimo.getDate(); d++) {
    const dt  = new Date(y,m,d);
    const ymd = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const div = document.createElement('div');
    div.className='cal-day'; div.textContent=d;
    if (dt < hoje) { div.classList.add('cal-day--disabled'); }
    else {
      if (dt.toDateString()===hoje.toDateString()) div.classList.add('cal-day--today');
      if (state.dataSelecionada===ymd) div.classList.add('cal-day--selected');
      div.onclick = () => selecionarData(ymd, div);
    }
    grid.appendChild(div);
  }
}

function mudaMes(dir) { calViewDate.setMonth(calViewDate.getMonth()+dir); renderCalendar(); }

async function selecionarData(data, el) {
  state.dataSelecionada=data; state.horaSelecionada=null; state.duracao=null;
  document.querySelectorAll('.cal-day--selected').forEach(d=>d.classList.remove('cal-day--selected'));
  el.classList.add('cal-day--selected');
  setStep(2);
  document.getElementById('card-slots').style.display='';
  document.getElementById('card-duration').style.display='none';
  document.getElementById('card-confirm').style.display='none';
  document.getElementById('slots-grid').style.display='none';
  document.getElementById('slots-loading').style.display='';
  scrollTo('card-slots');
  const p=data.split('-'), dt=new Date(+p[0],+p[1]-1,+p[2]);
  document.getElementById('slots-date-label').textContent = DIAS_SEM[dt.getDay()]+', '+parseInt(p[2])+' de '+MESES[+p[1]-1];
  const r=await fetch(`index.php?page=lavanderia&action=slots&data=${data}`);
  const d=await r.json();
  state.horasDisp=d.horas_disponiveis;
  document.getElementById('creditos-lav').innerHTML=
    `<span style="color:var(--accent-text);font-weight:600">${parseFloat(d.horas_disponiveis).toFixed(1)}h</span> disponíveis nesta semana`;
  renderSlots(d.slots);
  document.getElementById('slots-loading').style.display='none';
  document.getElementById('slots-grid').style.display='';
}

function renderSlots(slots) {
  const grid=document.getElementById('slots-grid'); grid.innerHTML='';
  slots.forEach(slot=>{
    const btn=document.createElement('button');
    btn.className='slot-btn'+(slot.disponivel?'':' slot-btn--occupied');
    btn.textContent=slot.inicio;
    if (!slot.disponivel && slot.ocupado_por && slot.ocupado_por!=='passado') btn.title='Ocupado: '+slot.ocupado_por;
    if (slot.disponivel) btn.onclick=()=>selecionarHora(slot.inicio,btn);
    if (state.horaSelecionada===slot.inicio) btn.classList.add('slot-btn--selected');
    grid.appendChild(btn);
  });
}

function selecionarHora(hora, btn) {
  state.horaSelecionada=hora;
  document.querySelectorAll('.slot-btn--selected').forEach(b=>b.classList.remove('slot-btn--selected'));
  btn.classList.add('slot-btn--selected');
  setStep(3); renderDuration();
  document.getElementById('card-duration').style.display='';
  document.getElementById('card-confirm').style.display='none';
  scrollTo('card-duration');
}

function renderDuration() {
  const grid=document.getElementById('duration-grid'); grid.innerHTML='';
  const max=Math.min(state.horasDisp*60,240);
  let algum=false;
  [30,60,90,120,150,180,210,240].forEach(min=>{
    if (min>max) return; algum=true;
    const btn=document.createElement('button');
    btn.className='dur-btn'+(state.duracao===min?' selected':'');
    btn.textContent=min<60?min+'min':(min/60)+'h'+(min%60?(min%60)+'min':'');
    btn.onclick=()=>selecionarDuracao(min,btn); grid.appendChild(btn);
  });
  document.getElementById('duration-info').innerHTML=algum
    ? `Você tem <strong style="color:var(--accent-text)">${parseFloat(state.horasDisp).toFixed(1)}h</strong> disponíveis nesta semana.`
    : '<span style="color:var(--red)">⚠️ Créditos insuficientes nesta semana.</span>';
}

function selecionarDuracao(min, btn) {
  state.duracao=min;
  document.querySelectorAll('.dur-btn.selected').forEach(b=>b.classList.remove('selected'));
  btn.classList.add('selected'); setStep(4); preencherConfirm();
  document.getElementById('card-confirm').style.display=''; scrollTo('card-confirm');
}

function preencherConfirm() {
  const p=state.dataSelecionada.split('-'), dt=new Date(+p[0],+p[1]-1,+p[2]);
  document.getElementById('confirm-data').textContent=DIAS_SEM[dt.getDay()]+', '+parseInt(p[2])+'/'+p[1]+'/'+p[0];
  document.getElementById('confirm-hora').textContent=state.horaSelecionada;
  document.getElementById('confirm-dur').textContent=state.duracao<60?state.duracao+'min':(state.duracao/60)+'h'+(state.duracao%60?(state.duracao%60)+'min':'');
  const [h,m]=state.horaSelecionada.split(':').map(Number), tot=h*60+m+state.duracao;
  document.getElementById('confirm-fim').textContent=String(Math.floor(tot/60)).padStart(2,'0')+':'+String(tot%60).padStart(2,'0');
}

async function confirmarAgendamento() {
  const btn=document.getElementById('btn-confirmar');
  btn.disabled=true; btn.innerHTML='<span class="spinner"></span> Agendando...';
  const r=await fetch('index.php?page=lavanderia&action=agendar',{method:'POST',body:new URLSearchParams({data:state.dataSelecionada,hora_inicio:state.horaSelecionada,duracao:state.duracao})});
  const d=await r.json();
  showToast(d.mensagem,d.sucesso?'success':'error');
  if (d.sucesso) { setTimeout(()=>location.reload(),1200); }
  else { btn.disabled=false; btn.innerHTML='Confirmar Agendamento'; }
}

async function cancelar(id) {
  if (!confirm('Cancelar este agendamento? Os créditos serão devolvidos.')) return;
  const r=await fetch('index.php?page=lavanderia&action=cancelar',{method:'POST',body:new URLSearchParams({id})});
  const d=await r.json();
  showToast(d.mensagem,d.sucesso?'success':'error');
  if (d.sucesso) setTimeout(()=>location.reload(),1200);
}

function resetar() {
  state={dataSelecionada:null,horaSelecionada:null,duracao:null,horasDisp:4};
  ['card-slots','card-duration','card-confirm'].forEach(id=>document.getElementById(id).style.display='none');
  document.querySelectorAll('.cal-day--selected').forEach(d=>d.classList.remove('cal-day--selected'));
  setStep(1); window.scrollTo({top:0,behavior:'smooth'});
}

function setStep(n) {
  for (let i=1;i<=4;i++) {
    const el=document.getElementById(`step${i}-indicator`);
    el.classList.remove('active','done');
    if (i<n) el.classList.add('done');
    if (i===n) el.classList.add('active');
  }
}

renderCalendar();
</script>

<?php include __DIR__ . '/../dashboard/_layout_bottom.php'; ?>
