/* ============================================================
   Sprint Max — Loja (home.php e favoritos.php)
   Modal de detalhe do produto + (no home) filtro de categoria/busca.
   Null-safe: no home o modal é só de visualização (sem botão de
   carrinho / seletor de quantidade); no favoritos esses elementos
   existem e são usados.
   ============================================================ */

(function () {
    'use strict';

    var qtdAtual = 1;
    var qtdMax = 99;

    function $(id) {
        return document.getElementById(id);
    }

    /* ── Modal de detalhe (global: chamado por onclick inline) ── */

    function verDetalhes(p) {
        qtdAtual = 1;
        qtdMax = p.quantidade === 0 ? 0 : Math.max(1, p.quantidade);

        var cat = $('detalheCat');
        if (cat) cat.textContent = p.categoria;
        var nome = $('detalheNome');
        if (nome) nome.textContent = p.nome;
        var preco = $('detalhePreco');
        if (preco) {
            preco.textContent = 'R$ ' + parseFloat(p.preco).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        var desc = $('detalheDesc');
        if (desc) desc.textContent = p.descricao || 'Sem descrição.';

        var estoqueEl = $('detalheEstoque');
        if (estoqueEl) {
            estoqueEl.className = 'dprod-estoque-pill';
            if (p.quantidade === 0) {
                estoqueEl.classList.add('indisponivel');
                estoqueEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Indisponível';
            } else if (p.quantidade <= 5) {
                estoqueEl.classList.add('baixo');
                estoqueEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Apenas ' + p.quantidade + ' em estoque';
            } else {
                estoqueEl.classList.add('disponivel');
                estoqueEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + p.quantidade + ' disponível(is)';
            }
        }

        var img = $('detalheImg');
        var noImg = $('detalheNoImg');
        if (img && noImg) {
            if (p.imagem) {
                img.src = p.imagem;
                img.alt = p.nome || '';
                img.style.display = 'block';
                noImg.style.display = 'none';
            } else {
                img.style.display = 'none';
                noImg.style.display = 'flex';
            }
        }

        /* Seletor de quantidade + botão de carrinho (só no favoritos) */
        var qtdNum = $('qtdNum');
        if (qtdNum) qtdNum.textContent = '1';
        var cartQtd = $('cartQuantidade');
        if (cartQtd) cartQtd.value = '1';
        var cartId = $('cartProdutoId');
        if (cartId) cartId.value = p.id;

        var btnCart = $('btnAdicionarCart');
        if (btnCart) {
            btnCart.disabled = p.quantidade === 0;
            btnCart.innerHTML = p.quantidade === 0
                ? '<i class="fa-solid fa-ban"></i> Indisponível'
                : '<i class="fa-solid fa-cart-shopping"></i> Adicionar ao Carrinho';
        }

        var backdrop = $('detalheModalBackdrop');
        if (backdrop) backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeDetalheModal() {
        var backdrop = $('detalheModalBackdrop');
        if (backdrop) backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    function fecharDetalhe(e) {
        if (e.target === $('detalheModalBackdrop')) closeDetalheModal();
    }

    window.verDetalhes = verDetalhes;
    window.closeDetalheModal = closeDetalheModal;
    window.fecharDetalhe = fecharDetalhe;

    /* ── Inicialização ─────────────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', function () {

        /* Seletor de quantidade (só existe no favoritos) */
        var qtdMenos = $('qtdMenos');
        var qtdMais = $('qtdMais');
        if (qtdMenos) {
            qtdMenos.addEventListener('click', function () {
                if (qtdAtual > 1) {
                    qtdAtual--;
                    $('qtdNum').textContent = qtdAtual;
                    var cq = $('cartQuantidade');
                    if (cq) cq.value = qtdAtual;
                }
            });
        }
        if (qtdMais) {
            qtdMais.addEventListener('click', function () {
                if (qtdAtual < qtdMax) {
                    qtdAtual++;
                    $('qtdNum').textContent = qtdAtual;
                    var cq = $('cartQuantidade');
                    if (cq) cq.value = qtdAtual;
                }
            });
        }

        /* Filtro de categoria + busca (só existe no home) */
        var lojaSearch = $('lojaSearch');
        var catBtns = document.querySelectorAll('.cat-btn');
        if (lojaSearch || catBtns.length) {
            var activeCategory = '';
            var searchQuery = '';

            var filtrar = function () {
                var cards = document.querySelectorAll('.produto-card');
                var visible = 0;
                cards.forEach(function (card) {
                    var catMatch = !activeCategory || card.dataset.categoria === activeCategory;
                    var srchMatch = !searchQuery || card.dataset.search.includes(searchQuery);
                    var show = catMatch && srchMatch;
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;
                });
                var empty = $('lojaEmpty');
                if (empty) empty.style.display = visible === 0 ? '' : 'none';
            };

            catBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    catBtns.forEach(function (b) { b.classList.remove('active'); });
                    this.classList.add('active');
                    activeCategory = this.dataset.cat;
                    filtrar();
                });
            });

            if (lojaSearch) {
                var searchTimer;
                lojaSearch.addEventListener('input', function () {
                    clearTimeout(searchTimer);
                    var val = this.value.trim().toLowerCase();
                    searchTimer = setTimeout(function () {
                        searchQuery = val;
                        filtrar();
                    }, 220);
                });
            }
        }

        /* ESC fecha o modal de detalhe */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDetalheModal();
        });
    });
})();
