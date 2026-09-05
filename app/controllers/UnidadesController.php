<?php

class UnidadesController extends Controller
{
    private Unidade $unidadeModel;
    private Empresa $empresaModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->unidadeModel = $this->model('Unidade');
        $this->empresaModel = $this->model('Empresa');
    }

    public function index(): void
    {
        $this->view('unidades/index', ['unidades' => $this->unidadeModel->listarTudo()]);
    }

    public function criar(): void
    {
        $this->view('unidades/criar', ['empresas' => $this->empresaModel->listarAtivas()]);
    }

    public function salvar(): never
    {
        $this->exigirPost('/unidades');
        $dados = $this->montarDadosFormulario();

        try {
            $this->validarEmpresa($dados);
            $this->validarDuplicidades($dados);
            $this->unidadeModel->salvar($dados);
            $_SESSION['sucesso'] = 'Unidade cadastrada com sucesso.';
            $this->redirecionar('/unidades');
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível cadastrar a unidade.';
            $this->redirecionar('/unidades/criar');
        }
    }

    public function editar($id = null): void
    {
        $id = $this->validarId($id);
        $unidade = $this->unidadeModel->buscarPorId($id);
        if (!$unidade) {
            $_SESSION['erro'] = 'Unidade não encontrada.';
            $this->redirecionar('/unidades');
        }

        $this->view('unidades/editar', [
            'unidade' => $unidade,
            'empresas' => $this->empresaModel->listarAtivas(),
        ]);
    }

    public function atualizar($id = null): never
    {
        $this->exigirPost('/unidades');
        $id = $this->validarId($id);
        $dados = $this->montarDadosFormulario();

        try {
            $this->validarEmpresa($dados);
            $this->validarDuplicidades($dados, $id);
            $this->unidadeModel->atualizar($id, $dados);
            $_SESSION['sucesso'] = 'Unidade atualizada com sucesso.';
            $this->redirecionar('/unidades');
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível atualizar a unidade.';
            $this->redirecionar('/unidades/editar/' . $id);
        }
    }

    public function excluir($id = null): never
    {
        $id = $this->validarId($id);
        try {
            $alterado = $this->unidadeModel->desativar($id);
            $_SESSION[$alterado ? 'sucesso' : 'erro'] = $alterado
                ? 'Unidade desativada sem excluir seus vínculos.'
                : 'A unidade já estava inativa ou não foi encontrada.';
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = 'Não foi possível desativar a unidade.';
        }
        $this->redirecionar('/unidades');
    }

    private function montarDadosFormulario(): array
    {
        return [
            'empresa_id' => (int)($_POST['empresa_id'] ?? 0),
            'codigo' => strtoupper(trim((string)($_POST['codigo'] ?? ''))),
            'codigo_externo' => strtoupper(trim((string)($_POST['codigo_externo'] ?? ''))),
            'nome' => trim((string)($_POST['nome'] ?? '')),
            'razao_social' => trim((string)($_POST['razao_social'] ?? '')),
            'nome_fantasia' => trim((string)($_POST['nome_fantasia'] ?? '')),
            'cnpj' => trim((string)($_POST['cnpj'] ?? '')),
            'inscricao_estadual' => trim((string)($_POST['inscricao_estadual'] ?? '')),
            'cnae' => trim((string)($_POST['cnae'] ?? '')),
            'descricao_cnae' => trim((string)($_POST['descricao_cnae'] ?? '')),
            'grau_risco' => trim((string)($_POST['grau_risco'] ?? '')),
            'quantidade_funcionarios' => $_POST['quantidade_funcionarios'] ?? null,
            'endereco' => trim((string)($_POST['endereco'] ?? '')),
            'logradouro' => trim((string)($_POST['logradouro'] ?? '')),
            'numero' => trim((string)($_POST['numero'] ?? '')),
            'complemento' => trim((string)($_POST['complemento'] ?? '')),
            'bairro' => trim((string)($_POST['bairro'] ?? '')),
            'cidade' => trim((string)($_POST['cidade'] ?? '')),
            'estado' => strtoupper(trim((string)($_POST['estado'] ?? ''))),
            'cep' => trim((string)($_POST['cep'] ?? '')),
            'telefone' => trim((string)($_POST['telefone'] ?? '')),
            'contato_responsavel' => trim((string)($_POST['contato_responsavel'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'responsavel' => trim((string)($_POST['responsavel'] ?? '')),
            'cargo_responsavel' => trim((string)($_POST['cargo_responsavel'] ?? '')),
            'tecnico_responsavel' => trim((string)($_POST['tecnico_responsavel'] ?? '')),
            'supervisor_responsavel' => trim((string)($_POST['supervisor_responsavel'] ?? '')),
            'periodicidade_visitas' => trim((string)($_POST['periodicidade_visitas'] ?? '')),
            'observacoes' => trim((string)($_POST['observacoes'] ?? '')),
            'ativo' => !empty($_POST['ativo']) ? 1 : 0,
        ];
    }

    private function validarEmpresa(array $dados): void
    {
        $empresaId = (int)$dados['empresa_id'];
        $empresa = $empresaId > 0 ? $this->empresaModel->buscarPorId($empresaId) : null;
        if (!$empresa || empty($empresa['ativo'])) {
            throw new RuntimeException('Selecione uma empresa ativa para a unidade.');
        }
    }

    private function validarDuplicidades(array $dados, int $ignorarId = 0): void
    {
        if ($dados['cnpj'] !== '') {
            $existente = $this->unidadeModel->buscarPorCnpj($dados['cnpj']);
            if ($existente && (int)$existente['id'] !== $ignorarId) {
                throw new RuntimeException('Já existe outra unidade cadastrada com este CNPJ.');
            }
        }
        if ($dados['codigo'] !== '') {
            $existente = $this->unidadeModel->buscarPorCodigo($dados['codigo']);
            if ($existente && (int)$existente['id'] !== $ignorarId) {
                throw new RuntimeException('Já existe outra unidade cadastrada com este código interno.');
            }
        }
    }

    private function exigirPost(string $retorno): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $_SESSION['erro'] = 'Método de requisição não permitido.';
            $this->redirecionar($retorno);
        }
    }

    private function validarId(mixed $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            $_SESSION['erro'] = 'Identificador de unidade inválido.';
            $this->redirecionar('/unidades');
        }
        return (int)$id;
    }

    private function registrarErro(Throwable $erro): void
    {
        error_log('[Unidades] ' . $erro->getMessage());
    }

    private function redirecionar(string $rota): never
    {
        header('Location: ' . BASE_URL . $rota);
        exit;
    }
}
