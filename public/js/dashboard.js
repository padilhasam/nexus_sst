const btnToggleSidebar = document.getElementById('btnToggleSidebar');
const sidebar = document.getElementById('sidebar');

if (btnToggleSidebar && sidebar) {
     btnToggleSidebar.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });
}

document.addEventListener('DOMContentLoaded', () => {

    if (document.getElementById('chartVisitas')) {

        new Chart(document.getElementById('chartVisitas'), {

            type: 'line',

            data: {
                labels: window.visitasMes.labels,
                datasets: [{
                    label: 'Levantamentos',
                    data: window.visitasMes.dados,
                    borderWidth: 3,
                    tension: .35,
                    fill: true
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false
            }

        });

    }

    if (document.getElementById('chartRiscos')) {

        new Chart(document.getElementById('chartRiscos'), {

            type: 'doughnut',

            data: {
                labels: window.riscosCategoria.labels,
                datasets: [{
                    data: window.riscosCategoria.dados
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false
            }

        });

    }

});