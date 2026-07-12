<?php

class AgendasController extends Controller
{
    private Agenda $agendaModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->agendaModel = $this->model('Agenda');
    }

    public function index()
    {
        $filtros = [
            'status' => trim($_GET['status'] ?? ''),
            'data_inicio' => trim($_GET['data_inicio'] ?? ''),
            'data_fim' => trim($_GET['data_fim'] ?? ''),
            'empresa_id' => filter_input(INPUT_GET, 'empresa_id', FILTER_VALIDATE_INT) ?: '',
            'tecnico_id' => filter_input(INPUT_GET, 'tecnico_id', FILTER_VALIDATE_INT) ?: '',
        ];

        $db = (new Database())->getConnection();

        $empresas = $db->query("
            SELECT
                id,
                razao_social,
                nome_fantasia
            FROM empresas
            ORDER BY razao_social ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $usuarios = $db->query("
            SELECT
                id,
                nome
            FROM usuarios
            ORDER BY nome ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $agendamentos = $this->agendaModel->listarTodos($filtros);
        $eventos = $this->agendaModel->eventosCalendario($filtros);
        $indicadores = $this->agendaModel->obterIndicadores($filtros);

        $eventosJson = json_encode(
            $eventos,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($eventosJson === false) {
            $eventosJson = '[]';
        }

        $this->view('agenda/index', [
            'rotaAtual' => 'agenda',
            'pageTitle' => 'Agenda',

            'pageSubtitle' =>
                'Calendário e acompanhamento completo dos agendamentos técnicos.',

            'breadcrumbs' => [
                [
                    'label' => 'Dashboard',
                    'url' => BASE_URL . '/dashboard',
                ],
                [
                    'label' => 'Operação',
                    'url' => null,
                ],
                [
                    'label' => 'Agenda',
                    'url' => null,
                ],
            ],

            'pageActionUrl' => BASE_URL . '/agenda/criar',
            'pageActionLabel' => 'Novo Agendamento',
            'pageActionIcon' => 'fa-solid fa-plus',

            'css' => 'agenda.css',

            'agendamentos' => $agendamentos,
            'eventosJson' => $eventosJson,
            'indicadores' => $indicadores,
            'empresas' => $empresas,
            'usuarios' => $usuarios,
            'filtros' => $filtros,
        ]);
    }

    public function criar()
    {
        $db = (new Database())->getConnection();

        $this->view('agenda/criar', [
            'empresas' => $db->query("SELECT id, razao_social, nome_fantasia FROM empresas ORDER BY razao_social ASC")->fetchAll(PDO::FETCH_ASSOC),
            'unidades' => $db->query("SELECT id, empresa_id, nome FROM unidades ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC),
            'usuarios' => $db->query("SELECT id, nome FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC),
            'veiculos' => $db->query("SELECT id, modelo, placa FROM veiculos ORDER BY modelo ASC")->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/agenda');
            exit;
        }

        $dados = $this->dadosFormulario();

        if (!$this->validar($dados, '/agenda/criar')) {
            return;
        }

        $conflito = $this->agendaModel->existeConflitoIntervalo(
            (int)$dados['tecnico_id'],
            !empty($dados['veiculo_id'])
                ? (int)$dados['veiculo_id']
                : null,
            $dados['data_agendada'],
            $dados['hora_inicio'],
            $dados['hora_fim']
        );

        if ($conflito) {
            $_SESSION['erro'] = 'Conflito de agenda: técnico ou veículo já possui agendamento neste intervalo.';
            header('Location: ' . BASE_URL . '/agenda/criar');
            exit;
        }

        $ok = $this->agendaModel->salvar($dados);
        $_SESSION[$ok ? 'sucesso' : 'erro'] = $ok ? 'Agendamento criado com sucesso.' : 'Erro ao criar agendamento.';
        header('Location: ' . BASE_URL . '/agenda');
        exit;
    }

    public function editar($id = null)
    {
        $id = (int)($id ?? $_GET['id'] ?? 0);
        $agendamento = $this->agendaModel->buscarPorId($id);

        if (!$agendamento) {
            $_SESSION['erro'] = 'Agendamento não encontrado.';
            header('Location: ' . BASE_URL . '/agenda');
            exit;
        }

        $db = (new Database())->getConnection();

        $this->view('agenda/editar', [
            'agendamento' => $agendamento,
            'empresas' => $db->query("SELECT id, razao_social, nome_fantasia FROM empresas ORDER BY razao_social ASC")->fetchAll(PDO::FETCH_ASSOC),
            'unidades' => $db->query("SELECT id, empresa_id, nome FROM unidades ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC),
            'usuarios' => $db->query("SELECT id, nome FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC),
            'veiculos' => $db->query("SELECT id, modelo, placa FROM veiculos ORDER BY modelo ASC")->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    public function atualizar($id = null)
    {
        $id = (int)($id ?? $_GET['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->agendaModel->buscarPorId($id)) {
            $_SESSION['erro'] = 'Agendamento não encontrado.';
            header('Location: ' . BASE_URL . '/agenda');
            exit;
        }

        $dados = $this->dadosFormulario();
        $dados['motivo'] = trim($_POST['motivo'] ?? '');

        if (empty($dados['motivo'])) {
            $_SESSION['erro'] = 'Informe o motivo da alteração.';
            header('Location: ' . BASE_URL . '/agenda/editar/' . $id);
            exit;
        }

        if (!$this->validar($dados, '/agenda/editar/' . $id)) {
            return;
        }

        $conflito = $this->agendaModel->existeConflitoIntervalo(
            (int)$dados['tecnico_id'],
            !empty($dados['veiculo_id'])
                ? (int)$dados['veiculo_id']
                : null,
            $dados['data_agendada'],
            $dados['hora_inicio'],
            $dados['hora_fim'],
            $id
        );

        if ($conflito) {
            $_SESSION['erro'] = 'Conflito de agenda: técnico ou veículo já possui agendamento neste intervalo.';
            header('Location: ' . BASE_URL . '/agenda/editar/' . $id);
            exit;
        }

        $ok = $this->agendaModel->atualizar($id, $dados);
        $_SESSION[$ok ? 'sucesso' : 'erro'] = $ok ? 'Agendamento atualizado.' : 'Erro ao atualizar agendamento.';
        header('Location: ' . BASE_URL . '/agenda');
        exit;
    }

    public function visualizar($id = null)
    {
        $id = (int)($id ?? $_GET['id'] ?? 0);
        $agendamento = $this->agendaModel->buscarPorId($id);

        if (!$agendamento) {
            $_SESSION['erro'] = 'Agendamento não encontrado.';
            header('Location: ' . BASE_URL . '/agenda');
            exit;
        }

        $this->view('agenda/visualizar', [
            'agendamento' => $agendamento,
            'historico' => $this->agendaModel->listarHistorico($id),
        ]);
    }

    public function cancelar($id = null)
    {
        $id = (int)($id ?? $_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');

        if (!$id || empty($motivo)) {
            $_SESSION['erro'] = 'Informe o motivo do cancelamento.';
            header('Location: ' . BASE_URL . '/agenda');
            exit;
        }

        $ok = $this->agendaModel->cancelar($id, $motivo);
        $_SESSION[$ok ? 'sucesso' : 'erro'] = $ok ? 'Agendamento cancelado.' : 'Erro ao cancelar agendamento.';
        header('Location: ' . BASE_URL . '/agenda');
        exit;
    }

    public function excluir($id = null)
    {
        $id = (int)($id ?? $_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');

        if (!$id || empty($motivo)) {
            $_SESSION['erro'] = 'Informe o motivo da exclusão.';
            header('Location: ' . BASE_URL . '/agenda');
            exit;
        }

        $ok = $this->agendaModel->excluir($id, $motivo);
        $_SESSION[$ok ? 'sucesso' : 'erro'] = $ok ? 'Agendamento excluído.' : 'Erro ao excluir agendamento.';
        header('Location: ' . BASE_URL . '/agenda');
        exit;
    }

    private function dadosFormulario(): array
    {
        return [
            'empresa_id' => filter_input(
                INPUT_POST,
                'empresa_id',
                FILTER_VALIDATE_INT
            ),

            'unidade_id' => filter_input(
                INPUT_POST,
                'unidade_id',
                FILTER_VALIDATE_INT
            ) ?: null,

            'tecnico_id' => filter_input(
                INPUT_POST,
                'tecnico_id',
                FILTER_VALIDATE_INT
            ),

            'veiculo_id' => filter_input(
                INPUT_POST,
                'veiculo_id',
                FILTER_VALIDATE_INT
            ) ?: null,

            'data_agendada' => trim($_POST['data_agendada'] ?? ''),
            'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
            'hora_fim' => trim($_POST['hora_fim'] ?? ''),

            'titulo' => trim($_POST['titulo'] ?? ''),
            'responsavel_acompanhamento' => trim(
                $_POST['responsavel_acompanhamento'] ?? ''
            ),

            'objetivo' => trim($_POST['objetivo'] ?? ''),
            'observacoes' => trim($_POST['observacoes'] ?? ''),

            'prioridade' => trim($_POST['prioridade'] ?? 'PADRAO'),
            'status' => trim($_POST['status'] ?? 'AGENDADO'),
        ];
    }

    private function validar(array $dados, string $retorno): bool
    {
        if (
            empty($dados['empresa_id']) ||
            empty($dados['tecnico_id']) ||
            empty($dados['data_agendada']) ||
            empty($dados['hora_inicio']) ||
            empty($dados['hora_fim'])
        ) {
            $_SESSION['erro'] =
                'Preencha empresa, técnico, data, horário inicial e horário final.';

            header('Location: ' . BASE_URL . $retorno);
            exit;
        }

        if ($dados['hora_inicio'] >= $dados['hora_fim']) {
            $_SESSION['erro'] =
                'O horário final deve ser maior que o horário inicial.';

            header('Location: ' . BASE_URL . $retorno);
            exit;
        }

        return true;
    }
}
