/* ============================================================
   Sprint Max — Login
   Validação em tempo real e estado de loading do formulário.
   Helpers compartilhados vêm de auth-comum.js.
   ============================================================ */

document.addEventListener("DOMContentLoaded", () => {

    const loginForm = document.getElementById("loginForm");
    if (!loginForm) return;

    const emailInput = document.getElementById("loginEmail");
    const senhaInput = document.getElementById("loginSenha");

    if (emailInput) {
        emailInput.addEventListener("blur", () => {
            const w = emailInput.closest(".entrada-conteiner");
            if (!emailInput.value.trim()) {
                setError(w, "E-mail é obrigatório.");
            } else if (!emailValido(emailInput.value)) {
                setError(w, "Digite um e-mail válido.");
            } else {
                setSuccess(w);
            }
        });
    }

    if (senhaInput) {
        senhaInput.addEventListener("blur", () => {
            const w = senhaInput.closest(".entrada-conteiner");
            if (!senhaInput.value.trim()) {
                setError(w, "Senha é obrigatória.");
            } else {
                setSuccess(w);
            }
        });
    }

    loginForm.addEventListener("submit", (e) => {
        let valid = true;

        const emailW = emailInput.closest(".entrada-conteiner");
        if (!emailInput.value.trim()) {
            setError(emailW, "E-mail é obrigatório.");
            valid = false;
        } else if (!emailValido(emailInput.value)) {
            setError(emailW, "Digite um e-mail válido.");
            valid = false;
        }

        const senhaW = senhaInput.closest(".entrada-conteiner");
        if (!senhaInput.value.trim()) {
            setError(senhaW, "Senha é obrigatória.");
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
            return;
        }

        setBtnLoading(loginForm.querySelector(".botao-sprint"), true, "Entrando...");
    });
});

