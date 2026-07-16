/* ============================================================
   Sprint Max — Cadastro
   Validação em tempo real, indicador de força de senha e estado
   de loading. Helpers compartilhados vêm de auth-comum.js.
   ============================================================ */

document.addEventListener("DOMContentLoaded", () => {

    const cadastroForm = document.getElementById("cadastroForm");
    if (!cadastroForm) return;

    const cadNome = document.getElementById("cadNome");
    const cadEmail = document.getElementById("cadEmail");
    const cadSenha = document.getElementById("cadSenha");
    const cadConfirmar = document.getElementById("cadConfirmar");

    /* ── Indicador de força de senha ────────────────────────── */

    (function initStrength() {
        const wrapper = document.getElementById("cadSenhaStrength");
        if (!cadSenha || !wrapper) return;

        const bars = wrapper.querySelectorAll(".forca-barra");
        const label = wrapper.querySelector(".forca-rotulo");

        cadSenha.addEventListener("input", () => {
            const val = cadSenha.value;
            let score = 0;

            if (val.length >= 6) score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val) && /[0-9!@#$%]/.test(val)) score++;

            const map = [
                { cls: "", text: "" },
                { cls: "weak", text: "Fraca" },
                { cls: "medium", text: "Média" },
                { cls: "strong", text: "Forte" },
            ];

            bars.forEach((bar, i) => {
                bar.className = "forca-barra";
                if (i < score) bar.classList.add(map[score].cls);
            });

            label.textContent = val.length ? map[score].text : "";
            label.className = "forca-rotulo " + (val.length ? map[score].cls : "");
        });
    })();

    /* ── Validações de campo ────────────────────────────────── */

    function validarNome() {
        if (!cadNome) return true;
        const w = cadNome.closest(".entrada-conteiner");
        if (!cadNome.value.trim()) {
            setError(w, "Nome é obrigatório.");
            return false;
        }
        if (cadNome.value.trim().length < 3) {
            setError(w, "Nome deve ter pelo menos 3 caracteres.");
            return false;
        }
        const regex = /^[A-Za-zÀ-ÿ ]+$/;
        if (!regex.test(cadNome.value.trim())) {
            setError(w, "Nome inválido.");
            return false;
        }
        return true;
    }

    function validarEmailCampo() {
        if (!cadEmail) return true;
        const w = cadEmail.closest(".entrada-conteiner");
        if (!cadEmail.value.trim()) {
            setError(w, "E-mail é obrigatório.");
            return false;
        }
        if (!emailValido(cadEmail.value)) {
            setError(w, "Digite um e-mail válido.");
            return false;
        }
        setSuccess(w);
        return true;
    }

    function validarSenha() {
        if (!cadSenha) return true;
        const w = cadSenha.closest(".entrada-conteiner");
        if (!cadSenha.value.trim()) {
            setError(w, "Senha é obrigatória.");
            return false;
        }
        if (cadSenha.value.length < 6) {
            setError(w, "Mínimo 6 caracteres.");
            return false;
        }
        setSuccess(w);
        return true;
    }

    function validarConfirmacao() {
        if (!cadConfirmar) return true;
        const w = cadConfirmar.closest(".entrada-conteiner");
        if (!cadConfirmar.value.trim()) {
            setError(w, "Confirme sua senha.");
            return false;
        }
        if (cadSenha && cadConfirmar.value !== cadSenha.value) {
            setError(w, "As senhas não coincidem.");
            return false;
        }
        setSuccess(w);
        return true;
    }

    if (cadNome) cadNome.addEventListener("blur", validarNome);
    if (cadEmail) cadEmail.addEventListener("blur", validarEmailCampo);
    if (cadSenha) cadSenha.addEventListener("blur", validarSenha);
    if (cadConfirmar) cadConfirmar.addEventListener("blur", validarConfirmacao);

    /* Verificação de coincidência das senhas em tempo real */
    if (cadConfirmar) {
        cadConfirmar.addEventListener("input", () => {
            if (!cadConfirmar.value) return;
            const w = cadConfirmar.closest(".entrada-conteiner");
            if (cadSenha && cadConfirmar.value !== cadSenha.value) {
                setError(w, "As senhas não coincidem.");
            } else {
                setSuccess(w);
            }
        });
    }

    cadastroForm.addEventListener("submit", (e) => {
        const n = validarNome();
        const m = validarEmailCampo();
        const s = validarSenha();
        const c = validarConfirmacao();

        if (!n || !m || !s || !c) {
            e.preventDefault();
            return;
        }

        setBtnLoading(cadastroForm.querySelector(".botao-sprint"), true, "Cadastrando...");
    });
});
