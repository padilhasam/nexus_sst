<?php

class SetoresController extends Controller
{

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

        $this->setorModel = $this->model('Setor');
    }


    public function index()
    {
        $setores = $this->setorModel->listarTudo();

        $this->view('setores/index', compact('setores'));
    }


    public function criar()
    {
        $this->view('setores/criar');
    }


    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/setores');
            exit;
        }

        $dados = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'codigo_externo' => trim($_POST['codigo_externo'] ?? ''),
            'nome' => trim($_POST['nome'] ?? ''),
            'descricao' => !empty($_POST['descricao']) 
                ? trim($_POST['descricao']) 
                : null,
            'ativo' => 1
        ];


        if (empty($dados['nome'])) {
            $_SESSION['erro'] = 'O nome do setor é obrigatório.';
            header('Location: ' . BASE_URL . '/setores/criar');
            exit;
        }


        if ($this->setorModel->salvar($dados)) {
            $_SESSION['sucesso'] = 'Setor cadastrado com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao cadastrar setor.';
        }


        header('Location: ' . BASE_URL . '/setores');
        exit;
    }


    public function editar($id)
    {
        $setor = $this->setorModel->buscarPorId((int)$id);


        if (!$setor) {
            $_SESSION['erro'] = 'Setor não encontrado.';
            header('Location: ' . BASE_URL . '/setores');
            exit;
        }


        $this->view('setores/editar', compact('setor'));
    }


    public function atualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/setores');
            exit;
        }


        $dados = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'codigo_externo' => trim($_POST['codigo_externo'] ?? ''),
            'nome' => trim($_POST['nome'] ?? ''),
            'descricao' => !empty($_POST['descricao']) 
                ? trim($_POST['descricao']) 
                : null
        ];


        if (empty($dados['nome'])) {
            $_SESSION['erro'] = 'O nome do setor é obrigatório.';
            header('Location: ' . BASE_URL . '/setores/editar/' . $id);
            exit;
        }


        if ($this->setorModel->atualizar((int)$id, $dados)) {
            $_SESSION['sucesso'] = 'Setor atualizado com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao atualizar setor.';
        }


        header('Location: ' . BASE_URL . '/setores');
        exit;
    }


    public function excluir($id)
    {
        $setor = $this->setorModel->buscarPorId((int)$id);


        if (!$setor) {

            $_SESSION['erro'] = 'Setor não encontrado.';

        } elseif ($this->setorModel->deletar((int)$id)) {

            $_SESSION['sucesso'] = 'Setor excluído com sucesso!';

        } else {

            $_SESSION['erro'] = 'Erro ao excluir setor.';
        }


        header('Location: ' . BASE_URL . '/setores');
        exit;
    }

}