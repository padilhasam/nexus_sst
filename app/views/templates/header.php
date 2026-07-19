<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once dirname(__DIR__, 2) . '/helpers/ToastHelper.php';

$rotaAtual = $rotaAtual ?? 'dashboard';

$usuario = $_SESSION['nome'] ?? 'Usuário Desconhecido';

$pageTitle = $pageTitle ?? [
    'dashboard' => 'Dashboard',
    'agenda' => 'Agenda',
    'visitas' => 'Visitas Técnicas',
    'checklists' => 'Check-list de Visita',
    'ghe' => 'Grupos Homogêneos de Exposição',
    'quantificacoes' => 'Quantificações',
    'nao_conformidades' => 'Não Conformidades',
    'empresas' => 'Empresas',
    'unidades' => 'Unidades',
    'setores' => 'Setores',
    'cargos' => 'Cargos',
    'funcionarios' => 'Funcionários',
    'hierarquias' => 'Hierarquia',
    'riscos' => 'Riscos',
    'equipamentos' => 'Equipamentos',
    'veiculos' => 'Veículos',
    'mensagens' => 'Mensagens',
    'relatorios' => 'Relatórios',
    'usuarios' => 'Usuários',
    'configuracoes' => 'Configurações',
][$rotaAtual] ?? ucfirst(str_replace('_', ' ', $rotaAtual));

function menuAtivo(array $rotas, string $rotaAtual): bool {
    return in_array($rotaAtual, $rotas, true);
}

function activeClass(string $rota, string $rotaAtual): string {
    return $rotaAtual === $rota ? 'active' : '';
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars($pageTitle) ?> | Nexus SST</title>

<link rel="icon" href="<?= BASE_URL ?>/image/favicon.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= time() ?>">

<?php if (!empty($css)) : ?>
    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/pages/<?= htmlspecialchars($css) ?>?v=<?= time() ?>"
    >
<?php endif; ?>
</head>

<body>

<div class="sst-app">

    <aside class="sst-sidebar" id="sidebar">

        <div class="sst-brand">
            <a class="logo d-flex align-items-center" href="<?= BASE_URL ?>/dashboard">
                <img src="<?= BASE_URL ?>/image/logo04.png" alt="Logo NEXUS SST" class="img-fluid logo-img">
            </a>
        </div>

        <div class="sst-user">
            <img src="https://i.pravatar.cc/80?img=12" alt="">
            <div>
                <strong><?= htmlspecialchars($usuario) ?></strong>
                <span>Técnico de Segurança</span>
            </div>
        </div>

        <nav class="sst-menu accordion" id="sidebarMenu">

            <a class="<?= activeClass('dashboard', $rotaAtual) ?>" href="<?= BASE_URL ?>/dashboard">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <?php $opAberto = menuAtivo(['agenda','visitas','checklists','ghe'], $rotaAtual); ?>
            <button class="menu-toggle <?= $opAberto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#menuOperacao">
                <span><i class="fa-solid fa-tablet-screen-button"></i> Operação</span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div id="menuOperacao" class="collapse <?= $opAberto ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
                <a class="<?= activeClass('agenda', $rotaAtual) ?>" href="<?= BASE_URL ?>/agenda"><i class="fa-regular fa-calendar"></i><span>Agenda</span></a>
                <a class="<?= activeClass('visitas', $rotaAtual) ?>" href="<?= BASE_URL ?>/visitas"><i class="fa-solid fa-route"></i><span>Visitas Técnicas</span></a>
                <a class="<?= activeClass('checklists', $rotaAtual) ?>" href="<?= BASE_URL ?>/checklists"><i class="fa-regular fa-square-check"></i><span>Check-lists</span></a>
                <a class="<?= activeClass('ghe', $rotaAtual) ?>" href="<?= BASE_URL ?>/ghe"><i class="fa-solid fa-people-group"></i><span>GHEs</span></a>
            </div>

            <?php $cadAberto = menuAtivo(['empresas','unidades','setores','cargos','funcionarios','hierarquias'], $rotaAtual); ?>
            <button class="menu-toggle <?= $cadAberto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#menuCadastros">
                <span><i class="fa-regular fa-building"></i> Cadastros</span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div id="menuCadastros" class="collapse <?= $cadAberto ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
                <a class="<?= activeClass('empresas', $rotaAtual) ?>" href="<?= BASE_URL ?>/empresas"><i class="fa-regular fa-building"></i><span>Empresas</span></a>
                <a class="<?= activeClass('unidades', $rotaAtual) ?>" href="<?= BASE_URL ?>/unidades"><i class="fa-solid fa-industry"></i><span>Unidades</span></a>
                <a class="<?= activeClass('setores', $rotaAtual) ?>" href="<?= BASE_URL ?>/setores"><i class="fa-solid fa-layer-group"></i><span>Setores</span></a>
                <a class="<?= activeClass('cargos', $rotaAtual) ?>" href="<?= BASE_URL ?>/cargos"><i class="fa-solid fa-briefcase"></i><span>Cargos</span></a>
                <a class="<?= activeClass('funcionarios', $rotaAtual) ?>" href="<?= BASE_URL ?>/funcionarios"><i class="fa-solid fa-id-badge"></i><span>Funcionários</span></a>
                <a class="<?= activeClass('hierarquias', $rotaAtual) ?>" href="<?= BASE_URL ?>/hierarquias"><i class="fa-solid fa-sitemap"></i><span>Hierarquia</span></a>
            </div>

            <?php $bibAberto = menuAtivo(['riscos','epis','epcs','fontes-geradoras','medidas-controle','itens-fiscalizacao','treinamentos'], $rotaAtual); ?>
            <button class="menu-toggle <?= $bibAberto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#menuBiblioteca">
                <span><i class="fa-solid fa-book-open"></i> Biblioteca Técnica</span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div id="menuBiblioteca" class="collapse <?= $bibAberto ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
                <a class="<?= activeClass('riscos', $rotaAtual) ?>" href="<?= BASE_URL ?>/riscos"><i class="fa-solid fa-flask-vial"></i><span>Riscos</span></a>
                <a href="<?= BASE_URL ?>/fontes-geradoras"><i class="fa-solid fa-industry"></i><span>Fontes Geradoras</span></a>
                <a href="<?= BASE_URL ?>/epis"><i class="fa-solid fa-helmet-safety"></i><span>EPIs</span></a>
                <a href="<?= BASE_URL ?>/epcs"><i class="fa-solid fa-shield-halved"></i><span>EPCs</span></a>
                <a href="<?= BASE_URL ?>/medidas-controle"><i class="fa-solid fa-shield-heart"></i><span>Medidas de Controle</span></a>
                <a href="<?= BASE_URL ?>/itens-fiscalizacao"><i class="fa-solid fa-clipboard-list"></i><span>Itens de Fiscalização</span></a>
                <a href="<?= BASE_URL ?>/treinamentos"><i class="fa-solid fa-chalkboard-user"></i><span>Treinamentos</span></a>
            </div>

            <?php $resAberto = menuAtivo(['quantificacoes','nao_conformidades','relatorios'], $rotaAtual); ?>
            <button class="menu-toggle <?= $resAberto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#menuResultados">
                <span><i class="fa-solid fa-chart-simple"></i> Resultados</span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div id="menuResultados" class="collapse <?= $resAberto ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
                <a class="<?= activeClass('quantificacoes', $rotaAtual) ?>" href="<?= BASE_URL ?>/quantificacoes"><i class="fa-solid fa-chart-line"></i><span>Quantificações</span></a>
                <a class="<?= activeClass('nao_conformidades', $rotaAtual) ?>" href="<?= BASE_URL ?>/nao_conformidades"><i class="fa-solid fa-triangle-exclamation"></i><span>Não Conformidades</span></a>
                <a class="<?= activeClass('relatorios', $rotaAtual) ?>" href="<?= BASE_URL ?>/relatorios"><i class="fa-regular fa-file-lines"></i><span>Relatórios Técnicos</span></a>
            </div>

            <?php $recAberto = menuAtivo(['equipamentos','veiculos'], $rotaAtual); ?>
            <button class="menu-toggle <?= $recAberto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#menuRecursos">
                <span><i class="fa-solid fa-toolbox"></i> Recursos</span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div id="menuRecursos" class="collapse <?= $recAberto ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
                <a class="<?= activeClass('equipamentos', $rotaAtual) ?>" href="<?= BASE_URL ?>/equipamentos"><i class="fa-solid fa-gauge-high"></i><span>Equipamentos</span></a>
                <a class="<?= activeClass('veiculos', $rotaAtual) ?>" href="<?= BASE_URL ?>/veiculos"><i class="fa-solid fa-car"></i><span>Veículos</span></a>
            </div>

            <?php $comAberto = menuAtivo(['mensagens','notificacoes'], $rotaAtual); ?>
            <button class="menu-toggle <?= $comAberto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#menuComunicacao">
                <span><i class="fa-regular fa-comments"></i> Comunicação</span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div id="menuComunicacao" class="collapse <?= $comAberto ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
                <a class="<?= activeClass('mensagens', $rotaAtual) ?>" href="<?= BASE_URL ?>/mensagens"><i class="fa-regular fa-envelope"></i><span>Mensagens</span><b>3</b></a>
                <a href="<?= BASE_URL ?>/notificacoes"><i class="fa-regular fa-bell"></i><span>Notificações</span></a>
            </div>

            <?php $admAberto = menuAtivo(['usuarios','historico','logs','configuracoes'], $rotaAtual); ?>
            <button class="menu-toggle <?= $admAberto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#menuAdmin">
                <span><i class="fa-solid fa-gear"></i> Administração</span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div id="menuAdmin" class="collapse <?= $admAberto ? 'show' : '' ?>" data-bs-parent="#sidebarMenu">
                <a class="<?= activeClass('usuarios', $rotaAtual) ?>" href="<?= BASE_URL ?>/usuarios"><i class="fa-regular fa-user"></i><span>Usuários</span></a>
                <a href="<?= BASE_URL ?>/historico"><i class="fa-solid fa-clock-rotate-left"></i><span>Histórico</span></a>
                <a href="<?= BASE_URL ?>/logs"><i class="fa-regular fa-file-lines"></i><span>Logs</span></a>
                <a class="<?= activeClass('configuracoes', $rotaAtual) ?>" href="<?= BASE_URL ?>/configuracoes"><i class="fa-solid fa-sliders"></i><span>Configurações</span></a>
            </div>

        </nav>

        <a class="sst-logout" href="<?= BASE_URL ?>/logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Sair</span>
        </a>

    </aside>

    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <section class="sst-main">
        <header class="sst-topbar">
            <button
                class="icon-btn sst-menu-button"
                id="btnToggleSidebar"
                type="button"
                aria-label="Abrir menu"
            >
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="sst-topbar-page">
                <div class="sst-topbar-title-row">
                    <h1 class="sst-page-title">
                        <?= htmlspecialchars($pageTitle ?? 'Nexus SST') ?>
                    </h1>

                    <?php if (!empty($pageBadge)): ?>
                        <span class="sst-page-badge">
                            <?= htmlspecialchars($pageBadge) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($pageSubtitle)): ?>
                    <p class="sst-page-subtitle">
                        <?= htmlspecialchars($pageSubtitle) ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($breadcrumbs) && is_array($breadcrumbs)): ?>
                    <nav class="sst-topbar-breadcrumb" aria-label="Navegação estrutural">
                        <?php foreach ($breadcrumbs as $indice => $breadcrumb): ?>

                            <?php if ($indice > 0): ?>
                                <i class="fa-solid fa-chevron-right"></i>
                            <?php endif; ?>

                            <?php if (!empty($breadcrumb['url'])): ?>
                                <a href="<?= htmlspecialchars($breadcrumb['url']) ?>">
                                    <?= htmlspecialchars($breadcrumb['label']) ?>
                                </a>
                            <?php else: ?>
                                <span><?= htmlspecialchars($breadcrumb['label']) ?></span>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>
            </div>
            <div class="topbar-spacer"></div>

            <?php if (!empty($pageActionUrl) && !empty($pageActionLabel)): ?>
                <a
                    href="<?= htmlspecialchars($pageActionUrl) ?>"
                    class="btn btn-primary sst-topbar-action"
                >
                    <?php if (!empty($pageActionIcon)): ?>
                        <i class="<?= htmlspecialchars($pageActionIcon) ?>"></i>
                    <?php endif; ?>

                    <span><?= htmlspecialchars($pageActionLabel) ?></span>
                </a>
            <?php endif; ?>

            <div class="sst-topbar-tools">
                <button
                    class="icon-btn notify"
                    type="button"
                    aria-label="Notificações"
                >
                    <i class="fa-regular fa-bell"></i>
                    <span></span>
                </button>

                <a
                    href="<?= BASE_URL ?>/mensagens"
                    class="icon-btn"
                    aria-label="Mensagens"
                >
                    <i class="fa-regular fa-message"></i>
                </a>
            </div>
        </header>
        <div class="sst-content">