function renderBarChart(id, label, value, color) {
    const canvas = document.getElementById(id);
    if (!canvas || typeof Chart === 'undefined') return;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: [label],
            datasets: [{
                data: [value],
                backgroundColor: color,
                borderRadius: 6,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            },
            scales: {
                x: { display: false },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    renderBarChart('chartEmpresas', 'Empresas', window.empresas ?? 0, '#0d6efd');
    renderBarChart('chartDocumentos', 'Documentos', window.documentos ?? 0, '#198754');
    renderBarChart('chartRiscos', 'Riscos', window.riscos ?? 0, '#dc3545');
    renderBarChart('chartQuantificacao', 'Quantificação', window.quantificacoes ?? 0, '#ffc107');
    renderBarChart('chartEpis', 'EPI / EPC', window.epis ?? 0, '#6c757d');
    renderBarChart('chartPets', 'PET', window.pets ?? 0, '#111827');
});
