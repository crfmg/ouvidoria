<?= $this->extend('fixo/layout') ?>

<?= $this->section('titulo') ?>
Nova Manifestação - Ouvidoria
<?= $this->endSection() ?>

<?= $this->section('css') ?>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
@media (min-width: 992px) {
    .linha-identificacoes > .campo-prazo {
        flex: 0 0 6.6667%;
        max-width: 6.6667%;
    }
    .linha-identificacoes > .campo-prazo .form-label {
        font-size: .75rem;
        white-space: nowrap;
    }
    .linha-identificacoes > .campo-identificacao {
        flex: 0 0 46.66665%;
        max-width: 46.66665%;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-plus-circle me-2"></i>Nova Manifestação</h1>
    <a href="<?= url_to('ouvidoria.manifestacoes.index') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
</div>

<?= form_open_multipart(url_to('ouvidoria.manifestacoes.store'), ['class' => 'needs-validation']) ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="mb-0">Dados da manifestação</h5></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4 d-none">
                <label class="form-label">Protocolo <small class="text-muted">(interno)</small></label>
                <input type="text" name="protocolo" class="form-control" value="<?= esc(old('protocolo')) ?>" placeholder="OUV-2025-000001">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Protocolo Fala.BR</label>
                <input type="text" name="protocolo_falabr" class="form-control" value="<?= esc(old('protocolo_falabr')) ?>" placeholder="Protocolo gerado pelo Fala.BR">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Origem</label>
                <input type="text" name="origem" class="form-control" value="<?= esc(old('origem', 'Fala.BR')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Data da manifestação <span class="text-danger">*</span></label>
                <input type="date" name="data_manifestacao" class="form-control" value="<?= esc(old('data_manifestacao', date('Y-m-d'))) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label">Assunto <span class="text-danger">*</span></label>
                <textarea name="assunto" id="assunto" class="form-control" rows="2" required><?= esc(old('assunto')) ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Descrição <span class="text-danger">*</span></label>
                <div id="editor-descricao" style="height: 200px;"></div>
                <input type="hidden" name="descricao" id="descricao" required>
            </div>
            <div class="col-12 d-none">
                <label class="form-label">Dados de identificação <small class="text-muted">(JSON ou texto livre)</small></label>
                <textarea name="dados_identificacao" class="form-control" rows="2" placeholder='{"nome":"...","contato":"..."}'><?= esc(old('dados_identificacao')) ?></textarea>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">Categorias</label>
                <select name="categorias_ids[]" id="selectCategorias" class="form-select select2-multiple" multiple placeholder="Selecione uma ou mais categorias">
                    <?php if (!empty($categorias)): foreach ($categorias as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>"><?= esc($cat['nome']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
                <small class="text-muted">Opcional. Selecione uma ou mais categorias.</small>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Prioridade</label>
                <select name="prioridade" class="form-select">
                    <option value="baixa" <?= old('prioridade') === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                    <option value="media" <?= old('prioridade') === 'media' || !old('prioridade') ? 'selected' : '' ?>>Média</option>
                    <option value="alta" <?= old('prioridade') === 'alta' ? 'selected' : '' ?>>Alta</option>
                </select>
            </div>
            <?php
            $inscricoesPj = old('inscricoes_pj');
            $inscricoesPf = old('inscricoes_pf');
            $inscricoesPj = is_array($inscricoesPj) && $inscricoesPj !== [] ? $inscricoesPj : [''];
            $inscricoesPf = is_array($inscricoesPf) && $inscricoesPf !== [] ? $inscricoesPf : [''];
            ?>
            <div class="col-12">
            <div class="row g-3 linha-identificacoes">
            <div class="col-12 campo-prazo">
                <label class="form-label">Prazo (dias)</label>
                <input type="number" name="sla_prazo_em_dias" class="form-control" value="<?= esc(old('sla_prazo_em_dias', 30)) ?>" min="1" aria-label="Prazo em dias">
            </div>
            <div class="col-12 campo-identificacao">
                <label class="form-label">Registros de Estabelecimento (PJ)</label>
                <div id="listaInscricoesPj" class="d-grid gap-2">
                    <?php foreach ($inscricoesPj as $inscricao): ?>
                    <div class="item-inscricao">
                        <div class="input-group">
                            <input type="text" inputmode="numeric" pattern="[0-9]+" maxlength="30" name="inscricoes_pj[]" class="form-control inscricao-lookup" data-tipo="PJ" value="<?= esc($inscricao) ?>" placeholder="Número do estabelecimento">
                            <button type="button" class="btn btn-outline-secondary btn-buscar-inscricao" title="Buscar estabelecimento"><i class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-remover-inscricao" title="Remover"><i class="fas fa-trash"></i></button>
                        </div>
                        <small class="resultado-inscricao d-block mt-1"></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2 btn-adicionar-inscricao" data-lista="listaInscricoesPj" data-nome="inscricoes_pj[]" data-tipo="PJ" data-placeholder="Número do estabelecimento">
                    <i class="fas fa-plus me-1"></i>Adicionar estabelecimento
                </button>
                <small class="text-muted d-block mt-1">Opcional. Adicione todos os estabelecimentos relacionados.</small>
            </div>
            <div class="col-12 campo-identificacao">
                <label class="form-label">Inscrições de Pessoa Física (PF)</label>
                <div id="listaInscricoesPf" class="d-grid gap-2">
                    <?php foreach ($inscricoesPf as $inscricao): ?>
                    <div class="item-inscricao">
                        <div class="input-group">
                            <input type="text" inputmode="numeric" pattern="[0-9]+" maxlength="30" name="inscricoes_pf[]" class="form-control inscricao-lookup" data-tipo="PF" value="<?= esc($inscricao) ?>" placeholder="Número da inscrição PF">
                            <button type="button" class="btn btn-outline-secondary btn-buscar-inscricao" title="Buscar pessoa física"><i class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-remover-inscricao" title="Remover"><i class="fas fa-trash"></i></button>
                        </div>
                        <small class="resultado-inscricao d-block mt-1"></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2 btn-adicionar-inscricao" data-lista="listaInscricoesPf" data-nome="inscricoes_pf[]" data-tipo="PF" data-placeholder="Número da inscrição PF">
                    <i class="fas fa-plus me-1"></i>Adicionar inscrição PF
                </button>
                <small class="text-muted d-block mt-1">Opcional. Adicione todas as pessoas físicas relacionadas.</small>
            </div>
            </div>
            </div>
            <div class="col-12">
                <label class="form-label">Anexos</label>
                <input type="file" id="anexosInput" name="anexos[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.txt">
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnAdicionarAnexo"><i class="fas fa-plus me-1"></i>Adicionar arquivos</button>
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered" id="tabelaAnexosPreview">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Tamanho</th>
                                <th width="80">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyAnexosPreview">
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Os anexos serão salvos ao clicar em Salvar.</small>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
        <a href="<?= url_to('ouvidoria.manifestacoes.index') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var anexosPendentes = [];
    var inputAnexos = document.getElementById('anexosInput');
    var urlBuscarIdentificacao = <?= json_encode(url_to('ouvidoria.manifestacoes.buscarIdentificacao'), JSON_UNESCAPED_SLASHES) ?>;

    function adicionarInscricao(lista, nome, tipo, placeholder) {
        var item = document.createElement('div');
        item.className = 'item-inscricao';
        var grupo = document.createElement('div');
        grupo.className = 'input-group';

        var input = document.createElement('input');
        input.type = 'text';
        input.inputMode = 'numeric';
        input.pattern = '[0-9]+';
        input.maxLength = 30;
        input.name = nome;
        input.className = 'form-control inscricao-lookup';
        input.dataset.tipo = tipo;
        input.placeholder = placeholder;

        var buscar = document.createElement('button');
        buscar.type = 'button';
        buscar.className = 'btn btn-outline-secondary btn-buscar-inscricao';
        buscar.title = tipo === 'PJ' ? 'Buscar estabelecimento' : 'Buscar pessoa física';
        buscar.innerHTML = '<i class="fas fa-search"></i>';

        var remover = document.createElement('button');
        remover.type = 'button';
        remover.className = 'btn btn-outline-danger btn-remover-inscricao';
        remover.title = 'Remover';
        remover.innerHTML = '<i class="fas fa-trash"></i>';

        var resultado = document.createElement('small');
        resultado.className = 'resultado-inscricao d-block mt-1';

        grupo.appendChild(input);
        grupo.appendChild(buscar);
        grupo.appendChild(remover);
        item.appendChild(grupo);
        item.appendChild(resultado);
        lista.appendChild(item);
        input.focus();
    }

    document.querySelectorAll('.btn-adicionar-inscricao').forEach(function(botao) {
        botao.addEventListener('click', function() {
            adicionarInscricao(
                document.getElementById(botao.dataset.lista),
                botao.dataset.nome,
                botao.dataset.tipo,
                botao.dataset.placeholder
            );
        });
    });

    document.addEventListener('click', function(evento) {
        var buscar = evento.target.closest('.btn-buscar-inscricao');
        if (buscar) {
            buscarInscricao(buscar.closest('.item-inscricao').querySelector('.inscricao-lookup'));
            return;
        }
        var botao = evento.target.closest('.btn-remover-inscricao');
        if (!botao) return;
        var item = botao.closest('.item-inscricao');
        var lista = item.parentElement;
        if (lista.querySelectorAll('.item-inscricao').length === 1) {
            item.querySelector('input').value = '';
            var resultado = item.querySelector('.resultado-inscricao');
            resultado.textContent = '';
            resultado.className = 'resultado-inscricao d-block mt-1';
            return;
        }
        item.remove();
    });

    function buscarInscricao(input) {
        var item = input.closest('.item-inscricao');
        var resultado = item.querySelector('.resultado-inscricao');
        var inscricao = input.value.trim();
        if (!inscricao) {
            resultado.textContent = '';
            resultado.className = 'resultado-inscricao d-block mt-1';
            return;
        }
        if (!/^\d+$/.test(inscricao)) {
            resultado.textContent = 'Informe somente números.';
            resultado.className = 'resultado-inscricao d-block mt-1 text-danger';
            return;
        }

        resultado.textContent = 'Buscando...';
        resultado.className = 'resultado-inscricao d-block mt-1 text-muted';
        fetch(urlBuscarIdentificacao + '?tipo=' + encodeURIComponent(input.dataset.tipo) + '&inscricao=' + encodeURIComponent(inscricao), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(response) {
            return response.json().then(function(data) { return { ok: response.ok, data: data }; });
        }).then(function(retorno) {
            if (input.value.trim() !== inscricao) return;
            if (!retorno.ok || !retorno.data.success) {
                throw new Error(retorno.data.message || 'Cadastro não encontrado.');
            }
            var detalhe = retorno.data.detalhe ? ' — ' + retorno.data.detalhe : '';
            resultado.textContent = retorno.data.nome + detalhe;
            resultado.className = 'resultado-inscricao d-block mt-1 text-success fw-semibold';
        }).catch(function(erro) {
            if (input.value.trim() !== inscricao) return;
            resultado.textContent = erro.message || 'Não foi possível realizar a consulta.';
            resultado.className = 'resultado-inscricao d-block mt-1 text-danger';
        });
    }

    document.addEventListener('focusout', function(evento) {
        if (evento.target.classList.contains('inscricao-lookup')) {
            buscarInscricao(evento.target);
        }
    });

    document.addEventListener('input', function(evento) {
        if (evento.target.classList.contains('inscricao-lookup')) {
            var resultado = evento.target.closest('.item-inscricao').querySelector('.resultado-inscricao');
            resultado.textContent = '';
            resultado.className = 'resultado-inscricao d-block mt-1';
        }
    });

    document.querySelectorAll('.inscricao-lookup').forEach(function(input) {
        if (input.value.trim()) buscarInscricao(input);
    });

    var quill = new Quill('#editor-descricao', {
        theme: 'snow',
        modules: { toolbar: [['bold', 'italic'], ['link'], [{ 'list': 'ordered'}, { 'list': 'bullet' }]] }
    });
    quill.on('text-change', function() {
        document.getElementById('descricao').value = quill.root.innerHTML;
    });

    function syncInputAnexos() {
        var dt = new DataTransfer();
        anexosPendentes.forEach(function(f) { dt.items.add(f); });
        inputAnexos.files = dt.files;
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024, s = ['B', 'KB', 'MB'], i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + s[i];
    }

    function renderTabelaAnexos() {
        var tbody = document.getElementById('tbodyAnexosPreview');
        tbody.innerHTML = '';
        anexosPendentes.forEach(function(f, i) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + (f.name || 'Arquivo') + '</td><td>' + (f.type || '-') + '</td><td>' + formatBytes(f.size) + '</td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remover-anexo" data-idx="' + i + '"><i class="fas fa-trash"></i></button></td>';
            tbody.appendChild(tr);
        });
        document.querySelectorAll('.btn-remover-anexo').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(btn.getAttribute('data-idx'), 10);
                anexosPendentes.splice(idx, 1);
                syncInputAnexos();
                renderTabelaAnexos();
            });
        });
    }

    inputAnexos.addEventListener('change', function() {
        var files = this.files;
        for (var i = 0; i < files.length; i++) {
            anexosPendentes.push(files[i]);
        }
        syncInputAnexos();
        renderTabelaAnexos();
        this.value = '';
    });

    document.getElementById('btnAdicionarAnexo').addEventListener('click', function() {
        inputAnexos.click();
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        document.getElementById('descricao').value = quill.root.innerHTML;
        syncInputAnexos();
    });

    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#selectCategorias').select2({ width: '100%', placeholder: 'Selecione uma ou mais categorias' });
    }
});
</script>
<?= $this->endSection() ?>
