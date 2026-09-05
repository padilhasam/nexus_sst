<?php

class HierarquiasController extends Controller
{
    private Hierarquia $hierarquiaModel;
    private Empresa $empresaModel;
    private Unidade $unidadeModel;
    private Setor $setorModel;
    private Cargo $cargoModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->hierarquiaModel = $this->model('Hierarquia');
        $this->empresaModel = $this->model('Empresa');
        $this->unidadeModel = $this->model('Unidade');
        $this->setorModel = $this->model('Setor');
        $this->cargoModel = $this->model('Cargo');
    }

    public function index(): void
    {
        $this->view('hierarquias/index', [
            'empresasEstruturadas' => $this->hierarquiaModel->listarEmpresasEstruturadas(),
            'total_empresas' => $this->hierarquiaModel->contarEmpresas(),
            'total_unidades' => $this->hierarquiaModel->contarUnidades(),
            'total_setores' => $this->hierarquiaModel->contarSetores(),
            'total_cargos' => $this->hierarquiaModel->contarCargos(),
        ]);
    }

    public function criar(): void
    {
        $this->view('hierarquias/criar', $this->dadosFormulario());
    }

    public function salvar(): never
    {
        $this->prepararPost();
        try {
            $resultado = $this->hierarquiaModel->salvarEmLote($_POST);
            $mensagem = sprintf(
                '%d vínculo(s) criado(s) com sucesso.',
                (int)$resultado['inseridos']
            );
            if ((int)$resultado['existentes'] > 0) {
                $mensagem .= sprintf(
                    ' %d vínculo(s) já existiam e foram preservados.',
                    (int)$resultado['existentes']
                );
            }
            $_SESSION['sucesso'] = $mensagem;
            $this->redirecionar('/hierarquias/estrutura/' . (int)$resultado['empresa_id']);
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível montar a hierarquia.';
            $_SESSION['form_hierarquia'] = $_POST;
            $this->redirecionar('/hierarquias/criar');
        }
    }

    public function editar($id = null): void
    {
        $id = $this->validarId($id);
        $hierarquia = $this->hierarquiaModel->buscarCompletaPorId($id);
        if (!$hierarquia) {
            $_SESSION['erro'] = 'Hierarquia não encontrada.';
            $this->redirecionar('/hierarquias');
        }
        $this->view('hierarquias/editar', array_merge(
            $this->dadosFormulario(),
            ['hierarquia' => $hierarquia]
        ));
    }

    public function atualizar($id = null): never
    {
        $this->prepararPost();
        $id = $this->validarId($id);
        try {
            $this->hierarquiaModel->atualizar($id, $_POST);
            $_SESSION['sucesso'] = 'Hierarquia atualizada com sucesso.';
            $this->redirecionar('/hierarquias');
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível atualizar a hierarquia.';
            $this->redirecionar('/hierarquias/editar/' . $id);
        }
    }

    public function excluir($id = null): never
    {
        $id = $this->validarId($id);
        try {
            $this->hierarquiaModel->excluir($id);
            $_SESSION['sucesso'] = 'Hierarquia excluída com sucesso.';
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível excluir a hierarquia.';
        }
        $this->redirecionar('/hierarquias');
    }

    public function estrutura($empresaId = null): void
    {
        $empresaId = $this->validarId($empresaId);
        $empresa = $this->hierarquiaModel->buscarEmpresaNaHierarquia($empresaId);
        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            $this->redirecionar('/hierarquias');
        }
        $this->view('hierarquias/estrutura', [
            'empresa' => $empresa,
            'estrutura' => $this->hierarquiaModel->listarEstruturaPorEmpresa($empresaId),
            'funcionariosEmpresa' => $this->hierarquiaModel->listarFuncionariosPorEmpresa($empresaId),
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function alocarFuncionarios($hierarquiaId = null): never
    {
        $this->prepararPost();
        $hierarquiaId = $this->validarId($hierarquiaId);
        $hierarquia = $this->hierarquiaModel->buscarPorId($hierarquiaId);

        if (!$hierarquia) {
            $_SESSION['erro'] = 'Cargo da hierarquia não encontrado.';
            $this->redirecionar('/hierarquias');
        }

        try {
            $total = $this->hierarquiaModel->alocarFuncionarios(
                $hierarquiaId,
                is_array($_POST['funcionarios'] ?? null) ? $_POST['funcionarios'] : []
            );
            $_SESSION['sucesso'] = sprintf(
                '%d funcionário(s) alocado(s) ao cargo selecionado.',
                $total
            );
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível alocar os funcionários.';
        }

        $this->redirecionar('/hierarquias/estrutura/' . (int)$hierarquia['empresa_id']);
    }

    public function importar(): void
    {
        $this->view('hierarquias/importar');
    }

    public function processarImportacao(): never
    {
        $_SESSION['erro'] = 'A importação será reconstruída após a validação do modelo manual.';
        $this->redirecionar('/hierarquias/importar');
    }

    private function dadosFormulario(): array
    {
        $dadosAnteriores = $_SESSION['form_hierarquia'] ?? [];
        unset($_SESSION['form_hierarquia']);
        if ($dadosAnteriores === [] && !empty($_GET['empresa_id'])) {
            $dadosAnteriores['empresa_id'] = max(0, (int)$_GET['empresa_id']);
        }
        return [
            'empresas' => $this->empresaModel->listarAtivas(),
            'unidades' => $this->unidadeModel->listarAtivas(),
            'setores' => $this->setorModel->listarAtivos(),
            'cargos' => $this->cargoModel->listarAtivos(),
            'vinculosExistentes' => $this->hierarquiaModel->listarTudo(),
            'dadosAnteriores' => $dadosAnteriores,
            'csrfToken' => $this->csrfToken(),
        ];
    }

    private function prepararPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $_SESSION['erro'] = 'Método de requisição não permitido.';
            $this->redirecionar('/hierarquias');
        }
        $recebido = (string)($_POST['_token'] ?? '');
        $esperado = (string)($_SESSION['csrf_hierarquias'] ?? '');
        if ($esperado === '' || !hash_equals($esperado, $recebido)) {
            $_SESSION['erro'] = 'A sessão do formulário expirou. Recarregue a página.';
            $this->redirecionar('/hierarquias');
        }
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_hierarquias'])) {
            $_SESSION['csrf_hierarquias'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_hierarquias'];
    }

    private function validarId(mixed $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            $_SESSION['erro'] = 'Identificador inválido.';
            $this->redirecionar('/hierarquias');
        }
        return (int)$id;
    }

    private function registrarErro(Throwable $erro): void
    {
        error_log('[Hierarquias] ' . $erro->getMessage());
    }

    private function redirecionar(string $rota): never
    {
        header('Location: ' . BASE_URL . $rota);
        exit;
    }
}
