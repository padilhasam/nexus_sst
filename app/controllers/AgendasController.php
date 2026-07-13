<?php

class AgendasController extends Controller
{
    private Agenda $agendaModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->agendaModel = $this->model('Agenda');
    }

    public function index(): void
    {
        $filtros = [
            'status' => strtoupper(trim($_GET['status'] ?? '')),
            'data_inicio' => trim($_GET['data_inicio'] ?? ''),
            'data_fim' => trim($_GET['data_fim'] ?? ''),

            'empresa_id' => filter_input(
                INPUT_GET,
                'empresa_id',
                FILTER_VALIDATE_INT
            ) ?: null,

            'tecnico_id' => filter_input(
                INPUT_GET,
                'tecnico_id',
                FILTER_VALIDATE_INT
            ) ?: null,
        ];

        $listas = $this->obterListasFormulario();

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
            'empresas' => $listas['empresas'],
            'usuarios' => $listas['usuarios'],
            'filtros' => $filtros,
        ]);
    }

    public function criar(): void
    {
        $listas = $this->obterListasFormulario();

        $this->view('agenda/criar', [
            'rotaAtual' => 'agenda',
            'pageTitle' => 'Novo Agendamento',
            'pageSubtitle' => 'Cadastre um novo agendamento técnico.',
            'css' => 'agenda.css',

            'empresas' => $listas['empresas'],
            'unidades' => $listas['unidades'],
            'usuarios' => $listas['usuarios'],
            'veiculos' => $listas['veiculos'],
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost('/agenda');

        $dados = $this->dadosFormulario();

        if (!$this->validarDados($dados)) {
            $this->redirecionar('/agenda/criar');
        }

        $conflito = $this->agendaModel->existeConflitoIntervalo(
            (int)$dados['tecnico_id'],
            $dados['veiculo_id'],
            $dados['data_agendada'],
            $dados['hora_inicio'],
            $dados['hora_fim']
        );

        if ($conflito) {
            $recurso = !empty($conflito['conflito_veiculo'])
                ? 'veículo'
                : 'técnico';

            $_SESSION['erro'] =
                'Conflito de agenda: o ' . $recurso .
                ' já possui um agendamento nesse intervalo.';

            $this->redirecionar('/agenda/criar');
        }

        try {
            $id = $this->agendaModel->salvar(
                $dados,
                (int)$_SESSION['usuario_id']
            );

            if (!$id) {
                throw new RuntimeException(
                    'Não foi possível cadastrar o agendamento.'
                );
            }

            $_SESSION['sucesso'] =
                'Agendamento cadastrado com sucesso.';

            $this->redirecionar(
                '/agenda/visualizar/' . $id
            );
        } catch (Throwable $erro) {
            $_SESSION['erro'] =
                'Erro ao cadastrar agendamento: ' .
                $erro->getMessage();

            $this->redirecionar('/agenda/criar');
        }
    }

    public function visualizar($id = null): void
    {
        $id = $this->validarId($id);

        $agendamento = $this->agendaModel->buscarPorId($id);

        if (!$agendamento) {
            $_SESSION['erro'] = 'Agendamento não encontrado.';
            $this->redirecionar('/agenda');
        }

        $this->view('agenda/visualizar', [
            'rotaAtual' => 'agenda',
            'pageTitle' => 'Detalhes do Agendamento',
            'pageSubtitle' =>
                'Consulta das informações do agendamento.',
            'css' => 'agenda.css',

            'agendamento' => $agendamento,

            /*
             * A tabela informada não possui agenda_historico.
             * Mantém a variável para não quebrar a view.
             */
            'historico' => [],
        ]);
    }

    public function editar($id = null): void
    {
        $id = $this->validarId($id);

        $agendamento = $this->agendaModel->buscarPorId($id);

        if (!$agendamento) {
            $_SESSION['erro'] = 'Agendamento não encontrado.';
            $this->redirecionar('/agenda');
        }

        $status = strtoupper(
            $agendamento['status'] ?? ''
        );

        if (in_array(
            $status,
            ['CANCELADO', 'CONCLUIDO', 'EXCLUIDO'],
            true
        )) {
            $_SESSION['erro'] =
                'Esse agendamento não pode mais ser editado.';

            $this->redirecionar(
                '/agenda/visualizar/' . $id
            );
        }

        $listas = $this->obterListasFormulario();

        $this->view('agenda/editar', [
            'rotaAtual' => 'agenda',
            'pageTitle' => 'Editar Agendamento',
            'pageSubtitle' =>
                'Atualize as informações do agendamento.',
            'css' => 'agenda.css',

            'agendamento' => $agendamento,
            'empresas' => $listas['empresas'],
            'unidades' => $listas['unidades'],
            'usuarios' => $listas['usuarios'],
            'veiculos' => $listas['veiculos'],
        ]);
    }

    public function atualizar($id = null): void
    {
        $this->exigirPost('/agenda');

        $id = $this->validarId($id);

        $agendamentoAtual =
            $this->agendaModel->buscarPorId($id);

        if (!$agendamentoAtual) {
            $_SESSION['erro'] = 'Agendamento não encontrado.';
            $this->redirecionar('/agenda');
        }

        $statusAtual = strtoupper(
            $agendamentoAtual['status'] ?? ''
        );

        if (in_array(
            $statusAtual,
            ['CANCELADO', 'CONCLUIDO', 'EXCLUIDO'],
            true
        )) {
            $_SESSION['erro'] =
                'Esse agendamento não pode mais ser alterado.';

            $this->redirecionar(
                '/agenda/visualizar/' . $id
            );
        }

        $dados = $this->dadosFormulario();

        if (!$this->validarDados($dados)) {
            $this->redirecionar(
                '/agenda/editar/' . $id
            );
        }

        $conflito = $this->agendaModel->existeConflitoIntervalo(
            (int)$dados['tecnico_id'],
            $dados['veiculo_id'],
            $dados['data_agendada'],
            $dados['hora_inicio'],
            $dados['hora_fim'],
            $id
        );

        if ($conflito) {
            $_SESSION['erro'] =
                'Conflito de agenda: o técnico ou o veículo já '
                . 'possui agendamento nesse intervalo.';

            $this->redirecionar(
                '/agenda/editar/' . $id
            );
        }

        try {
            $ok = $this->agendaModel->atualizar(
                $id,
                $dados,
                (int)$_SESSION['usuario_id']
            );

            $_SESSION[$ok ? 'sucesso' : 'erro'] = $ok
                ? 'Agendamento atualizado com sucesso.'
                : 'Não foi possível atualizar o agendamento.';

            $this->redirecionar(
                '/agenda/visualizar/' . $id
            );
        } catch (Throwable $erro) {
            $_SESSION['erro'] =
                'Erro ao atualizar agendamento: ' .
                $erro->getMessage();

            $this->redirecionar(
                '/agenda/editar/' . $id
            );
        }
    }

    public function cancelar($id = null): void
    {
        $this->exigirPost('/agenda');

        $id = $this->validarId($id);
        $motivo = trim($_POST['motivo'] ?? '');

        if ($motivo === '') {
            $_SESSION['erro'] =
                'Informe o motivo do cancelamento.';

            $this->redirecionar(
                '/agenda/visualizar/' . $id
            );
        }

        try {
            $ok = $this->agendaModel->cancelar(
                $id,
                $motivo,
                (int)$_SESSION['usuario_id']
            );

            $_SESSION[$ok ? 'sucesso' : 'erro'] = $ok
                ? 'Agendamento cancelado com sucesso.'
                : 'Não foi possível cancelar o agendamento.';

            $this->redirecionar(
                '/agenda/visualizar/' . $id
            );
        } catch (Throwable $erro) {
            $_SESSION['erro'] =
                'Erro ao cancelar agendamento: ' .
                $erro->getMessage();

            $this->redirecionar(
                '/agenda/visualizar/' . $id
            );
        }
    }

    public function excluir($id = null): void
    {
        $this->exigirPost('/agenda');

        $id = $this->validarId($id);
        $motivo = trim($_POST['motivo'] ?? '');

        if ($motivo === '') {
            $_SESSION['erro'] =
                'Informe o motivo da exclusão.';

            $this->redirecionar(
                '/agenda/visualizar/' . $id
            );
        }

        try {
            $ok = $this->agendaModel->excluir(
                $id,
                $motivo,
                (int)$_SESSION['usuario_id']
            );

            $_SESSION[$ok ? 'sucesso' : 'erro'] = $ok
                ? 'Agendamento excluído com sucesso.'
                : 'Não foi possível excluir o agendamento.';

            $this->redirecionar('/agenda');
        } catch (Throwable $erro) {
            $_SESSION['erro'] =
                'Erro ao excluir agendamento: ' .
                $erro->getMessage();

            $this->redirecionar(
                '/agenda/visualizar/' . $id
            );
        }
    }

    private function dadosFormulario(): array
    {
        return [
            'empresa_id' => filter_input(
                INPUT_POST,
                'empresa_id',
                FILTER_VALIDATE_INT
            ) ?: null,

            'unidade_id' => filter_input(
                INPUT_POST,
                'unidade_id',
                FILTER_VALIDATE_INT
            ) ?: null,

            'tecnico_id' => filter_input(
                INPUT_POST,
                'tecnico_id',
                FILTER_VALIDATE_INT
            ) ?: null,

            'veiculo_id' => filter_input(
                INPUT_POST,
                'veiculo_id',
                FILTER_VALIDATE_INT
            ) ?: null,

            'data_agendada' => trim(
                $_POST['data_agendada'] ?? ''
            ),

            'hora_inicio' => trim(
                $_POST['hora_inicio'] ?? ''
            ),

            'hora_fim' => trim(
                $_POST['hora_fim'] ?? ''
            ),

            'titulo' => trim(
                $_POST['titulo'] ?? ''
            ),

            'objetivo' => trim(
                $_POST['objetivo'] ?? ''
            ),

            'observacoes' => trim(
                $_POST['observacoes'] ?? ''
            ),

            'responsavel_acompanhamento' => trim(
                $_POST['responsavel_acompanhamento'] ?? ''
            ),

            'prioridade' => strtoupper(
                trim($_POST['prioridade'] ?? 'PADRAO')
            ),

            'status' => strtoupper(
                trim($_POST['status'] ?? 'AGENDADO')
            ),
        ];
    }

    private function validarDados(array $dados): bool
    {
        if (
            empty($dados['empresa_id']) ||
            empty($dados['tecnico_id']) ||
            empty($dados['data_agendada']) ||
            empty($dados['hora_inicio']) ||
            empty($dados['hora_fim'])
        ) {
            $_SESSION['erro'] =
                'Preencha empresa, técnico, data, horário inicial '
                . 'e horário final.';

            return false;
        }

        if (!$this->dataValida($dados['data_agendada'])) {
            $_SESSION['erro'] = 'Informe uma data válida.';
            return false;
        }

        if (
            !$this->horaValida($dados['hora_inicio']) ||
            !$this->horaValida($dados['hora_fim'])
        ) {
            $_SESSION['erro'] = 'Informe horários válidos.';
            return false;
        }

        if ($dados['hora_inicio'] >= $dados['hora_fim']) {
            $_SESSION['erro'] =
                'O horário final deve ser maior que o inicial.';

            return false;
        }

        if (!in_array(
            $dados['prioridade'],
            ['PADRAO', 'URGENTE', 'CRITICA'],
            true
        )) {
            $_SESSION['erro'] = 'Prioridade inválida.';
            return false;
        }

        if (!in_array(
            $dados['status'],
            [
                'AGENDADO',
                'CONFIRMADO',
                'REAGENDADO',
                'CANCELADO',
                'CONCLUIDO',
            ],
            true
        )) {
            $_SESSION['erro'] = 'Status inválido.';
            return false;
        }

        return true;
    }

    private function obterListasFormulario(): array
    {
        $db = (new Database())->getConnection();

        return [
            'empresas' => $db->query("
                SELECT
                    id,
                    razao_social,
                    nome_fantasia
                FROM empresas
                ORDER BY
                    COALESCE(NULLIF(nome_fantasia, ''), razao_social)
            ")->fetchAll(PDO::FETCH_ASSOC),

            'unidades' => $db->query("
                SELECT
                    id,
                    empresa_id,
                    nome
                FROM unidades
                ORDER BY nome
            ")->fetchAll(PDO::FETCH_ASSOC),

            'usuarios' => $db->query("
                SELECT
                    id,
                    nome
                FROM usuarios
                ORDER BY nome
            ")->fetchAll(PDO::FETCH_ASSOC),

            'veiculos' => $db->query("
                SELECT
                    id,
                    modelo,
                    placa
                FROM veiculos
                ORDER BY modelo, placa
            ")->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    private function validarId($id): int
    {
        $id = filter_var(
            $id,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id <= 0) {
            $_SESSION['erro'] =
                'Identificador do agendamento inválido.';

            $this->redirecionar('/agenda');
        }

        return (int)$id;
    }

    private function exigirPost(string $retorno): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar($retorno);
        }
    }

    private function dataValida(string $data): bool
    {
        $objeto = DateTime::createFromFormat(
            'Y-m-d',
            $data
        );

        return $objeto !== false
            && $objeto->format('Y-m-d') === $data;
    }

    private function horaValida(string $hora): bool
    {
        return preg_match(
            '/^(?:[01]\d|2[0-3]):[0-5]\d$/',
            $hora
        ) === 1;
    }

    private function redirecionar(string $rota): void
    {
        header('Location: ' . BASE_URL . $rota);
        exit;
    }
}