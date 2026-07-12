<?php

class Dashboard extends Model
{
    public function contar(string $tabela): int
    {
        $tabelasPermitidas = [
            'empresas',
            'unidades',
            'setores',
            'cargos'
        ];

        if (!in_array($tabela, $tabelasPermitidas, true)) {
            return 0;
        }

        $sql = "SELECT COUNT(*) AS total FROM {$tabela}";

        $stmt = $this->db->query($sql);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($resultado['total'] ?? 0);
    }
}