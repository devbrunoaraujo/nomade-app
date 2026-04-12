// public/js/app.js

/**
 * Exibe toast de notificação
 */
function showToast(msg, type = 'success', duration = 3500) {
  const container = document.getElementById('toast-container');
  if (!container) return;

  container.querySelectorAll('.toast').forEach(t => t.remove());

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.setAttribute('role', 'alert');
  toast.innerHTML = `
    <span style="font-size:18px;flex-shrink:0">${type === 'success' ? '✅' : '❌'}</span>
    <span style="flex:1">${msg}</span>
  `;
  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('removing');
    toast.addEventListener('animationend', () => toast.remove(), { once: true });
  }, duration);
}

/**
 * Formata Y-m-d → dd/mm/aaaa
 */
function formatDate(ymd) {
  if (!ymd) return '';
  const [y, m, d] = ymd.split('-');
  return `${d}/${m}/${y}`;
}

/**
 * Escapa HTML para evitar XSS
 */
function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

/**
 * Debounce simples
 */
function debounce(fn, delay = 300) {
  let timer;
  return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), delay); };
}

/**
 * Busca elemento com segurança — nunca lança null
 */
function getEl(id) {
  return document.getElementById(id);
}

// Auto-remove SOMENTE flash alerts visíveis no corpo da página
// Não remove alertas de formulário dentro de modais (display:none)
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert').forEach(el => {
    // Ignora se está oculto (é um alerta de formulário/modal)
    if (el.style.display === 'none') return;
    // Ignora se está dentro de um modal ou overlay
    if (el.closest('.modal-overlay') || el.closest('.modal')) return;

    setTimeout(() => {
      el.style.transition = 'opacity 0.5s';
      el.style.opacity = '0';
      setTimeout(() => { if (el.parentNode) el.remove(); }, 500);
    }, 5000);
  });
});
