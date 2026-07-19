<?php

class VisitasController extends Controller
{
    private Visita $visitaModel;

    private const ABAS_VALIDAS = [
        'abertas',
        'concluidas',
        'todas',
    ];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->visitaModel = $this->model('Visita');
    }

    public function index(): void
    {
        $aba = strtolower(trim((string)($_GET['aba'] ?? 'abertas')));
        if (!in_array($aba, self::ABAS_VALIDAS, true)) {
            $aba = 'abertas';
        }

        $filtros = [
            'prioridade' => strtoupper(trim((string)($_GET['prioridade'] ?? ''))),
            'data_inicio' => $this->normalizarData((string)($_GET['data_inicio'] ?? '')),
            'data_fim' => $this->normalizarData((string)($_GET['data_fim'] ?? '')),
        ];

        $usuarioId = $this->usuarioLogadoId();
        $tipoUsuario = $this->tipoUsuario();

        $this->view('visitas/index', [
            'visitas' => $this->visitaModel->listarFila(
                $usuarioId,
                $tipoUsuario,
                $aba,
                $filtros
            ),
            'indicadores' => $this->visitaModel->obterIndicadores(
                $usuarioId,
                $tipoUsuario
            ),
            'abaAtual' => $aba,
            'filtros' => $filtros,
            'usuarioAdministrador' => $this->usuarioAdministrador(),
        ]);
    }

    /**
     * A visita nasce automaticamente no módulo Agenda.
     */
    public function criar(): never
    {
        $_SESSION['info'] = 'Cadastre o compromisso pela Agenda. A visita técnica será criada automaticamente.';
        $this->redirecionar('/agenda/criar');
    }

    public function salvar(): never
    {
        $_SESSION['erro'] = 'A criação direta de visitas foi desativada. Utilize o módulo Agenda.';
        $this->redirecionar('/agenda/criar');
    }

    public function visualizar($id = null): void
    {
        $id = $this->validarId($id ?? $_GET['id'] ?? null);
        $visita = $this->visitaModel->buscarPorId($id);

        if (!$visita) {
            $_SESSION['erro'] = 'Visita técnica não encontrada.';
            $this->redirecionar('/visitas');
        }

        if (!$this->visitaModel->usuarioPodeAcessar(
            $visita,
            $this->usuarioLogadoId(),
            $this->tipoUsuario()
        )) {
            $_SESSION['erro'] = 'Você não possui permissão para acessar esta visita técnica.';
            $this->redirecionar('/visitas');
        }

        $this->view('visitas/visualizar', [
            'visita' => $visita,
            'historico' => $this->visitaModel->listarHistorico($id),
            'podeIniciarChecklist' => $this->visitaModel->podeIniciarChecklist($visita),
        ]);
    }

    /**
     * Os dados da visita vinculada são administrados pela Agenda, evitando
     * duas fontes diferentes para data, técnico, empresa e veículo.
     */
    public function editar($id = null): never
    {
        $id = $this->validarId($id ?? $_GET['id'] ?? null);
        $visita = $this->visitaModel->buscarPorId($id);

        if (!$visita) {
            $_SESSION['erro'] = 'Visita técnica não encontrada.';
            $this->redirecionar('/visitas');
        }

        if (!$this->visitaModel->usuarioPodeAcessar(
            $visita,
            $this->usuarioLogadoId(),
            $this->tipoUsuario()
        )) {
            $_SESSION['erro'] = 'Você não possui permissão para alterar esta visita técnica.';
            $this->redirecionar('/visitas');
        }

        if (!empty($visita['agenda_ref_id'])) {
            $this->redirecionar('/agenda/editar/' . (int)$visita['agenda_ref_id']);
        }

        $_SESSION['erro'] = 'Esta visita antiga não está vinculada a um agendamento.';
        $this->redirecionar('/visitas/visualizar/' . $id);
    }

    public function atualizar($id = null): never
    {
        $_SESSION['erro'] = 'Atualize os dados pelo módulo Agenda para manter a sincronização.';

        if ($id !== null && filter_var($id, FILTER_VALIDATE_INT)) {
            $this->redirecionar('/visitas/editar/' . (int)$id);
        }

        $this->redirecionar('/visitas');
    }

    public function atualizarData(): never
    {
        http_response_code(409);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => 'A data deve ser alterada pela operação Reagendar da Agenda.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function atualizarStatus(): never
    {
        $_SESSION['erro'] = 'O status da visita é alterado automaticamente pelo fluxo do check-list.';
        $this->redirecionar('/visitas');
    }

    public function cancelar($id = null): never
    {
        $id = $this->validarId($id ?? $_GET['id'] ?? null);
        $visita = $this->visitaModel->buscarPorId($id);

        if ($visita && !empty($visita['agenda_ref_id'])) {
            $_SESSION['info'] = 'O cancelamento deve ser realizado na Agenda, com motivo e histórico.';
            $this->redirecionar('/agenda/visualizar/' . (int)$visita['agenda_ref_id']);
        }

        $_SESSION['erro'] = 'Não foi possível localizar o agendamento vinculado.';
        $this->redirecionar('/visitas');
    }

    public function excluir($id = null): never
    {
        $id = $this->validarId($id ?? $_GET['id'] ?? null);
        $visita = $this->visitaModel->buscarPorId($id);

        if ($visita && !empty($visita['agenda_ref_id'])) {
            $_SESSION['info'] = 'A exclusão deve ser realizada na Agenda, com motivo e histórico.';
            $this->redirecionar('/agenda/visualizar/' . (int)$visita['agenda_ref_id']);
        }

        $_SESSION['erro'] = 'Não foi possível localizar o agendamento vinculado.';
        $this->redirecionar('/visitas');
    }

    private function validarId($id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            $_SESSION['erro'] = 'Identificador da visita técnica inválido.';
            $this->redirecionar('/visitas');
        }

        return (int)$id;
    }

    private function normalizarData(string $data): string
    {
        $data = trim($data);
        if ($data === '') {
            return '';
        }

        $objeto = DateTime::createFromFormat('Y-m-d', $data);
        return $objeto !== false && $objeto->format('Y-m-d') === $data
            ? $data
            : '';
    }

    private function usuarioLogadoId(): int
    {
        return (int)$_SESSION['usuario_id'];
    }

    private function tipoUsuario(): string
    {
        return strtoupper(trim((string)($_SESSION['tipo'] ?? '')));
    }

    private function usuarioAdministrador(): bool
    {
        return in_array($this->tipoUsuario(), ['ADMIN', 'ADMINISTRADOR'], true);
    }

    private function redirecionar(string $rota): never
    {
        header('Location: ' . BASE_URL . $rota);
        exit;
    }
}
