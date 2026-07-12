<?php

class Login extends Model
{
    public function autenticar(string $email, string $senha)
    {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => $email
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return false;
        }

        if (!password_verify($senha, $usuario['senha'])) {
            return false;
        }

        return $usuario;
    }

    public function registrarUltimoAcesso(int $usuarioId): bool
    {
        $sql = "UPDATE usuarios 
                SET ultimo_acesso = NOW() 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $usuarioId
        ]);
    }
}