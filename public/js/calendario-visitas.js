window.calendar = null;

function getPrioridadeColor(prioridade) {
    switch (prioridade) {
        case 'EMERGENCIA': return '#212529';
        case 'URGENTE':    return '#dc3545';
        case 'ALTA':       return '#fd7e14';
        case 'NORMAL':     return '#198754';
        case 'BAIXA':      return '#0d6efd';
        default:           return '#6c757d';
    }
}

function getStatusColor(status) {
    switch (status) {
        case 'FINALIZADA': return '#198754';
        case 'CANCELADA':  return '#dc3545';
        case 'CHECKLIST_INICIADO': return '#6610f2';
        case 'EM_ANDAMENTO': return '#0d6efd';
        default: return '#ffc107';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const eventosFormatados = (window.eventosData || []).map(evento => {
        const prioridade = evento.extendedProps?.prioridade || 'NORMAL';
        const status = evento.extendedProps?.status || 'ABERTA';

        const cor = ['CANCELADA', 'FINALIZADA'].includes(status)
            ? getStatusColor(status)
            : getPrioridadeColor(prioridade);

        return {
            ...evento,
            backgroundColor: cor,
            borderColor: cor,
            textColor: '#ffffff'
        };
    });

    window.calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: window.innerWidth <= 640 ? 'listWeek' : 'dayGridMonth',
        locale: 'pt-br',
        themeSystem: 'bootstrap5',
        height: 'auto',
        headerToolbar: { 
            left: 'prev,next today', 
            center: 'title', 
            right: 'dayGridMonth,timeGridWeek' 
        },
        events: eventosFormatados,
        editable: true, 
        droppable: true,
        dayMaxEvents: true,

        eventDrop: function(info) {
            const dataFormatada = info.event.startStr.split('T')[0];

            $.ajax({
                url: window.baseUrl + '/visitas/atualizarData',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: info.event.id,
                    nova_data: dataFormatada
                },
                success: function(response) {
                    if (response.status === 'success') {
                        showToast(response.mensagem || 'Data atualizada com sucesso.', 'success');
                    } else {
                        showToast(response.mensagem || 'Erro ao atualizar data.', 'danger');
                        info.revert();
                    }
                },
                error: function() {
                    showToast('Erro ao conectar com o servidor.', 'danger');
                    info.revert();
                }
            });
        },

        dateClick: function(info) {
            if (window.baseUrl) {
                window.location.href = `${window.baseUrl}/visitas/criar?data=${info.dateStr}`;
            }
        },

        eventClick: function(info) {
            info.jsEvent.preventDefault();
            if (info.event.url) {
                window.location.href = info.event.url;
            }
        },

        windowResize: function () {
            if (window.innerWidth <= 640) {
                if (calendar.view.type !== 'listWeek') {
                    calendar.changeView('listWeek');
                }
            } else if (calendar.view.type === 'listWeek') {
                calendar.changeView('dayGridMonth');
            }

            calendar.updateSize();
        }
    });

    window.calendar.render();
});