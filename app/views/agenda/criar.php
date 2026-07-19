<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

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
    <form action="<?= BASE_URL ?>/agenda/salvar" method="POST" class="row g-3">
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

                        Novo Agendamento de Visita
                    </h3>

                    <small class="text-muted d-block mt-1">
                        Aloque um veículo da frota e defina o itinerário da visita técnica
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
                                    <option value="" disabled selected>
                                        Selecione o usuário condutor...
                                    </option>

                                    <?php foreach ($usuarios as $user): ?>
                                        <option value="<?= (int)$user['id'] ?>">
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
                                    <option value="">
                                        Nenhum veículo alocado (Meios próprios / UBER)
                                    </option>

                                    <?php foreach ($veiculos as $carro): ?>
                                        <option value="<?= (int)$carro['id'] ?>">
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
                                    <option value="" disabled selected>
                                        Selecione a empresa alvo...
                                    </option>

                                    <?php foreach ($empresas as $empresa): ?>
                                        <?php
                                        $nomeEmpresa = !empty($empresa['nome_fantasia'])
                                            ? $empresa['nome_fantasia']
                                            : $empresa['razao_social'];
                                        ?>

                                        <option value="<?= (int)$empresa['id'] ?>">
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
                                    <option value="">
                                        Matriz / Unidade Principal
                                    </option>

                                    <?php foreach ($unidades as $unidade): ?>
                                        <option
                                            value="<?= (int)$unidade['id'] ?>"
                                            data-empresa-id="<?= (int)$unidade['empresa_id'] ?>"
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
                                    min="<?= date('Y-m-d') ?>"
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
                                ></textarea>
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
                                ></textarea>
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
                                    <option value="PADRAO">Padrão</option>
                                    <option value="URGENTE">Urgente</option>
                                    <option value="CRITICA">Crítica</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="status"
                        value="AGENDADO"
                    >

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a
                            href="<?= BASE_URL ?>/agenda"
                            class="btn btn-outline-danger rounded-pill px-4 fw-medium"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn btn-success rounded-pill px-4 fw-medium shadow-sm"
                        >
                            <i class="fas fa-check me-1"></i>
                            Cadastrar Agendamento
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('.needs-validation');
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

                empresa.addEventListener('change', function () {
                    const empresaId = this.value;

                    unidade.value = '';

                    opcoes.forEach(function (option) {
                        option.hidden =
                            option.dataset.empresaId !== empresaId;
                    });
                });
            }
        });
        </script>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
