/**
 * CRUD de produtos via AJAX (jQuery).
 * Endpoints em window.API_PRODUTO (definido na view).
 */
(function ($) {
    'use strict';

    var $mensagem = $('#mensagem');
    var $tbody = $('#tabela-produtos tbody');
    var $tabelaVazia = $('#tabela-vazia');
    var $modal = $('#modal-produto');
    var $form = $('#form-produto');
    var $modalTitulo = $('#modal-titulo');

    function mostrarMensagem(texto, tipo) {
        tipo = tipo || 'sucesso';
        $mensagem.removeClass('sucesso erro').addClass('visivel ' + tipo).text(texto);
        setTimeout(function () {
            $mensagem.removeClass('visivel');
        }, 5000);
    }

    function abrirModal(titulo) {
        $modalTitulo.text(titulo);
        $form[0].reset();
        $('#produto-id').val('');
        $modal.attr('aria-hidden', 'false').addClass('aberto');
    }

    function fecharModal() {
        $modal.attr('aria-hidden', 'true').removeClass('aberto');
    }

    function removerInvalidos() {
        $form.find('input, select, textarea').removeClass('invalido');
    }

    function truncar(str, maxLen) {
        if (str == null) return '';
        str = String(str);
        if (str.length <= maxLen) return str;
        return str.slice(0, maxLen) + '…';
    }

    function listarProdutos() {
        $.ajax({
            url: window.API_PRODUTO.lista,
            method: 'GET',
            dataType: 'json'
        }).done(function (res) {
            if (!res.success || !res.data) return;
            var rows = res.data.map(function (p) {
                var statusBadge = p.status === 'A' ? '<span class="badge badge-ativo">Ativo</span>' : '<span class="badge badge-inativo">Inativo</span>';
                var descricao = truncar(p.descricao, 60);
                return '<tr data-id="' + p.id + '">' +
                    '<td>' + p.id + '</td>' +
                    '<td>' + escapeHtml(p.nome) + '</td>' +
                    '<td class="cell-descricao">' + escapeHtml(descricao) + '</td>' +
                    '<td>' + escapeHtml(p.codigo_interno || '') + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td>' +
                    '<button type="button" class="btn btn-small btn-editar" data-id="' + p.id + '">Editar</button> ' +
                    '<button type="button" class="btn btn-small btn-excluir" data-id="' + p.id + '">Excluir</button>' +
                    '</td></tr>';
            });
            $tbody.html(rows.join(''));
            $tabelaVazia.toggle(rows.length === 0);
        }).fail(function () {
            mostrarMensagem('Erro ao carregar a lista de produtos.', 'erro');
        });
    }

    function escapeHtml(s) {
        if (s == null) return '';
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    $('#btn-novo-produto').on('click', function () {
        abrirModal('Novo produto');
    });

    $('#modal-close, #modal-backdrop, #btn-cancelar').on('click', function () {
        fecharModal();
    });

    $modal.on('keydown', function (e) {
        if (e.key === 'Escape') fecharModal();
    });

    $(document).on('click', '.btn-editar', function () {
        var id = $(this).data('id');
        $.ajax({
            url: window.API_PRODUTO.buscar,
            method: 'GET',
            data: { id: id },
            dataType: 'json'
        }).done(function (res) {
            if (!res.success || !res.data) {
                mostrarMensagem('Produto não encontrado.', 'erro');
                return;
            }
            var d = res.data;
            $('#produto-id').val(d.id);
            $('#nome').val(d.nome);
            $('#descricao').val(d.descricao || '');
            $('#codigo_interno').val(d.codigo_interno || '');
            $('#status').val(d.status || 'A');
            $modalTitulo.text('Editar produto');
            $modal.attr('aria-hidden', 'false').addClass('aberto');
        }).fail(function () {
            mostrarMensagem('Erro ao carregar produto.', 'erro');
        });
    });

    $(document).on('click', '.btn-excluir', function () {
        var id = $(this).data('id');
        if (!window.confirm('Deseja realmente excluir este produto?')) return;
        $.ajax({
            url: window.API_PRODUTO.excluir,
            method: 'POST',
            data: { id: id },
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                mostrarMensagem(res.message, 'sucesso');
                $tbody.find('tr[data-id="' + id + '"]').remove();
                if ($tbody.find('tr').length === 0) $tabelaVazia.show();
            } else {
                mostrarMensagem(res.message || 'Erro ao excluir.', 'erro');
            }
        }).fail(function () {
            mostrarMensagem('Erro de conexão ao excluir.', 'erro');
        });
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        removerInvalidos();
        var id = $('#produto-id').val();
        var url = id ? window.API_PRODUTO.atualizar : window.API_PRODUTO.salvar;
        var data = $form.serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                mostrarMensagem(res.message, 'sucesso');
                fecharModal();
                listarProdutos();
            } else {
                mostrarMensagem(res.message || 'Erro ao salvar.', 'erro');
                if (res.errors && res.errors.length) {
                    res.errors.forEach(function (field) {
                        $form.find('[name="' + field + '"]').addClass('invalido');
                    });
                }
            }
        }).fail(function () {
            mostrarMensagem('Erro de conexão ao salvar.', 'erro');
        });
    });

    // ---------- Modal: vínculos produto × fornecedor ----------
    var $modalVinculos = $('#modal-fornecedores-produto');
    var $vinculosProdutoId = $('#vinculos-produto-id');
    var $vinculosLista = $('#vinculos-lista');
    var $vinculosListaVazia = $('#vinculos-lista-vazia');
    var $vinculosListaLoading = $('#vinculos-lista-loading');
    var $vinculosBusca = $('#vinculos-busca');
    var $vinculosResultados = $('#vinculos-resultados');
    var $vinculosResultadosVazia = $('#vinculos-resultados-vazia');
    var $vinculosBuscaLoading = $('#vinculos-busca-loading');
    var $vinculosMensagem = $('#vinculos-mensagem');
    var $btnRemoverTodos = $('#btn-remover-todos-vinculos');
    var buscaVinculosTimer = null;
    var DEBOUNCE_MS = 350;

    function mostrarVinculosMensagem(texto, tipo) {
        $vinculosMensagem.removeClass('sucesso erro visivel').addClass('visivel ' + (tipo || 'sucesso')).text(texto);
        setTimeout(function () { $vinculosMensagem.removeClass('visivel'); }, 4000);
    }

    function carregarFornecedoresVinculados(produtoId) {
        $vinculosListaLoading.show();
        $vinculosLista.hide();
        $vinculosListaVazia.hide();
        $.ajax({
            url: window.API_PRODUTO.listaFornecedores,
            method: 'GET',
            data: { produto_id: produtoId },
            dataType: 'json'
        }).done(function (res) {
            $vinculosListaLoading.hide();
            if (!res.success || !res.data) {
                $vinculosLista.empty().show();
                $vinculosListaVazia.show();
                $btnRemoverTodos.prop('disabled', true);
                return;
            }
            var itens = res.data.map(function (f) {
                return '<li data-fornecedor-id="' + f.id + '">' +
                    '<span>' + escapeHtml(f.nome) + (f.email ? ' <small>(' + escapeHtml(f.email) + ')</small>' : '') + '</span>' +
                    '<button type="button" class="btn btn-small btn-excluir btn-remover-vinculo" data-fornecedor-id="' + f.id + '">Remover</button>' +
                    '</li>';
            });
            $vinculosLista.html(itens.join('')).show();
            $vinculosListaVazia.toggle(itens.length === 0);
            $btnRemoverTodos.prop('disabled', itens.length === 0);
        }).fail(function () {
            $vinculosListaLoading.hide();
            $vinculosLista.show();
            mostrarVinculosMensagem('Erro ao carregar fornecedores.', 'erro');
        });
    }

    function buscarFornecedoresParaVincular(produtoId, q) {
        $vinculosBuscaLoading.show();
        $vinculosResultados.empty().hide();
        $vinculosResultadosVazia.hide();
        $.ajax({
            url: window.API_PRODUTO.buscaFornecedoresParaVincular,
            method: 'GET',
            data: { produto_id: produtoId, q: q || '' },
            dataType: 'json'
        }).done(function (res) {
            $vinculosBuscaLoading.hide();
            if (!res.success || !res.data || res.data.length === 0) {
                $vinculosResultadosVazia.show();
                return;
            }
            var itens = res.data.map(function (f) {
                return '<li data-fornecedor-id="' + f.id + '">' +
                    '<span>' + escapeHtml(f.nome) + (f.email ? ' (' + escapeHtml(f.email) + ')' : '') + '</span>' +
                    '<button type="button" class="btn btn-small btn-primary btn-add-vincular" data-fornecedor-id="' + f.id + '" data-nome="' + escapeHtml(f.nome) + '">Adicionar</button>' +
                    '</li>';
            });
            $vinculosResultados.html(itens.join('')).show();
        }).fail(function () {
            $vinculosBuscaLoading.hide();
            mostrarVinculosMensagem('Erro na busca.', 'erro');
        });
    }

    function abrirModalFornecedores(produtoId, produtoNome) {
        $('#modal-vinculos-titulo').text('Fornecedores do produto: ' + produtoNome);
        $vinculosProdutoId.val(produtoId);
        $vinculosBusca.val('');
        $vinculosResultados.empty().hide();
        $vinculosResultadosVazia.hide();
        $vinculosMensagem.removeClass('visivel');
        $modalVinculos.attr('aria-hidden', 'false').addClass('aberto');
        carregarFornecedoresVinculados(produtoId);
    }

    function fecharModalFornecedores() {
        $modalVinculos.attr('aria-hidden', 'true').removeClass('aberto');
    }

    $(document).on('click', '.btn-fornecedores', function () {
        var id = $(this).data('id');
        var nome = $(this).data('nome') || 'ID ' + id;
        abrirModalFornecedores(id, nome);
    });

    $('#modal-vinculos-close, #modal-vinculos-backdrop').on('click', function () {
        fecharModalFornecedores();
    });

    $modalVinculos.on('keydown', function (e) {
        if (e.key === 'Escape') fecharModalFornecedores();
    });

    $vinculosBusca.on('input', function () {
        var produtoId = $vinculosProdutoId.val();
        if (!produtoId) return;
        clearTimeout(buscaVinculosTimer);
        var q = $(this).val();
        if (q.length < 2) {
            $vinculosResultados.empty().hide();
            $vinculosResultadosVazia.hide();
            return;
        }
        buscaVinculosTimer = setTimeout(function () {
            buscarFornecedoresParaVincular(produtoId, q);
        }, DEBOUNCE_MS);
    });

    $(document).on('click', '.btn-add-vincular', function () {
        var produtoId = $vinculosProdutoId.val();
        var fornecedorId = $(this).data('fornecedor-id');
        var $btn = $(this);
        $btn.prop('disabled', true).text('…');
        $.ajax({
            url: window.API_PRODUTO.vincularFornecedor,
            method: 'POST',
            data: { produto_id: produtoId, fornecedor_id: fornecedorId },
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                mostrarVinculosMensagem(res.message, 'sucesso');
                carregarFornecedoresVinculados(produtoId);
                $vinculosBusca.trigger('input');
            } else {
                mostrarVinculosMensagem(res.message || 'Erro ao vincular.', 'erro');
                $btn.prop('disabled', false).text('Adicionar');
            }
        }).fail(function () {
            mostrarVinculosMensagem('Erro de conexão.', 'erro');
            $btn.prop('disabled', false).text('Adicionar');
        });
    });

    $(document).on('click', '.btn-remover-vinculo', function () {
        var produtoId = $vinculosProdutoId.val();
        var fornecedorId = $(this).data('fornecedor-id');
        var $btn = $(this);
        $btn.prop('disabled', true).text('…');
        $.ajax({
            url: window.API_PRODUTO.desvincularFornecedor,
            method: 'POST',
            data: { produto_id: produtoId, fornecedor_id: fornecedorId },
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                mostrarVinculosMensagem(res.message, 'sucesso');
                carregarFornecedoresVinculados(produtoId);
                $vinculosBusca.trigger('input');
            } else {
                mostrarVinculosMensagem(res.message || 'Erro ao remover.', 'erro');
                $btn.prop('disabled', false).text('Remover');
            }
        }).fail(function () {
            mostrarVinculosMensagem('Erro de conexão.', 'erro');
            $btn.prop('disabled', false).text('Remover');
        });
    });

    $('#btn-remover-todos-vinculos').on('click', function () {
        var produtoId = $vinculosProdutoId.val();
        if (!produtoId || !window.confirm('Remover todos os vínculos deste produto?')) return;
        var $btn = $(this);
        $btn.prop('disabled', true).text('Removendo…');
        $.ajax({
            url: window.API_PRODUTO.desvincularTodosFornecedores,
            method: 'POST',
            data: { produto_id: produtoId },
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                mostrarVinculosMensagem(res.message, 'sucesso');
                carregarFornecedoresVinculados(produtoId);
                $vinculosBusca.trigger('input');
            } else {
                mostrarVinculosMensagem(res.message || 'Erro.', 'erro');
            }
            $btn.prop('disabled', false).text('Remover todos');
        }).fail(function () {
            mostrarVinculosMensagem('Erro de conexão.', 'erro');
            $btn.prop('disabled', false).text('Remover todos');
        });
    });
})(jQuery);
