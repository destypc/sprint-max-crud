/* ============================================================
   Sprint Max — Autenticação (comum a Login e Cadastro)
   Helpers de validação, estado de loading e comportamentos de
   interface compartilhados. Carregado antes de login.js/cadastro.js.
   ============================================================ */

/* ── Utilitários de validação (globais) ─────────────────────── */

function emailValido(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
}

function setError(wrapper, msg) {
    clearState(wrapper);
    wrapper.classList.add("error");
    const p = document.createElement("p");
    p.className = "field-error";
    p.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${msg}`;
    wrapper.insertAdjacentElement("afterend", p);
}

function setSuccess(wrapper) {
    clearState(wrapper);
    wrapper.classList.add("success");
}

function clearState(wrapper) {
    wrapper.classList.remove("error", "success");
    const sibling = wrapper.nextElementSibling;
    if (sibling && sibling.classList.contains("field-error")) {
        sibling.remove();
    }
}

/* ── Estado de loading no botão ─────────────────────────────── */

function setBtnLoading(btn, loading, text = "Aguarde...") {
    if (loading) {
        btn.dataset.original = btn.innerHTML;
        btn.innerHTML = `<span class="btn-spinner"></span> ${text}`;
        btn.classList.add("loading");
        btn.disabled = true;
    } else {
        btn.innerHTML = btn.dataset.original || btn.innerHTML;
        btn.classList.remove("loading");
        btn.disabled = false;
    }
}

/* ── Comportamentos de interface compartilhados ─────────────── */

document.addEventListener("DOMContentLoaded", () => {

    /* Toggle mostrar / ocultar senha */
    document.querySelectorAll(".alternar-senha").forEach((btn) => {
        btn.addEventListener("click", () => {
            const input = btn.closest(".entrada-conteiner").querySelector("input");
            const icon = btn.querySelector("i");
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        });
    });

    /* Limpa o estado de validação ao digitar */
    document.querySelectorAll(".entrada-conteiner input").forEach((input) => {
        input.addEventListener("input", () => {
            clearState(input.closest(".entrada-conteiner"));
        });
    });
});
