<?php

class DashboardController extends Controller
{
    private $dashboardModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->dashboardModel = $this->model('Dashboard');
    }

    public function index()
    {
        $this->view('dashboard/index', [
            'empresas_total' => $this->dashboardModel->contar('empresas'),
            'unidades_total' => $this->dashboardModel->contar('unidades'),
            'setores_total' => $this->dashboardModel->contar('setores'),
            'cargos_total' => $this->dashboardModel->contar('cargos'),

            'visitas_hoje' => 0,
            'levantamentos_andamento' => 0,
            'quantificacoes_pendentes' => 0,
            'nao_conformidades' => 0,

            'levantamentos_mes' => 0,
            'riscos_aplicados_mes' => 0,
            'relatorios_pendentes' => 0,
            'visitas_sem_levantamento' => 0,
            'planos_acao_abertos' => 0,

            'lista_visitas_hoje' => [],
            'tecnicos_operacao' => [],
            'trabalhos_andamento' => [],

            'visitas_mes' => [
                'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                'dados' => [0, 0, 0, 0, 0, 0]
            ],

            'riscos_categoria' => [
                'labels' => ['Físicos', 'Químicos', 'Biológicos', 'Ergonômicos', 'Acidentes'],
                'dados' => [0, 0, 0, 0, 0]
            ]
        ]);
    }
}

// class DashboardController extends AuthController {
//     public function index() {
//         $this->view('dashboard/index', [
//             'usuario' => $_SESSION['nome'] ?? 'Usuário'
//         ]);
//     }
// }