<?php

class CargosController extends Controller
{
    private $cargoModel;
    private $setorModel;


    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->cargoModel = $this->model('Cargo');
        $this->setorModel = $this->model('Setor');
    }


    public function index()
    {
        $cargos = $this->cargoModel->listarTudo();

        $this->view('cargos/index', [
            'cargos' => $cargos
        ]);
    }


    public function criar()
    {
        $setores = $this->setorModel->listarTudo();

        $this->view('cargos/criar', [
            'setores' => $setores
        ]);
    }


    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cargos');
            exit;
        }


        $dados = [

            'setor_id' => (int)($_POST['setor_id'] ?? 0),

            'codigo' => !empty($_POST['codigo'])
                ? trim($_POST['codigo'])
                : null,

            'codigo_externo' => !empty($_POST['codigo_externo'])
                ? trim($_POST['codigo_externo'])
                : null,

            'nome' => trim($_POST['nome'] ?? ''),

            'cbo' => !empty($_POST['cbo'])
                ? trim($_POST['cbo'])
                : null,

            'descricao' => !empty($_POST['descricao'])
                ? trim($_POST['descricao'])
                : null,

            'ativo' => 1
        ];


        if (empty($dados['nome']) || empty($dados['setor_id'])) {

            $_SESSION['erro'] =
            'Nome do cargo e setor são obrigatórios.';

            header('Location: ' . BASE_URL . '/cargos/criar');
            exit;
        }


        if ($this->cargoModel->salvar($dados)) {

            $_SESSION['sucesso'] =
            'Cargo cadastrado com sucesso!';

        } else {

            $_SESSION['erro'] =
            'Erro ao cadastrar cargo.';
        }


        header('Location: ' . BASE_URL . '/cargos');
        exit;
    }


    public function editar($id)
    {
        $cargo =
        $this->cargoModel->buscarPorId((int)$id);


        if (!$cargo) {

            $_SESSION['erro'] =
            'Cargo não encontrado.';

            header('Location: ' . BASE_URL . '/cargos');
            exit;
        }


        $setores =
        $this->setorModel->listarTudo();


        $this->view('cargos/editar', [

            'cargo' => $cargo,

            'setores' => $setores

        ]);
    }


    public function atualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            header('Location: ' . BASE_URL . '/cargos');
            exit;
        }


        $dados = [

            'setor_id' => (int)($_POST['setor_id'] ?? 0),

            'codigo' => !empty($_POST['codigo'])
                ? trim($_POST['codigo'])
                : null,

            'codigo_externo' => !empty($_POST['codigo_externo'])
                ? trim($_POST['codigo_externo'])
                : null,

            'nome' => trim($_POST['nome'] ?? ''),

            'cbo' => !empty($_POST['cbo'])
                ? trim($_POST['cbo'])
                : null,

            'descricao' => !empty($_POST['descricao'])
                ? trim($_POST['descricao'])
                : null

        ];


        if (empty($dados['nome']) || empty($dados['setor_id'])) {

            $_SESSION['erro'] =
            'Preencha os campos obrigatórios.';

            header(
                'Location: ' . BASE_URL . '/cargos/editar/' . $id
            );

            exit;
        }


        if ($this->cargoModel->atualizar((int)$id, $dados)) {

            $_SESSION['sucesso'] =
            'Cargo atualizado com sucesso!';

        } else {

            $_SESSION['erro'] =
            'Erro ao atualizar cargo.';
        }


        header('Location: ' . BASE_URL . '/cargos');
        exit;
    }


    public function excluir($id)
    {
        if ($this->cargoModel->desativar((int)$id)) {

            $_SESSION['sucesso'] =
            'Cargo desativado com sucesso!';

        } else {

            $_SESSION['erro'] =
            'Erro ao desativar cargo.';
        }


        header('Location: ' . BASE_URL . '/cargos');
        exit;
    }
}