<?php
$dados = $unidade ?? ($dadosAnteriores ?? []);
$codigoPadrao = $dados['codigo'] ?? ('UND' . strtoupper(substr(md5((string)microtime(true)), 0, 8)));
$codigoExternoPadrao = $dados['codigo_externo'] ?? ('EXT-UND-' . date('YmdHis'));
?>

<section class="org-form-section">
    <div class="org-section-title"><div class="org-section-icon"><i class="fa-solid fa-building"></i></div><div><h2>Identificação da unidade</h2><p>Defina o vínculo empresarial e os dados cadastrais.</p></div></div>
    <div class="org-form-grid">
        <div class="org-field org-col-6"><label for="empresa_id">Empresa vinculada *</label><select id="empresa_id" class="form-select" name="empresa_id" required><option value="">Selecione a empresa</option><?php foreach (($empresas ?? []) as $empresa): ?><option value="<?= (int)$empresa['id'] ?>" <?= (string)($dados['empresa_id'] ?? '') === (string)$empresa['id'] ? 'selected' : '' ?>><?= htmlspecialchars($empresa['nome_fantasia'] ?: $empresa['razao_social']) ?></option><?php endforeach; ?></select></div>
        <div class="org-field org-col-3"><label for="codigo">Código interno</label><input id="codigo" class="form-control text-uppercase" name="codigo" maxlength="30" value="<?= htmlspecialchars((string)$codigoPadrao) ?>"></div>
        <div class="org-field org-col-3"><label for="codigo_externo">Código externo</label><input id="codigo_externo" class="form-control text-uppercase" name="codigo_externo" maxlength="50" value="<?= htmlspecialchars((string)$codigoExternoPadrao) ?>"></div>
        <div class="org-field org-col-4"><label for="nome">Nome da unidade *</label><input id="nome" class="form-control" name="nome" maxlength="150" required value="<?= htmlspecialchars((string)($dados['nome'] ?? '')) ?>" placeholder="Ex.: Matriz, Filial Curitiba"></div>
        <div class="org-field org-col-4"><label for="razao_social">Razão social</label><input id="razao_social" class="form-control" name="razao_social" maxlength="200" value="<?= htmlspecialchars((string)($dados['razao_social'] ?? '')) ?>"></div>
        <div class="org-field org-col-4"><label for="nome_fantasia">Nome fantasia</label><input id="nome_fantasia" class="form-control" name="nome_fantasia" maxlength="200" value="<?= htmlspecialchars((string)($dados['nome_fantasia'] ?? '')) ?>"></div>
        <div class="org-field org-col-4"><label for="cnpj">CNPJ</label><input id="cnpj" class="form-control" name="cnpj" maxlength="18" value="<?= htmlspecialchars((string)($dados['cnpj'] ?? '')) ?>" placeholder="00.000.000/0000-00"></div>
        <div class="org-field org-col-4"><label for="inscricao_estadual">Inscrição estadual</label><input id="inscricao_estadual" class="form-control" name="inscricao_estadual" maxlength="50" value="<?= htmlspecialchars((string)($dados['inscricao_estadual'] ?? '')) ?>"></div>
        <div class="org-field org-col-4"><label>Status</label><div class="org-switch"><input type="hidden" name="ativo" value="0"><div class="form-check form-switch"><input class="form-check-input" id="ativo" type="checkbox" name="ativo" value="1" <?= !isset($dados['ativo']) || !empty($dados['ativo']) ? 'checked' : '' ?>><label class="form-check-label" for="ativo">Unidade ativa</label></div></div></div>
    </div>
</section>

<section class="org-form-section">
    <div class="org-section-title"><div class="org-section-icon"><i class="fa-solid fa-chart-pie"></i></div><div><h2>Atividade econômica</h2><p>Dados que apoiam o enquadramento e o planejamento das visitas.</p></div></div>
    <div class="org-form-grid">
        <div class="org-field org-col-3"><label for="cnae">CNAE</label><input id="cnae" class="form-control" name="cnae" maxlength="20" value="<?= htmlspecialchars((string)($dados['cnae'] ?? '')) ?>"></div>
        <div class="org-field org-col-5"><label for="descricao_cnae">Descrição do CNAE</label><input id="descricao_cnae" class="form-control" name="descricao_cnae" maxlength="255" value="<?= htmlspecialchars((string)($dados['descricao_cnae'] ?? '')) ?>"></div>
        <div class="org-field org-col-2"><label for="grau_risco">Grau de risco</label><select id="grau_risco" class="form-select" name="grau_risco"><option value="">Não informado</option><?php for ($i = 1; $i <= 4; $i++): ?><option value="<?= $i ?>" <?= (string)($dados['grau_risco'] ?? '') === (string)$i ? 'selected' : '' ?>><?= $i ?></option><?php endfor; ?></select></div>
        <div class="org-field org-col-2"><label for="quantidade_funcionarios">Funcionários</label><input id="quantidade_funcionarios" type="number" min="0" class="form-control" name="quantidade_funcionarios" value="<?= htmlspecialchars((string)($dados['quantidade_funcionarios'] ?? '')) ?>"></div>
    </div>
</section>

<section class="org-form-section">
    <div class="org-section-title"><div class="org-section-icon"><i class="fa-solid fa-location-dot"></i></div><div><h2>Endereço</h2><p>Localização utilizada na Agenda, Visitas e relatórios.</p></div></div>
    <div class="org-form-grid">
        <div class="org-field org-col-3"><label for="cep">CEP</label><input id="cep" class="form-control" name="cep" maxlength="9" value="<?= htmlspecialchars((string)($dados['cep'] ?? '')) ?>" placeholder="00000-000"></div>
        <div class="org-field org-col-6"><label for="logradouro">Logradouro</label><input id="logradouro" class="form-control" name="logradouro" maxlength="200" value="<?= htmlspecialchars((string)($dados['logradouro'] ?? '')) ?>"></div>
        <div class="org-field org-col-3"><label for="numero">Número</label><input id="numero" class="form-control" name="numero" maxlength="20" value="<?= htmlspecialchars((string)($dados['numero'] ?? '')) ?>"></div>
        <div class="org-field org-col-4"><label for="complemento">Complemento</label><input id="complemento" class="form-control" name="complemento" maxlength="150" value="<?= htmlspecialchars((string)($dados['complemento'] ?? '')) ?>"></div>
        <div class="org-field org-col-3"><label for="bairro">Bairro</label><input id="bairro" class="form-control" name="bairro" maxlength="120" value="<?= htmlspecialchars((string)($dados['bairro'] ?? '')) ?>"></div>
        <div class="org-field org-col-3"><label for="cidade">Cidade</label><input id="cidade" class="form-control" name="cidade" maxlength="120" value="<?= htmlspecialchars((string)($dados['cidade'] ?? '')) ?>"></div>
        <div class="org-field org-col-2"><label for="estado">UF</label><input id="estado" class="form-control text-uppercase" name="estado" maxlength="2" value="<?= htmlspecialchars((string)($dados['estado'] ?? '')) ?>"></div>
        <div class="org-field org-col-12"><label for="endereco">Endereço consolidado</label><textarea id="endereco" class="form-control" name="endereco" rows="2" readonly><?= htmlspecialchars((string)($dados['endereco'] ?? '')) ?></textarea><small>Atualizado automaticamente com os campos acima.</small></div>
    </div>
</section>

<section class="org-form-section">
    <div class="org-section-title"><div class="org-section-icon"><i class="fa-solid fa-address-book"></i></div><div><h2>Contatos e responsáveis</h2><p>Informações para acompanhamento técnico e comunicação.</p></div></div>
    <div class="org-form-grid">
        <div class="org-field org-col-3"><label for="telefone">Telefone</label><input id="telefone" class="form-control" name="telefone" maxlength="20" value="<?= htmlspecialchars((string)($dados['telefone'] ?? '')) ?>"></div>
        <div class="org-field org-col-3"><label for="contato_responsavel">Celular / WhatsApp</label><input id="contato_responsavel" class="form-control" name="contato_responsavel" maxlength="20" value="<?= htmlspecialchars((string)($dados['contato_responsavel'] ?? '')) ?>"></div>
        <div class="org-field org-col-6"><label for="email">E-mail</label><input id="email" type="email" class="form-control" name="email" maxlength="180" value="<?= htmlspecialchars((string)($dados['email'] ?? '')) ?>"></div>
        <div class="org-field org-col-4"><label for="responsavel">Responsável principal</label><input id="responsavel" class="form-control" name="responsavel" maxlength="150" value="<?= htmlspecialchars((string)($dados['responsavel'] ?? '')) ?>"></div>
        <div class="org-field org-col-4"><label for="cargo_responsavel">Cargo do responsável</label><input id="cargo_responsavel" class="form-control" name="cargo_responsavel" maxlength="120" value="<?= htmlspecialchars((string)($dados['cargo_responsavel'] ?? '')) ?>"></div>
        <div class="org-field org-col-4"><label for="periodicidade_visitas">Periodicidade de visitas</label><input id="periodicidade_visitas" class="form-control" name="periodicidade_visitas" maxlength="100" value="<?= htmlspecialchars((string)($dados['periodicidade_visitas'] ?? '')) ?>" placeholder="Ex.: mensal, trimestral"></div>
        <div class="org-field org-col-6"><label for="tecnico_responsavel">Técnico responsável</label><input id="tecnico_responsavel" class="form-control" name="tecnico_responsavel" maxlength="150" value="<?= htmlspecialchars((string)($dados['tecnico_responsavel'] ?? '')) ?>"></div>
        <div class="org-field org-col-6"><label for="supervisor_responsavel">Supervisor responsável</label><input id="supervisor_responsavel" class="form-control" name="supervisor_responsavel" maxlength="150" value="<?= htmlspecialchars((string)($dados['supervisor_responsavel'] ?? '')) ?>"></div>
        <div class="org-field org-col-12"><label for="observacoes">Observações</label><textarea id="observacoes" class="form-control" name="observacoes" rows="4"><?= htmlspecialchars((string)($dados['observacoes'] ?? '')) ?></textarea></div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const byId = id => document.getElementById(id);
    const preencher = (id, valor) => { const el = byId(id); if (el && valor !== null && valor !== undefined && String(valor).trim() !== '') el.value = valor; };

    function mascaraTelefone(input) {
        input?.addEventListener('input', function () {
            let valor = this.value.replace(/\D/g, '').slice(0, 11);
            if (valor.length > 10) valor = valor.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
            else valor = valor.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            this.value = valor;
        });
    }
    mascaraTelefone(byId('telefone'));
    mascaraTelefone(byId('contato_responsavel'));

    const cnpj = byId('cnpj');
    cnpj?.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '').slice(0, 14);
        value = value.replace(/^(\d{2})(\d)/, '$1.$2').replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3').replace(/\.(\d{3})(\d)/, '.$1/$2').replace(/(\d{4})(\d)/, '$1-$2');
        this.value = value;
    });
    cnpj?.addEventListener('blur', async function () {
        const numero = this.value.replace(/\D/g, '');
        if (numero.length !== 14) return;
        try {
            const resposta = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${numero}`);
            if (!resposta.ok) return;
            const data = await resposta.json();
            preencher('razao_social', data.razao_social || data.nome);
            preencher('nome_fantasia', data.nome_fantasia);
            preencher('nome', data.nome_fantasia || data.razao_social || data.nome);
            preencher('telefone', data.ddd_telefone_1 || data.ddd_telefone_2);
            preencher('email', data.email);
            preencher('cep', data.cep);
            preencher('numero', data.numero);
            preencher('complemento', data.complemento);
            preencher('cnae', data.cnae_fiscal);
            preencher('descricao_cnae', data.cnae_fiscal_descricao);
            if (Array.isArray(data.qsa) && data.qsa.length) { preencher('responsavel', data.qsa[0].nome_socio); preencher('cargo_responsavel', data.qsa[0].qualificacao_socio); }
            if (data.cep) await buscarCep(data.cep);
        } catch (erro) { console.warn('Consulta de CNPJ indisponível.', erro); }
    });

    async function buscarCep(valor) {
        const numero = String(valor || '').replace(/\D/g, '');
        if (numero.length !== 8) return;
        try {
            const resposta = await fetch(`https://viacep.com.br/ws/${numero}/json/`);
            const data = await resposta.json();
            if (data.erro) return;
            preencher('logradouro', data.logradouro);
            preencher('bairro', data.bairro);
            preencher('cidade', data.localidade);
            preencher('estado', data.uf);
            atualizarEndereco();
        } catch (erro) { console.warn('Consulta de CEP indisponível.', erro); }
    }

    const cep = byId('cep');
    cep?.addEventListener('input', function () { let valor = this.value.replace(/\D/g, '').slice(0, 8); this.value = valor.replace(/^(\d{5})(\d)/, '$1-$2'); });
    cep?.addEventListener('blur', function () { buscarCep(this.value); });

    function atualizarEndereco() {
        const partes = ['logradouro','numero','complemento','bairro','cidade','estado'].map(id => byId(id)?.value.trim()).filter(Boolean);
        const endereco = byId('endereco');
        if (endereco) endereco.value = partes.join(', ');
    }
    ['logradouro','numero','complemento','bairro','cidade','estado'].forEach(id => { byId(id)?.addEventListener('input', atualizarEndereco); byId(id)?.addEventListener('change', atualizarEndereco); });
    atualizarEndereco();
});
</script>
