<?php

class VeiculosController extends Controller
{
    private $veiculoModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Corrigido: Instancia corretamente o Model de Veículo
        $this->veiculoModel = $this->model('Veiculo');
    }

    /**
     * LISTAGEM DE VEÍCULOS
     */
    public function index()
    {
        // Corrigido: Chama o método do model correto e injeta o CSS adequado
        $dados = [
            'veiculos' => $this->veiculoModel->listarTodos(),
            'css' => 'veiculos.css' 
        ];

        $this->view('veiculos/index', $dados);
    }

    /**
     * FORMULÁRIO DE CRIAÇÃO
     */
    public function criar()
    {
        $dados = [
            'css' => 'veiculos.css'
        ];

        $this->view('veiculos/criar', $dados);
    }

    /**
     * SALVAR NOVO VEÍCULO
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/veiculos');
            exit;
        }

        // Corrigido: Mapeamento de campos alterado de Técnico para Veículo
        $dados = [
            'modelo' => trim($_POST['modelo'] ?? ''),
            'placa'  => strtoupper(trim($_POST['placa'] ?? '')),
            'cor'    => trim($_POST['cor'] ?? null),
            'ativo'  => isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1
        ];

        // Validações básicas de negócio para um veículo
        if (empty($dados['modelo'])) {
            $_SESSION['erro'] = 'O modelo do veículo é obrigatório.';
            header('Location: ' . BASE_URL . '/veiculos/criar');
            exit;
        }

        if (empty($dados['placa'])) {
            $_SESSION['erro'] = 'A placa do veículo é obrigatória.';
            header('Location: ' . BASE_URL . '/veiculos/criar');
            exit;
        }

        // Remove caracteres especiais da placa caso venha com máscara (ex: ABC-1234 -> ABC1234)
        $dados['placa'] = str_replace('-', '', $dados['placa']);

        // Corrigido: Chama o método de cadastro no modelo correto
        $ok = $this->veiculoModel->cadastrar($dados);

        if ($ok) {
            $_SESSION['sucesso'] = 'Veículo cadastrado com sucesso.';
        } else {
            $_SESSION['erro'] = 'Erro ao cadastrar veículo. Verifique se a placa já não está cadastrada.';
        }

        header('Location: ' . BASE_URL . '/veiculos');
        exit;
    }

    /**
     * FORMULÁRIO DE EDIÇÃO
     */
    public function editar($id)
    {
        // Corrigido: Busca utilizando o model de veículos
        $veiculo = $this->veiculoModel->buscarPorId((int)$id);

        if (!$veiculo) {
            $_SESSION['erro'] = 'Veículo não encontrado.';
            header('Location: ' . BASE_URL . '/veiculos');
            exit;
        }

        $dados = [
            'veiculo' => $veiculo,
            'css'     => 'veiculos.css'
        ];

        $this->view('veiculos/editar', $dados);
    }

    /**
     * ATUALIZAR VEÍCULO
     */
    public function atualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/veiculos');
            exit;
        }

        // Corrigido: Mapeamento de campos atualizado
        $dados = [
            'modelo' => trim($_POST['modelo'] ?? ''),
            'placa'  => strtoupper(trim($_POST['placa'] ?? '')),
            'cor'    => trim($_POST['cor'] ?? null),
            'ativo'  => isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1
        ];

        if (empty($dados['modelo']) || empty($dados['placa'])) {
            $_SESSION['erro'] = 'Modelo e Placa são obrigatórios.';
            header('Location: ' . BASE_URL . '/veiculos/editar/' . $id);
            exit;
        }

        $dados['placa'] = str_replace('-', '', $dados['placa']);

        // Corrigido: Atualização via veiculoModel redirecionando para a rota correta
        $ok = $this->veiculoModel->atualizar((int)$id, $dados);

        if ($ok) {
            $_SESSION['sucesso'] = 'Veículo atualizado com sucesso.';
        } else {
            $_SESSION['erro'] = 'Erro ao atualizar veículo.';
        }

        header('Location: ' . BASE_URL . '/veiculos');
        exit;
    }

    /**
     * EXCLUIR VEÍCULO
     */
    public function excluir($id)
    {
        // Corrigido: Validação e exclusão baseadas na entidade Veículo
        $veiculo = $this->veiculoModel->buscarPorId((int)$id);

        if (!$veiculo) {
            $_SESSION['erro'] = 'Veículo não encontrado.';
            header('Location: ' . BASE_URL . '/veiculos');
            exit;
        }

        $ok = $this->veiculoModel->excluir((int)$id);

        if ($ok) {
            $_SESSION['sucesso'] = 'Veículo excluído com sucesso.';
        } else {
            $_SESSION['erro'] = 'Erro ao excluir veículo. Ele pode estar vinculado a um agendamento existente.';
        }

        header('Location: ' . BASE_URL . '/veiculos');
        exit;
    }
}