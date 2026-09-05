<?php
$esc = static fn (mixed $valor): string => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
$totalHierarquias = count($hierarquia ?? []);
$empresaIdEstrutura = (int)($c['empresa_id'] ?? $checklist['empresa_id'] ?? 0);
$agrupadaChecklist = [];
foreach (($hierarquia ?? []) as $linha) {
    $agrupadaChecklist[$linha['setor_nome']][] = $linha;
}
?>

<section class="checklist-workspace">
    <header class="checklist-workspace-header">
        <div>
            <span class="checklist-eyebrow">Estrutura organizacional oficial</span>
            <h2>Hierarquia da unidade</h2>
            <p>
                Esta etapa utiliza somente a estrutura previamente montada no módulo Hierarquias. Setores e Cargos
                não são criados dentro do check-list, evitando duplicidades e divergências entre visitas.
            </p>
        </div>
        <span class="checklist-count"><strong><?= $totalHierarquias ?></strong> vínculos</span>
    </header>

    <div class="checklist-flow-note">
        <div class="checklist-flow-note-icon"><i class="fa-solid fa-sitemap"></i></div>
        <div>
            <strong>Fonte oficial: Empresa → Unidade → Setor → Cargo → Funcionário</strong>
            <span>Qualquer alteração estrutural deve ser feita antes da visita ou no módulo Hierarquias.</span>
        </div>
        <?php if (!$somenteLeitura && $empresaIdEstrutura > 0): ?>
            <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/hierarquias/estrutura/<?= $empresaIdEstrutura ?>">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir estrutura
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($hierarquia)): ?>
        <div class="checklist-empty">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <h3>A unidade ainda não possui hierarquia</h3>
            <p>Monte a estrutura oficial com os setores e cargos antes de continuar o levantamento.</p>
            <?php if ($empresaIdEstrutura > 0): ?>
                <a class="btn btn-primary" href="<?= BASE_URL ?>/hierarquias/criar?empresa_id=<?= $empresaIdEstrutura ?>">
                    <i class="fa-solid fa-plus"></i> Montar hierarquia
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="checklist-official-tree">
            <?php foreach ($agrupadaChecklist as $setorNome => $linhas): ?>
                <article class="checklist-official-sector">
                    <header>
                        <span><i class="fa-solid fa-layer-group"></i></span>
                        <div><strong><?= $esc($setorNome) ?></strong><small><?= count($linhas) ?> cargo(s) na unidade</small></div>
                    </header>
                    <div class="checklist-official-roles">
                        <?php foreach ($linhas as $linha): ?>
                            <?php
                            $codigoCargo = trim((string)($linha['cargo_codigo'] ?? ''));
                            $ativos = (int)($linha['funcionarios_ativos'] ?? 0);
                            ?>
                            <div class="checklist-official-role">
                                <span class="checklist-official-role-icon"><i class="fa-solid fa-briefcase"></i></span>
                                <div>
                                    <strong><?= $esc($linha['cargo_nome'] ?? '-') ?></strong>
                                    <small>
                                        <?= $codigoCargo !== '' ? 'Código ' . $esc($codigoCargo) . ' · ' : '' ?>
                                        <?= !empty($linha['cbo']) ? 'CBO ' . $esc($linha['cbo']) : 'CBO não informado' ?>
                                    </small>
                                </div>
                                <span class="checklist-official-role-count"><strong><?= $ativos ?></strong><small>funcionários</small></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
