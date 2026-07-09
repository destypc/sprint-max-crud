/* ============================================================
   Sprint Max — Vendas
   ============================================================ */

/* ── Modal Nova Venda ──────────────────────────────────────── */
const vendaModalBackdrop = document.getElementById("vendaModalBackdrop");

function openVendaModal() {
  // Reset fields
  const form = vendaModalBackdrop.querySelector("form");
  if (form) form.reset();
  document.getElementById("estoqueInfo").textContent = "";
  document.getElementById("vendaQuantidade").removeAttribute("max");

  vendaModalBackdrop.classList.add("open");
  document.body.style.overflow = "hidden";
  setTimeout(() => document.getElementById("vendaCliente").focus(), 120);
}

function closeVendaModal() {
  vendaModalBackdrop.classList.remove("open");
  document.body.style.overflow = "";
}

function handleVendaModalClick(e) {
  if (e.target === vendaModalBackdrop) closeVendaModal();
}

/* ── Seleção de produto: auto-preenche valor e exibe estoque ── */
document.getElementById("vendaProduto").addEventListener("change", function () {
  const opt = this.options[this.selectedIndex];
  const estoque = parseInt(opt.dataset.estoque || "0", 10);
  const preco = parseFloat(opt.dataset.preco || "0");
  const info = document.getElementById("estoqueInfo");
  const qtdFld = document.getElementById("vendaQuantidade");

  if (this.value) {
    document.getElementById("vendaValor").value = preco.toFixed(2);
    qtdFld.max = estoque;
    qtdFld.value = Math.min(parseInt(qtdFld.value || 1, 10), estoque) || 1;

    if (estoque === 0) {
      info.textContent = "⚠ Sem estoque disponível.";
      info.style.color = "#ef4444";
    } else if (estoque <= 5) {
      info.textContent = `⚠ Apenas ${estoque} unidade(s) em estoque.`;
      info.style.color = "#f59e0b";
    } else {
      info.textContent = `✓ ${estoque} unidades disponíveis.`;
      info.style.color = "#10b981";
    }
  } else {
    info.textContent = "";
    qtdFld.removeAttribute("max");
  }
});

/* ── Validação de quantidade no modal ─────────────────────── */
document
  .getElementById("vendaQuantidade")
  .addEventListener("input", function () {
    const prodSel = document.getElementById("vendaProduto");
    const opt = prodSel.options[prodSel.selectedIndex];
    const estoque = parseInt(opt?.dataset.estoque || "0", 10);
    const qtd = parseInt(this.value || "0", 10);
    const info = document.getElementById("estoqueInfo");

    if (estoque > 0 && qtd > estoque) {
      this.value = estoque;
      info.textContent = `⚠ Máximo disponível: ${estoque} unidade(s).`;
      info.style.color = "#ef4444";
    }
  });

/* ── Drawer Editar Venda ───────────────────────────────────── */
const editVendaOverlay = document.getElementById("editVendaOverlay");
const editVendaDrawer = document.getElementById("editVendaDrawer");

function openEditVendaDrawer(venda) {
  document.getElementById("editVendaId").value = venda.id;
  document.getElementById("editVendaCliente").value = venda.cliente;
  document.getElementById("editVendaProduto").value = venda.produto;
  document.getElementById("editVendaQuantidade").value = venda.quantidade;
  document.getElementById("editVendaValor").value = parseFloat(
    venda.valor,
  ).toFixed(2);
  document.getElementById("editVendaStatus").value = venda.status;

  // Limpar erros anteriores
  editVendaDrawer
    .querySelectorAll(".field-inline-error")
    .forEach(function (el) {
      el.remove();
    });
  editVendaDrawer.querySelectorAll(".form-input").forEach(function (el) {
    el.style.borderColor = "";
    el.style.boxShadow = "";
  });

  editVendaOverlay.classList.add("open");
  editVendaDrawer.classList.add("open");
  document.body.style.overflow = "hidden";
}

function closeEditVendaDrawer() {
  editVendaOverlay.classList.remove("open");
  editVendaDrawer.classList.remove("open");
  document.body.style.overflow = "";
}

/* ── Confirmar exclusão ────────────────────────────────────── */
function confirmDeleteVenda(form) {
  var overlay = document.createElement("div");
  overlay.style.cssText =
    "position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;";
  overlay.innerHTML = [
    '<div style="background:#1a1a1d;border:1px solid #3a3a3f;border-radius:14px;padding:28px 24px;max-width:340px;width:90%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.5)">',
    '<div style="color:#ef4444;font-size:2rem;margin-bottom:12px"><i class="fa-solid fa-triangle-exclamation"></i></div>',
    '<h4 style="color:#fff;font-family:Poppins,sans-serif;font-size:1rem;font-weight:700;margin-bottom:8px">Excluir venda?</h4>',
    '<p style="color:#9a9aa0;font-size:.84rem;margin-bottom:22px">Esta ação não pode ser desfeita.</p>',
    '<div style="display:flex;gap:10px;justify-content:center">',
    '<button id="_vdCancelar" style="padding:9px 22px;border:1.5px solid #3a3a3f;border-radius:8px;background:transparent;color:#fff;cursor:pointer;font-size:.875rem">Cancelar</button>',
    '<button id="_vdConfirmar" style="padding:9px 22px;border:none;border-radius:8px;background:#ef4444;color:#fff;cursor:pointer;font-size:.875rem;font-weight:600">Excluir</button>',
    "</div>",
    "</div>",
  ].join("");

  document.body.appendChild(overlay);

  overlay.querySelector("#_vdConfirmar").addEventListener("click", function () {
    overlay.remove();
    form.submit();
  });
  overlay.querySelector("#_vdCancelar").addEventListener("click", function () {
    overlay.remove();
  });
  overlay.addEventListener("click", function (e) {
    if (e.target === overlay) overlay.remove();
  });
  document.addEventListener("keydown", function esc(e) {
    if (e.key === "Escape") {
      overlay.remove();
      document.removeEventListener("keydown", esc);
    }
  });
}

/* ── ESC fecha modais/drawers ──────────────────────────────── */
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    closeVendaModal();
    closeEditVendaDrawer();
  }
});
