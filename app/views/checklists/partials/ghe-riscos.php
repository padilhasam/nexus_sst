<?php
$esc = static fn (mixed $valor): string => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
$ghesLista = $ghes ?? [];
$riscosLista = $riscos ?? [];
$categorias = [
    'fisico' => 'Físico',
    'quimico' => 'Químico',
    'biologico' => 'Biológico',
    'ergonomico' => 'Ergonômico',
    'acidente' => 'Acidente',
    'psicossocial' => 'Psicossocial',
];
$frequencias = [
    'EVENTUAL' => 'Eventual',
    'ESPORADICA' => 'Esporádica',
    'INTERMITENTE' => 'Intermitente',
    'HABITUAL' => 'Habitual',
    'PERMANENTE' => 'Permanente',
];
$tempos = [
    'MUITO_BAIXO' => 'Muito baixo',
    'BAIXO' => 'Baixo',
    'MODERADO' => 'Moderado',
    'ALTO' => 'Alto',
    'MUITO_ALTO' => 'Muito alto',
];
$riscosPorCategoria = [];
foreach ($riscosLista as $riscoDisponivel) {
    $categoria = strtolower((string)($riscoDisponivel['categoria'] ?? 'outros'));
    $riscosPorCategoria[$categoria][] = $riscoDisponivel;
}
?>

<section class="checklist-workspace">
    <header class="checklist-workspace-header">
        <div>
            <span class="checklist-eyebrow">Caracterização da exposição</span>
            <h2>Grupos Homogêneos de Exposição e riscos</h2>
            <p>
                Monte cada GHE em três etapas: crie o grupo, aplique os riscos cadastrados e, somente depois,
                vincule os cargos da estrutura que possuem exposição semelhante.
            </p>
        </div>
        <span class="checklist-count"><strong><?= count($ghesLista) ?></strong> GHEs</span>
    </header>

    <div class="ghe-flow-banner">
        <div class="ghe-flow-step"><span>1</span><div><strong>Criar GHE</strong><small>Identificar o grupo</small></div></div>
        <i class="fa-solid fa-chevron-right"></i>
        <div class="ghe-flow-step"><span>2</span><div><strong>Adicionar riscos</strong><small>Usar o catálogo configurado</small></div></div>
        <i class="fa-solid fa-chevron-right"></i>
        <div class="ghe-flow-step"><span>3</span><div><strong>Vincular cargos</strong><small>Aplicar à hierarquia</small></div></div>
    </div>

    <?php if (!$somenteLeitura): ?>
        <article class="checklist-form-card">
            <div class="checklist-form-heading">
                <span class="checklist-form-icon"><i class="fa-solid fa-flask-vial"></i></span>
                <div>
                    <h3>1. Criar novo GHE</h3>
                    <p>Nesta etapa informe somente a identificação do grupo. Riscos e cargos serão adicionados depois.</p>
                </div>
            </div>

            <?php if (empty($hierarquia)): ?>
                <div class="checklist-empty is-compact">
                    <i class="fa-solid fa-sitemap"></i>
                    <h3>Hierarquia oficial necessária</h3>
                    <p>Monte a estrutura da unidade antes de iniciar os GHEs do levantamento.</p>
                    <a class="btn btn-primary" href="<?= BASE_URL ?>/checklists/visualizar/<?= $checklistId ?>?aba=hierarquia">
                        <i class="fa-solid fa-arrow-right"></i> Ver hierarquia
                    </a>
                </div>
            <?php else: ?>
                <form
                    method="POST"
                    action="<?= BASE_URL ?>/checklists/<?= $checklistId ?>/ghe/salvar"
                    class="checklist-form-grid columns-3"
                    data-prevent-double-submit
                >
                    <input type="hidden" name="_token" value="<?= $esc($csrfToken ?? '') ?>">

                    <div class="checklist-field">
                        <label for="ghe_codigo">Código do GHE *</label>
                        <input class="form-control" id="ghe_codigo" name="codigo" maxlength="40" required placeholder="GHE-01">
                    </div>
                    <div class="checklist-field span-2">
                        <label for="ghe_nome">Nome do grupo *</label>
                        <input class="form-control" id="ghe_nome" name="nome" maxlength="180" required placeholder="Ex.: Manutenção mecânica">
                    </div>
                    <div class="checklist-field span-2">
                        <label for="ghe_descricao">Descrição das atividades e exposição</label>
                        <textarea class="form-control" id="ghe_descricao" name="descricao" rows="2"></textarea>
                    </div>
                    <div class="checklist-field">
                        <label for="ghe_observacoes">Observações</label>
                        <textarea class="form-control" id="ghe_observacoes" name="observacoes" rows="2"></textarea>
                    </div>

                    <div class="checklist-form-action span-3">
                        <button class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-plus"></i> Criar GHE e adicionar riscos
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </article>
    <?php endif; ?>

    <div class="ghe-list">
        <?php if (empty($ghesLista)): ?>
            <div class="checklist-empty">
                <i class="fa-solid fa-flask"></i>
                <h3>Nenhum GHE criado</h3>
                <p>Crie o primeiro grupo, aplique seus riscos e finalize vinculando os cargos.</p>
            </div>
        <?php else: ?>
            <?php foreach ($ghesLista as $ghe): ?>
                <?php
                $riscosGhe = $ghe['riscos'] ?? [];
                $cargosGhe = $ghe['cargos'] ?? [];
                $idsCargosGhe = array_map('intval', array_column($cargosGhe, 'hierarquia_id'));
                $temRiscos = count($riscosGhe) > 0;
                $temCargos = count($cargosGhe) > 0;
                ?>
                <article class="ghe-work-card">
                    <header class="ghe-work-header">
                        <span class="ghe-code"><?= $esc($ghe['codigo'] ?? '-') ?></span>
                        <div class="ghe-title">
                            <h3><?= $esc($ghe['nome'] ?? 'GHE') ?></h3>
                            <p><?= $esc($ghe['descricao'] ?? 'Descrição não informada.') ?></p>
                        </div>
                        <div class="ghe-header-actions">
                            <div class="ghe-stats">
                                <strong><?= count($riscosGhe) ?></strong><span>riscos</span>
                                <strong><?= count($cargosGhe) ?></strong><span>cargos</span>
                            </div>
                            <?php if (!$somenteLeitura): ?>
                                <form
                                    method="POST"
                                    action="<?= BASE_URL ?>/checklists/<?= $checklistId ?>/ghe/<?= (int)$ghe['id'] ?>/inativar"
                                    data-confirm-message="Confirma a inativação deste GHE? Os vínculos e riscos permanecerão no histórico."
                                    data-prevent-double-submit
                                >
                                    <input type="hidden" name="_token" value="<?= $esc($csrfToken ?? '') ?>">
                                    <button class="btn btn-outline-danger btn-sm" type="submit" title="Inativar GHE"><i class="fa-solid fa-box-archive"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </header>

                    <div class="ghe-card-progress">
                        <span class="is-done"><i class="fa-solid fa-check"></i> GHE criado</span>
                        <span class="<?= $temRiscos ? 'is-done' : 'is-pending' ?>"><i class="fa-solid <?= $temRiscos ? 'fa-check' : 'fa-clock' ?>"></i> Riscos</span>
                        <span class="<?= $temCargos ? 'is-done' : 'is-pending' ?>"><i class="fa-solid <?= $temCargos ? 'fa-check' : 'fa-clock' ?>"></i> Cargos</span>
                    </div>

                    <section class="ghe-step-section">
                        <header class="ghe-step-header">
                            <span>2</span>
                            <div><strong>Riscos que compõem o GHE</strong><small>Selecione apenas riscos já cadastrados e configurados no catálogo.</small></div>
                            <a href="<?= BASE_URL ?>/riscos" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-gear"></i> Catálogo de riscos</a>
                        </header>

                        <?php if (empty($riscosGhe)): ?>
                            <div class="ghe-no-risk"><i class="fa-solid fa-circle-info"></i> Nenhum risco aplicado a este GHE.</div>
                        <?php else: ?>
                            <div class="ghe-risk-list">
                                <?php foreach ($riscosGhe as $riscoGhe): ?>
                                    <?php
                                    $categoria = strtolower((string)($riscoGhe['categoria'] ?? ''));
                                    $frequencia = strtoupper((string)($riscoGhe['frequencia'] ?? ''));
                                    $tempo = strtoupper((string)($riscoGhe['tempo_exposicao'] ?? ''));
                                    ?>
                                    <div class="ghe-risk-row">
                                        <span class="risk-category category-<?= $esc($categoria) ?>"><?= $esc($categorias[$categoria] ?? ucfirst($categoria)) ?></span>
                                        <div>
                                            <strong><?= $esc($riscoGhe['risco_nome'] ?? '-') ?></strong>
                                            <small>
                                                <?= !empty($riscoGhe['fonte_geradora']) ? 'Fonte: ' . $esc($riscoGhe['fonte_geradora']) : 'Fonte não informada' ?>
                                                <?= !empty($riscoGhe['meio_propagacao']) ? ' · Meio: ' . $esc($riscoGhe['meio_propagacao']) : '' ?>
                                            </small>
                                        </div>
                                        <div class="risk-exposure">
                                            <strong><?= $esc($frequencias[$frequencia] ?? 'Frequência não informada') ?></strong>
                                            <small>
                                                <?= $esc($tempos[$tempo] ?? 'Tempo não informado') ?>
                                                <?= !empty($riscoGhe['intensidade']) ? ' · ' . $esc($riscoGhe['intensidade']) . ' ' . $esc($riscoGhe['unidade_medida'] ?? '') : '' ?>
                                            </small>
                                        </div>
                                        <div class="risk-row-actions">
                                            <?php if ((int)($riscoGhe['exige_quantificacao'] ?? 0) === 1): ?>
                                                <span class="risk-quantifiable"><i class="fa-solid fa-chart-column"></i> Quantificar</span>
                                            <?php endif; ?>
                                            <?php if (!$somenteLeitura): ?>
                                                <form
                                                    method="POST"
                                                    action="<?= BASE_URL ?>/checklists/<?= $checklistId ?>/ghe/<?= (int)$ghe['id'] ?>/riscos/remover/<?= (int)$riscoGhe['id'] ?>"
                                                    data-confirm-message="Remover este risco do GHE?"
                                                    data-prevent-double-submit
                                                >
                                                    <input type="hidden" name="_token" value="<?= $esc($csrfToken ?? '') ?>">
                                                    <button class="btn btn-outline-danger btn-sm" type="submit" title="Remover risco"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!$somenteLeitura): ?>
                            <form
                                method="POST"
                                action="<?= BASE_URL ?>/checklists/<?= $checklistId ?>/ghe/<?= (int)$ghe['id'] ?>/riscos/salvar"
                                class="ghe-risk-form"
                                data-prevent-double-submit
                            >
                                <input type="hidden" name="_token" value="<?= $esc($csrfToken ?? '') ?>">

                                <div class="checklist-field span-2">
                                    <label for="ghe_<?= (int)$ghe['id'] ?>_risco">Risco cadastrado *</label>
                                    <select class="form-select" id="ghe_<?= (int)$ghe['id'] ?>_risco" name="risco_id" required>
                                        <option value="">Selecione o agente/fator</option>
                                        <?php foreach ($riscosPorCategoria as $categoriaRisco => $itensCategoria): ?>
                                            <optgroup label="<?= $esc($categorias[$categoriaRisco] ?? ucfirst($categoriaRisco)) ?>">
                                                <?php foreach ($itensCategoria as $riscoDisponivel): ?>
                                                    <option value="<?= (int)$riscoDisponivel['id'] ?>">
                                                        <?= $esc(trim(($riscoDisponivel['codigo'] ?? '') . ' · ' . ($riscoDisponivel['nome'] ?? ''), ' ·')) ?>
                                                        <?= (int)($riscoDisponivel['exige_quantificacao'] ?? 0) === 1 ? ' — QUANTIFICÁVEL' : '' ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="checklist-field span-2"><label>Fonte geradora</label><input class="form-control" name="fonte_geradora" maxlength="255" placeholder="Máquina, processo, produto..."></div>
                                <div class="checklist-field span-2"><label>Meio de propagação</label><input class="form-control" name="meio_propagacao" maxlength="180" placeholder="Ar, contato, superfície..."></div>
                                <div class="checklist-field"><label>Frequência</label><select class="form-select" name="frequencia"><option value="">Selecione</option><?php foreach ($frequencias as $valor => $rotulo): ?><option value="<?= $valor ?>"><?= $esc($rotulo) ?></option><?php endforeach; ?></select></div>
                                <div class="checklist-field"><label>Tempo de exposição</label><select class="form-select" name="tempo_exposicao"><option value="">Selecione</option><?php foreach ($tempos as $valor => $rotulo): ?><option value="<?= $valor ?>"><?= $esc($rotulo) ?></option><?php endforeach; ?></select></div>
                                <div class="checklist-field"><label>Intensidade/medição</label><input class="form-control" name="intensidade" maxlength="100" placeholder="Valor observado"></div>
                                <div class="checklist-field span-2"><label>Observações</label><input class="form-control" name="observacoes" maxlength="255"></div>
                                <div class="checklist-form-action"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus"></i> Aplicar risco</button></div>
                            </form>
                        <?php endif; ?>
                    </section>

                    <section class="ghe-step-section ghe-cargo-step <?= !$temRiscos ? 'is-locked' : '' ?>">
                        <header class="ghe-step-header">
                            <span>3</span>
                            <div><strong>Vincular o GHE aos cargos</strong><small>Marque os cargos da estrutura que compartilham exatamente este conjunto de riscos.</small></div>
                            <?php if (!$temRiscos): ?><em><i class="fa-solid fa-lock"></i> Adicione um risco primeiro</em><?php endif; ?>
                        </header>

                        <?php if ($temCargos): ?>
                            <div class="ghe-cargo-chips">
                                <?php foreach ($cargosGhe as $cargoGhe): ?>
                                    <span><i class="fa-solid fa-user-gear"></i><?= $esc(($cargoGhe['setor_nome'] ?? '-') . ' · ' . ($cargoGhe['cargo_nome'] ?? '-')) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="ghe-no-risk"><i class="fa-solid fa-link-slash"></i> Nenhum cargo vinculado a este GHE.</div>
                        <?php endif; ?>

                        <?php if (!$somenteLeitura && $temRiscos): ?>
                            <form
                                method="POST"
                                action="<?= BASE_URL ?>/checklists/<?= $checklistId ?>/ghe/<?= (int)$ghe['id'] ?>/cargos/salvar"
                                class="ghe-cargo-link-form"
                                data-prevent-double-submit
                            >
                                <input type="hidden" name="_token" value="<?= $esc($csrfToken ?? '') ?>">
                                <div class="hierarchy-selector">
                                    <?php foreach ($hierarquia as $linha): ?>
                                        <?php $selecionado = in_array((int)$linha['id'], $idsCargosGhe, true); ?>
                                        <label>
                                            <input type="checkbox" name="hierarquias[]" value="<?= (int)$linha['id'] ?>" <?= $selecionado ? 'checked' : '' ?>>
                                            <span>
                                                <strong><?= $esc($linha['cargo_nome'] ?? '-') ?></strong>
                                                <small><?= $esc($linha['setor_nome'] ?? '-') ?><?= !empty($linha['cbo']) ? ' · CBO ' . $esc($linha['cbo']) : '' ?> · <?= (int)($linha['funcionarios_ativos'] ?? 0) ?> funcionário(s)</small>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="ghe-cargo-link-actions">
                                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-link"></i> Salvar vínculos com cargos</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </section>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
