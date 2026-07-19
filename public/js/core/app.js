/**
 * ARQUIVO GLOBAL DO NEXUS SST
 * public/js/core/app.js
 *
 * Responsabilidades:
 * - Sidebar responsiva
 * - Overlay mobile
 * - Toasts renderizados pelo PHP
 * - Toasts dinâmicos
 */

document.addEventListener('DOMContentLoaded', function () {
    inicializarSidebar();
    inicializarToastsRenderizados();
});

/* =========================================================
   SIDEBAR
========================================================= */

function inicializarSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const botao = document.getElementById('btnToggleSidebar');

    if (!sidebar || !overlay || !botao) {
        return;
    }

    function abrirMenu() {
        sidebar.classList.add('show');
        overlay.classList.add('show');
        document.body.classList.add('sidebar-open');

        botao.setAttribute('aria-expanded', 'true');
    }

    function fecharMenu() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.classList.remove('sidebar-open');

        botao.setAttribute('aria-expanded', 'false');
    }

    function alternarMenu(event) {
        event.preventDefault();
        event.stopPropagation();

        if (sidebar.classList.contains('show')) {
            fecharMenu();
            return;
        }

        abrirMenu();
    }

    botao.addEventListener('click', alternarMenu);

    overlay.addEventListener('click', fecharMenu);

    document.addEventListener('click', function (event) {
        if (!sidebar.classList.contains('show')) {
            return;
        }

        const clicouNoSidebar = sidebar.contains(event.target);
        const clicouNoBotao = botao.contains(event.target);

        if (!clicouNoSidebar && !clicouNoBotao) {
            fecharMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            sidebar.classList.contains('show')
        ) {
            fecharMenu();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 1100) {
            fecharMenu();
        }
    });
}

/* =========================================================
   TOASTS RENDERIZADOS PELO PHP
========================================================= */

function inicializarToastsRenderizados() {
    if (!bootstrapToastDisponivel()) {
        console.warn('Bootstrap Toast não está disponível.');
        return;
    }

    const toasts = document.querySelectorAll('.toast');

    toasts.forEach(function (toastEl) {
        if (toastEl.dataset.toastInicializado === 'true') {
            return;
        }

        toastEl.dataset.toastInicializado = 'true';

        const tempoInformado = Number(toastEl.dataset.delay);

        const tempo = Number.isFinite(tempoInformado) &&
            tempoInformado >= 1000
            ? tempoInformado
            : 4000;

        const toast = bootstrap.Toast.getOrCreateInstance(
            toastEl,
            {
                animation: true,
                autohide: true,
                delay: tempo
            }
        );

        toastEl.addEventListener(
            'hidden.bs.toast',
            function () {
                toast.dispose();
                toastEl.remove();
            },
            { once: true }
        );

        toast.show();
    });
}

/* =========================================================
   TOAST DINÂMICO GLOBAL
========================================================= */

/**
 * Exibe um toast dinamicamente.
 *
 * @param {string} mensagem
 * @param {'success'|'danger'|'warning'|'info'} tipo
 * @param {number|null} tempo Tempo em milissegundos
 */
window.showToast = function (
    mensagem,
    tipo = 'success',
    tempo = null
) {
    if (!bootstrapToastDisponivel()) {
        console.warn(
            'Bootstrap Toast não está disponível:',
            mensagem
        );

        return;
    }

    const tiposPermitidos = [
        'success',
        'danger',
        'warning',
        'info'
    ];

    if (!tiposPermitidos.includes(tipo)) {
        tipo = 'info';
    }

    const temposPadrao = {
        success: 4000,
        info: 5000,
        warning: 6000,
        danger: 7000
    };

    const tempoInformado =
        tempo === null || tempo === undefined
            ? NaN
            : Number(tempo);

    const duracao =
        Number.isFinite(tempoInformado) &&
        tempoInformado >= 1000
            ? tempoInformado
            : temposPadrao[tipo];

    const configuracoes = {
        success: {
            classe: 'text-bg-success',
            icone: 'fa-circle-check',
            titulo: 'Sucesso'
        },

        danger: {
            classe: 'text-bg-danger',
            icone: 'fa-circle-exclamation',
            titulo: 'Erro'
        },

        warning: {
            classe: 'text-bg-warning',
            icone: 'fa-triangle-exclamation',
            titulo: 'Atenção'
        },

        info: {
            classe: 'text-bg-info',
            icone: 'fa-circle-info',
            titulo: 'Informação'
        }
    };

    const config = configuracoes[tipo];
    const container = obterToastContainer();

    const toastEl = criarToastElemento(
        mensagem,
        config,
        tipo
    );

    container.appendChild(toastEl);

    limitarQuantidadeToasts(container, 4);

    const toast = new bootstrap.Toast(
        toastEl,
        {
            animation: true,
            autohide: true,
            delay: duracao
        }
    );

    toastEl.addEventListener(
        'hidden.bs.toast',
        function () {
            toast.dispose();
            toastEl.remove();
        },
        { once: true }
    );

    toast.show();
};

/* =========================================================
   FUNÇÕES AUXILIARES DO TOAST
========================================================= */

function bootstrapToastDisponivel() {
    return (
        typeof bootstrap !== 'undefined' &&
        typeof bootstrap.Toast !== 'undefined'
    );
}

function obterToastContainer() {
    let container = document.querySelector(
        '.toast-container'
    );

    if (container) {
        return container;
    }

    container = document.createElement('div');

    container.className = [
        'toast-container',
        'position-fixed',
        'top-0',
        'end-0',
        'p-3'
    ].join(' ');

    container.style.zIndex = '9999';

    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-atomic', 'true');

    document.body.appendChild(container);

    return container;
}

function criarToastElemento(
    mensagem,
    config,
    tipo
) {
    const toastEl = document.createElement('div');

    toastEl.className = [
        'toast',
        config.classe,
        'border-0',
        'shadow-lg'
    ].join(' ');

    toastEl.setAttribute('role', 'alert');

    toastEl.setAttribute(
        'aria-live',
        tipo === 'danger'
            ? 'assertive'
            : 'polite'
    );

    toastEl.setAttribute(
        'aria-atomic',
        'true'
    );

    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex';

    const body = document.createElement('div');
    body.className = 'toast-body';

    const icone = document.createElement('i');
    icone.className =
        `fa-solid ${config.icone} me-2`;

    const titulo = document.createElement('strong');
    titulo.className = 'me-1';
    titulo.textContent = `${config.titulo}:`;

    const texto = document.createElement('span');
    texto.textContent = String(mensagem);

    const fechar = document.createElement('button');

    fechar.type = 'button';

    fechar.className = [
        'btn-close',
        tipo === 'warning'
            ? ''
            : 'btn-close-white',
        'me-2',
        'm-auto'
    ].filter(Boolean).join(' ');

    fechar.setAttribute(
        'data-bs-dismiss',
        'toast'
    );

    fechar.setAttribute(
        'aria-label',
        'Fechar'
    );

    body.appendChild(icone);
    body.appendChild(titulo);
    body.appendChild(texto);

    wrapper.appendChild(body);
    wrapper.appendChild(fechar);

    toastEl.appendChild(wrapper);

    return toastEl;
}

function limitarQuantidadeToasts(
    container,
    limite
) {
    const toasts = container.querySelectorAll(
        '.toast'
    );

    if (toasts.length <= limite) {
        return;
    }

    const quantidadeRemover =
        toasts.length - limite;

    for (
        let indice = 0;
        indice < quantidadeRemover;
        indice++
    ) {
        const toastEl = toasts[indice];

        const instancia =
            bootstrap.Toast.getInstance(toastEl);

        if (instancia) {
            instancia.hide();
        } else {
            toastEl.remove();
        }
    }
}