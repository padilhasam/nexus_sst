<?php

require_once __DIR__ . '/../../core/Database.php';

class Empresa
{
    private PDO $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function listar(): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM empresas
            ORDER BY COALESCE(nome_fantasia, razao_social) ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAtivas(): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM empresas
            WHERE ativo = 1
            ORDER BY COALESCE(nome_fantasia, razao_social) ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM empresas
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM empresas
            WHERE codigo = :codigo
            LIMIT 1
        ");

        $stmt->bindValue(':codigo', $codigo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorCnpj(string $cnpj): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM empresas
            WHERE cnpj = :cnpj
            LIMIT 1
        ");

        $stmt->bindValue(':cnpj', $cnpj);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function salvar(array $dados)
    {
        $sql = "
            INSERT INTO empresas (
                codigo,
                codigo_externo,
                razao_social,
                nome_fantasia,
                cnpj,
                inscricao_estadual,
                cnae,
                descricao_cnae,
                grau_risco,
                quantidade_funcionarios,
                telefone,
                email,
                responsavel,
                cargo_responsavel,
                contato_responsavel,
                endereco,
                cidade,
                estado,
                cep,
                logradouro,
                numero,
                complemento,
                bairro,
                tecnico_responsavel,
                supervisor_responsavel,
                periodicidade_visitas,
                observacoes,
                ativo
            ) VALUES (
                :codigo,
                :codigo_externo,
                :razao_social,
                :nome_fantasia,
                :cnpj,
                :inscricao_estadual,
                :cnae,
                :descricao_cnae,
                :grau_risco,
                :quantidade_funcionarios,
                :telefone,
                :email,
                :responsavel,
                :cargo_responsavel,
                :contato_responsavel,
                :endereco,
                :cidade,
                :estado,
                :cep,
                :logradouro,
                :numero,
                :complemento,
                :bairro,
                :tecnico_responsavel,
                :supervisor_responsavel,
                :periodicidade_visitas,
                :observacoes,
                :ativo
            )
        ";

        $stmt = $this->db->prepare($sql);
        $this->bindDados($stmt, $dados);

        if ($stmt->execute()) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = "
            UPDATE empresas SET
                codigo = :codigo,
                codigo_externo = :codigo_externo,
                razao_social = :razao_social,
                nome_fantasia = :nome_fantasia,
                cnpj = :cnpj,
                inscricao_estadual = :inscricao_estadual,
                cnae = :cnae,
                descricao_cnae = :descricao_cnae,
                grau_risco = :grau_risco,
                quantidade_funcionarios = :quantidade_funcionarios,
                telefone = :telefone,
                email = :email,
                responsavel = :responsavel,
                cargo_responsavel = :cargo_responsavel,
                contato_responsavel = :contato_responsavel,
                endereco = :endereco,
                cidade = :cidade,
                estado = :estado,
                cep = :cep,
                logradouro = :logradouro,
                numero = :numero,
                complemento = :complemento,
                bairro = :bairro,
                tecnico_responsavel = :tecnico_responsavel,
                supervisor_responsavel = :supervisor_responsavel,
                periodicidade_visitas = :periodicidade_visitas,
                observacoes = :observacoes,
                ativo = :ativo
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);
        $this->bindDados($stmt, $dados);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    private function bindDados(PDOStatement $stmt, array $dados): void
    {
        $stmt->bindValue(':codigo', $dados['codigo'] ?? null);
        $stmt->bindValue(':codigo_externo', $dados['codigo_externo'] ?? null);
        $stmt->bindValue(':razao_social', $dados['razao_social']);
        $stmt->bindValue(':nome_fantasia', $dados['nome_fantasia'] ?? null);
        $stmt->bindValue(':cnpj', $dados['cnpj'] ?? null);
        $stmt->bindValue(':inscricao_estadual', $dados['inscricao_estadual'] ?? null);
        $stmt->bindValue(':cnae', $dados['cnae'] ?? null);
        $stmt->bindValue(':descricao_cnae', $dados['descricao_cnae'] ?? null);
        $stmt->bindValue(':grau_risco', $dados['grau_risco'] ?? null);

        if (isset($dados['quantidade_funcionarios']) && $dados['quantidade_funcionarios'] !== null) {
            $stmt->bindValue(':quantidade_funcionarios', (int) $dados['quantidade_funcionarios'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':quantidade_funcionarios', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':telefone', $dados['telefone'] ?? null);
        $stmt->bindValue(':email', $dados['email'] ?? null);
        $stmt->bindValue(':responsavel', $dados['responsavel'] ?? null);
        $stmt->bindValue(':cargo_responsavel', $dados['cargo_responsavel'] ?? null);
        $stmt->bindValue(':contato_responsavel', $dados['contato_responsavel'] ?? null);
        $stmt->bindValue(':endereco', $dados['endereco'] ?? null);
        $stmt->bindValue(':cidade', $dados['cidade'] ?? null);
        $stmt->bindValue(':estado', $dados['estado'] ?? null);
        $stmt->bindValue(':cep', $dados['cep'] ?? null);
        $stmt->bindValue(':logradouro', $dados['logradouro'] ?? null);
        $stmt->bindValue(':numero', $dados['numero'] ?? null);
        $stmt->bindValue(':complemento', $dados['complemento'] ?? null);
        $stmt->bindValue(':bairro', $dados['bairro'] ?? null);
        $stmt->bindValue(':tecnico_responsavel', $dados['tecnico_responsavel'] ?? null);
        $stmt->bindValue(':supervisor_responsavel', $dados['supervisor_responsavel'] ?? null);
        $stmt->bindValue(':periodicidade_visitas', $dados['periodicidade_visitas'] ?? null);
        $stmt->bindValue(':observacoes', $dados['observacoes'] ?? null);
        $stmt->bindValue(':ativo', $dados['ativo'] ?? 1, PDO::PARAM_INT);
    }

    public function desativar(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE empresas
            SET ativo = 0
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}