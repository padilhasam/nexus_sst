<?php

class EmpresasController extends AuthController
{
    private Empresa $empresaModel;

    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/../models/Empresa.php';
        $this->empresaModel = new Empresa();
    }

    public function index(): void
    {
        $this->view('empresas/index', [
            'empresas' => $this->empresaModel->listar(),
        ]);
    }

    public function criar(): void
    {
        $dadosAnteriores = $_SESSION['form_empresas'] ?? [];
        unset($_SESSION['form_empresas']);

        $this->view('empresas/criar', [
            'csrfToken' => $this->csrfToken(),
            'dadosAnteriores' => $dadosAnteriores,
        ]);
    }

    public function armazenar(): never
    {
        $this->prepararPost('/empresas/criar');
        $dados = $this->montarDadosFormulario();

        try {
            $this->validarDados($dados);
            $this->validarDuplicidades($dados);

            $empresaId = $this->empresaModel->salvar($dados);
            if (!$empresaId) {
                throw new RuntimeException('Não foi possível salvar a empresa.');
            }

            unset($_SESSION['form_empresas']);
            $_SESSION['sucesso'] = 'Empresa cadastrada com sucesso.';
            $this->redirecionarPara('/empresas');
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível cadastrar a empresa.';
            $_SESSION['form_empresas'] = $_POST;
            $this->redirecionarPara('/empresas/criar');
        }
    }

    public function editar($id = null): void
    {
        $id = $this->validarId($id);
        $empresa = $this->empresaModel->buscarPorId($id);

        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            $this->redirecionarPara('/empresas');
        }

        $this->view('empresas/editar', [
            'empresa' => $empresa,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function atualizar($id = null): never
    {
        $id = $this->validarId($id);
        $this->prepararPost('/empresas/editar/' . $id);
        $dados = $this->montarDadosFormulario();

        try {
            $this->validarDados($dados);
            $this->validarDuplicidades($dados, $id);

            if (!$this->empresaModel->atualizar($id, $dados)) {
                throw new RuntimeException('Não foi possível atualizar a empresa.');
            }

            $_SESSION['sucesso'] = 'Empresa atualizada com sucesso.';
            $this->redirecionarPara('/empresas');
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível atualizar a empresa.';
            $this->redirecionarPara('/empresas/editar/' . $id);
        }
    }

    public function excluir($id = null): never
    {
        $id = $this->validarId($id);

        try {
            $alterado = $this->empresaModel->desativar($id);
            $_SESSION[$alterado ? 'sucesso' : 'erro'] = $alterado
                ? 'Empresa desativada sem excluir seus vínculos históricos.'
                : 'Não foi possível desativar a empresa.';
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = 'Não foi possível desativar a empresa.';
        }

        $this->redirecionarPara('/empresas');
    }

    private function montarDadosFormulario(): array
    {
        $textoOuNulo = static function (string $campo): ?string {
            $valor = trim((string)($_POST[$campo] ?? ''));
            return $valor !== '' ? $valor : null;
        };

        $codigo = strtoupper(trim((string)($_POST['codigo'] ?? '')));
        if ($codigo === '') {
            $codigo = 'EMP' . strtoupper(substr(md5(uniqid('', true)), 0, 10));
        }

        $quantidadeFuncionarios = trim((string)($_POST['quantidade_funcionarios'] ?? ''));

        return [
            'codigo' => $codigo,
            'codigo_externo' => ($valor = strtoupper(trim((string)($_POST['codigo_externo'] ?? '')))) !== '' ? $valor : null,
            'razao_social' => trim((string)($_POST['razao_social'] ?? '')),
            'nome_fantasia' => $textoOuNulo('nome_fantasia'),
            'cnpj' => $textoOuNulo('cnpj'),
            'inscricao_estadual' => $textoOuNulo('inscricao_estadual'),
            'cnae' => $textoOuNulo('cnae'),
            'descricao_cnae' => $textoOuNulo('descricao_cnae'),
            'grau_risco' => $textoOuNulo('grau_risco'),
            'quantidade_funcionarios' => $quantidadeFuncionarios !== '' ? max(0, (int)$quantidadeFuncionarios) : null,
            'telefone' => $textoOuNulo('telefone'),
            'email' => $textoOuNulo('email'),
            'responsavel' => $textoOuNulo('responsavel'),
            'cargo_responsavel' => $textoOuNulo('cargo_responsavel'),
            'contato_responsavel' => $textoOuNulo('contato_responsavel'),
            'cep' => $textoOuNulo('cep'),
            'logradouro' => $textoOuNulo('logradouro'),
            'numero' => $textoOuNulo('numero'),
            'complemento' => $textoOuNulo('complemento'),
            'bairro' => $textoOuNulo('bairro'),
            'cidade' => $textoOuNulo('cidade'),
            'estado' => ($valor = strtoupper(trim((string)($_POST['estado'] ?? '')))) !== '' ? $valor : null,
            'endereco' => $textoOuNulo('endereco'),
            'tecnico_responsavel' => $textoOuNulo('tecnico_responsavel'),
            'supervisor_responsavel' => $textoOuNulo('supervisor_responsavel'),
            'periodicidade_visitas' => $textoOuNulo('periodicidade_visitas'),
            'observacoes' => $textoOuNulo('observacoes'),
            'ativo' => !empty($_POST['ativo']) ? 1 : 0,
        ];
    }

    private function validarDados(array $dados): void
    {
        if ($dados['razao_social'] === '') {
            throw new RuntimeException('A razão social é obrigatória.');
        }

        if (!empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Informe um endereço de e-mail válido.');
        }

        if (!empty($dados['estado']) && strlen((string)$dados['estado']) !== 2) {
            throw new RuntimeException('Informe a UF com duas letras.');
        }
    }

    private function validarDuplicidades(array $dados, int $ignorarId = 0): void
    {
        if (!empty($dados['cnpj'])) {
            $existente = $this->empresaModel->buscarPorCnpj((string)$dados['cnpj']);
            if ($existente && (int)$existente['id'] !== $ignorarId) {
                throw new RuntimeException('Já existe outra empresa cadastrada com este CNPJ.');
            }
        }

        if (!empty($dados['codigo'])) {
            $existente = $this->empresaModel->buscarPorCodigo((string)$dados['codigo']);
            if ($existente && (int)$existente['id'] !== $ignorarId) {
                throw new RuntimeException('Já existe outra empresa cadastrada com este código interno.');
            }
        }
    }

    private function prepararPost(string $retorno): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $_SESSION['erro'] = 'Método de requisição não permitido.';
            $this->redirecionarPara($retorno);
        }

        $recebido = (string)($_POST['_token'] ?? '');
        $esperado = (string)($_SESSION['csrf_empresas'] ?? '');
        if ($esperado === '' || !hash_equals($esperado, $recebido)) {
            $_SESSION['erro'] = 'A sessão do formulário expirou. Recarregue a página.';
            $this->redirecionarPara($retorno);
        }
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_empresas'])) {
            $_SESSION['csrf_empresas'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_empresas'];
    }

    private function validarId(mixed $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            $_SESSION['erro'] = 'Identificador de empresa inválido.';
            $this->redirecionarPara('/empresas');
        }
        return (int)$id;
    }

    private function registrarErro(Throwable $erro): void
    {
        error_log('[EmpresasController] ' . $erro->getMessage());
    }

    private function redirecionarPara(string $rota): never
    {
        header('Location: ' . BASE_URL . $rota);
        exit;
    }
}
