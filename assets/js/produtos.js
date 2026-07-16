/* ============================================================
   Sprint Max — Produtos (admin)
   Busca, drawer de edição, modais (novo / excluir), validação,
   upload de imagem e tag picker.
   O shell (sidebar, perfil, toast) vem de painel.js.
   ============================================================ */

(function () {
    'use strict';

    function $(id) {
        return document.getElementById(id);
    }

    /* ── Erros de campo (compartilhado novo/editar) ────────────── */

    function showErr(el, msg) {
        clearErr(el);
        el.style.borderColor = '#ef4444';
        el.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.18)';
        var span = document.createElement('span');
        span.className = 'field-inline-error';
        span.style.cssText = 'display:flex;align-items:center;gap:5px;' +
            'color:#ef4444;font-size:0.74rem;margin-top:6px;font-weight:500;';
        span.innerHTML = '<i class="fa-solid fa-circle-exclamation" style="font-size:0.7rem"></i> ' + msg;
        el.insertAdjacentElement('afterend', span);
    }

    function clearErr(el) {
        var next = el.nextElementSibling;
        if (next && next.classList.contains('field-inline-error')) next.remove();
        el.style.borderColor = '';
        el.style.boxShadow = '';
    }

    /* Validação dos campos comuns (nome/preço/quantidade). Retorna true se ok. */
    function validarCampos(nomeFld, precoFld, qtdFld) {
        var ok = true;
        if (!nomeFld.value.trim()) {
            showErr(nomeFld, 'Nome é obrigatório.');
            ok = false;
        }
        var pv = parseFloat(precoFld.value);
        if (precoFld.value === '' || isNaN(pv) || pv < 0.01) {
            showErr(precoFld, 'Informe um preço válido (mínimo R$ 0,01).');
            ok = false;
        }
        var ev = parseInt(qtdFld.value, 10);
        if (qtdFld.value === '' || isNaN(ev) || ev < 0) {
            showErr(qtdFld, 'Informe uma quantidade válida.');
            ok = false;
        }
        return ok;
    }

    /* Sanitização em tempo real (nome/preço/quantidade) */
    function bindSanitizers(nomeFld, precoFld, qtdFld) {
        if (nomeFld) nomeFld.addEventListener('input', function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ0-9\s\-.'()&\/]/g, '');
            clearErr(this);
        });
        if (precoFld) precoFld.addEventListener('input', function () {
            if (parseFloat(this.value) > 999999.99) this.value = '999999.99';
            clearErr(this);
        });
        if (qtdFld) qtdFld.addEventListener('input', function () {
            if (parseInt(this.value, 10) > 99999) this.value = '99999';
            clearErr(this);
        });
    }

    /* ── Upload de imagem (compartilhado novo/editar) ──────────── */

    var IMG_MIN_W = 300, IMG_MIN_H = 300, IMG_MAX_W = 4000, IMG_MAX_H = 4000;

    function initUploadArea(cfg) {
        var area = $(cfg.area);
        var input = $(cfg.input);
        var vazio = $(cfg.vazio);
        var prev = $(cfg.prev);
        var img = $(cfg.img);
        var nome = $(cfg.nome);
        var btnRem = $(cfg.btnRem);
        if (!area || !input) return;

        function erroUpload(msg) {
            input.value = '';
            area.classList.remove('tem-arquivo', 'drag-over');
            vazio.style.display = 'flex';
            prev.style.display = 'none';
            var jaErro = area.querySelector('.upload-dim-erro');
            if (jaErro) jaErro.remove();
            var span = document.createElement('span');
            span.className = 'upload-dim-erro';
            span.style.cssText = 'display:block;margin-top:8px;font-size:.74rem;color:#ef4444;font-weight:500';
            span.innerHTML = '<i class="fa-solid fa-circle-exclamation" style="margin-right:4px"></i>' + msg;
            vazio.appendChild(span);
            setTimeout(function () {
                if (span.parentNode) span.remove();
            }, 4500);
        }

        function mostrarPreview(arquivo) {
            var r = new FileReader();
            r.onload = function (e) {
                var tmpImg = new Image();
                tmpImg.onload = function () {
                    var w = tmpImg.width, h = tmpImg.height;
                    if (w < IMG_MIN_W || h < IMG_MIN_H) {
                        erroUpload('Imagem muito pequena (' + w + '×' + h + 'px). Mínimo: ' + IMG_MIN_W + '×' + IMG_MIN_H + 'px.');
                        return;
                    }
                    if (w > IMG_MAX_W || h > IMG_MAX_H) {
                        erroUpload('Imagem muito grande (' + w + '×' + h + 'px). Máximo: ' + IMG_MAX_W + '×' + IMG_MAX_H + 'px.');
                        return;
                    }
                    var jaErro = area.querySelector('.upload-dim-erro');
                    if (jaErro) jaErro.remove();
                    img.src = e.target.result;
                    nome.textContent = arquivo.name + '  (' + w + '×' + h + 'px)';
                    vazio.style.display = 'none';
                    prev.style.display = 'block';
                    area.classList.add('tem-arquivo');
                };
                tmpImg.src = e.target.result;
            };
            r.readAsDataURL(arquivo);
        }

        area.addEventListener('click', function () {
            if (!area.classList.contains('tem-arquivo')) input.click();
        });
        area.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (!area.classList.contains('tem-arquivo')) area.classList.add('drag-over');
        });
        area.addEventListener('dragleave', function () {
            area.classList.remove('drag-over');
        });
        area.addEventListener('drop', function (e) {
            e.preventDefault();
            area.classList.remove('drag-over');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                var dt = new DataTransfer();
                dt.items.add(e.dataTransfer.files[0]);
                input.files = dt.files;
                mostrarPreview(e.dataTransfer.files[0]);
            }
        });
        input.addEventListener('change', function () {
            if (this.files && this.files[0]) mostrarPreview(this.files[0]);
        });
        if (btnRem) btnRem.addEventListener('click', function (e) {
            e.stopPropagation();
            input.value = '';
            img.src = '';
            nome.textContent = '';
            prev.style.display = 'none';
            vazio.style.display = 'flex';
            area.classList.remove('tem-arquivo');
        });
    }

    /* ── Tag Picker ────────────────────────────────────────────── */

    function toggleChip(chip, pickerId, inputId) {
        chip.classList.toggle('ativo');
        syncTagInput(pickerId, inputId);
    }

    function initTagPicker(pickerId, inputId) {
        var picker = $(pickerId);
        if (!picker) return;
        picker.querySelectorAll('.tag-chip').forEach(function (chip) {
            chip.addEventListener('click', function () { toggleChip(chip, pickerId, inputId); });
        });
    }

    /* Adiciona um chip ao picker (ou apenas o seleciona, se já existir). */
    function adicionarChip(pickerId, inputId, nome, selecionar) {
        var picker = $(pickerId);
        if (!picker) return;
        var existente = null;
        picker.querySelectorAll('.tag-chip').forEach(function (c) {
            if (c.dataset.tag === nome) existente = c;
        });
        if (existente) {
            if (selecionar) existente.classList.add('ativo');
        } else {
            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'tag-chip' + (selecionar ? ' ativo' : '');
            chip.dataset.tag = nome;
            chip.textContent = nome;
            chip.addEventListener('click', function () { toggleChip(chip, pickerId, inputId); });
            picker.appendChild(chip);
        }
        syncTagInput(pickerId, inputId);
    }

    /* Cria uma tag personalizada no catálogo e a disponibiliza nos dois pickers. */
    function criarTagPersonalizada(nome, pickerId, inputId, botao) {
        nome = (nome || '').trim();
        if (!nome) return;

        var dados = new FormData();
        dados.append('acao', 'criar');
        dados.append('nome', nome);

        if (botao) botao.disabled = true;

        fetch('/app/controller/tagsController.php', { method: 'POST', body: dados })
            .then(function (r) {
                // Aceita JSON mesmo em respostas de erro (422/500); protege contra HTML inesperado.
                return r.json().catch(function () {
                    return { ok: false, erro: 'Resposta inválida do servidor.' };
                });
            })
            .then(function (res) {
                if (!res.ok) {
                    showToast(res.erro || 'Não foi possível criar a tag.', 'error');
                    return;
                }
                // Disponível para reuso em ambos os formulários; selecionada onde foi criada.
                adicionarChip('criarTagPicker', 'criarTagsInput', res.tag.nome, pickerId === 'criarTagPicker');
                adicionarChip('editTagPicker', 'editTagsInput', res.tag.nome, pickerId === 'editTagPicker');
            })
            .catch(function () { showToast('Erro de conexão ao criar a tag.', 'error'); })
            .finally(function () { if (botao) botao.disabled = false; });
    }

    function setTagPicker(pickerId, inputId, tagsStr) {
        var picker = $(pickerId);
        if (!picker) return;
        var tags = tagsStr ? tagsStr.split(',').map(function (t) { return t.trim(); }) : [];
        // Garante que tags do produto que não estejam no catálogo apareçam como chips.
        tags.forEach(function (nome) {
            if (nome) adicionarChip(pickerId, inputId, nome, false);
        });
        picker.querySelectorAll('.tag-chip').forEach(function (chip) {
            chip.classList.toggle('ativo', tags.indexOf(chip.dataset.tag) !== -1);
        });
        syncTagInput(pickerId, inputId);
    }

    function syncTagInput(pickerId, inputId) {
        var selected = [];
        document.querySelectorAll('#' + pickerId + ' .tag-chip.ativo').forEach(function (c) {
            selected.push(c.dataset.tag);
        });
        var inp = $(inputId);
        if (inp) inp.value = selected.join(',');
    }

    /* Liga os controles de "criar tag" (input + botão) de todos os seletores. */
    function initCriarTag() {
        document.querySelectorAll('.tag-criar-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = btn.parentElement.querySelector('.tag-criar-input');
                if (!input) return;
                criarTagPersonalizada(input.value, btn.dataset.alvoPicker, btn.dataset.alvoInput, btn);
                input.value = '';
            });
        });
        document.querySelectorAll('.tag-criar-input').forEach(function (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var botao = input.parentElement.querySelector('.tag-criar-btn');
                    criarTagPersonalizada(input.value, input.dataset.alvoPicker, input.dataset.alvoInput, botao);
                    input.value = '';
                }
            });
        });
    }

    window.setTagPicker = setTagPicker;

    /* ── Drawer editar produto (global) ────────────────────────── */

    function openEditDrawer(produto) {
        $('editId').value = produto.id;
        $('editNome').value = produto.nome;
        $('editMarca').value = produto.marca || '';
        $('editCor').value = produto.cor || '';
        $('editCategoria').value = produto.categoria;
        $('editPreco').value = produto.preco;
        $('editEstoque').value = produto.quantidade;

        setTagPicker('editTagPicker', 'editTagsInput', produto.tags || '');
        var editVis = $('editVisivel');
        if (editVis) editVis.value = produto.visivel === 0 ? '0' : '1';

        var editArea = $('editUploadArea');
        var editVazio = $('editUploadVazio');
        var editPrev = $('editUploadPreview');
        var editImg = $('editImgPreview');
        var editNome = $('editUploadNome');
        var editFile = $('editImagem');

        if (editFile) editFile.value = '';

        if (produto.imagem) {
            editImg.src = produto.imagem;
            editNome.textContent = 'Imagem atual';
            editVazio.style.display = 'none';
            editPrev.style.display = 'block';
            editArea.classList.add('tem-arquivo');
        } else {
            editImg.src = '';
            editVazio.style.display = 'flex';
            editPrev.style.display = 'none';
            editArea.classList.remove('tem-arquivo');
        }

        var f = $('editForm');
        f.querySelectorAll('.field-inline-error').forEach(function (el) { el.remove(); });
        f.querySelectorAll('.entrada-formulario, .selecao-formulario').forEach(function (el) {
            el.style.borderColor = '';
            el.style.boxShadow = '';
        });

        $('drawerOverlay').classList.add('open');
        $('editDrawer').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeEditDrawer() {
        var ov = $('drawerOverlay');
        var dr = $('editDrawer');
        if (ov) ov.classList.remove('open');
        if (dr) dr.classList.remove('open');
        document.body.style.overflow = '';
    }

    /* ── Modal novo produto (global) ───────────────────────────── */

    function openProductModal() {
        var backdrop = $('productModalBackdrop');
        var f = backdrop.querySelector('form');
        if (f) {
            f.reset();
            f.querySelectorAll('.field-inline-error').forEach(function (el) { el.remove(); });
            f.querySelectorAll('.entrada-formulario, .selecao-formulario').forEach(function (el) {
                el.style.borderColor = '';
                el.style.boxShadow = '';
            });
            var area = $('criarUploadArea');
            if (area) {
                area.classList.remove('tem-arquivo');
                $('criarUploadVazio').style.display = 'flex';
                $('criarUploadPreview').style.display = 'none';
                $('criarImgPreview').src = '';
                $('criarUploadNome').textContent = '';
            }
            setTagPicker('criarTagPicker', 'criarTagsInput', '');
            var vis = $('criarVisivel');
            if (vis) vis.value = '1';
        }
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(function () {
            var n = $('criarNome');
            if (n) n.focus();
        }, 120);
    }

    function closeProductModal() {
        var backdrop = $('productModalBackdrop');
        if (backdrop) backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    function handleProductModalClick(e) {
        if (e.target === $('productModalBackdrop')) closeProductModal();
    }

    /* ── Exclusão (usa o modal reutilizável de painel.js) ──────── */

    function openDeleteModal(id, nome) {
        abrirModalExclusao({
            titulo: 'Excluir produto?',
            alvo: nome,
            aoConfirmar: function () {
                $('deleteProductId').value = id;
                $('deleteProductForm').submit();
            }
        });
    }

    window.openEditDrawer = openEditDrawer;
    window.closeEditDrawer = closeEditDrawer;
    window.openProductModal = openProductModal;
    window.closeProductModal = closeProductModal;
    window.handleProductModalClick = handleProductModalClick;
    window.openDeleteModal = openDeleteModal;

    /* ── Inicialização ─────────────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', function () {

        /* Busca client-side */
        var searchInput = $('searchInput');
        var tableBody = $('tableBody');
        if (searchInput && tableBody) {
            var emptyRow = $('emptyRow');
            var countEl = $('countVisible');
            var searchTimer;
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    var query = searchInput.value.toLowerCase().trim();
                    var rows = tableBody.querySelectorAll('tr[data-searchable]');
                    var visible = 0;
                    rows.forEach(function (row) {
                        var match = !query || row.getAttribute('data-searchable').includes(query);
                        row.style.display = match ? '' : 'none';
                        if (match) visible++;
                    });
                    if (emptyRow) emptyRow.style.display = visible === 0 ? '' : 'none';
                    if (countEl) countEl.textContent = visible;
                }, 300);
            });
        }

        /* Validação — novo produto (no submit) */
        var novoForm = $('novoProdutoForm');
        if (novoForm) {
            var nNome = $('criarNome'), nPreco = $('criarPreco'), nQtd = $('criarEstoque'), nCat = $('criarCategoria');
            bindSanitizers(nNome, nPreco, nQtd);
            if (nCat) nCat.addEventListener('change', function () { clearErr(this); });
            novoForm.addEventListener('submit', function (e) {
                var ok = validarCampos(nNome, nPreco, nQtd);
                if (nCat && !nCat.value) { showErr(nCat, 'Selecione uma categoria.'); ok = false; }
                if (!ok) e.preventDefault();
            });
        }

        /* Validação — editar produto (no clique de salvar) */
        var eNome = $('editNome'), ePreco = $('editPreco'), eQtd = $('editEstoque');
        var saveBtn = document.querySelector('.btn-save-drawer');
        if (eNome && saveBtn) {
            bindSanitizers(eNome, ePreco, eQtd);
            saveBtn.addEventListener('click', function () {
                [eNome, ePreco, eQtd].forEach(clearErr);
                if (validarCampos(eNome, ePreco, eQtd)) $('editForm').submit();
            });
        }

        /* Upload de imagem (novo + editar) */
        initUploadArea({
            area: 'criarUploadArea', input: 'criarImagem', vazio: 'criarUploadVazio',
            prev: 'criarUploadPreview', img: 'criarImgPreview', nome: 'criarUploadNome', btnRem: 'criarUploadRemover'
        });
        initUploadArea({
            area: 'editUploadArea', input: 'editImagem', vazio: 'editUploadVazio',
            prev: 'editUploadPreview', img: 'editImgPreview', nome: 'editUploadNome', btnRem: 'editUploadRemover'
        });

        /* Tag pickers */
        initTagPicker('criarTagPicker', 'criarTagsInput');
        initTagPicker('editTagPicker', 'editTagsInput');
        initCriarTag();

        /* ESC fecha drawers/modais desta página */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeEditDrawer();
                closeProductModal();
            }
        });
    });
})();
