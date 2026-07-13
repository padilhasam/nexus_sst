<?php
$css = 'dashboard.css';

require_once dirname(__DIR__) . '/templates/header.php';
?>

<div class="dashboard-toolbar">

    <div class="dashboard-title">
        <h2>Indicadores</h2>
        <small>Acompanhamento operacional em tempo real</small>
    </div>

    <div class="dashboard-actions">

        <div class="period-filter">

            <i class="fa-regular fa-calendar"></i>

            <input
                type="date"
                name="data_inicio"
                value="<?= date('Y-m-01') ?>"
            >

            <span>até</span>

            <input
                type="date"
                name="data_fim"
                value="<?= date('Y-m-t') ?>"
            >

        </div>

        <button class="btn btn-primary">
            <i class="fa-solid fa-filter"></i>
            Atualizar
        </button>

    </div>

</div>

<section class="kpi-grid">

    <article class="kpi-card">
        <div class="kpi-icon kpi-blue">
            <i class="fa-regular fa-calendar-check"></i>
        </div>

        <div>
            <small>Visitas Agendadas</small>
            <strong>28</strong>
            <div class="trend">
                ▲ 12% vs período anterior
            </div>
        </div>
    </article>

    <article class="kpi-card">
        <div class="kpi-icon kpi-green">
            <i class="fa-regular fa-square-check"></i>
        </div>

        <div>
            <small>Visitas Concluídas</small>
            <strong>18</strong>
            <div class="trend">
                ▲ 8% vs período anterior
            </div>
        </div>
    </article>

    <article class="kpi-card">
        <div class="kpi-icon kpi-orange">
            <i class="fa-regular fa-clipboard"></i>
        </div>

        <div>
            <small>
                Check-lists<br>
                em Andamento
            </small>

            <strong>9</strong>

            <div class="trend down">
                ▼ 5% vs período anterior
            </div>
        </div>
    </article>

    <article class="kpi-card">
        <div class="kpi-icon kpi-purple">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <div>
            <small>
                Não Conformidades<br>
                Abertas
            </small>

            <strong>24</strong>

            <div class="trend">
                ▲ 15% vs período anterior
            </div>
        </div>
    </article>

    <article class="kpi-card">
        <div class="kpi-icon kpi-red">
            <i class="fa-regular fa-calendar-days"></i>
        </div>

        <div>
            <small>
                Quantificações<br>
                Pendentes
            </small>

            <strong>16</strong>

            <div class="trend down">
                ▼ 3% vs período anterior
            </div>
        </div>
    </article>

</section>

<section class="dash-grid desktop-only">

    <article class="panel dashboard-chart-card dashboard-status-card">
        <h2>Visitas por Status</h2>

        <div class="donut-row">

            <div class="donut"></div>

            <div class="legend">
                <span class="l-blue">Agendada — 28 (35%)</span>
                <span class="l-green">Em Andamento — 15 (19%)</span>
                <span class="l-yellow">Concluída — 18 (23%)</span>
                <span class="l-orange">Atrasada — 10 (13%)</span>
                <span class="l-red">Cancelada — 9 (10%)</span>
            </div>

        </div>

    </article>

    <article class="panel">

        <h2>
            Próximas Visitas

            <a href="<?= BASE_URL ?>/visitas">
                Ver todas
            </a>
        </h2>

        <div class="visit-list">

            <div class="visit-row">
                <div class="date-box">
                    22
                    <span>MAI</span>
                </div>

                <div class="time">
                    08:00<br>
                    08:00
                </div>

                <div>
                    <strong>Empresa ABC Ltda</strong>
                    <small>
                        Unidade Matriz<br>
                        Padrão
                    </small>
                </div>

                <span class="badge-soft b-blue">
                    Padrão
                </span>
            </div>

            <div class="visit-row">
                <div class="date-box">
                    22
                    <span>MAI</span>
                </div>

                <div class="time">
                    09:00<br>
                    14:00
                </div>

                <div>
                    <strong>Indústria XYZ S.A.</strong>
                    <small>
                        Unidade 02<br>
                        Padrão
                    </small>
                </div>

                <span class="badge-soft b-red">
                    Urgente
                </span>
            </div>

            <div class="visit-row">
                <div class="date-box">
                    23
                    <span>MAI</span>
                </div>

                <div class="time">
                    08:30<br>
                    08:30
                </div>

                <div>
                    <strong>Comércio 123 Ltda</strong>
                    <small>
                        Unidade Centro<br>
                        Padrão
                    </small>
                </div>

                <span class="badge-soft b-blue">
                    Padrão
                </span>
            </div>

            <div class="visit-row">
                <div class="date-box">
                    24
                    <span>MAI</span>
                </div>

                <div class="time">
                    10:00<br>
                    10:00
                </div>

                <div>
                    <strong>Obras e Serviços Ltda</strong>
                    <small>Unidade SP</small>
                </div>

                <span class="badge-soft b-blue">
                    Padrão
                </span>
            </div>

        </div>

    </article>

    <article class="panel dashboard-chart-card dashboard-gravity-card">
        <h2>Não Conformidades por Gravidade</h2>

        <div class="bar-chart">
            <div
                class="bar"
                style="height: 90px; background: #f04438;"
            >
                <span>6</span>
                <small>Crítica</small>
            </div>

            <div
                class="bar"
                style="height: 132px; background: #f79009;"
            >
                <span>9</span>
                <small>Alta</small>
            </div>

            <div
                class="bar"
                style="height: 108px; background: #f4bd16;"
            >
                <span>7</span>
                <small>Média</small>
            </div>

            <div
                class="bar"
                style="height: 54px; background: #12b76a;"
            >
                <span>2</span>
                <small>Baixa</small>
            </div>

        </div>

    </article>

</section>

<section class="activity-grid desktop-only">

    <article class="panel">

        <h2>
            Atividades Recentes

            <a href="<?= BASE_URL ?>/historico">
                Ver todas
            </a>
        </h2>

        <div class="activity">

            <div class="activity-item">
                <i class="fa-regular fa-square-check"></i>

                <div>
                    <strong>
                        Check-list iniciado para a visita — Empresa ABC Ltda,
                        Unidade Matriz
                    </strong>

                    <br>

                    <small>
                        João Silva • 22/05/2024 08:45
                    </small>
                </div>
            </div>

            <div class="activity-item">
                <i
                    class="fa-solid fa-triangle-exclamation"
                    style="background: #fff0f3; color: #e11d48;"
                ></i>

                <div>
                    <strong>
                        Não conformidade criada na visita Indústria XYZ S.A.,
                        Unidade 02
                    </strong>

                    <br>

                    <small>
                        Maria Santos • 22/05/2024 10:15
                    </small>
                </div>
            </div>

            <div class="activity-item">
                <i
                    class="fa-solid fa-vial"
                    style="background: #e8fff3; color: #10b981;"
                ></i>

                <div>
                    <strong>
                        Quantificação concluída para exposição a Ruído —
                        Setor Produção
                    </strong>

                    <br>

                    <small>
                        João Silva • 22/05/2024 11:30
                    </small>
                </div>
            </div>

            <div class="activity-item">
                <i class="fa-regular fa-calendar"></i>

                <div>
                    <strong>
                        Visita agendada para Comércio 123 Ltda —
                        Unidade Centro
                    </strong>

                    <br>

                    <small>
                        Carlos Oliveira • 22/05/2024 14:20
                    </small>
                </div>
            </div>

        </div>

    </article>

    <article class="panel">

        <h2>
            Mensagens

            <a href="<?= BASE_URL ?>/mensagens">
                Ver todas
            </a>
        </h2>

        <div class="message-row">
            <img
                src="https://i.pravatar.cc/60?img=11"
                alt="Carlos Oliveira"
            >

            <div>
                <strong>Carlos Oliveira</strong>
                <small>Sobre a visita de amanhã</small>
            </div>

            <small>10:30</small>
        </div>

        <div class="message-row">
            <img
                src="https://i.pravatar.cc/60?img=12"
                alt="Maria Santos"
            >

            <div>
                <strong>Maria Santos</strong>
                <small>Documentos atualizados</small>
            </div>

            <small>Ontem</small>
        </div>

        <div class="message-row">
            <img
                src="https://i.pravatar.cc/60?img=13"
                alt="Gestor Regional"
            >

            <div>
                <strong>Gestor Regional</strong>
                <small>Reunião mensal de indicadores</small>
            </div>

            <small>20/05</small>
        </div>

    </article>

</section>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>