<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<?php
$agendamento = $agendamento ?? [];
$empresas = $empresas ?? [];
$unidades = $unidades ?? [];
$usuarios = $usuarios ?? [];
$veiculos = $veiculos ?? [];

$id = (int)($agendamento['id'] ?? 0);
$statusAtual = strtoupper((string)($agendamento['status'] ?? 'AGENDADO'));

$valor = static function (string $campo, string $padrao = '') use ($agendamento): string {
    return htmlspecialchars(
        (string)($agendamento[$campo] ?? $padrao),
        ENT_QUOTES,
        'UTF-8'
    );
};

$selecionado = static function (string $campo, mixed $valorOpcao) use ($agendamento): string {
    return (string)($agendamento[$campo] ?? '') === (string)$valorOpcao
        ? 'selected'
        : '';
};
?>

<?php if (!empty($_SESSION['erro'])): ?>
    <div
        class="alert alert-danger alert-dismissible fade show rounded-4"
        role="alert"
    >
        <i class="fa-solid fa-circle-exclamation me-2"></i>

        <?= htmlspecialchars($_SESSION['erro']) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Fechar"
        ></button>
    </div>

    <?php unset($_SESSION['erro']); ?>
<?php endif; ?>

<div class="panel">
    <form
        id="formEditarAgenda"
        action="<?= BASE_URL ?>/agenda/atualizar/<?= $id ?>"
        method="POST"
        class="row g-3 needs-validation"
        novalidate
    >
        <div class="container py-4">
            <header class="mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between">
                <div>
                    <h3
                        class="m-0 fw-bold text-dark d-flex align-items-center gap-3"
                        style="font-size: 1.5rem;"
                    >
                        <span
                            class="icon-container d-flex align-items-center justify-content-center"
                            style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #0a58ca); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.2);"
                        >
                            <i
                                class="fas fa-calendar-plus text-white"
                                style="font-size: 1.15rem;"
                            ></i>
                        </span>

                        Editar Agendamento de Visita
                    </h3>

                    <small class="text-muted d-block mt-1">
                        Atualize o responsável, o transporte e o itinerário da visita técnica
                    </small>
                </div>

                <a
                    href="<?= BASE_URL ?>/agenda"
                    class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium"
                >
                    <i class="fas fa-arrow-left me-1"></i>
                    Voltar à Lista
                </a>
            </header>

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4">

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-user-tie"></i>
                            Responsável & Transporte
                        </h6>

                        <div class="row g-3">

                            <div class="col-12 col-md-6">
                                <label
                                    for="tecnico_id"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Usuário / Responsável *
                                </label>

                                <select
                                    name="tecnico_id"
                                    id="tecnico_id"
                                    class="form-select rounded-3 border-dark-subtle"
                                    required
                                >
                                    <option
                                        value=""
                                        disabled
                                        <?= empty($agendamento['tecnico_id']) ? 'selected' : '' ?>
                                    >
                                        Selecione o usuário condutor...
                                    </option>

                                    <?php foreach ($usuarios as $user): ?>
                                        <option
                                            value="<?= (int)$user['id'] ?>"
                                            <?= $selecionado('tecnico_id', $user['id']) ?>
                                        >
                                            <?= htmlspecialchars($user['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <div class="invalid-feedback">
                                    Selecione o usuário condutor/responsável.
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label
                                    for="veiculo_id"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Veículo Coletivo / Frota
                                </label>

                                <select
                                    name="veiculo_id"
                                    id="veiculo_id"
                                    class="form-select rounded-3 border-dark-subtle"
                                >
                                    <option
                                        value=""
                                        <?= empty($agendamento['veiculo_id']) ? 'selected' : '' ?>
                                    >
                                        Nenhum veículo alocado (Meios próprios / UBER)
                                    </option>

                                    <?php foreach ($veiculos as $carro): ?>
                                        <option
                                            value="<?= (int)$carro['id'] ?>"
                                            <?= $selecionado('veiculo_id', $carro['id']) ?>
                                        >
                                            <?= htmlspecialchars($carro['modelo']) ?>

                                            <?php if (!empty($carro['placa'])): ?>
                                                — Placa:
                                                <?= htmlspecialchars($carro['placa']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-route"></i>
                            Destino & Cronograma
                        </h6>

                        <div class="row g-3">

                            <div class="col-12 col-md-6">
                                <label
                                    for="empresa_id"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Empresa Cliente *
                                </label>

                                <select
                                    name="empresa_id"
                                    id="empresa_id"
                                    class="form-select rounded-3 border-dark-subtle"
                                    required
                                >
                                    <option
                                        value=""
                                        disabled
                                        <?= empty($agendamento['empresa_id']) ? 'selected' : '' ?>
                                    >
                                        Selecione a empresa alvo...
                                    </option>

                                    <?php foreach ($empresas as $empresa): ?>
                                        <?php
                                        $nomeEmpresa = !empty($empresa['nome_fantasia'])
                                            ? $empresa['nome_fantasia']
                                            : $empresa['razao_social'];
                                        ?>

                                        <option
                                            value="<?= (int)$empresa['id'] ?>"
                                            <?= $selecionado('empresa_id', $empresa['id']) ?>
                                        >
                                            <?= htmlspecialchars($nomeEmpresa) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <div class="invalid-feedback">
                                    Por favor, escolha a empresa destino.
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label
                                    for="unidade_id"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Unidade Operacional
                                </label>

                                <select
                                    name="unidade_id"
                                    id="unidade_id"
                                    class="form-select rounded-3 border-dark-subtle"
                                >
                                    <option
                                        value=""
                                        <?= empty($agendamento['unidade_id']) ? 'selected' : '' ?>
                                    >
                                        Matriz / Unidade Principal
                                    </option>

                                    <?php foreach ($unidades as $unidade): ?>
                                        <option
                                            value="<?= (int)$unidade['id'] ?>"
                                            data-empresa-id="<?= (int)$unidade['empresa_id'] ?>"
                                            <?= $selecionado('unidade_id', $unidade['id']) ?>
                                        >
                                            <?= htmlspecialchars($unidade['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label
                                    for="data_agendada"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Data do Agendamento *
                                </label>

                                <input
                                    type="date"
                                    name="data_agendada"
                                    id="data_agendada"
                                    class="form-control rounded-3 border-dark-subtle"
                                    value="<?= $valor('data_agendada') ?>"
                                    required
                                >

                                <div class="invalid-feedback">
                                    Defina uma data válida.
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <label
                                    for="hora_inicio"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Início *
                                </label>

                                <input
                                    type="time"
                                    name="hora_inicio"
                                    id="hora_inicio"
                                    class="form-control rounded-3 border-dark-subtle"
                                    value="<?= !empty($agendamento['hora_inicio']) ? htmlspecialchars(substr((string)$agendamento['hora_inicio'], 0, 5)) : '' ?>"
                                    required
                                >

                                <div class="invalid-feedback">
                                    Informe o horário inicial.
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <label
                                    for="hora_fim"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Fim *
                                </label>

                                <input
                                    type="time"
                                    name="hora_fim"
                                    id="hora_fim"
                                    class="form-control rounded-3 border-dark-subtle"
                                    value="<?= !empty($agendamento['hora_fim']) ? htmlspecialchars(substr((string)$agendamento['hora_fim'], 0, 5)) : '' ?>"
                                    required
                                >

                                <div class="invalid-feedback">
                                    Informe o horário final.
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label
                                    for="responsavel_acompanhamento"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Responsável no Local
                                </label>

                                <input
                                    type="text"
                                    name="responsavel_acompanhamento"
                                    id="responsavel_acompanhamento"
                                    class="form-control rounded-3 border-dark-subtle"
                                    maxlength="150"
                                    value="<?= $valor('responsavel_acompanhamento') ?>"
                                    placeholder="Ex: Gerente de RH, Engenheiro"
                                >
                            </div>

                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-clipboard-list"></i>
                            Detalhes da Visita & Frota
                        </h6>

                        <div class="row g-3">

                            <div class="col-12 col-md-6">
                                <label
                                    for="objetivo"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Objetivo da Visita
                                </label>

                                <textarea
                                    name="objetivo"
                                    id="objetivo"
                                    rows="3"
                                    class="form-control rounded-3 border-dark-subtle"
                                    placeholder="Descreva a finalidade da agenda (Ex: Renovação de LTCAT, Auditoria)..."
                                    style="resize: none;"
                                ><?= $valor('objetivo') ?></textarea>
                            </div>

                            <div class="col-12 col-md-6">
                                <label
                                    for="observacoes"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Observações Gerais / Frota
                                </label>

                                <textarea
                                    name="observacoes"
                                    id="observacoes"
                                    rows="3"
                                    class="form-control rounded-3 border-dark-subtle"
                                    placeholder="Observações sobre quilometragem, retirada de chaves do veículo, etc..."
                                    style="resize: none;"
                                ><?= $valor('observacoes') ?></textarea>
                            </div>

                            <div class="col-12 col-md-6">
                                <label
                                    for="titulo"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Título do Agendamento
                                </label>

                                <input
                                    type="text"
                                    name="titulo"
                                    id="titulo"
                                    class="form-control rounded-3 border-dark-subtle"
                                    maxlength="150"
                                    value="<?= $valor('titulo') ?>"
                                    placeholder="Ex: Levantamento de riscos ocupacionais"
                                >
                            </div>

                            <div class="col-12 col-md-6">
                                <label
                                    for="prioridade"
                                    class="form-label fw-semibold text-secondary small"
                                >
                                    Prioridade
                                </label>

                                <select
                                    name="prioridade"
                                    id="prioridade"
                                    class="form-select rounded-3 border-dark-subtle"
                                >
                                    <option value="PADRAO" <?= $selecionado('prioridade', 'PADRAO') ?>>Padrão</option>
                                    <option value="URGENTE" <?= $selecionado('prioridade', 'URGENTE') ?>>Urgente</option>
                                    <option value="CRITICA" <?= $selecionado('prioridade', 'CRITICA') ?>>Crítica</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="status"
                        value="<?= htmlspecialchars($statusAtual, ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <div class="d-flex justify-content-between align-items-center gap-3 border-top pt-3 flex-wrap">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button
                                type="button"
                                class="btn btn-outline-warning rounded-pill px-4 fw-medium"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCancelar"
                            >
                                <i class="fa-solid fa-ban me-1"></i>
                                Cancelar Agenda
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-danger rounded-pill px-4 fw-medium"
                                data-bs-toggle="modal"
                                data-bs-target="#modalExcluir"
                            >
                                <i class="fa-regular fa-trash-can me-1"></i>
                                Excluir
                            </button>

                            <?php if ($statusAtual !== 'CONCLUIDO'): ?>
                                <button
                                    type="submit"
                                    form="formConcluirAgenda"
                                    class="btn btn-outline-success rounded-pill px-4 fw-medium"
                                    onclick="return confirm('Confirma a conclusão deste agendamento?');"
                                >
                                    <i class="fa-solid fa-circle-check me-1"></i>
                                    Concluir
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a
                                href="<?= BASE_URL ?>/agenda"
                                class="btn btn-outline-secondary rounded-pill px-4 fw-medium"
                            >
                                Voltar
                            </a>

                            <button
                                type="submit"
                                class="btn btn-success rounded-pill px-4 fw-medium shadow-sm"
                            >
                                <i class="fas fa-check me-1"></i>
                                Atualizar Agendamento
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<form
    id="formConcluirAgenda"
    action="<?= BASE_URL ?>/agenda/concluir/<?= $id ?>"
    method="POST"
    class="d-none"
></form>

<div
    class="modal fade"
    id="modalCancelar"
    tabindex="-1"
    aria-labelledby="modalCancelarTitulo"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <form
            class="modal-content border-0 shadow"
            method="POST"
            action="<?= BASE_URL ?>/agenda/cancelar/<?= $id ?>"
        >
            <div class="modal-header">
                <h5 class="modal-title" id="modalCancelarTitulo">
                    Cancelar agendamento
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>
            </div>

            <div class="modal-body">
                <label
                    for="motivo_cancelamento"
                    class="form-label fw-semibold"
                >
                    Motivo do cancelamento *
                </label>

                <textarea
                    name="motivo"
                    id="motivo_cancelamento"
                    class="form-control rounded-3"
                    rows="3"
                    required
                    placeholder="Informe o motivo do cancelamento"
                ></textarea>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-light rounded-pill px-4"
                    data-bs-dismiss="modal"
                >
                    Voltar
                </button>

                <button
                    type="submit"
                    class="btn btn-warning rounded-pill px-4"
                >
                    Confirmar cancelamento
                </button>
            </div>
        </form>
    </div>
</div>

<div
    class="modal fade"
    id="modalExcluir"
    tabindex="-1"
    aria-labelledby="modalExcluirTitulo"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <form
            class="modal-content border-0 shadow"
            method="POST"
            action="<?= BASE_URL ?>/agenda/excluir/<?= $id ?>"
        >
            <div class="modal-header">
                <h5 class="modal-title" id="modalExcluirTitulo">
                    Excluir agendamento
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>
            </div>

            <div class="modal-body">
                <label
                    for="motivo_exclusao"
                    class="form-label fw-semibold"
                >
                    Motivo da exclusão *
                </label>

                <textarea
                    name="motivo"
                    id="motivo_exclusao"
                    class="form-control rounded-3"
                    rows="3"
                    required
                    placeholder="Informe o motivo da exclusão"
                ></textarea>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-light rounded-pill px-4"
                    data-bs-dismiss="modal"
                >
                    Voltar
                </button>

                <button
                    type="submit"
                    class="btn btn-danger rounded-pill px-4"
                >
                    Confirmar exclusão
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formEditarAgenda');
    const horaInicio = document.getElementById('hora_inicio');
    const horaFim = document.getElementById('hora_fim');
    const empresa = document.getElementById('empresa_id');
    const unidade = document.getElementById('unidade_id');

    if (form) {
        form.addEventListener('submit', function (event) {
            if (
                horaInicio &&
                horaFim &&
                horaInicio.value &&
                horaFim.value &&
                horaInicio.value >= horaFim.value
            ) {
                horaFim.setCustomValidity(
                    'O horário final deve ser maior que o inicial.'
                );
            } else if (horaFim) {
                horaFim.setCustomValidity('');
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    }

    if (horaInicio && horaFim) {
        horaInicio.addEventListener('change', function () {
            horaFim.setCustomValidity('');
        });

        horaFim.addEventListener('change', function () {
            horaFim.setCustomValidity('');
        });
    }

    if (empresa && unidade) {
        const opcoes = Array.from(
            unidade.querySelectorAll('option[data-empresa-id]')
        );

        function filtrarUnidades(limparSelecao) {
            const empresaId = empresa.value;

            if (limparSelecao) {
                unidade.value = '';
            }

            opcoes.forEach(function (option) {
                option.hidden =
                    empresaId !== '' &&
                    option.dataset.empresaId !== empresaId;
            });

            const selecionada = unidade.options[unidade.selectedIndex];

            if (
                selecionada &&
                selecionada.dataset.empresaId &&
                selecionada.dataset.empresaId !== empresaId
            ) {
                unidade.value = '';
            }
        }

        empresa.addEventListener('change', function () {
            filtrarUnidades(true);
        });

        filtrarUnidades(false);
    }
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>