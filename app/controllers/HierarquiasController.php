<?php

class HierarquiasController
{
    private Hierarquia $hierarquia;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->hierarquia = new Hierarquia();
    }

    public function index()
    {
        $empresasEstruturadas = $this->hierarquia->listarEmpresasEstruturadas();

        $total_empresas = $this->hierarquia->contarEmpresas();
        $total_setores = $this->hierarquia->contarSetores();
        $total_cargos = $this->hierarquia->contarCargos();

        require '../app/views/hierarquias/index.php';
    }

    public function criar()
    {
        require '../app/views/hierarquias/criar.php';
    }

    public function salvar()
    {
        $empresaId = $_POST['empresa_id'] ?? null;
        $unidadeId = $_POST['unidade_id'] ?? null;
        $setorId = $_POST['setor_id'] ?? null;
        $cargoId = $_POST['cargo_id'] ?? null;

        if (empty($empresaId) || empty($unidadeId) || empty($setorId) || empty($cargoId)) {
            $_SESSION['erro'] = 'Preencha todos os campos.';
            header('Location: ' . BASE_URL . '/hierarquias/criar');
            exit;
        }

        if ($this->hierarquia->existe((int)$empresaId, (int)$unidadeId, (int)$setorId, (int)$cargoId)) {
            $_SESSION['erro'] = 'Esta hierarquia já está cadastrada.';
            header('Location: ' . BASE_URL . '/hierarquias/criar');
            exit;
        }

        $this->hierarquia->salvar([
            'empresa_id' => $empresaId,
            'unidade_id' => $unidadeId,
            'setor_id' => $setorId,
            'cargo_id' => $cargoId
        ]);

        $_SESSION['sucesso'] = 'Hierarquia cadastrada com sucesso.';

        header('Location: ' . BASE_URL . '/hierarquias');
        exit;
    }

    public function editar($id)
    {
        $hierarquia = $this->hierarquia->buscarPorId((int)$id);

        if (!$hierarquia) {
            $_SESSION['erro'] = 'Hierarquia não encontrada.';
            header('Location: ' . BASE_URL . '/hierarquias');
            exit;
        }

        require '../app/views/hierarquias/editar.php';
    }

    public function atualizar($id)
    {
        $this->hierarquia->atualizar((int)$id, [
            'empresa_id' => $_POST['empresa_id'],
            'unidade_id' => $_POST['unidade_id'],
            'setor_id' => $_POST['setor_id'],
            'cargo_id' => $_POST['cargo_id']
        ]);

        $_SESSION['sucesso'] = 'Hierarquia atualizada com sucesso.';

        header('Location: ' . BASE_URL . '/hierarquias');
        exit;
    }

    public function excluir($id)
    {
        $this->hierarquia->excluir((int)$id);

        $_SESSION['sucesso'] = 'Hierarquia excluída com sucesso.';

        header('Location: ' . BASE_URL . '/hierarquias');
        exit;
    }

    public function importar()
    {
        require '../app/views/hierarquias/importar.php';
    }

    public function processarImportacao()
    {
        $_SESSION['sucesso'] = 'Importação ainda será implementada.';

        header('Location: ' . BASE_URL . '/hierarquias');
        exit;
    }

    public function estrutura($empresaId)
    {
        $empresaId = (int)$empresaId;

        $estrutura = $this->hierarquia->listarEstruturaPorEmpresa($empresaId);
        $empresa = $this->hierarquia->buscarEmpresaNaHierarquia($empresaId);

        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada na estrutura.';
            header('Location: ' . BASE_URL . '/hierarquias');
            exit;
        }

        require '../app/views/hierarquias/estrutura.php';
    }

    public function listarEmpresasEstruturadas()
    {
        $sql = "
            SELECT
                e.id,
                COALESCE(e.nome_fantasia, e.razao_social, e.nome) AS empresa_nome,
                COUNT(DISTINCT h.unidade_id) AS total_unidades,
                COUNT(DISTINCT h.setor_id) AS total_setores,
                COUNT(DISTINCT h.cargo_id) AS total_cargos,
                COUNT(h.id) AS total_hierarquias
            FROM hierarquias h
            INNER JOIN empresas e ON e.id = h.empresa_id
            GROUP BY e.id, empresa_nome
            ORDER BY empresa_nome ASC
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarEmpresaNaHierarquia(int $empresaId)
    {
        $sql = "
            SELECT
                e.id,
                COALESCE(e.nome_fantasia, e.razao_social, e.nome) AS empresa_nome
            FROM empresas e
            WHERE e.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $empresaId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarEstruturaPorEmpresa(int $empresaId)
    {
        $sql = "
            SELECT
                h.*,
                u.nome AS unidade_nome,
                s.nome AS setor_nome,
                c.nome AS cargo_nome
            FROM hierarquias h
            INNER JOIN unidades u ON u.id = h.unidade_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            WHERE h.empresa_id = :empresa_id
            ORDER BY u.nome ASC, s.nome ASC, c.nome ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':empresa_id' => $empresaId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}