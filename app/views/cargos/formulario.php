<?php $dados = $cargo ?? ($dadosAnteriores ?? []); ?>
<input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
<div class="org-callout"><i class="fa-solid fa-circle-info"></i><div>O cargo é um item do catálogo global. O relacionamento com Setor, Unidade e Empresa é definido exclusivamente em <strong>Hierarquia</strong>.</div></div>
<section class="org-form-section">
    <div class="org-section-title"><div class="org-section-icon"><i class="fa-solid fa-briefcase"></i></div><div><h2>Identificação do cargo</h2><p>Cadastre códigos, CBO, nome e situação.</p></div></div>
    <div class="org-form-grid">
        <div class="org-field org-col-3"><label for="codigo">Código interno</label><input id="codigo" class="form-control" name="codigo" maxlength="30" value="<?= htmlspecialchars((string)($dados['codigo'] ?? '')) ?>"></div>
        <div class="org-field org-col-3"><label for="codigo_externo">Código externo</label><input id="codigo_externo" class="form-control" name="codigo_externo" maxlength="50" value="<?= htmlspecialchars((string)($dados['codigo_externo'] ?? '')) ?>"></div>
        <div class="org-field org-col-4"><label for="nome">Nome do cargo *</label><input id="nome" class="form-control" name="nome" maxlength="150" required value="<?= htmlspecialchars((string)($dados['nome'] ?? '')) ?>"></div>
        <div class="org-field org-col-2"><label for="cbo">CBO</label><input id="cbo" class="form-control" name="cbo" maxlength="20" value="<?= htmlspecialchars((string)($dados['cbo'] ?? '')) ?>"></div>
        <div class="org-field org-col-3"><label>Status</label><div class="org-switch"><input type="hidden" name="ativo" value="0"><div class="form-check form-switch"><input class="form-check-input" id="ativo" type="checkbox" name="ativo" value="1" <?= !isset($dados['ativo']) || !empty($dados['ativo']) ? 'checked' : '' ?>><label class="form-check-label" for="ativo">Ativo</label></div></div></div>
        <div class="org-field org-col-12"><label for="descricao">Descrição das atividades</label><textarea id="descricao" class="form-control" name="descricao" rows="6" placeholder="Descreva as principais atribuições e responsabilidades do cargo."><?= htmlspecialchars((string)($dados['descricao'] ?? '')) ?></textarea></div>
    </div>
</section>
