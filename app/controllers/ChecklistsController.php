<?php

class ChecklistsController extends AuthController
{
    public function iniciar($visitaId)
    {
        require_once __DIR__ . '/../models/ChecklistVisita.php';

        $usuarioId = $_SESSION['usuario_id'] ?? null;

        if (!$usuarioId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        try {
            $model = new ChecklistVisita();
            $checklistId = $model->iniciarPorVisita($visitaId, $usuarioId);

            header('Location: ' . BASE_URL . '/checklists/visualizar/' . $checklistId);
            exit;

        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/visitas');
            exit;
        }
    }

    public function visualizar($checklistId)
    {
        require_once __DIR__ . '/../models/ChecklistVisita.php';

        $model = new ChecklistVisita();
        $checklist = $model->buscarDadosTela($checklistId);

        if (!$checklist) {
            $_SESSION['erro'] = 'Checklist não encontrado.';
            header('Location: ' . BASE_URL . '/visitas');
            exit;
        }

        require_once __DIR__ . '/../views/checklists/visualizar.php';
    }
}