<?php
$css = 'funcionarios.css';
require_once dirname(__DIR__) . '/templates/header.php';

$funcionarios = $funcionarios ?? [];
$indicadores = $indicadores ?? [];
$empresas = $empresas ?? [];
$unidades = $unidades ?? [];
$filtros = $filtros ?? [];
$csrfToken = $csrfToken ?? '';
?>

<div class="funcionarios-page">
    <?php if (!empty($_SESSION['sucesso'])): ?>
        <div class="alert alert-success rounded-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= htmlspecialchars($_SESSION['sucesso']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger rounded-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?= htmlspecialchars($_SESSION['erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <header class="funcionarios-header">
        <div>
            <h2>Funcionários</h2>
            <p>Gerencie os trabalhadores vinculados à hierarquia das empresas.</p>
        </div>
        <a href="<?= BASE_URL ?>/funcionarios/criar" class="btn btn-primary funcionarios-new-button">
            <i class="fa-solid fa-user-plus"></i>
            Novo funcionário
        </a>
    </header>

    <section class="funcionarios-kpis">
        <article class="funcionario-kpi-card">
            <div class="funcionario-kpi-icon funcionario-kpi-blue"><i class="fa-solid fa-users"></i></div>
            <div><span>Total</span><strong><?= (int)($indicadores['total'] ?? 0) ?></strong></div>
        </article>
        <article class="funcionario-kpi-card">
            <div class="funcionario-kpi-icon funcionario-kpi-green"><i class="fa-solid fa-user-check"></i></div>
            <div><span>Ativos</span><strong><?= (int)($indicadores['ativos'] ?? 0) ?></strong></div>
        </article>
        <article class="funcionario-kpi-card">
            <div class="funcionario-kpi-icon funcionario-kpi-red"><i class="fa-solid fa-user-slash"></i></div>
            <div><span>Inativos</span><strong><?= (int)($indicadores['inativos'] ?? 0) ?></strong></div>
        </article>
        <article class="funcionario-kpi-card">
            <div class="funcionario-kpi-icon funcionario-kpi-purple"><i class="fa-regular fa-calendar-plus"></i></div>
            <div><span>Admitidos no mês</span><strong><?= (int)($indicadores['admitidos_mes'] ?? 0) ?></strong></div>
        </article>
    </section>

    <section class="funcionarios-filter-card">
        <form method="GET" action="<?= BASE_URL ?>/funcionarios" class="funcionarios-filter-grid">
            <div class="funcionarios-filter-field funcionarios-filter-search">
                <label for="busca">Busca</label>
                <input type="search" id="busca" name="busca" class="form-control"
                       placeholder="Nome, CPF, matrícula, empresa ou cargo"
                       value="<?= htmlspecialchars((string)($filtros['busca'] ?? '')) ?>">
            </div>

            <div class="funcionarios-filter-field">
                <label for="empresa_id">Empresa</label>
                <select id="empresa_id" name="empresa_id" class="form-select">
                    <option value="">Todas as empresas</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= (int)$empresa['id'] ?>"
                            <?= (string)($filtros['empresa_id'] ?? '') === (string)$empresa['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia'] ?: $empresa['razao_social']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="funcionarios-filter-field">
                <label for="unidade_id">Unidade</label>
                <select id="unidade_id" name="unidade_id" class="form-select">
                    <option value="">Todas as unidades</option>
                    <?php foreach ($unidades as $unidade): ?>
                        <option value="<?= (int)$unidade['id'] ?>"
                                data-empresa="<?= (int)$unidade['empresa_id'] ?>"
                            <?= (string)($filtros['unidade_id'] ?? '') === (string)$unidade['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($unidade['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="funcionarios-filter-field">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="ATIVO" <?= ($filtros['status'] ?? '') === 'ATIVO' ? 'selected' : '' ?>>Ativos</option>
                    <option value="INATIVO" <?= ($filtros['status'] ?? '') === 'INATIVO' ? 'selected' : '' ?>>Inativos</option>
                </select>
            </div>

            <div class="funcionarios-filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Filtrar
                </button>
                <a href="<?= BASE_URL ?>/funcionarios" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Limpar
                </a>
            </div>
        </form>
    </section>

    <?php if (!empty($funcionarios)): ?>
        <section class="funcionarios-grid">
            <?php foreach ($funcionarios as $funcionario): ?>
                <?php $ativo = (int)($funcionario['ativo'] ?? 0) === 1; ?>
                <article class="funcionario-card <?= $ativo ? '' : 'funcionario-card-inativo' ?>">
                    <header class="funcionario-card-header">
                        <div class="funcionario-avatar">
                            <?= htmlspecialchars(strtoupper(substr((string)$funcionario['nome'], 0, 1))) ?>
                        </div>
                        <div class="funcionario-title">
                            <h3><?= htmlspecialchars($funcionario['nome']) ?></h3>
                            <p><?= htmlspecialchars($funcionario['cargo_nome'] ?? 'Cargo não informado') ?></p>
                        </div>
                        <span class="funcionario-status <?= $ativo ? 'status-ativo' : 'status-inativo' ?>">
                            <?= $ativo ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </header>

                    <div class="funcionario-card-body">
                        <div class="funcionario-info">
                            <i class="fa-regular fa-building"></i>
                            <div><span>Empresa</span><strong><?= htmlspecialchars($funcionario['empresa_nome'] ?? '-') ?></strong></div>
                        </div>
                        <div class="funcionario-info">
                            <i class="fa-solid fa-industry"></i>
                            <div><span>Unidade</span><strong><?= htmlspecialchars($funcionario['unidade_nome'] ?? '-') ?></strong></div>
                        </div>
                        <div class="funcionario-info">
                            <i class="fa-solid fa-layer-group"></i>
                            <div><span>Setor</span><strong><?= htmlspecialchars($funcionario['setor_nome'] ?? '-') ?></strong></div>
                        </div>
                        <div class="funcionario-info">
                            <i class="fa-regular fa-id-card"></i>
                            <div><span>Matrícula / CPF</span><strong><?= htmlspecialchars($funcionario['matricula'] ?: ($funcionario['cpf'] ?: '-')) ?></strong></div>
                        </div>
                        <div class="funcionario-info">
                            <i class="fa-regular fa-calendar-check"></i>
                            <div><span>Admissão</span><strong><?= !empty($funcionario['data_admissao']) ? date('d/m/Y', strtotime($funcionario['data_admissao'])) : '-' ?></strong></div>
                        </div>
                        <?php if (!$ativo): ?>
                            <div class="funcionario-info funcionario-info-wide">
                                <i class="fa-solid fa-circle-info"></i>
                                <div><span>Motivo da inativação</span><strong><?= htmlspecialchars($funcionario['motivo_inativacao'] ?? '-') ?></strong></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <footer class="funcionario-card-actions">
                        <a href="<?= BASE_URL ?>/funcionarios/editar/<?= (int)$funcionario['id'] ?>" class="btn btn-outline-primary">
                            <i class="fa-regular fa-pen-to-square"></i> Editar
                        </a>

                        <?php if ($ativo): ?>
                            <button type="button" class="btn btn-outline-danger btn-inativar-funcionario"
                                    data-bs-toggle="modal" data-bs-target="#modalInativarFuncionario"
                                    data-id="<?= (int)$funcionario['id'] ?>"
                                    data-nome="<?= htmlspecialchars($funcionario['nome'], ENT_QUOTES) ?>">
                                <i class="fa-solid fa-user-slash"></i> Inativar
                            </button>
                        <?php else: ?>
                            <form method="POST" action="<?= BASE_URL ?>/funcionarios/reativar/<?= (int)$funcionario['id'] ?>">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="btn btn-outline-success">
                                    <i class="fa-solid fa-user-check"></i> Reativar
                                </button>
                            </form>
                        <?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <section class="funcionarios-empty-state">
            <i class="fa-solid fa-users-slash"></i>
            <h3>Nenhum funcionário encontrado</h3>
            <p>Não existem funcionários correspondentes aos filtros selecionados.</p>
            <a href="<?= BASE_URL ?>/funcionarios/criar" class="btn btn-primary">
                <i class="fa-solid fa-user-plus"></i> Cadastrar funcionário
            </a>
        </section>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalInativarFuncionario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="formInativarFuncionario">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-user-slash me-2 text-danger"></i>Inativar funcionário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Informe o motivo da inativação de <strong id="nomeFuncionarioInativar"></strong>.</p>
                <div class="mb-3">
                    <label for="motivo_inativacao" class="form-label">Motivo *</label>
                    <textarea id="motivo_inativacao" name="motivo" class="form-control" rows="3" required></textarea>
                </div>
                <div>
                    <label for="data_desligamento" class="form-label">Data do desligamento</label>
                    <input type="date" id="data_desligamento" name="data_desligamento" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Confirmar inativação</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const empresa = document.getElementById('empresa_id');
    const unidade = document.getElementById('unidade_id');

    function filtrarUnidades() {
        const empresaId = empresa.value;
        Array.from(unidade.options).forEach(function (option) {
            if (!option.value) return;
            option.hidden = empresaId !== '' && option.dataset.empresa !== empresaId;
            if (option.hidden && option.selected) unidade.value = '';
        });
    }

    empresa.addEventListener('change', filtrarUnidades);
    filtrarUnidades();

    document.querySelectorAll('.btn-inativar-funcionario').forEach(function (botao) {
        botao.addEventListener('click', function () {
            document.getElementById('formInativarFuncionario').action =
                '<?= BASE_URL ?>/funcionarios/inativar/' + botao.dataset.id;
            document.getElementById('nomeFuncionarioInativar').textContent = botao.dataset.nome;
        });
    });
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
