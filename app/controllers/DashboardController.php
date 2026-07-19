<?php

class DashboardController extends Controller
{
    private $dashboardModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->dashboardModel = $this->model('Dashboard');
    }

    public function index(): void
    {
        $this->view('dashboard/index', [
            /*
             * Configurações globais da página.
             */
            'rotaAtual' => 'dashboard',

            'pageTitle' => 'Dashboard',

            'pageSubtitle' =>
                'Visão geral dos indicadores, atividades e operações do Nexus SST.',

            'breadcrumbs' => [
                [
                    'label' => 'Dashboard',
                    'url' => null,
                ],
            ],

            'css' => 'dashboard.css',

            /*
             * Indicadores cadastrais.
             */
            'empresas_total' =>
                $this->dashboardModel->contar('empresas'),

            'unidades_total' =>
                $this->dashboardModel->contar('unidades'),

            'setores_total' =>
                $this->dashboardModel->contar('setores'),

            'cargos_total' =>
                $this->dashboardModel->contar('cargos'),

            /*
             * Indicadores operacionais.
             */
            'visitas_hoje' => 0,
            'levantamentos_andamento' => 0,
            'quantificacoes_pendentes' => 0,
            'nao_conformidades' => 0,

            'levantamentos_mes' => 0,
            'riscos_aplicados_mes' => 0,
            'relatorios_pendentes' => 0,
            'visitas_sem_levantamento' => 0,
            'planos_acao_abertos' => 0,

            /*
             * Listagens.
             */
            'lista_visitas_hoje' => [],
            'tecnicos_operacao' => [],
            'trabalhos_andamento' => [],

            /*
             * Gráfico de visitas por mês.
             */
            'visitas_mes' => [
                'labels' => [
                    'Jan',
                    'Fev',
                    'Mar',
                    'Abr',
                    'Mai',
                    'Jun',
                ],

                'dados' => [
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                ],
            ],

            /*
             * Gráfico de riscos por categoria.
             */
            'riscos_categoria' => [
                'labels' => [
                    'Físicos',
                    'Químicos',
                    'Biológicos',
                    'Ergonômicos',
                    'Acidentes',
                ],

                'dados' => [
                    0,
                    0,
                    0,
                    0,
                    0,
                ],
            ],
        ]);
    }
}