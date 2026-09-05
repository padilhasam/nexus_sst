(() => {
    'use strict';

    const $ = (selector, context = document) => context.querySelector(selector);
    const $$ = (selector, context = document) => Array.from(context.querySelectorAll(selector));

    const bindExclusiveExistingOrNew = (selectId, inputId) => {
        const select = document.getElementById(selectId);
        const input = document.getElementById(inputId);
        if (!select || !input) return;

        select.addEventListener('change', () => {
            if (select.value !== '') input.value = '';
        });
        input.addEventListener('input', () => {
            if (input.value.trim() !== '') select.value = '';
        });
    };

    bindExclusiveExistingOrNew('hierarquia_setor_id', 'hierarquia_novo_setor');
    bindExclusiveExistingOrNew('hierarquia_cargo_id', 'hierarquia_novo_cargo');

    $$('input[data-mask="cpf"]').forEach((input) => {
        input.addEventListener('input', () => {
            const digits = input.value.replace(/\D/g, '').slice(0, 11);
            input.value = digits
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1-$2');
        });
    });

    $$('form[data-prevent-double-submit]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirmMessage;
            if (message && !window.confirm(message)) {
                event.preventDefault();
                return;
            }

            const hierarchyCheckboxes = $$('input[name="hierarquias[]"]', form);
            if (hierarchyCheckboxes.length > 0 && !hierarchyCheckboxes.some((item) => item.checked)) {
                event.preventDefault();
                window.alert('Selecione ao menos um cargo para compor o GHE.');
                hierarchyCheckboxes[0].focus();
                return;
            }

            const button = $('button[type="submit"]', form);
            if (!button || button.disabled) return;

            button.disabled = true;
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando...';
        });
    });
})();
