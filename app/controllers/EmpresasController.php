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

    public function index()
    {
        $empresas = $this->empresaModel->listar();

        $this->view('empresas/index', [
            'empresas' => $empresas
        ]);
    }

    public function criar()
    {
        $this->view('empresas/criar');
    }

    private function montarDadosFormulario(): array
    {
        return [
            'codigo' => !empty($_POST['codigo'])
                ? strtoupper(trim($_POST['codigo']))
                : 'EMP' . strtoupper(uniqid()),

            'codigo_externo' => !empty($_POST['codigo_externo'])
                ? strtoupper(trim($_POST['codigo_externo']))
                : null,

            'razao_social' => trim($_POST['razao_social'] ?? ''),

            'nome_fantasia' => !empty($_POST['nome_fantasia'])
                ? trim($_POST['nome_fantasia'])
                : null,

            'cnpj' => !empty($_POST['cnpj'])
                ? trim($_POST['cnpj'])
                : null,

            'inscricao_estadual' => !empty($_POST['inscricao_estadual'])
                ? trim($_POST['inscricao_estadual'])
                : null,

            'cnae' => !empty($_POST['cnae'])
                ? trim($_POST['cnae'])
                : null,
            
            'descricao_cnae' => !empty($_POST['descricao_cnae'])
                ? trim($_POST['descricao_cnae'])
                : null,

            'grau_risco' => !empty($_POST['grau_risco'])
                ? trim($_POST['grau_risco'])
                : null,

            'quantidade_funcionarios' => isset($_POST['quantidade_funcionarios']) && $_POST['quantidade_funcionarios'] !== ''
                ? (int) $_POST['quantidade_funcionarios']
                : null,

            'telefone' => !empty($_POST['telefone'])
                ? trim($_POST['telefone'])
                : null,

            'email' => !empty($_POST['email'])
                ? trim($_POST['email'])
                : null,

            'responsavel' => !empty($_POST['responsavel'])
                ? trim($_POST['responsavel'])
                : null,

            'cargo_responsavel' => !empty($_POST['cargo_responsavel'])
                ? trim($_POST['cargo_responsavel'])
                : null,

            'contato_responsavel' => !empty($_POST['contato_responsavel'])
                ? trim($_POST['contato_responsavel'])
                : null,

            'cep' => !empty($_POST['cep'])
                ? trim($_POST['cep'])
                : null,

            'logradouro' => !empty($_POST['logradouro'])
                ? trim($_POST['logradouro'])
                : null,

            'numero' => !empty($_POST['numero'])
                ? trim($_POST['numero'])
                : null,

            'complemento' => !empty($_POST['complemento'])
                ? trim($_POST['complemento'])
                : null,

            'bairro' => !empty($_POST['bairro'])
                ? trim($_POST['bairro'])
                : null,

            'cidade' => !empty($_POST['cidade'])
                ? trim($_POST['cidade'])
                : null,

            'estado' => !empty($_POST['estado'])
                ? strtoupper(trim($_POST['estado']))
                : null,

            'endereco' => !empty($_POST['endereco'])
                ? trim($_POST['endereco'])
                : null,

            'tecnico_responsavel' => !empty($_POST['tecnico_responsavel'])
                ? trim($_POST['tecnico_responsavel'])
                : null,

            'supervisor_responsavel' => !empty($_POST['supervisor_responsavel'])
                ? trim($_POST['supervisor_responsavel'])
                : null,

            'periodicidade_visitas' => !empty($_POST['periodicidade_visitas'])
                ? trim($_POST['periodicidade_visitas'])
                : null,

            'observacoes' => !empty($_POST['observacoes'])
                ? trim($_POST['observacoes'])
                : null,

            'ativo' => isset($_POST['ativo'])
                ? (int) $_POST['ativo']
                : 0
        ];
    }

    public function armazenar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/empresas');
            exit;
        }

        $dados = $this->montarDadosFormulario();

        if (empty($dados['razao_social'])) {
            $_SESSION['erro'] = 'A Razão Social é obrigatória.';
            header('Location: ' . BASE_URL . '/empresas/criar');
            exit;
        }

        if (!empty($dados['cnpj'])) {
            $empresaExistente = $this->empresaModel->buscarPorCnpj($dados['cnpj']);

            if ($empresaExistente) {
                $_SESSION['erro'] = 'Já existe uma empresa cadastrada com este CNPJ.';
                header('Location: ' . BASE_URL . '/empresas/criar');
                exit;
            }
        }

        if (!empty($dados['codigo'])) {
            $empresaExistente = $this->empresaModel->buscarPorCodigo($dados['codigo']);

            if ($empresaExistente) {
                $_SESSION['erro'] = 'Já existe uma empresa cadastrada com este código interno.';
                header('Location: ' . BASE_URL . '/empresas/criar');
                exit;
            }
        }

        $empresaId = $this->empresaModel->salvar($dados);

        $_SESSION['sucesso'] = $empresaId
            ? 'Empresa cadastrada com sucesso!'
            : 'Erro ao salvar empresa.';

        header('Location: ' . BASE_URL . '/empresas');
        exit;
    }

    public function editar($id)
    {
        $empresa = $this->empresaModel->buscarPorId((int) $id);

        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            header('Location: ' . BASE_URL . '/empresas');
            exit;
        }

        $this->view('empresas/editar', [
            'empresa' => $empresa
        ]);
    }

    public function atualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/empresas');
            exit;
        }

        $id = (int) $id;
        $dados = $this->montarDadosFormulario();

        if (empty($dados['razao_social'])) {
            $_SESSION['erro'] = 'A Razão Social é obrigatória.';
            header('Location: ' . BASE_URL . '/empresas/editar/' . $id);
            exit;
        }

        if (!empty($dados['cnpj'])) {
            $empresaExistente = $this->empresaModel->buscarPorCnpj($dados['cnpj']);

            if ($empresaExistente && (int) $empresaExistente['id'] !== $id) {
                $_SESSION['erro'] = 'Já existe outra empresa cadastrada com este CNPJ.';
                header('Location: ' . BASE_URL . '/empresas/editar/' . $id);
                exit;
            }
        }

        if (!empty($dados['codigo'])) {
            $empresaExistente = $this->empresaModel->buscarPorCodigo($dados['codigo']);

            if ($empresaExistente && (int) $empresaExistente['id'] !== $id) {
                $_SESSION['erro'] = 'Já existe outra empresa cadastrada com este código interno.';
                header('Location: ' . BASE_URL . '/empresas/editar/' . $id);
                exit;
            }
        }

        if ($this->empresaModel->atualizar($id, $dados)) {
            $_SESSION['sucesso'] = 'Empresa atualizada com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao atualizar empresa.';
        }

        header('Location: ' . BASE_URL . '/empresas');
        exit;
    }

    public function excluir($id)
    {
        if ($this->empresaModel->desativar((int) $id)) {
            $_SESSION['sucesso'] = 'Empresa desativada com sucesso!';
        } else {
            $_SESSION['erro'] = 'Não foi possível desativar a empresa.';
        }

        header('Location: ' . BASE_URL . '/empresas');
        exit;
    }
}