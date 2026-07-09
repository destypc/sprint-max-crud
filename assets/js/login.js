/* ============================================================
   Sprint Max — Login & Cadastro (JavaScript v2)
   Validação em tempo real, loading states, SweetAlert2
   ============================================================ */

document.addEventListener("DOMContentLoaded", () => {
  /* ── Utilitários de validação ──────────────────────────── */

  /** Verifica formato de e-mail */
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
  }

  /** Marca o input-wrapper com estado de erro */
  function setError(wrapper, msg) {
    clearState(wrapper);
    wrapper.classList.add("error");
    const p = document.createElement("p");
    p.className = "field-error";
    p.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${msg}`;
    wrapper.insertAdjacentElement("afterend", p);
  }

  /** Marca o input-wrapper com estado de sucesso */
  function setSuccess(wrapper) {
    clearState(wrapper);
    wrapper.classList.add("success");
  }

  /** Remove qualquer estado de validação */
  function clearState(wrapper) {
    wrapper.classList.remove("error", "success");
    const sibling = wrapper.nextElementSibling;
    if (sibling && sibling.classList.contains("field-error")) {
      sibling.remove();
    }
  }

  /* ── Estado de loading no botão ───────────────────────── */

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

  /* ── Toggle mostrar / ocultar senha ───────────────────── */

  document.querySelectorAll(".toggle-password").forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = btn.closest(".input-wrapper").querySelector("input");
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

  /* ── Limpar estado ao digitar ─────────────────────────── */

  document.querySelectorAll(".input-wrapper input").forEach((input) => {
    input.addEventListener("input", () => {
      clearState(input.closest(".input-wrapper"));
    });
  });

  /* ── Indicador de força de senha ──────────────────────── */

  function initStrength(inputId, wrapperId) {
    const input = document.getElementById(inputId);
    const wrapper = document.getElementById(wrapperId);
    if (!input || !wrapper) return;

    const bars = wrapper.querySelectorAll(".strength-bar");
    const label = wrapper.querySelector(".strength-label");

    input.addEventListener("input", () => {
      const val = input.value;
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
        bar.className = "strength-bar";
        if (i < score) bar.classList.add(map[score].cls);
      });

      label.textContent = val.length ? map[score].text : "";
      label.className = "strength-label " + (val.length ? map[score].cls : "");
    });
  }

  initStrength("cadSenha", "cadSenhaStrength");

  /* ── Formulário de Login ──────────────────────────────── */

  const loginForm = document.getElementById("loginForm");

  if (loginForm) {
    const emailInput = document.getElementById("loginEmail");
    const senhaInput = document.getElementById("loginSenha");

    if (emailInput) {
      emailInput.addEventListener("blur", () => {
        const w = emailInput.closest(".input-wrapper");
        if (!emailInput.value.trim()) {
          setError(w, "E-mail é obrigatório.");
        } else if (!isValidEmail(emailInput.value)) {
          setError(w, "Digite um e-mail válido.");
        } else {
          setSuccess(w);
        }
      });
    }

    if (senhaInput) {
      senhaInput.addEventListener("blur", () => {
        const w = senhaInput.closest(".input-wrapper");
        if (!senhaInput.value.trim()) {
          setError(w, "Senha é obrigatória.");
        } else {
          setSuccess(w);
        }
      });
    }

    loginForm.addEventListener("submit", (e) => {
      let valid = true;

      const emailW = emailInput.closest(".input-wrapper");
      if (!emailInput.value.trim()) {
        setError(emailW, "E-mail é obrigatório.");
        valid = false;
      } else if (!isValidEmail(emailInput.value)) {
        setError(emailW, "Digite um e-mail válido.");
        valid = false;
      }

      const senhaW = senhaInput.closest(".input-wrapper");
      if (!senhaInput.value.trim()) {
        setError(senhaW, "Senha é obrigatória.");
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
        return;
      }

      // Campos válidos: loading e deixa o form submeter ao PHP
      setBtnLoading(
        loginForm.querySelector(".btn-sprint"),
        true,
        "Entrando...",
      );
    });
  }

  /* ── Formulário de Cadastro ───────────────────────────── */

  const cadastroForm = document.getElementById("cadastroForm");

  if (cadastroForm) {
    const cadNome = document.getElementById("cadNome");
    const cadEmail = document.getElementById("cadEmail");
    const cadSenha = document.getElementById("cadSenha");
    const cadConfirmar = document.getElementById("cadConfirmar");

    function validateNome() {
      if (!cadNome) return true;
      const w = cadNome.closest(".input-wrapper");
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
      setSuccess(w);
    }

    function validateEmail() {
      if (!cadEmail) return true;
      const w = cadEmail.closest(".input-wrapper");
      if (!cadEmail.value.trim()) {
        setError(w, "E-mail é obrigatório.");
        return false;
      }
      if (!isValidEmail(cadEmail.value)) {
        setError(w, "Digite um e-mail válido.");
        return false;
      }
      setSuccess(w);
      return true;
    }

    function validateSenha() {
      if (!cadSenha) return true;
      const w = cadSenha.closest(".input-wrapper");
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

    function validateConfirmar() {
      if (!cadConfirmar) return true;
      const w = cadConfirmar.closest(".input-wrapper");
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

    if (cadNome) cadNome.addEventListener("blur", validateNome);
    if (cadEmail) cadEmail.addEventListener("blur", validateEmail);
    if (cadSenha) cadSenha.addEventListener("blur", validateSenha);
    if (cadConfirmar) cadConfirmar.addEventListener("blur", validateConfirmar);

    // Verificação de match em tempo real
    if (cadConfirmar) {
      cadConfirmar.addEventListener("input", () => {
        if (!cadConfirmar.value) return;
        const w = cadConfirmar.closest(".input-wrapper");
        if (cadSenha && cadConfirmar.value !== cadSenha.value) {
          setError(w, "As senhas não coincidem.");
        } else {
          setSuccess(w);
        }
      });
    }

    cadastroForm.addEventListener("submit", (e) => {
      const n = validateNome();
      const m = validateEmail();
      const s = validateSenha();
      const c = validateConfirmar();

      if (!n || !m || !s || !c) {
        e.preventDefault();
        return;
      }

      // Campos válidos: loading e submete ao PHP
      setBtnLoading(
        cadastroForm.querySelector(".btn-sprint"),
        true,
        "Cadastrando...",
      );
    });
  }

  /* ── Alertas vindos do backend via query string ─────────── */

  const params = new URLSearchParams(window.location.search);

  if (params.has("erro") && typeof Swal !== "undefined") {
    const msgs = {
      credenciais: "E-mail ou senha incorretos.",
      email_existe: "Este e-mail já está cadastrado.",
      campos: "Preencha todos os campos obrigatórios.",
      senha_curta: "A senha deve ter pelo menos 6 caracteres.",
      senhas_diferentes: "As senhas não coincidem.",
    };
    const codigo = params.get("erro");
    const texto = msgs[codigo] || "Ocorreu um erro. Tente novamente.";

    Swal.fire({
      icon: "error",
      title: "Ops!",
      text: texto,
      background: "#1A1A1D",
      color: "#FFFFFF",
      confirmButtonColor: "#F97316",
      confirmButtonText: "Entendido",
    });
  }

  if (params.has("sucesso") && typeof Swal !== "undefined") {
    const msgsSucesso = {
      cadastro: "Conta criada com sucesso! Faça login para continuar.",
      logout: "Você saiu da sua conta.",
    };
    const codigo = params.get("sucesso");
    const texto = msgsSucesso[codigo] || "Operação realizada com sucesso.";

    Swal.fire({
      icon: "success",
      title: "Sucesso!",
      text: texto,
      background: "#1A1A1D",
      color: "#FFFFFF",
      confirmButtonColor: "#F97316",
      confirmButtonText: "Ok",
      timer: 3000,
      timerProgressBar: true,
    });
  }
});
