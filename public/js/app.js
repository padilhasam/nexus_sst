/**
 * ARQUIVO GLOBAL DE SCRIPTS
 * public/js/app.js
 */

document.addEventListener('DOMContentLoaded', function () {
   if (
        typeof bootstrap === 'undefined' ||
        typeof bootstrap.Toast === 'undefined'
    ) {
        console.warn('Bootstrap Toast não está disponível.');
        return;
    }

    inicializarToastsRenderizados();
});

/**
 * Inicializa os toasts que já vieram renderizados pelo PHP.
 */
function inicializarToastsRenderizados() {
    if (
        typeof bootstrap === 'undefined' ||
        typeof bootstrap.Toast === 'undefined'
    ) {
        console.warn('Bootstrap Toast não está disponível.');
        return;
    }

    const toasts = document.querySelectorAll('.toast');

    toasts.forEach(function (toastEl) {
        if (toastEl.dataset.toastInicializado === 'true') {
            return;
        }

        toastEl.dataset.toastInicializado = 'true';

        const tempo = Number(
            toastEl.dataset.delay || 4000
        );

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

/**
 * Exibe um toast dinamicamente.
 *
 * @param {string} mensagem Texto exibido no toast.
 * @param {'success'|'danger'|'warning'|'info'} tipo Tipo visual.
 * @param {number|null} tempo Tempo em milissegundos.
 */
window.showToast = function (
    mensagem,
    tipo = 'success',
    tempo = null
) {
    if (
        typeof bootstrap === 'undefined' ||
        typeof bootstrap.Toast === 'undefined'
    ) {
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

    const tempoInformado = Number(tempo);

   const tempoInformado =
    tempo === null || tempo === undefined
        ? NaN
        : Number(tempo);

    tempo =
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
};

/**
 * Cria ou retorna o container global dos toasts.
 *
 * @returns {HTMLElement}
 */
function obterToastContainer() {
    let container = document.querySelector(
        '.toast-container'
    );

    if (!container) {
        container = document.createElement('div');

        container.className = [
            'toast-container',
            'position-fixed',
            'top-0',
            'end-0',
            'p-3'
        ].join(' ');

        container.style.zIndex = '9999';
        container.setAttribute(
            'aria-live',
            'polite'
        );
        container.setAttribute(
            'aria-atomic',
            'true'
        );

        document.body.appendChild(container);
    }

    return container;
}

/**
 * Monta o elemento do toast sem inserir HTML da mensagem.
 *
 * @param {string} mensagem
 * @param {object} config
 * @param {string} tipo
 * @returns {HTMLElement}
 */
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
        tipo === 'danger' ? 'assertive' : 'polite'
    );
    toastEl.setAttribute(
        'aria-atomic',
        'true'
    );

    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex';

    const body = document.createElement('div');
    body.className = 'toast-body';

    const icon = document.createElement('i');
    icon.className =
        `fa-solid ${config.icone} me-2`;

    const title = document.createElement('strong');
    title.className = 'me-1';
    title.textContent = config.titulo + ':';

    const message = document.createElement('span');
    message.textContent = String(mensagem);

    const closeButton =
        document.createElement('button');

    closeButton.type = 'button';
    closeButton.className = [
        'btn-close',
        tipo === 'warning'
            ? ''
            : 'btn-close-white',
        'me-2',
        'm-auto'
    ].filter(Boolean).join(' ');

    closeButton.setAttribute(
        'data-bs-dismiss',
        'toast'
    );

    closeButton.setAttribute(
        'aria-label',
        'Fechar'
    );

    body.appendChild(icon);
    body.appendChild(title);
    body.appendChild(message);

    wrapper.appendChild(body);
    wrapper.appendChild(closeButton);

    toastEl.appendChild(wrapper);

    return toastEl;
}

/**
 * Limita a quantidade de toasts visíveis.
 *
 * @param {HTMLElement} container
 * @param {number} limite
 */
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