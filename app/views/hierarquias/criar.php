<?php
$rotaAtual = 'hierarquias';
$css = 'hierarquias.css';
require_once dirname(__DIR__) . '/templates/header.php';
?>
<div class="org-page"><div class="org-container">
    <header class="org-header">
        <div class="org-header-main">
            <div class="org-header-icon"><i class="fa-solid fa-sitemap"></i></div>
            <div class="org-header-copy">
                <div class="org-eyebrow">Montagem organizacional guiada</div>
                <h1>Montar hierarquia</h1>
                <p>Utilize os catálogos globais para estruturar uma unidade e depois alocar os funcionários.</p>
            </div>
        </div>
        <div class="org-header-actions">
            <a href="<?= BASE_URL ?>/hierarquias" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
        </div>
    </header>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger org-alert"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div>
    <?php endif; ?>

    <div class="org-form-layout org-form-layout-wide">
        <form action="<?= BASE_URL ?>/hierarquias/salvar" method="post" class="org-form org-form-card" data-hierarchy-builder>
            <?php require __DIR__ . '/formulario.php'; ?>
            <div class="org-form-actions">
                <a href="<?= BASE_URL ?>/hierarquias" class="btn btn-outline-secondary">Cancelar</a>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Salvar e alocar funcionários</button>
            </div>
        </form>

        <aside class="org-form-aside">
            <div class="org-side-card">
                <h3>Cadastros anteriores</h3>
                <ol>
                    <li>Cadastre a Empresa.</li>
                    <li>Cadastre a Unidade dentro dela.</li>
                    <li>Cadastre Setores globais.</li>
                    <li>Cadastre Cargos globais.</li>
                </ol>
            </div>
            <div class="org-side-card">
                <h3>Sem duplicidade</h3>
                <p>Vínculos já existentes são identificados e bloqueados. A tela adiciona apenas as novas combinações.</p>
            </div>
            <div class="org-side-card">
                <h3>Próxima etapa</h3>
                <p>Depois de salvar, selecione os funcionários que pertencem a cada cargo da estrutura.</p>
            </div>
        </aside>
    </div>
</div></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('[data-hierarchy-builder]');
    form?.addEventListener('submit', function (event) {
        const novos = form.querySelectorAll('input[name="vinculos[]"]:checked:not(:disabled)');
        if (novos.length === 0) {
            event.preventDefault();
            window.alert('Selecione ao menos um novo cargo dentro de um setor.');
            document.getElementById('setoresSelector')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }
        const button = form.querySelector('button[type="submit"]');
        if (button && !button.disabled) {
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando estrutura...';
        }
    });
});
</script>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
