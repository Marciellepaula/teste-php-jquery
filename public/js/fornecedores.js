(function ($) {
    'use strict';

    var $mensagem = $('#mensagem');
    var $tbody = $('#tabela-fornecedores tbody');
    var $tabelaVazia = $('#tabela-vazia');
    var $modal = $('#modal-fornecedor');
    var $form = $('#form-fornecedor');
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
        $('#fornecedor-id').val('');
        $modal.attr('aria-hidden', 'false').addClass('aberto');
    }

    function fecharModal() {
        $modal.attr('aria-hidden', 'true').removeClass('aberto');
    }

    function removerInvalidos() {
        $form.find('input, select').removeClass('invalido');
    }

    function listarFornecedores() {
        $.ajax({
            url: window.API_FORNECEDOR.lista,
            method: 'GET',
            dataType: 'json'
        }).done(function (res) {
            if (!res.success || !res.data) return;
            var rows = res.data.map(function (f) {
                var statusBadge = f.status === 'A' ? '<span class="badge badge-ativo">Ativo</span>' : '<span class="badge badge-inativo">Inativo</span>';
                return '<tr data-id="' + f.id + '">' +
                    '<td>' + f.id + '</td>' +
                    '<td>' + escapeHtml(f.nome) + '</td>' +
                    '<td>' + escapeHtml(f.cnpj || '') + '</td>' +
                    '<td>' + escapeHtml(f.email || '') + '</td>' +
                    '<td>' + escapeHtml(f.telefone || '') + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td>' +
                    '<button type="button" class="btn btn-small btn-editar" data-id="' + f.id + '">Editar</button> ' +
                    '<button type="button" class="btn btn-small btn-excluir" data-id="' + f.id + '">Excluir</button>' +
                    '</td></tr>';
            });
            $tbody.html(rows.join(''));
            $tabelaVazia.toggle(rows.length === 0);
        }).fail(function () {
            mostrarMensagem('Erro ao carregar a lista de fornecedores.', 'erro');
        });
    }

    function escapeHtml(s) {
        if (s == null) return '';
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    $('#btn-novo-fornecedor').on('click', function () {
        abrirModal('Novo fornecedor');
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
            url: window.API_FORNECEDOR.buscar,
            method: 'GET',
            data: { id: id },
            dataType: 'json'
        }).done(function (res) {
            if (!res.success || !res.data) {
                mostrarMensagem('Fornecedor não encontrado.', 'erro');
                return;
            }
            var d = res.data;
            $('#fornecedor-id').val(d.id);
            $('#nome').val(d.nome);
            $('#cnpj').val(d.cnpj || '');
            $('#email').val(d.email || '');
            $('#telefone').val(d.telefone || '');
            $('#status').val(d.status || 'A');
            $modalTitulo.text('Editar fornecedor');
            $modal.attr('aria-hidden', 'false').addClass('aberto');
        }).fail(function () {
            mostrarMensagem('Erro ao carregar fornecedor.', 'erro');
        });
    });

    $(document).on('click', '.btn-excluir', function () {
        var id = $(this).data('id');
        if (!window.confirm('Deseja realmente excluir este fornecedor?')) return;
        $.ajax({
            url: window.API_FORNECEDOR.excluir,
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
        var id = $('#fornecedor-id').val();
        var url = id ? window.API_FORNECEDOR.atualizar : window.API_FORNECEDOR.salvar;
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
                listarFornecedores();
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
})(jQuery);
