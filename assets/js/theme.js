/* ============================================================
   Sprint Max — Theme Manager
   Gerencia troca de tema Dark / Light com persistência
   via localStorage e sem flash na recarga da página.
   ============================================================ */

(function () {
  var STORAGE_KEY = "sprint-theme";
  var DEFAULT = "dark";

  /* ── Lê o tema salvo (ou usa o padrão) ─────────────────── */
  function getSaved() {
    return localStorage.getItem(STORAGE_KEY) || DEFAULT;
  }

  /* ── Aplica o tema no elemento <html> ───────────────────── */
  function applyTheme(theme, animate) {
    var html = document.documentElement;

    if (animate) {
      html.classList.add("theme-transition");
      setTimeout(function () {
        html.classList.remove("theme-transition");
      }, 320);
    }

    html.setAttribute("data-theme", theme);
    localStorage.setItem(STORAGE_KEY, theme);
    syncToggleState(theme);
  }

  /* ── Atualiza ícone e tooltip do botão ──────────────────── */
  function syncToggleState(theme) {
    var btn = document.getElementById("themeToggle");
    if (!btn) return;
    var label = theme === "dark" ? "Ativar modo claro" : "Ativar modo escuro";
    btn.title = label;
    btn.setAttribute("aria-label", label);
  }

  /* ── Inicializa ao carregar o DOM ───────────────────────── */
  function init() {
    syncToggleState(getSaved());

    var btn = document.getElementById("themeToggle");
    if (!btn) return;

    btn.addEventListener("click", function () {
      var current =
        document.documentElement.getAttribute("data-theme") || DEFAULT;
      applyTheme(current === "dark" ? "light" : "dark", true);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
