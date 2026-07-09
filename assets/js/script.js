/* ============================================================
   Sprint Max — Main Application JavaScript
   Utilitários globais: toast, loading de botão, sidebar mobile.
   Disponível em todas as páginas do painel.
   ============================================================ */

/* ──────────────────────────────────────────────────────────
   TOAST — Notificação flutuante no canto inferior direito
   ────────────────────────────────────────────────────────── */

/**
 * Exibe uma notificação toast.
 *
 * @param {string} message   - Texto da notificação
 * @param {'success'|'error'|'warning'|'info'} type - Tipo visual
 * @param {number} duration  - Duração em ms (padrão: 3500)
 *
 * Exemplo de uso:
 *   showToast('Produto salvo com sucesso!', 'success');
 *   showToast('Erro ao conectar.', 'error');
 */
function showToast(message, type = "success", duration = 3500) {
  // Remove toasts anteriores para não empilhar
  document.querySelectorAll(".sprint-toast").forEach((t) => t.remove());

  const icons = {
    success: "fa-circle-check",
    error: "fa-circle-exclamation",
    warning: "fa-triangle-exclamation",
    info: "fa-circle-info",
  };

  const toast = document.createElement("div");
  toast.className = `sprint-toast sprint-toast--${type}`;
  toast.innerHTML = `
    <i class="fa-solid ${icons[type] || icons.info}"></i>
    <span>${message}</span>
    <button class="toast-close" aria-label="Fechar">
      <i class="fa-solid fa-xmark"></i>
    </button>
  `;

  // Botão de fechar manual
  toast.querySelector(".toast-close").addEventListener("click", () => {
    toast.classList.remove("visible");
    setTimeout(() => toast.remove(), 300);
  });

  document.body.appendChild(toast);

  // Anima entrada
  requestAnimationFrame(() => {
    requestAnimationFrame(() => toast.classList.add("visible"));
  });

  // Remove automaticamente após `duration` ms
  setTimeout(() => {
    toast.classList.remove("visible");
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

/* ──────────────────────────────────────────────────────────
   LOADING — Estado de carregamento em botões
   ────────────────────────────────────────────────────────── */

/**
 * Ativa ou desativa o estado de loading em um botão.
 *
 * @param {HTMLButtonElement} btn    - O botão alvo
 * @param {boolean}           state  - true = loading, false = normal
 * @param {string}            text   - Texto enquanto carrega
 *
 * Exemplo de uso:
 *   setLoading(document.getElementById('btnSalvar'), true, 'Salvando...');
 *   setLoading(document.getElementById('btnSalvar'), false);
 */
function setLoading(btn, state, text = "Aguarde...") {
  if (!btn) return;

  if (state) {
    btn.dataset.originalContent = btn.innerHTML;
    btn.innerHTML = `<span class="btn-spinner"></span> ${text}`;
    btn.disabled = true;
    btn.classList.add("loading");
  } else {
    btn.innerHTML = btn.dataset.originalContent || btn.innerHTML;
    btn.disabled = false;
    btn.classList.remove("loading");
  }
}

/* ──────────────────────────────────────────────────────────
   SIDEBAR — Toggle em telas mobile
   ────────────────────────────────────────────────────────── */

document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.querySelector(".sidebar-overlay");
  const btnOpen = document.getElementById("sidebarToggle");

  if (!sidebar) return; // só executa nas páginas do painel

  /** Abre o sidebar */
  function openSidebar() {
    sidebar.classList.add("open");
    if (overlay) overlay.classList.add("open");
  }

  /** Fecha o sidebar */
  function closeSidebar() {
    sidebar.classList.remove("open");
    if (overlay) overlay.classList.remove("open");
  }

  // Botão de hambúrguer — alterna abrir/fechar
  if (btnOpen)
    btnOpen.addEventListener("click", () => {
      sidebar.classList.toggle("open");
      if (overlay) overlay.classList.toggle("open");
    });

  // Clique no overlay fecha o sidebar
  if (overlay) overlay.addEventListener("click", closeSidebar);

  // Fecha com Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeSidebar();
  });

  /* ──────────────────────────────────────────────────────
     Confirmação de exclusão com SweetAlert2
     ────────────────────────────────────────────────────── */

  /**
   * Confirmação de exclusão padrão do sistema.
   * Chama callback se o usuário confirmar.
   *
   * @param {Function} onConfirm - Executado ao confirmar
   * @param {string}   title     - Título do modal
   * @param {string}   text      - Descrição
   *
   * Exemplo de uso:
   *   confirmDelete(() => { window.location.href = '/produtos/remover?id=5'; });
   */
  window.confirmDelete = function (
    onConfirm,
    title = "Tem certeza?",
    text = "Esta ação não pode ser desfeita.",
  ) {
    if (typeof Swal === "undefined") {
      if (confirm(title + "\n" + text)) onConfirm();
      return;
    }

    Swal.fire({
      title,
      text,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sim, excluir",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#EF4444",
      cancelButtonColor: "#3A3A3F",
      background: "#1A1A1D",
      color: "#FFFFFF",
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) onConfirm();
    });
  };

  /* ── Profile Dropdown ────────────────────────────────────── */
  const profileBtn = document.getElementById("profileBtn");
  const profileDropdown = document.getElementById("profileDropdown");
  if (profileBtn) {
    profileBtn.addEventListener("click", () => {
      const open = profileBtn.classList.toggle("open");
      profileDropdown.classList.toggle("open", open);
    });
    document.addEventListener("click", (e) => {
      if (!profileBtn.contains(e.target)) {
        profileBtn.classList.remove("open");
        profileDropdown?.classList.remove("open");
      }
    });
  }
});
