  </main>
</div>

<div id="toast-container" aria-live="polite"></div>
<script src="<?= ASSETS_URL ?>/js/app.js"></script>
<script>
(function () {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (!sidebar || !overlay) return;

  function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('visible'); document.body.style.overflow = 'hidden'; }
  function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('visible'); document.body.style.overflow = ''; }

  overlay.addEventListener('click', closeSidebar);
  let startX = 0;
  sidebar.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
  sidebar.addEventListener('touchend',   e => { if (startX - e.changedTouches[0].clientX > 60) closeSidebar(); }, { passive: true });

  window.openSidebar  = openSidebar;
  window.closeSidebar = closeSidebar;
})();

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('<?= ASSETS_URL ?>/sw.js')
      .then(reg => {
        reg.addEventListener('updatefound', () => {
          const w = reg.installing;
          w.addEventListener('statechange', () => {
            if (w.state === 'installed' && navigator.serviceWorker.controller)
              showToast('Nova versão disponível! Recarregue.', 'success', 8000);
          });
        });
      }).catch(() => {});
  });
}

let deferredPrompt = null;
window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault(); deferredPrompt = e;
  if (localStorage.getItem('pwa-dismissed')) return;
  const banner = document.createElement('div');
  banner.className = 'install-banner'; banner.id = 'installBanner';
  banner.innerHTML = `
    <div class="install-banner-text"><strong>Instalar Nomade Agenda</strong><span>Adicione ao celular para acesso rápido</span></div>
    <button class="btn btn-primary btn-sm" onclick="instalarPWA()">Instalar</button>
    <button class="btn btn-ghost btn-sm" onclick="dispensarBanner()" style="padding:6px 8px">✕</button>`;
  document.body.appendChild(banner);
});

function instalarPWA() {
  if (!deferredPrompt) return;
  deferredPrompt.prompt();
  deferredPrompt.userChoice.then(r => { deferredPrompt = null; dispensarBanner(); if (r.outcome === 'accepted') showToast('App instalado!', 'success'); });
}
function dispensarBanner() { const b = document.getElementById('installBanner'); if (b) b.remove(); localStorage.setItem('pwa-dismissed','1'); }
if (window.matchMedia('(display-mode: standalone)').matches) localStorage.setItem('pwa-dismissed','1');
</script>
</body>
</html>
