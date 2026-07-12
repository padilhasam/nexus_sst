/**
 * ARQUIVO GLOBAL DE SCRIPTS (public/js/app.js)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Inicializa Toasts que foram renderizados via PHP no servidor
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toastEl => {
        new bootstrap.Toast(toastEl, { delay: 4000 }).show();
        // Garante que o toast seja removido do DOM após o fechamento
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    });
});

/**
 * Função global para disparar Toasts dinamicamente via AJAX ou eventos JS
 * @param {string} mensagem - Texto a exibir
 * @param {string} tipo - 'success' ou 'danger'
 */
function showToast(mensagem, tipo = 'success') {
    // Busca o container ou cria um dinamicamente se não existir
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }

    const id = 'toast-' + Date.now();
    const classe = tipo === 'success' ? 'text-bg-success' : 'text-bg-danger';
    const icone = tipo === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';

    const html = `
        <div id="${id}" class="toast ${classe} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${icone} me-2"></i> ${mensagem}
                </div>
                <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    
    const toastEl = document.getElementById(id);
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
    
    // Remove o elemento do DOM após a animação de saída para manter o HTML limpo
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());

}