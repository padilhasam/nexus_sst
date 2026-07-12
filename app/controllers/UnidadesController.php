<?php

class UnidadesController extends Controller
{
    private $unidadeModel;
    private $empresaModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->unidadeModel = $this->model('Unidade');
        $this->empresaModel = $this->model('Empresa');
    }

    public function index()
    {
        $unidades = $this->unidadeModel->listarTudo();

        $this->view('unidades/index', [
            'unidades' => $unidades
        ]);
    }

    public function criar()
    {
        $empresas = $this->empresaModel->listarAtivas();

        $this->view('unidades/criar', [
            'empresas' => $empresas
        ]);
    }

    private function montarDadosFormulario(): array
    {
        return [
            'empresa_id' => !empty($_POST['empresa_id']) ? (int) $_POST['empresa_id'] : null,

            'codigo' => !empty($_POST['codigo']) ? strtoupper(trim($_POST['codigo'])) : null,

            'codigo_externo' => !empty($_POST['codigo_externo']) ? strtoupper(trim($_POST['codigo_externo'])) : null,

            'nome' => trim($_POST['nome'] ?? ''),

            'nome_fantasia' => !empty($_POST['nome_fantasia']) ? trim($_POST['nome_fantasia']) : null,

            'cnpj' => !empty($_POST['cnpj']) ? trim($_POST['cnpj']) : null,

            'inscricao_estadual' => !empty($_POST['inscricao_estadual']) ? trim($_POST['inscricao_estadual']) : null,

            'cnae' => !empty($_POST['cnae']) ? trim($_POST['cnae']) : null,

            'descricao_cnae' => !empty($_POST['descricao_cnae']) ? trim($_POST['descricao_cnae']) : null,

            'grau_risco' => !empty($_POST['grau_risco']) ? trim($_POST['grau_risco']) : null,

            'quantidade_funcionarios' => isset($_POST['quantidade_funcionarios']) && $_POST['quantidade_funcionarios'] !== ''
                ? (int) $_POST['quantidade_funcionarios']
                : null,

            'endereco' => !empty($_POST['endereco']) ? trim($_POST['endereco']) : null,

            'logradouro' => !empty($_POST['logradouro']) ? trim($_POST['logradouro']) : null,

            'numero' => !empty($_POST['numero']) ? trim($_POST['numero']) : null,

            'complemento' => !empty($_POST['complemento']) ? trim($_POST['complemento']) : null,

            'bairro' => !empty($_POST['bairro']) ? trim($_POST['bairro']) : null,

            'cidade' => !empty($_POST['cidade']) ? trim($_POST['cidade']) : null,

            'estado' => !empty($_POST['estado']) ? strtoupper(trim($_POST['estado'])) : null,

            'cep' => !empty($_POST['cep']) ? trim($_POST['cep']) : null,

            'telefone' => !empty($_POST['telefone']) ? trim($_POST['telefone']) : null,

            'contato_responsavel' => !empty($_POST['contato_responsavel']) ? trim($_POST['contato_responsavel']) : null,

            'email' => !empty($_POST['email']) ? trim($_POST['email']) : null,

            'responsavel' => !empty($_POST['responsavel']) ? trim($_POST['responsavel']) : null,

            'cargo_responsavel' => !empty($_POST['cargo_responsavel']) ? trim($_POST['cargo_responsavel']) : null,

            'tecnico_responsavel' => !empty($_POST['tecnico_responsavel']) ? trim($_POST['tecnico_responsavel']) : null,

            'supervisor_responsavel' => !empty($_POST['supervisor_responsavel']) ? trim($_POST['supervisor_responsavel']) : null,

            'periodicidade_visitas' => !empty($_POST['periodicidade_visitas']) ? trim($_POST['periodicidade_visitas']) : null,

            'observacoes' => !empty($_POST['observacoes']) ? trim($_POST['observacoes']) : null,

            'ativo' => isset($_POST['ativo']) ? (int) $_POST['ativo'] : 0
        ];
    }

    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/unidades');
            exit;
        }

        $dados = $this->montarDadosFormulario();

        if (empty($dados['empresa_id'])) {
            $_SESSION['erro'] = 'A empresa vinculada é obrigatória.';
            header('Location: ' . BASE_URL . '/unidades/criar');
            exit;
        }

        if (empty($dados['nome'])) {
            $_SESSION['erro'] = 'O nome da unidade é obrigatório.';
            header('Location: ' . BASE_URL . '/unidades/criar');
            exit;
        }

        if (!empty($dados['cnpj'])) {
            $unidadeExistente = $this->unidadeModel->buscarPorCnpj($dados['cnpj']);

            if ($unidadeExistente) {
                $_SESSION['erro'] = 'Já existe uma unidade cadastrada com este CNPJ.';
                header('Location: ' . BASE_URL . '/unidades/criar');
                exit;
            }
        }

        if (!empty($dados['codigo'])) {
            $unidadeExistente = $this->unidadeModel->buscarPorCodigo($dados['codigo']);

            if ($unidadeExistente) {
                $_SESSION['erro'] = 'Já existe uma unidade cadastrada com este código interno.';
                header('Location: ' . BASE_URL . '/unidades/criar');
                exit;
            }
        }

        $unidadeId = $this->unidadeModel->salvar($dados);

        $_SESSION['sucesso'] = $unidadeId
            ? 'Unidade cadastrada com sucesso!'
            : 'Erro ao cadastrar unidade.';

        header('Location: ' . BASE_URL . '/unidades');
        exit;
    }

    public function editar($id)
    {
        $unidade = $this->unidadeModel->buscarPorId((int) $id);

        if (!$unidade) {
            $_SESSION['erro'] = 'Unidade não encontrada.';
            header('Location: ' . BASE_URL . '/unidades');
            exit;
        }

        $empresas = $this->empresaModel->listarAtivas();

        $this->view('unidades/editar', [
            'unidade' => $unidade,
            'empresas' => $empresas
        ]);
    }

    public function atualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/unidades');
            exit;
        }

        $id = (int) $id;
        $dados = $this->montarDadosFormulario();

        if (empty($dados['empresa_id'])) {
            $_SESSION['erro'] = 'A empresa vinculada é obrigatória.';
            header('Location: ' . BASE_URL . '/unidades/editar/' . $id);
            exit;
        }

        if (empty($dados['nome'])) {
            $_SESSION['erro'] = 'O nome da unidade é obrigatório.';
            header('Location: ' . BASE_URL . '/unidades/editar/' . $id);
            exit;
        }

        if (!empty($dados['cnpj'])) {
            $unidadeExistente = $this->unidadeModel->buscarPorCnpj($dados['cnpj']);

            if ($unidadeExistente && (int) $unidadeExistente['id'] !== $id) {
                $_SESSION['erro'] = 'Já existe outra unidade cadastrada com este CNPJ.';
                header('Location: ' . BASE_URL . '/unidades/editar/' . $id);
                exit;
            }
        }

        if (!empty($dados['codigo'])) {
            $unidadeExistente = $this->unidadeModel->buscarPorCodigo($dados['codigo']);

            if ($unidadeExistente && (int) $unidadeExistente['id'] !== $id) {
                $_SESSION['erro'] = 'Já existe outra unidade cadastrada com este código interno.';
                header('Location: ' . BASE_URL . '/unidades/editar/' . $id);
                exit;
            }
        }

        if ($this->unidadeModel->atualizar($id, $dados)) {
            $_SESSION['sucesso'] = 'Unidade atualizada com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao atualizar unidade.';
        }

        header('Location: ' . BASE_URL . '/unidades');
        exit;
    }

    public function excluir($id)
    {
        if ($this->unidadeModel->desativar((int) $id)) {
            $_SESSION['sucesso'] = 'Unidade desativada com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao desativar unidade.';
        }

        header('Location: ' . BASE_URL . '/unidades');
        exit;
    }
}