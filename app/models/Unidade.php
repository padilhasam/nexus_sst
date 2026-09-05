<?php

class Unidade extends Model
{
    public function listarTudo(): array
    {
        return $this->query("
            SELECT
                u.*,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                e.cnpj AS empresa_cnpj,
                (
                    SELECT COUNT(*)
                    FROM hierarquias h
                    WHERE h.unidade_id = u.id
                ) AS total_hierarquias
            FROM unidades u
            INNER JOIN empresas e ON e.id = u.empresa_id
            ORDER BY u.ativo DESC, COALESCE(e.nome_fantasia, e.razao_social), u.nome
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAtivas(): array
    {
        return $this->query("
            SELECT
                u.*,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                e.cnpj AS empresa_cnpj
            FROM unidades u
            INNER JOIN empresas e ON e.id = u.empresa_id
            WHERE u.ativo = 1
              AND e.ativo = 1
            ORDER BY COALESCE(e.nome_fantasia, e.razao_social), u.nome
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorEmpresa(int $empresaId, bool $apenasAtivas = true): array
    {
        $sql = "
            SELECT u.*
            FROM unidades u
            WHERE u.empresa_id = :empresa_id
        ";
        if ($apenasAtivas) {
            $sql .= ' AND u.ativo = 1';
        }
        $sql .= ' ORDER BY u.nome';

        return $this->query($sql, [':empresa_id' => $empresaId])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array
    {
        $registro = $this->query("
            SELECT
                u.*,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                e.cnpj AS empresa_cnpj
            FROM unidades u
            INNER JOIN empresas e ON e.id = u.empresa_id
            WHERE u.id = :id
            LIMIT 1
        ", [':id' => $id])->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    public function buscarPorCodigo(string $codigo): ?array
    {
        $registro = $this->query(
            'SELECT * FROM unidades WHERE codigo = :codigo LIMIT 1',
            [':codigo' => trim($codigo)]
        )->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    public function buscarPorCnpj(string $cnpj): ?array
    {
        $registro = $this->query(
            'SELECT * FROM unidades WHERE cnpj = :cnpj LIMIT 1',
            [':cnpj' => trim($cnpj)]
        )->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    public function pertenceEmpresa(int $unidadeId, int $empresaId, bool $exigirAtiva = false): bool
    {
        $sql = '
            SELECT COUNT(*)
            FROM unidades
            WHERE id = :unidade_id
              AND empresa_id = :empresa_id
        ';
        if ($exigirAtiva) {
            $sql .= ' AND ativo = 1';
        }

        return (int)$this->query($sql, [
            ':unidade_id' => $unidadeId,
            ':empresa_id' => $empresaId,
        ])->fetchColumn() > 0;
    }

    public function salvar(array $dados): int
    {
        $this->validarDados($dados);

        $stmt = $this->db->prepare("
            INSERT INTO unidades (
                empresa_id, codigo, codigo_externo, nome, razao_social,
                nome_fantasia, cnpj, inscricao_estadual, cnae, descricao_cnae,
                grau_risco, quantidade_funcionarios, endereco, logradouro,
                numero, complemento, bairro, cidade, estado, cep, telefone,
                contato_responsavel, email, responsavel, cargo_responsavel,
                tecnico_responsavel, supervisor_responsavel,
                periodicidade_visitas, observacoes, ativo
            ) VALUES (
                :empresa_id, :codigo, :codigo_externo, :nome, :razao_social,
                :nome_fantasia, :cnpj, :inscricao_estadual, :cnae, :descricao_cnae,
                :grau_risco, :quantidade_funcionarios, :endereco, :logradouro,
                :numero, :complemento, :bairro, :cidade, :estado, :cep, :telefone,
                :contato_responsavel, :email, :responsavel, :cargo_responsavel,
                :tecnico_responsavel, :supervisor_responsavel,
                :periodicidade_visitas, :observacoes, :ativo
            )
        ");
        $stmt->execute($this->mapearParametros($dados));

        return (int)$this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $atual = $this->buscarPorId($id);
        if (!$atual) {
            throw new RuntimeException('Unidade não encontrada.');
        }
        $this->validarDados($dados);

        $novaEmpresaId = (int)($dados['empresa_id'] ?? 0);
        if ((int)$atual['empresa_id'] !== $novaEmpresaId) {
            $vinculos = $this->contarVinculos($id);
            if (array_sum($vinculos) > 0) {
                throw new RuntimeException(sprintf(
                    'A empresa da unidade não pode ser alterada porque existem vínculos: %d hierarquia(s), %d funcionário(s), %d GHE(s), %d check-list(s), %d visita(s) e %d agendamento(s).',
                    $vinculos['hierarquias'],
                    $vinculos['funcionarios'],
                    $vinculos['ghes'],
                    $vinculos['checklists'],
                    $vinculos['visitas'],
                    $vinculos['agendas']
                ));
            }
        }

        $params = $this->mapearParametros($dados);
        $params[':id'] = $id;

        return $this->query("
            UPDATE unidades SET
                empresa_id = :empresa_id,
                codigo = :codigo,
                codigo_externo = :codigo_externo,
                nome = :nome,
                razao_social = :razao_social,
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
        ", $params)->rowCount() >= 0;
    }

    public function desativar(int $id): bool
    {
        $stmt = $this->query('UPDATE unidades SET ativo = 0 WHERE id = :id AND ativo = 1', [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function contarVinculos(int $id): array
    {
        $consultas = [
            'hierarquias' => 'SELECT COUNT(*) FROM hierarquias WHERE unidade_id = :id',
            'funcionarios' => 'SELECT COUNT(*) FROM funcionarios WHERE unidade_id = :id',
            'ghes' => 'SELECT COUNT(*) FROM ghes WHERE unidade_id = :id',
            'checklists' => 'SELECT COUNT(*) FROM checklists_visita WHERE unidade_id = :id',
            'visitas' => 'SELECT COUNT(*) FROM visitas_tecnicas WHERE unidade_id = :id',
            'agendas' => 'SELECT COUNT(*) FROM agendas WHERE unidade_id = :id',
        ];

        $resultado = [];
        foreach ($consultas as $nome => $sql) {
            $resultado[$nome] = (int)$this->query($sql, [':id' => $id])->fetchColumn();
        }
        return $resultado;
    }

    private function validarDados(array $dados): void
    {
        if ((int)($dados['empresa_id'] ?? 0) <= 0) {
            throw new RuntimeException('Selecione a empresa responsável pela unidade.');
        }
        if (trim((string)($dados['nome'] ?? '')) === '') {
            throw new RuntimeException('Informe o nome da unidade.');
        }
    }

    private function mapearParametros(array $dados): array
    {
        $nome = trim((string)$dados['nome']);
        $logradouro = $this->textoOuNull($dados['logradouro'] ?? null);
        $endereco = $this->textoOuNull($dados['endereco'] ?? null);
        if ($endereco === null && $logradouro !== null) {
            $endereco = trim(implode(', ', array_filter([
                $logradouro,
                $this->textoOuNull($dados['numero'] ?? null),
                $this->textoOuNull($dados['bairro'] ?? null),
            ])));
        }

        return [
            ':empresa_id' => (int)$dados['empresa_id'],
            ':codigo' => $this->textoOuNull($dados['codigo'] ?? null),
            ':codigo_externo' => $this->textoOuNull($dados['codigo_externo'] ?? null),
            ':nome' => $nome,
            ':razao_social' => $this->textoOuNull($dados['razao_social'] ?? null) ?? $nome,
            ':nome_fantasia' => $this->textoOuNull($dados['nome_fantasia'] ?? null),
            ':cnpj' => $this->textoOuNull($dados['cnpj'] ?? null),
            ':inscricao_estadual' => $this->textoOuNull($dados['inscricao_estadual'] ?? null),
            ':cnae' => $this->textoOuNull($dados['cnae'] ?? null),
            ':descricao_cnae' => $this->textoOuNull($dados['descricao_cnae'] ?? null),
            ':grau_risco' => $this->textoOuNull($dados['grau_risco'] ?? null),
            ':quantidade_funcionarios' => ($dados['quantidade_funcionarios'] ?? '') !== ''
                ? (int)$dados['quantidade_funcionarios'] : null,
            ':endereco' => $endereco,
            ':logradouro' => $logradouro,
            ':numero' => $this->textoOuNull($dados['numero'] ?? null),
            ':complemento' => $this->textoOuNull($dados['complemento'] ?? null),
            ':bairro' => $this->textoOuNull($dados['bairro'] ?? null),
            ':cidade' => $this->textoOuNull($dados['cidade'] ?? null),
            ':estado' => $this->textoOuNull($dados['estado'] ?? null),
            ':cep' => $this->textoOuNull($dados['cep'] ?? null),
            ':telefone' => $this->textoOuNull($dados['telefone'] ?? null),
            ':contato_responsavel' => $this->textoOuNull($dados['contato_responsavel'] ?? null),
            ':email' => $this->textoOuNull($dados['email'] ?? null),
            ':responsavel' => $this->textoOuNull($dados['responsavel'] ?? null),
            ':cargo_responsavel' => $this->textoOuNull($dados['cargo_responsavel'] ?? null),
            ':tecnico_responsavel' => $this->textoOuNull($dados['tecnico_responsavel'] ?? null),
            ':supervisor_responsavel' => $this->textoOuNull($dados['supervisor_responsavel'] ?? null),
            ':periodicidade_visitas' => $this->textoOuNull($dados['periodicidade_visitas'] ?? null),
            ':observacoes' => $this->textoOuNull($dados['observacoes'] ?? null),
            ':ativo' => !empty($dados['ativo']) ? 1 : 0,
        ];
    }

    private function textoOuNull(mixed $valor): ?string
    {
        $valor = trim((string)$valor);
        return $valor !== '' ? $valor : null;
    }
}
