<?php

class Unidade extends Model
{
    public function listarTudo()
    {
        $sql = "
            SELECT 
                u.*,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                e.cnpj AS empresa_cnpj
            FROM unidades u
            LEFT JOIN empresas e ON e.id = u.empresa_id
            ORDER BY u.nome ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAtivas()
    {
        $sql = "
            SELECT 
                u.*,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                e.cnpj AS empresa_cnpj
            FROM unidades u
            LEFT JOIN empresas e ON e.id = u.empresa_id
            WHERE u.ativo = 1
            ORDER BY u.nome ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id)
    {
        $sql = "
            SELECT 
                u.*,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                e.cnpj AS empresa_cnpj
            FROM unidades u
            LEFT JOIN empresas e ON e.id = u.empresa_id
            WHERE u.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorCodigo(string $codigo)
    {
        $sql = "
            SELECT *
            FROM unidades
            WHERE codigo = :codigo
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':codigo' => $codigo
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorCnpj(string $cnpj)
    {
        $sql = "
            SELECT *
            FROM unidades
            WHERE cnpj = :cnpj
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cnpj' => $cnpj
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados)
    {
        $sql = "
            INSERT INTO unidades (
                empresa_id,
                codigo,
                codigo_externo,
                nome,
                nome_fantasia,
                cnpj,
                inscricao_estadual,
                cnae,
                descricao_cnae,
                grau_risco,
                quantidade_funcionarios,
                endereco,
                logradouro,
                numero,
                complemento,
                bairro,
                cidade,
                estado,
                cep,
                telefone,
                contato_responsavel,
                email,
                responsavel,
                cargo_responsavel,
                tecnico_responsavel,
                supervisor_responsavel,
                periodicidade_visitas,
                observacoes,
                ativo
            ) VALUES (
                :empresa_id,
                :codigo,
                :codigo_externo,
                :nome,
                :nome_fantasia,
                :cnpj,
                :inscricao_estadual,
                :cnae,
                :descricao_cnae,
                :grau_risco,
                :quantidade_funcionarios,
                :endereco,
                :logradouro,
                :numero,
                :complemento,
                :bairro,
                :cidade,
                :estado,
                :cep,
                :telefone,
                :contato_responsavel,
                :email,
                :responsavel,
                :cargo_responsavel,
                :tecnico_responsavel,
                :supervisor_responsavel,
                :periodicidade_visitas,
                :observacoes,
                :ativo
            )
        ";

        $stmt = $this->db->prepare($sql);

        $sucesso = $stmt->execute($this->mapearParametros($dados));

        return $sucesso ? $this->db->lastInsertId() : false;
    }

    public function atualizar(int $id, array $dados)
    {
        $sql = "
            UPDATE unidades SET
                empresa_id = :empresa_id,
                codigo = :codigo,
                codigo_externo = :codigo_externo,
                nome = :nome,
                nome_fantasia = :nome_fantasia,
                cnpj = :cnpj,
                inscricao_estadual = :inscricao_estadual,
                cnae = :cnae,
                descricao_cnae = :descricao_cnae,
                grau_risco = :grau_risco,
                quantidade_funcionarios = :quantidade_funcionarios,
                endereco = :endereco,
                logradouro = :logradouro,
                numero = :numero,
                complemento = :complemento,
                bairro = :bairro,
                cidade = :cidade,
                estado = :estado,
                cep = :cep,
                telefone = :telefone,
                contato_responsavel = :contato_responsavel,
                email = :email,
                responsavel = :responsavel,
                cargo_responsavel = :cargo_responsavel,
                tecnico_responsavel = :tecnico_responsavel,
                supervisor_responsavel = :supervisor_responsavel,
                periodicidade_visitas = :periodicidade_visitas,
                observacoes = :observacoes,
                ativo = :ativo
            WHERE id = :id
        ";

        $params = $this->mapearParametros($dados);
        $params[':id'] = $id;

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    private function mapearParametros(array $dados): array
    {
        return [
            ':empresa_id' => $dados['empresa_id'] ?? null,
            ':codigo' => $dados['codigo'] ?? null,
            ':codigo_externo' => $dados['codigo_externo'] ?? null,
            ':nome' => $dados['nome'],
            ':nome_fantasia' => $dados['nome_fantasia'] ?? null,
            ':cnpj' => $dados['cnpj'] ?? null,
            ':inscricao_estadual' => $dados['inscricao_estadual'] ?? null,
            ':cnae' => $dados['cnae'] ?? null,
            ':descricao_cnae' => $dados['descricao_cnae'] ?? null,
            ':grau_risco' => $dados['grau_risco'] ?? null,
            ':quantidade_funcionarios' => $dados['quantidade_funcionarios'] ?? null,
            ':endereco' => $dados['endereco'] ?? null,
            ':logradouro' => $dados['logradouro'] ?? null,
            ':numero' => $dados['numero'] ?? null,
            ':complemento' => $dados['complemento'] ?? null,
            ':bairro' => $dados['bairro'] ?? null,
            ':cidade' => $dados['cidade'] ?? null,
            ':estado' => $dados['estado'] ?? null,
            ':cep' => $dados['cep'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':contato_responsavel' => $dados['contato_responsavel'] ?? null,
            ':email' => $dados['email'] ?? null,
            ':responsavel' => $dados['responsavel'] ?? null,
            ':cargo_responsavel' => $dados['cargo_responsavel'] ?? null,
            ':tecnico_responsavel' => $dados['tecnico_responsavel'] ?? null,
            ':supervisor_responsavel' => $dados['supervisor_responsavel'] ?? null,
            ':periodicidade_visitas' => $dados['periodicidade_visitas'] ?? null,
            ':observacoes' => $dados['observacoes'] ?? null,
            ':ativo' => $dados['ativo'] ?? 1
        ];
    }

    public function desativar(int $id)
    {
        $sql = "
            UPDATE unidades
            SET ativo = 0
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}